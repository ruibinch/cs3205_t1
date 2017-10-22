<?php
    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
    $patient_id = $result->uid;

    $therapists_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/therapists'));
    $therapists_list = $therapists_list_json->users;

    $therapists_assigned_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/patient/' . $patient_id . '/true'));
    $therapists_assigned_ids = array();
    if (isset($therapists_assigned_json->treatments)) {
        $therapists_assigned = $therapists_assigned_json->treatments;
        for ($i = 0; $i < count($therapists_assigned); $i++) {
            array_push($therapists_assigned_ids, $therapists_assigned[$i]->therapistId);
        }
    }

    $therapists_pending_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/patient/' . $patient_id . '/false'));
    $therapists_pending_ids = array();
    if (isset($therapists_pending_json->treatments)) {
        $therapists_pending = $therapists_pending_json->treatments;
        for ($j = 0; $j < count($therapists_pending); $j++) {
            array_push($therapists_pending_ids, $therapists_pending[$j]->therapistId);
        }
    }

?>

<html>
<meta charset="utf-8">

    <head>
        <title>Search Therapists</title>
        <link href="../css/main.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.2.1.min.js"
            integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
            crossorigin="anonymous"></script>
    </head>

    <body>
        <?php include '../sidebar.php' ?>
        <div class="shifted">
            <h1>Search All Therapists</h1>
            <hr style="margin-top:-15px">

            <table class="main-table">
                <tr>
                    <th class = "first-col">S/N</th>
                    <th>Name</th>
                    <th class="last-col"></th>
                    <th class="last-col" style="text-align:right">Action</th>
                </tr>
                <?php for ($i = 0; $i < count($therapists_list); $i++) {
                    $therapist_info = $therapists_list[$i];
                    $therapist_name = $therapist_info->firstname . " " . $therapist_info->lastname;
                    if ($therapists_list[$i]->uid !== $patient_id) { // check that therapist is not the patient himself/herself ?>
                        <tr>
                            <form method="post" action="managet.php">
                                <input name="therapist_search" value="<?php echo $therapist_info->uid ?>" type="hidden">
                                <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                                <td><button class="list-button"><?php echo $therapist_name ?></button></td>
                            </form>
                            <?php if (in_array($therapists_list[$i]->uid, $therapists_assigned_ids)) { ?>
                                <td style="text-align:right">Therapist already assigned</td>
                                <td style="text-align:right">
                                    <button id="sendTreatmentReq"
                                        value="<?php echo $therapist_info->uid ?>" disabled>Send Treatment Request</button>
                                </td>
                            <?php } else if (in_array($therapists_list[$i]->uid, $therapists_pending_ids)) { ?> 
                                <td style="text-align:right">Treatment request pending</td>
                                <td style="text-align:right">
                                    <button id="sendTreatmentReq"
                                        value="<?php echo $therapist_info->uid ?>" disabled>Send Treatment Request</button>
                                </td>
                            <?php } else { ?>
                                <td></td>
                                <td style="text-align:right">
                                    <button id="sendTreatmentReq"
                                        value="<?php echo $therapist_info->uid ?>">Send Treatment Request</button>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php }
                } ?>
            </table>
	    </div>

        <script>
            
            $(document).ready(function() {

                $(document).on('click', '#sendTreatmentReq', function(e) {
                    $.ajax({
                        type: "POST",
                        url: "../util/ajax-process.php",
                        data: { "patientId": '<?php echo $patient_id ?>', "therapistId": $(this).val() }
                    }).done(function(response) {
                        if (response == 1) {
                            alert("Treatment request sent");
                        } else {
                            alert("Error in sending treatment request");
                        }
                    });
                    location.reload();
                });

            });

        </script>

    </body>
</html>
