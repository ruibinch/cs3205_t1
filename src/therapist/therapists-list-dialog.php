<?php
    include_once '../util/ssl.php';
    include_once '../util/jwt.php';
    include_once '../util/csrf.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"]);

    $patientId = $therapistId = $rid = "";

    if (isset($_POST['patientId'])) {
        $patientId = $_POST['patientId'];
    }
    if (isset($_POST['therapistId'])) {
        $therapistId = $_POST['therapistId'];
    }
    if (isset($_POST['rid'])) {
        $rid = $_POST['rid'];
    }

    $csrf = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/csrf/".$_POST['csrf']));
    if (isset($csrf->result) || $csrf->expiry < time() || $csrf->description != "viewTherapistsListDialog" || $csrf->uid != $result->uid) {
        Log::recordTX($jwt_result->uid, "Warning", "Invalid csrf when viewing therapist list dialog");
        header('HTTP/1.0 400 Bad Request.');
        die();
    }
    CSRFToken::deleteToken($_POST['csrf']);

    if ($patientId === "0") {
        $therapist_list = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/user/therapists"))->users;
    } else {
        $therapist_list = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/treatment/patient/".$patientId."/true"))->treatments;
    }

    function getUser($uid) {
        return json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/user/uid/".$uid));
    }

    function hasConsent($uid, $rid) {
        $hasConsent = "";
        $consent_array = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/consent/user/".$uid));
        if (!isset($consent_array->result)) {
            $consent_array = $consent_array->consents;
            foreach ($consent_array AS $consent_elem) {
                if (strcmp($consent_elem->rid, $rid) == 0 && $consent_elem->status) {
                    $hasConsent = "checked";
                }
            }
        }
        return $hasConsent;
    }

?>

<table class="main-table">
    <tr>
        <th class = "first-col">S/N</th>
        <th>Therapist</th>
        <th class="last-col">Select</th>
    </tr>

    <?php
    $i = 0;
    foreach($therapist_list AS $therapist) {
        if ($patientId !== "0") {
            $therapist = getUser($therapist->therapistId);
        }
        if (strcmp($therapistId, $therapist->uid) !== 0) {
    ?>
            <tr>
                <td><?php echo $i + 1 ?></td>
                <td><?php echo htmlspecialchars($therapist->firstname." ".$therapist->lastname) ?></td>
                <td><input type="checkbox" class="selectDocumentCheckbox" value="<?php echo $therapist->uid ?>" <?php echo hasConsent($therapist->uid, $rid) ?>/></td>
            </tr>
    <?php
            $i++;
        }
    }
    ?>
</table>
