<?php

    include_once '../util/ssl.php';
    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");

    // Gets the list of therapists assigned to the specified patient
    $therapists_list_json = json_decode(ssl::get_content('http://172.25.76.76/api/team1/treatment/patient/' . $result->uid . '/true'));
    if (isset($therapists_list_json->treatments)) {
        $therapists_list = $therapists_list_json->treatments;
    }
    if (isset($therapists_list)) {
        $num_therapists = count($therapists_list);
    } else {
        $num_therapists = 0;
    }
    $user_type = $result->istherapist ? "therapist" : "patient";

    // Retrieves the user JSON object based on the uid
    function getJsonFromUid($uid) {
        $user_json_tmp = json_decode(ssl::get_content('http://172.25.76.76/api/team1/user/uid/' . $uid));
        return $user_json_tmp;
    }
?>

<html>
<meta charset="utf-8">

    <head>
        <title>Therapist List</title>
        <link href="../css/main.css" rel="stylesheet">
        <link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.2.1.min.js"
            integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
            crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"
            integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30="
            crossorigin="anonymous"></script>
    </head>

    <body>
        <?php include '../sidebar.php' ?>
        <div class="shifted">
            <h1>Therapist<?php if ($num_therapists != 1) { echo "s"; } ?> you are assigned to (<?php echo $num_therapists ?>):</h1>
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
                            <td><button class="list-button"><?php echo htmlspecialchars($therapist_name) ?></button></td>
                            <td><?php echo htmlspecialchars($therapist_json->phone[0]) ?></td>
                        </form>
                        <td class="last-col">
                            <button id="removeTherapist" value="<?php echo $therapists_list[$i]->id ?>">Remove</button>
                        </td>
                    </tr>
                <?php } ?>
            </table>

            <button id="searchAllTherapists" class="login-btn" style="margin-top:80px">Search All Therapists</button>
	    
        </div>

        <div id="acknowledgementDialog"><p id="ackMessage" style="text-align:center"></p></div>
        <style> .jqueryDialogNoTitle .ui-dialog-titlebar { display: none; } </style>

        <script>

            $(document).ready(function() {

                $('#acknowledgementDialog').dialog({
                    dialogClass: 'jqueryDialogNoTitle',
                    width: 300,
                    height: 80,
                    autoOpen: false,
                    resizable: false,
                    draggable: false,
                    position: { my: "center", at: "top" },
                    show: {
                        effect: 'fade',
                        duration: 300
                    },
                    hide: {
                        effect: 'fade',
                        delay: 800
                    },
                    open: function() {
                        $('#ackMessage').text($(this).data('message'));
                        $(this).dialog('close');
                    },
                    close: function() {
                        location.reload();
                    }
                });

                $(document).on('click', '#removeTherapist', function() {
                    if (confirm("Are you sure you want to remove this therapist?")) {
                        $.ajax({
                            type: "POST",
                            url: "../ajax-process.php",
                            data: { "removeTreatmentId": $(this).val() }
                        }).done(function(response) {
                            if (response == 1) {
                                $('#acknowledgementDialog')
                                    .data('message', "Therapist removed")
                                    .dialog('open');
                            } else {
                                $('#acknowledgementDialog')
                                    .data('message', "Error in removing therapist")
                                    .dialog('open');
                            }
                        });
                    }
                });

                $('#searchAllTherapists').click(function() {
                    location.href='searcht.php';
                });

            });
            
        </script>

    </body>
</html>
