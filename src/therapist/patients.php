<?php

    include_once '../util/ssl.php';
    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"]);
    
    // Gets the list of patients under the specified therapist
    $patients_list_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/therapist/' . $result->uid . '/true'));
    if (isset($patients_list_json->treatments)) {
        $patients_list = $patients_list_json->treatments;
    }
    if (isset($patients_list)) {
        $num_patients = count($patients_list);
    } else {
        $num_patients = 0;
    }
    $user_type = $result->istherapist ? "therapist" : "patient";

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

<html>
<meta charset="utf-8">
    <head>
        <title>Patient List</title>
        <link href="../css/main.css" rel="stylesheet">
    </head>

    <body>
        <?php include '../sidebar.php' ?>

        <div class="shifted">
            <h1>Patient<?php if ($num_patients != 1) { echo "s"; } ?> you are assigned to (<?php echo $num_patients ?>):</h1>
            <hr style="margin-top:-15px">
            <table class="main-table">
                <tr>
                    <th class = "first-col">S/N</th>
                    <th>Name</th>
                    <th>Contact info</th>
                    <th>D.O.B</th>
                </tr>
                <?php for ($i = 0; $i < $num_patients; $i++) { 
                    $patient_json = getJsonFromUid($patients_list[$i]->patientId);
                    $patient_name = $patient_json->firstname." ".$patient_json->lastname; ?>
                    <form method="post" action="managep.php">
                        <input name="patient_search" value="<?php echo $patients_list[$i]->patientId ?>" type="hidden">
                        <tr>
                            <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                            <td><button class="list-button"><?php echo htmlspecialchars($patient_name) ?></button></td>
                            <td><?php echo htmlspecialchars($patient_json->phone[0]) ?></td>
                            <td><?php echo htmlspecialchars($patient_json->dob) ?></td>
                        </tr>
                    </form>
                <?php } ?>
            </table>
        </div>
    </body>
</html>
