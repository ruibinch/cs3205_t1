<?php

    include_once '../util/ssl.php';
    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"]);
    $user_type = $result->istherapist ? "therapist" : "patient";

    if (isset($_POST["therapist_search"])) {
        $therapistId = $_POST["therapist_search"];
    }
    $therapist = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/user/uid/public/' . $therapistId));

    // Iterate through the list of therapists assigned to this patient
    // and obtain the treatment object associated to this treatment between the patient and therapist
    $therapists_list_json = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/treatment/patient/' . $result->uid . '/true'));
    if (isset($therapists_list_json->treatments)) {
        $treatments_list = $therapists_list_json->treatments;
        for ($i = 0; $i < count($treatments_list); $i++) {
            if ($treatments_list[$i]->therapistId == $therapistId) {
                $treatment = $treatments_list[$i];
            }
        }
    }

    // Gets the list of records of the patient
    $records_list_json = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/record/all/' . $result->uid));
    if (isset($records_list_json->records)) {
        $records_list = $records_list_json->records;
    }

    $documents_list_json = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/consent/owner/' . $therapistId . '/' . $result->uid));
    if (isset($documents_list_json->consents)) {
        $documents_list = $documents_list_json->consents;
        for ($i = 0; $i < count($documents_list); $i++) {
            if (!$documents_list[$i]->status) {
                unset($documents_list[$i]);
            }
        }
        $documents_list = array_values($documents_list);
    }
    if (isset($documents_list)) {
        $num_documents = count($documents_list);
    } else {
        $num_documents = 0;
    }

?>

<html>
<meta charset="utf-8">
    <head>
        <title>Therapist Management</title>
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
            <h1><?php echo $therapist->firstname . " " . $therapist->lastname ?></h1>
            <hr style="margin-top:-15px">
            <table width="70%">
                <th>
                    <td width="60%"></td>
                    <td></td>
                </th>
                <tr>
                    <td>First Name:</td>
                    <td><?php echo $therapist->firstname ?></td>
                </tr>
                <tr>
                    <td>Last Name:</td>
                    <td><?php echo $therapist->lastname ?></td>
                </tr>
                <tr>
                    <td>Sex:</td>
                    <td><?php if ($therapist->sex === "M") { ?>Male<?php } else { ?>Female<?php } ?></td>
                </tr>
                <tr>
                    <td>Ethnicity:</td>
                    <td><?php if (isset($therapist->ethnicity)) { echo $therapist->ethnicity; } ?></td>
                </tr>
                <tr>
                    <td>Contact Number:</td>
                    <td><?php echo $therapist->phone ?></td>
                </tr>
            </table>

            <h2 style="margin-top:40px">Consent Settings
                <button class="login-btn" id="updateConsentSettings" style="width:20%; height:30px; margin-left:50px">Update Consent Settings</button>
            </h2>
            <table>
                <tr>
                    <td><input type="checkbox" id="currentConsent" <?php if ($treatment->currentConsent) { echo "checked "; } ?>/></td>
                    <td><label for="currentConsent">Allow therapist to view all my current records</td>
                </tr>
                <tr>
                    <td><input type="checkbox" id="futureConsent" <?php if ($treatment->futureConsent) { echo "checked"; } ?>/></td>
                    <td><label for="futureConsent">Allow therapist to view all my future records by default</label></td>
                </tr>
            </table>

            <span id="showHideRecords" style="cursor:pointer; position:relative; top:20px; left:10px"><u>Show Records Table</u></span>
            <table class="main-table" id="recordsTable" style="margin-top:20px; margin-left:15px; width:85%; display:none">
                <tr>
                    <th class = "first-col">S/N</th>
                    <th>Last Modified</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Consent Given</th>
                    <th style="text-align:right">Actions</th>
                </tr>
                <?php for ($i = 0; $i < count($records_list); $i++) {   
                    $record = $records_list[$i];
                    // Get the corresponding consent between the therapist and this specific record
                    $consents_json = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/consent/record/' . $record->rid));
                    $consents = $consents_json->consents;
                    for ($j = 0; $j < count($consents); $j++) {
                        if ($consents[$j]->uid == $therapistId) {
                            $consent = $consents[$j];
                        }
                    }
                    
                    $checked_status = "";
                    if ($consent->status) {
                        $checked_status = "checked";
                    }
                    ?>
                    <tr>
                        <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                        <td><?php echo $record->modifieddate ?></button></td>
                        <td><?php echo $record->type ?></td>
                        <td><?php echo $record->title ?></td>
                        <td><input type="checkbox" id="setconsent" value="<?php echo $consent->consentId ?>" <?php echo $checked_status ?>/>
                        <td style="text-align:right">
                            <input type="button" class="details" id="<?php echo $record->rid ?>" value="Details"/>
                        </td>
                    </tr>
                <?php } ?>
            </table>

            <h2 style="margin-top:50px">Associated Documents</h2>
            <table class="main-table">
                <tr>
                    <th class="first-col">S/N</th>
                    <th>Last modified</th>
                    <th width="40%">Title</th>
                    <th>Document Owner</th>
                    <th>Shared With</th>
                </tr>
                <?php for ($i = 0; $i < $num_documents; $i++) {
                    $documentId = $documents_list[$i]->rid;
                    $document = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/record/get/' . $documentId)); ?>
                    <tr>
                        <td class="first-col" style="vertical-align:top"><?php echo ($i + 1)."." ?></td>
                        <td style="vertical-align:top"><?php echo substr($document->modifieddate, 0, 10); ?></td>
                        <td>
                            <details>
                                <summary><?php echo $document->title ?></summary>
                                <p><?php echo $document->notes ?></p>
                            </details>
                        </td>
                        <td style="vertical-align:top"><?php echo $therapist->firstname . " " . $therapist->lastname ?></td>
                        <td style="vertical-align:top">-</td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <div id="acknowledgementDialog"><p id="ackMessage" style="text-align:center"></p></div>
        <style> .jqueryDialogNoTitle .ui-dialog-titlebar { display: none; } </style>

        <script>

            var consentChanges = [ ];

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
                        delay: 500
                    },
                    open: function() {
                        $('#ackMessage').text($(this).data('message'));
                        $(this).dialog('close');
                    },
                    close: function() {
                        location.reload();
                    }
                });

                $('#updateConsentSettings').click(function() {
                    console.log($('#currentConsent').is(':checked'));
                    console.log($('#futureConsent').is(':checked'));
                    console.log(consentChanges);
                    $.ajax({
                        type: "POST",
                        url: "../ajax-process.php",
                        data: { "treatmentId": <?php echo $treatment->id; ?>,
                                "currentConsentSetting": $('#currentConsent').is(':checked'),
                                "futureConsentSetting": $('#futureConsent').is(':checked'),
                                "consentChanges": consentChanges }
                    }).done(function(response) {
                        if (response) {
                            $('#acknowledgementDialog')
                                .data('message', "Consent settings updated")
                                .dialog('open');
                        } else {
                            $('#acknowledgementDialog')
                                .data('message', "Error in updating consent settings")
                                .dialog('open');
                        }
                    });


                });

                $('#showHideRecords').click(function() {
                    if ($('#recordsTable').is(':visible')) {
                        $('#recordsTable').hide();
                        $('#showHideRecords').html("<u>Show Records Table</u>");
                    } else {
                        $('#recordsTable').show();
                        $('#showHideRecords').html("<u>Hide Records Table</u>");
                    }
                });

                $(document).on('click', 'input:checkbox#setconsent', function() {
                    var consentId = $(this).val();
                    consentChanges[consentId] = !consentChanges[consentId]; // toggle
                });

            });

        </script>

    </body>
</html>