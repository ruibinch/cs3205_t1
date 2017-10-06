<?php
    session_start();
    
    $_SESSION['user_type'] = "therapist";
    
    if (isset($_POST['patient_search'])) {
        $patientId = $_POST['patient_search'];
    }
    $patient_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/'.$patientId));;

?>

<html>
<meta charset="utf-8">
    <head>
        <title>Patient Management</title>
        <link href="../css/main.css" rel="stylesheet">
    </head>

    <body>
        <?php include '../sidebar.php' ?>

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
                    <td>Date of Birth:</td>
                    <td><?php echo $patient_json->dob ?></td>
                </tr>
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

            <h3>Patient Records</h3>
            <p><i>(To be included)</i></p>
        </div>

  </body>
</html>
