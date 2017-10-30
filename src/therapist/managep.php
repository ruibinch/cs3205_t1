<?php
    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
    $user_type = $result->istherapist ? "therapist" : "patient";
    
    if (isset($_POST['patient_search'])) {
        $patientId = $_POST['patient_search'];
    }
    $patient_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $patientId));

    // Verify that the patient is indeed assigned to the therapist
    // Serves as a check against the manipulation of the POST data from patients.php
    $patients_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/therapist/' . $result->uid . '/true'));
    $patients_ids = array();
    if (isset($patients_list_json->treatments)) {
        $patients_list = $patients_list_json->treatments;
        for ($i = 0; $i < count($patients_list); $i++) {
            array_push($patients_ids, $patients_list[$i]->patientId);
        }
    }
    
    // Gets the list of consents associated with this therapist
    $consents_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/consent/user/' . $result->uid));
    $consents_list_status = array();
    if (isset($consents_list_json->consents)) {
        $consents_list = $consents_list_json->consents;
        for ($i = 0; $i < count($consents_list); $i++) {
            $consent = $consents_list[$i];
            $consents_list_status[$consent->rid] = $consent->status;
        }
    }

    // Gets the list of records assigned to the specified patient
    $records_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/record/all/' . $patientId));
    if (isset($records_list_json->records)) {
        $records_list = $records_list_json->records;
    }
    if (isset($records_list)) {
        $num_records = count($records_list);
    } else {
        $num_records = 0;
    }


?>

<html>
<meta charset="utf-8">
    <head>
        <title>Patient Management</title>
        <link href="../css/main.css" rel="stylesheet">
    </head>

    <body>
        <?php include '../sidebar.php' ?>

        <?php if (in_array($patientId, $patients_ids)) { ?>
            <div class="shifted">
                <h1><?php echo $patient_json->firstname." ".$patient_json->lastname ?></h1>
                <hr style="margin-top:-15px">
                <table width="70%">
                    <th>
                        <td width="60%"></td>
                        <td></td>
                    </th>
                    <tr>
                        <td>First Name:</td>
                        <td><?php echo $patient_json->firstname ?></td>
                    </tr>
                    <tr>
                        <td>Last Name:</td>
                        <td><?php echo $patient_json->lastname ?></td>
                    </tr>
                    <tr>
                        <td>Sex:</td>
                        <td><?php if ($patient_json->sex === "M") { ?>Male<?php } else { ?>Female<?php } ?></td>
                    </tr>
                    <tr>
                        <td>NRIC:</td>
                        <td><?php echo $patient_json->nric ?></td>
                    </tr>
                    <tr>
                        <td>Ethnicity:</td>
                        <td><?php if (isset($patient_json->ethnicity)) { echo $patient_json->ethnicity; } ?></td>
                    </tr>
                    <tr>
                        <td>Date of Birth:</td>
                        <td><?php echo $patient_json->dob ?></td>
                    </tr>
                    <tr>
                        <td>Blood Type:</td>
                        <td><?php echo $patient_json->bloodtype ?></td>
                    </tr>
                    <tr>
                        <td>Drug Allergy:</td>
                        <td><?php if ($patient_json->drugAllergy) {?>Yes<?php } else {?>No<?php } ?></td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td>Primary Contact Number:</td>
                        <td><?php echo $patient_json->phone[0] ?></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Secondary Contact Numbers:</td>
                        <td>
                            <?php if (isset($patient_json->phone[1])) { 
                                echo $patient_json->phone[1]; ?><br>
                                <?php if (isset($patient_json->phone[2])) {
                                    echo $patient_json->phone[2];
                                }
                            } else {
                                echo "-";
                            } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Primary Address:</td>
                        <td><?php echo $patient_json->address[0].", ".$patient_json->zipcode[0] ?></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Secondary Addresses:</td>
                        <td>
                            <?php if (isset($patient_json->address[1])) { 
                                echo $patient_json->address[1].", ".$patient_json->zipcode[1]; ?><br>
                                <?php if (isset($patient_json->address[2])) {
                                    echo $patient_json->address[2].", ".$patient_json->zipcode[2];
                                }
                            } else {
                                echo "-";
                            } ?>
                        </td>
                    </tr>
                </table>

                <h2 style="margin-top:50px">Patient Records</h2>
                <table class="main-table">
                    <tr>
                        <th class = "first-col">S/N</th>
                        <th>Last Modified</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th class="last-col">Actions</th>
                    </tr>
                    <?php for ($i = 0; $i < $num_records; $i++) {
                        $record = $records_list[$i]; ?>
                        <tr>
                            <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                            <td><?php echo $record->modifieddate ?></button></td>
                            <td><?php echo $record->type ?></td>
                            <td><?php echo $record->title ?></td>
                            <td class="last-col">
                                <?php
                                    if ($consents_list_status[$record->rid]) {
                                        echo "<input type='button' value='Details'"; // TODO - include link
                                    } else {
                                        echo "<input type='button' value='Details' disabled";
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        <?php } ?>

  </body>
</html>

