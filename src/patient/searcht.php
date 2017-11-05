<?php

    include_once '../util/ssl.php';
    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"]);
    $patient_id = $result->uid;

    $therapists_list_json = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/user/therapists'));
    $therapists_list = $therapists_list_json->users;
    for ($i = 0; $i < count($therapists_list); $i++) {
        if ($therapists_list[$i]->uid === $patient_id) { // if the patient is also a therapist, remove his/her name
            unset($therapists_list[$i]);
        }
    }
    $therapists_list = array_values($therapists_list);

    $therapists_assigned_json = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/treatment/patient/' . $patient_id . '/true'));
    $therapists_assigned_ids = array();
    if (isset($therapists_assigned_json->treatments)) {
        $therapists_assigned = $therapists_assigned_json->treatments;
        for ($i = 0; $i < count($therapists_assigned); $i++) {
            array_push($therapists_assigned_ids, $therapists_assigned[$i]->therapistId);
        }
    }

    $therapists_pending_json = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/treatment/patient/' . $patient_id . '/false'));
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
                    $therapist_name = $therapist_info->firstname . " " . $therapist_info->lastname; ?>
                    <tr>
                        <form method="post" action="managet.php">
                            <input name="therapist_search" value="<?php echo $therapist_info->uid ?>" type="hidden">
                            <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                            <td><button class="list-button"><?php echo htmlspecialchars($therapist_name) ?></button></td>
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
                                <button id="sendTreatmentReq" name="<?php echo htmlspecialchars($therapist_name) ?>"
                                    value="<?php echo $therapist_info->uid ?>">Send Treatment Request</button>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </table>
	    </div>

        <div id="treatmentReqSettingsDialog">
            <p><label><input type="checkbox" class="consentsettings" id="currentConsent"/>  Allow therapist to view all my current records</label></p>
            <p><label><input type="checkbox" class="consentsettings" id="futureConsent"/>  Allow therapist to view all my future records by default</label></p>
        </div>

        <div id="acknowledgementDialog"><p id="ackMessage" style="text-align:center"></p></div>
        <style> .jqueryDialogNoTitle .ui-dialog-titlebar { display: none; } </style>

        <script>

            var consentSettings = [ false, false ]; // currentConsent, futureConsent
            
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

                $('#treatmentReqSettingsDialog').dialog({
                    width: 500,
                    height: 220,
                    autoOpen: false,
                    resizable: false,
                    draggable: false,
                    modal: true,
                    title: "Treatment Request",
                    buttons: [
                        {
                            text: "Send Request",
                            click: function() {
                                $.ajax({
                                    type: "POST",
                                    url: "../ajax-process.php",
                                    data: { "patientId": $(this).data('patientId'), 
                                            "therapistId": $(this).data('therapistId'),
                                            "consentSettings": consentSettings }
                                }).done(function(response) {
                                    if (response == 1) {
                                        $('#acknowledgementDialog')
                                            .data('message', "Treatment request sent")
                                            .dialog('open');
                                    } else {
                                        $('#acknowledgementDialog')
                                            .data('message', "Error in sending treatment request")
                                            .dialog('open');
                                    }
                                })
                                $(this).dialog('close');
                            }
                        },
                        {
                            text: "Cancel",
                            click: function() { $(this).dialog('close'); }
                        }
                    ]
                })

                $(document).on('click', '#sendTreatmentReq', function() {
                    $('#treatmentReqSettingsDialog')
                        .data('patientId', <?php echo $patient_id ?>)
                        .data('therapistId', $(this).val())
                        .dialog('option', 'title', "Treatment Request to " + $(this).attr('name'))
                        .dialog('open');
                });

                $(document).on('click', 'input:checkbox.consentsettings', function() {
                    if ($(this).attr('id') == "currentConsent") {
                        consentSettings[0] = !consentSettings[0];
                    } else if ($(this).attr('id') == "futureConsent") {
                        consentSettings[1] = !consentSettings[1];
                    }

                })

            });

        </script>

    </body>
</html>
