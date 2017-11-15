<?php

    include_once '../util/ssl.php';
    include_once '../util/jwt.php';
    include_once '../util/csrf.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"]);

    if (isset($_POST['recordId'])) {
        $recordId = $_POST['recordId'];
    }

    $csrf = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/csrf/".$_POST['csrf']));
    if (isset($csrf->result) || $csrf->expiry < time() || $csrf->description != "viewConsentDialog" || $csrf->uid != $result->uid) {
        Log::recordTX($jwt_result->uid, "Warning", "Invalid csrf when viewing consent dialog");
        header('HTTP/1.0 400 Bad Request.');
        die();
    }
    CSRFToken::deleteToken($_POST['csrf']);
    
    // Retrieves the user JSON object based on the uid
    function getJsonFromUid($uid) {
        if (strpos($uid, '/') !== false) {
            Log::recordTX($uid, "Error", "Unrecognised uid: " . $uid);
            header('HTTP/1.0 400 Bad Request.');
            die();
        }
        $user_json_tmp = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/uid/' . $uid));
        return $user_json_tmp;
    }

?>

<table width="100%">
    <tr>
        <th style="text-align:center">Name of Therapist</th>
        <th style="text-align:center">Permission</th>
    </tr>
    <?php 
        $consents_list_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/record/' . $recordId));
        if (isset($consents_list_json->consents)) {
            $consents_list = $consents_list_json->consents;
            for ($i = 0; $i < count($consents_list); $i++) {
                $consent = $consents_list[$i];
                $therapist_name = $consent->firstname . " " . $consent->lastname;

                $checked_status = "";
                if ($consent->status) {
                    $checked_status = "checked";
                }
                
                echo "<tr>";
                echo "<td style='text-align:center'>" . htmlspecialchars($therapist_name) . "</td>";
                echo "<td style='text-align:center'><input type='checkbox' class='setconsent' value='" . $consent->consentId . "' " . $checked_status . "/></td>";
                echo "</tr>";
            }
        }
    ?>
</table>

