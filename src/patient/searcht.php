<?php
    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
    $patient_id = $result->uid;

    $therapists_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/therapists'));
    $therapists_list = $therapists_list_json->users; 

?>

<html>
<meta charset="utf-8">

    <head>
        <title>Search Therapists</title>
        <link href="../css/main.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
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
                </tr>
                <?php for ($i = 0; $i < count($therapists_list); $i++) {
                    $therapist_info = $therapists_list[$i];
                    $therapist_name = $therapist_info->firstname . " " . $therapist_info->lastname; ?>
                    <tr>
                        <form method="post" action="managet.php">
                            <input name="therapist_search" value="<?php echo $therapist_info->uid ?>" type="hidden">
                            <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                            <td><button class="list-button"><?php echo $therapist_name ?></button></td>
                        </form>
                        <td id="treatmentReqs" style="text-align:right">
                            <button id="sendTreatmentReq"
							    value="<?php echo $therapist_info->uid ?>">Send Treatment Request</button>
                        </td>
                    </tr>
                <?php } ?>
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
                });

            });

        </script>

    </body>
</html>
