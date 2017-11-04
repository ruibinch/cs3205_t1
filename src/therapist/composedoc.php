<?php

    include_once '../util/ssl.php';
    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
    $user_json = json_decode(ssl::get_content('http://172.25.76.76/api/team1/user/uid/' . $result->uid));
    $user_type = $result->istherapist ? "therapist" : "patient";

    $patients_list_json = json_decode(ssl::get_content('http://172.25.76.76/api/team1/treatment/therapist/'.$user_json->uid.'/true'));
    if (isset($patients_list_json->treatments)) {
        $patients_list = $patients_list_json->treatments;
    }
    if (isset($patients_list)) {
        $num_patients = count($patients_list);
    } else {
        $num_patients = 0;
    }

    function getUserFromUid($uid) {
        return json_decode(ssl::get_content('http://172.25.76.76/api/team1/user/uid/' . $uid));
    }

?>

<html>
<meta charset="utf-8">
    <head>
        <title>Compose Document</title>
        <link href="../css/main.css" rel="stylesheet">
        <link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.2.1.min.js"
            integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
            crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"
            integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30="
            crossorigin="anonymous"></script>
    </head>

    <body class="newdoc">
        <?php include '../sidebar.php' ?>

        <div class="shifted">
            <h1>Compose a document</h1>
            <hr style="margin-top:-15px">
            <form id="documentForm" class="profile-form" name="profile-form" method="post" action="managedoc.php">
                <table class="newdoc-table">
                    <tr>
                        <td class="first-col" style="vertical-align:top">Associated Patient:</td>
                        <td>
                            <select name="document-associated-patient">
                                <option value=""></option>
                                <?php for ($i = 0; $i < $num_patients; $i++) { 
                                    $patient = getUserFromUid($patients_list[$i]->patientId); ?>
                                    <option value="<?php echo $patient->uid ?>">
                                        <?php
                                            echo $patient->firstname." ".$patient->lastname;
                                        ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <p id="addOption" style="display:none">
                                <label><input name="allow-patient-viewdoc" id="allowPatientViewDoc" type="checkbox"> Allow patient to view document</input></label>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td class="first-col">Title:</td>
                        <td><input name="document-title" type="text"/></td>
                    </tr>
                    <tr>
                        <td class="first-col" style="vertical-align:top">Notes:</td>
                        <td><textarea name="document-notes" class="newdoc-text"></textarea></td>
                    </tr>
                    <tr>
                        <td class="first-col" style="vertical-align:top">Attach Records:</td>
                        <td>
                            <input type="button" id="viewRecords" value="View Records"/>
                            <div id="attachedRecordsTable"></div>
                            <input type="hidden" name="attached-records"/>
                        </td>
                    </tr>
                </table>
            </form>
            <button name="save-form">Save</button>
            <button onclick="window.location.href='../main.php'">Cancel</button>
        </div>

        <div id="noRecordsDialog"><br>This document does not have an associated patient.</div>
        <div id="recordsDialog"></div>

        <script>

            var attachRecordsList = [];
            //var attachedRids = [];

            $(document).ready(function() {

                $('#noRecordsDialog').dialog({
                    width: 450,
                    height: 200,
                    autoOpen: false,
                    resizable: false,
                    draggable: true,
                    modal: true,
                    title: 'Attach Records',
                    buttons: [
                        {
                            text: "Close",
                            click: function() { $(this).dialog('close'); }
                        }
                    ]
                });

                $('#recordsDialog').dialog({
                    width: 800,
                    height: 600,
                    autoOpen: false,
                    resizable: false,
                    draggable: true,
                    modal: true,
                    title: 'Attach Records',
                    buttons: [
                        {
                            text: "Attach",
                            click: function() {
                                $.ajax({
                                    type: "POST",
                                    url: "../ajax-process.php",
                                    data: { "attachRecords": attachRecordsList }
                                }).done(function(response) {
                                    console.log(response);
                                    $('#attachedRecordsTable').html(response);

                                    /* Display just for reference
                                    for (var i = 0; i < attachRecordsList.length; i++) {
                                        if (attachRecordsList[i]) {
                                            attachedRids.push(i);
                                        }
                                    }
                                    $('#attachedRecordsTable').append("<br>Attached RIDs:<br>" + attachedRids);
                                    */
                                });
                                $(this).dialog('close');
                            }
                        },
                        {
                            text: "Cancel",
                            click: function() { $(this).dialog('close'); }
                        }
                    ],
                    open: function(event, ui) {
                        $(this).load(
                            'records-dialog.php', 
                            { 
                                "patientId": $(this).data('patientId'), 
                                "therapistId": $(this).data('therapistId'),
                                "attachRecordsList": $(this).data('attachRecordsList')
                            }
                        );
                    }
                });

                $('select[name="document-associated-patient"]').change(function() {
                    if ($('select[name="document-associated-patient"]').val() == "") {
                        $('#addOption').hide();    
                    } else {
                        $('#addOption').show();
                        $('#allowPatientViewDoc').attr('checked', true);
                    }

                    attachRecordsList = []; // reset attached records list whenever the associated patient is changed
                    $('#attachedRecordsTable').html('');
                });

                $('#viewRecords').click(function() {
                    attachedRids = []; // reset
                    var patientId = $('select[name="document-associated-patient"]').val();
                    if (patientId == "") {
                        $('#noRecordsDialog').dialog('open');
                    } else {
                        $('#recordsDialog')
                            .data('patientId', patientId)
                            .data('therapistId', <?php echo $result->uid ?>)
                            .data('attachRecordsList', attachRecordsList)
                            .dialog('open');
                    }
                });

                $("button[name='save-form']").click(function() {
                    var title = $('input[name="document-title"]').val().trim();
                    var notes = $('textarea[name="document-notes"]').val().trim();
                    $('input[name="attached-records"]').val(attachedRids);

                    if (title === "" || notes === "") {
                        alert("Please check for any empty fields");
                    } else {
                        $("#documentForm").submit();
                    }
                });

                $(document).on('click', 'input:checkbox.selectRecordCheckbox', function() {
                    var recordId = $(this).val();
                    attachRecordsList[recordId] = !attachRecordsList[recordId]; // toggle
                    //console.log("recordId = " + recordId + ", attachRecordsList[recordId] = " + attachRecordsList[recordId]);
                });

            });

        </script>
    
    </body>

</html>
