<?php
    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
    $user_type = $result->istherapist ? "therapist" : "patient";

    if (isset($_POST["therapist_search"])) {
        $therapistId = $_POST["therapist_search"];
    }
    $therapist_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/public/' . $therapistId));;
    
    // Verify that the therapist is indeed assigned to the patient
    // Serves as a check against the manipulation of the POST data from therapists.php
    $therapists_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/patient/' . $result->uid . '/true'));
    $therapists_ids = array();
    if (isset($therapists_list_json->treatments)) {
        $therapists_list = $therapists_list_json->treatments;
        for ($i = 0; $i < count($therapists_list); $i++) {
            array_push($therapists_ids, $therapists_list[$i]->therapistId);
        }
    }
?>

<html>
<meta charset="utf-8">
    <head>
        <title>Therapist Management</title>
        <link href="../css/main.css" rel="stylesheet">
    </head>

    <body>
        <?php include '../sidebar.php' ?>

        <?php if (in_array($therapistId, $therapists_ids)) { ?>
            <div class="shifted">
                <h1><?php echo $therapist_json->firstname . " " . $therapist_json->lastname ?></h1>
                <hr style="margin-top:-15px">
                <table width="70%">
                    <th>
                        <td width="60%"></td>
                        <td></td>
                    </th>
                    <tr>
                        <td>First Name:</td>
                        <td><?php echo $therapist_json->firstname ?></td>
                    </tr>
                    <tr>
                        <td>Last Name:</td>
                        <td><?php echo $therapist_json->lastname ?></td>
                    </tr>
                    <tr>
                        <td>Gender:</td>
                        <td><?php if ($therapist_json->gender === "M") { ?>Male<?php } else { ?>Female<?php } ?></td>
                    </tr>
                    <tr>
                        <td>Contact Number:</td>
                        <td><?php echo $therapist_json->phone ?></td>
                    </tr>
                </table>

                <h2 style="margin-top:50px">Associated Documents</h2>
                <i>TBD</i>
            </div>
        <?php } ?>

    </body>
</html>
