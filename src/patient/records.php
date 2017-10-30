<?php
    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
    $user_type = $result->istherapist ? "therapist" : "patient";

    // Gets the list of records assigned to the specified patient
    $records_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/record/all/' . $result->uid));
    if (isset($records_list_json->records)) {
        $records_list = $records_list_json->records;
    }
    if (isset($records_list)) {
        $num_records = count($records_list);
    } else {
        $num_records = 0;
    }

    // Retrieves the user JSON object based on the uid
    function getJsonFromUid($uid) {
        $user_json_tmp = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $uid));
        return $user_json_tmp;
    }

?>

<html>
<meta charset="utf-8">

    <head>
        <title>View Records</title>
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
            <h1>View Record<?php if ($num_records != 1) { ?>s<?php } ?> (<?php echo $num_records ?>):</h1>
            <hr style="margin-top:-15px">

            <table class="main-table"   >
                <tr>
                    <th class = "first-col">S/N</th>
                    <th>Last Modified</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th style="text-align:right">Actions</th>
                </tr>
                <?php for ($i = 0; $i < $num_records; $i++) {   
                    $record = $records_list[$i]; ?>
                    <tr>
                        <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                        <td><?php echo $record->modifieddate ?></button></td>
                        <td><?php echo $record->type ?></td>
                        <td><?php echo $record->title ?></td>
                        <td style="text-align:right">
                            <input type="button" class="details" id="<?php echo $record->rid ?>" value="Details"/>
                            <input type="button" class="consent" id="<?php echo $record->rid ?>" value="Consent"/>
                        </td>
                    </tr>
                <?php } ?>
            </table>
	    </div>

        <div id="consentDialog"></div>
        
        <div id="acknowledgementDialog"><p id="ackMessage" style="text-align:center"></p></div>
        <style> .jqueryDialogNoTitle .ui-dialog-titlebar { display: none; } </style>

        <script>

            var consentChanges = [];

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
                        $('#ackMessage').text("Changes saved");
                        $(this).dialog('close');
                    },
                });

                $('#consentDialog').dialog({
                    width: 400,
                    height: 400,
                    autoOpen: false,
                    resizable: false,
                    draggable: true,
                    modal: true,
                    title: 'Consent Permissions',
                    buttons: [
                        {
                            text: "Save",
                            click: function() { 
                                $.ajax({
                                    type: "POST",
                                    url: "../ajax-process.php",
                                    data: { "consentChanges": consentChanges }
                                });
                                $(this).dialog('close');
                                $('#acknowledgementDialog').dialog('open');
                            }
                        },
                        {
                            text: "Cancel",
                            click: function() { $(this).dialog('close'); }
                        }
                    ],
                    open: function(event, ui) {
                        $(this).load(
                            'consent-dialog.php', 
                            { "recordId": $(this).data('recordId') },
                        );
                    }
                });


                $(document).on('click', 'input:button.consent', function() {
                    consentChanges = []; // reset array each type a new consent dialog window is opened
                    $('#consentDialog')
                        .data('recordId', $(this).attr('id'))
                        .dialog('open');
                });

                $(document).on('click', 'input:checkbox.setconsent', function() {
                    var consentId = $(this).val();
                    consentChanges[consentId] = !consentChanges[consentId]; // toggle
                });

            });
        
        </script>

    </body>
</html>
