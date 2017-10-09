<?php
    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
    
    // Gets the list of patients under the specified therapist
    $patients_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/therapist/' . $result->uid . '/true'));
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
        $user_json_tmp = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $uid));
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
            <h1>Patient<?php if ($num_patients != 1) { ?>s<?php } ?> you are assigned to (<?php echo $num_patients ?>):</h1>
            <hr style="margin-top:-15px">
            <p>Search by name: <input type="text" id="searchbox" name="searchbox" style="width:30%"/></p> <!-- TODO - search function -->
            <table class="main-table">
                <tr>
                    <th class = "first-col">S/N</th>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th>D.O.B</th>
                </tr>
                <?php for ($i = 0; $i < $num_patients; $i++) { 
                    $patient_json = getJsonFromUid($patients_list[$i]->patientId);
                    $patient_name = $patient_json->firstname." ".$patient_json->lastname; ?>
                    <form method="post" action="managep.php">
                        <input name="patient_search" value="<?php echo $patients_list[$i]->patientId ?>" type="hidden">
                        <tr>
                            <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                            <td><button class="list-button"><?php echo $patient_name ?></button></td>
                            <td><?php echo $patient_json->phone[0] ?></td>
                            <td><?php echo $patient_json->dob ?></td>
                        </tr>
                    </form>
                <?php } ?>
            </table>
        </div>
    </body>
</html>
