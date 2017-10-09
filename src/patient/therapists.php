<?php

    session_start();
    $_SESSION['user_type'] = "patient";

    $therapists_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/patient/1/true'));
    if (isset($therapists_list_json->treatments)) {
        $therapists_list = $therapists_list_json->treatments;
    }
    if (isset($therapists_list)) {
        $num_therapists = count($therapists_list);
    } else {
        $num_therapists = 0;
    }

    // Retrieves the user JSON object based on the uid
    function getJsonFromUid($uid) {
        $user_json_tmp = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/'.$uid));
        return $user_json_tmp;
    }
?>

<html>
<meta charset="utf-8">

    <head>
        <title>Therapist List</title>
        <link href="../css/main.css" rel="stylesheet">
        <script	src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    </head>

    <body>
        <?php include '../sidebar.php' ?>
        <div class="shifted">
            <h1>Therapist<?php if ($num_therapists != 1) { ?>s<?php } ?> you are assigned to (<?php echo $num_therapists ?>):</h1>
            <hr style="margin-top:-15px">

            <table class="main-table">
                <tr>
                    <th class = "first-col">S/N</th>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th class="last-col">Actions</th>
                </tr>
                <?php for ($i = 0; $i < $num_therapists; $i++) {
                    $therapist_id = $therapists_list[$i]->therapistId;
                    $therapist_json = getJsonFromUid($therapist_id);
                    $therapist_name = $therapist_json->firstname . " " . $therapist_json->lastname; ?>
                    <tr>
                        <form method="post" action="managet.php">
                            <input name="therapist_search" value="<?php echo $therapist_id ?>" type="hidden">
                            <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                            <td><button class="list-button"><?php echo $therapist_name ?></button></td>
                            <td><?php echo $therapist_json->phone[0] ?></td>
                        </form>
                        <td class="last-col">
                            <button id="removeTherapist" value="<?php echo $therapists_list[$i]->id ?>">Remove</button>
                        </td>
                    </tr>
                <?php } ?>
            </table>

            <button id="searchAllTherapists" class="login-btn" style="margin-top:80px">Search All Therapists</button>

	    </div>

        <script>

            $(document).ready(function() {

                $('#removeTherapist').click(function() {
                    $.ajax({
                        type: "POST",
                        url: "../util/ajax-process.php",
                        data: { "removeTreatmentId": $(this).val() }
                    }).done(function(response) {
                        if (response == 1) {
                            alert("Therapist removed");
                        } else {
                            alert("Error in removing therapist");
                        }
                        location.reload();
                    });
                });

                $('#searchAllTherapists').click(function() {
                    location.href='searcht.php';
                });

            });
            
        </script>

    </body>
</html>
