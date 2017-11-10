<?php

    include_once 'util/ssl.php';
    include_once 'util/jwt.php';
    include_once 'util/logger.php';
    ini_set("allow_url_fopen", 1); // allow connection to DB server

    /*
    * // Get JSON object of user
    * if (isset($_SESSION['user_json'])) {
    * $user_json = $_SESSION['user_json'];
    * $_SESSION['user_json'] = $user_json;
    * }
    *
    * // Get user type
    * if (isset($_SESSION['user_type'])) {
    * $user_type = $_SESSION['user_type'];
    * }
    */

    // TODO: change the dummy key here to the real key
    $result = WebToken::verifyToken($_COOKIE["jwt"]);
    $user_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/uid/' . $result->uid));
    $user_type = $result->istherapist ? "therapist" : "patient";

    if ($user_type === "patient") {
        $user = "Patient";
        // Populate notifications list; TODO this part
        $treatment_pending_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/patient/' . $user_json->uid . '/false'));
        if (isset($treatment_pending_json->treatments)) {
            $treatment_pending = $treatment_pending_json->treatments;
        }
        if (isset($treatment_pending)) {
            $num_notifications = count($treatment_pending);
        } else {
            $num_notifications = 0;
        }
        
        // Populate therapist list
        $therapists_list_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/patient/' . $user_json->uid . '/true'));
        if (isset($therapists_list_json->treatments)) {
            $therapists_list = $therapists_list_json->treatments;
        }
        if (isset($therapists_list)) {
            $num_therapists = count($therapists_list);
        } else {
            $num_therapists = 0;
        }

        // Populate documents list
        $documents_list_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/user/' . $result->uid . '/true'));
        if (isset($documents_list_json->consents)) {
            $documents_list = $documents_list_json->consents;
        }
        if (isset($documents_list)) {
            $num_documents = count($documents_list);
        } else {
            $num_documents = 0;
        }
        // filter the list to remove all non-documents - for the case where a user is both a patient and a therapist
        if ($user_json->qualify === 1) {
            $documents_list_filtered = array();
            for ($i = 0; $i < $num_documents; $i++) {
                $consent = $documents_list[$i];
                $record_details = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/' . $consent->rid));
                if (isset($record_details->subtype)) {
                    if ($record_details->subtype === "document") {
                        array_push($documents_list_filtered, $consent);
                    }
                }
            }
            $documents_list = $documents_list_filtered;
            $num_documents = count($documents_list);
        }
    } else if ($user_type === "therapist") {
        $user = "Therapist";
        // Populate notifications list; TODO - include notifications for documents
        $treatment_reqs_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/therapist/' . $user_json->uid . '/false'));
        if (isset($treatment_reqs_json->treatments)) { // there are pending treatment requests
            $treatment_reqs = $treatment_reqs_json->treatments;
        }
        if (isset($treatment_reqs)) {
            $num_notifications = count($treatment_reqs);
        } else {
            $num_notifications = 0;
        }
        
        // Populate patients list
        $patients_list_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/therapist/' . $user_json->uid . '/true'));
        if (isset($patients_list_json->treatments)) {
            $patients_list = $patients_list_json->treatments;
        }
        if (isset($patients_list)) {
            $num_patients = count($patients_list);
        } else {
            $num_patients = 0;
        }
    }

    // Retrieves the user JSON object based on the uid
    function getJsonFromUid($uid)
    {
        $user_json_tmp = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/uid/' . $uid));
        return $user_json_tmp;
    }

?>

<html>
<meta charset="utf-8">

<head>
    <title>Healthcare System</title>
    <link href="css/main.css" rel="stylesheet">
    <link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"
        integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
        crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"
        integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30="
        crossorigin="anonymous"></script>
</head>

<body>
    <?php include 'sidebar.php' ?>
    <div class="shifted">
        <h1>Welcome, <?php echo (isset($user_json->firstname) ? htmlspecialchars($user_json->firstname) : $user) ?></h1>
        <hr style="margin-top: -15px">
        
        <div class="tab">
            <button class="tablinks active"
                onclick="openTab(event, 'Notifications')">Notifications</button>
            <?php if ($user_type === "patient") { ?>
                <button class="tablinks" onclick="openTab(event, 'Therapist')">Therapists</button>
                <button class="tablinks"
                    onclick="openTab(event, 'Documents')">Documents</button>
            <?php } else { ?>
                <button class="tablinks" onclick="openTab(event, 'Patient')">Patients</button>
            <?php } ?>
        </div>

        <div id="Notifications" class="tabcontent" style="display: block;">
            <h3>You have <?php echo htmlspecialchars($num_notifications) ?> notification<?php if ($num_notifications != 1) { ?>s<?php } ?>.</h3>
            <table class="main-table">
                <?php
                    if ($user_type === "therapist" && isset($treatment_reqs)) {
                        for ($i = 0; $i < count($treatment_reqs); $i++) {
                            $patient_json = getJsonFromUid($treatment_reqs[$i]->patientId); 
                            $patient_name = $patient_json->firstname . " " . $patient_json->lastname;
                    ?>
                    <tr>
                        <td><?php echo "Treatment request from " . htmlspecialchars($patient_name) ?></td>
                        <td class="last-col"><button id="treatmentReqDetails" name="<?php echo $patient_name ?>"
                                value="<?php echo $treatment_reqs[$i]->id ?>">Details</button></td>
                    </tr>
                <?php
                        }
                    } else if ($user_type === "patient" && isset($treatment_pending)) {
                        for ($i = 0; $i < count($treatment_pending); $i ++) {
                            $therapist_json = getJsonFromUid($treatment_pending[$i]->therapistId);
                            $therapist_name = $therapist_json->firstname . " " . $therapist_json->lastname;
                    ?>
                    <tr>
                        <td><?php echo "Treatment request pending approval from " . htmlspecialchars($therapist_name) ?></td>
                    </tr>
                <?php
                        }
                    }
                ?>
            </table>
        </div>

        <div id="Therapist" class="tabcontent">
            <h3>You are assigned to <?php echo $num_therapists ?> therapist<?php if ($num_therapists != 1) { ?>s<?php } ?>.</h3>
            <table class="main-table">
                <?php
                    for ($i = 0; $i < $num_therapists; $i ++) {
                        $therapist_json = getJsonFromUid($therapists_list[$i]->therapistId);
                        $therapist_name = $therapist_json->firstname . " " . $therapist_json->lastname;
                    ?>
                    <tr>
                        <td class="first-col"><?php echo ($i + 1)."." ?></td>
                        <td><?php echo htmlspecialchars($therapist_name) ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <div id="Patient" class="tabcontent">
            <h3>You are assigned to <?php echo $num_patients ?> patient<?php if ($num_patients != 1) { ?>s<?php } ?>.</h3>
            <table class="main-table">
                <?php
                    for ($i = 0; $i < $num_patients; $i ++) {
                        $patient_json = getJsonFromUid($patients_list[$i]->patientId);
                        $patient_name = $patient_json->firstname . " " . $patient_json->lastname;
                    ?>
                    <tr>
                        <td class="first-col"><?php echo ($i + 1)."." ?></td>
                        <td><?php echo htmlspecialchars($patient_name) ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <div id="Documents" class="tabcontent">
            <h3>You have <?php echo $num_documents ?> document<?php if ($num_documents != 1) { ?>s<?php } ?>.</h3>

            <table class="main-table">
                <tr>
                    <th class="first-col">S/N</th>
                    <th>Last modified</th>
                    <th width="40%">Title</th>
                    <th>Document Owner</th>
                </tr>
                <?php for ($i = 0; $i < $num_documents; $i++) {
                    $documentId = $documents_list[$i]->rid;
                    $document = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/get/' . $documentId));
                    $therapist = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/uid/public/' . $document->therapistId)); ?>
                    <tr>
                        <td class="first-col" style="vertical-align:top"><?php echo ($i + 1)."." ?></td>
                        <td style="vertical-align:top"><?php echo htmlspecialchars(substr($document->modifieddate, 0, 10)); ?></td>
                        <td>
                            <details>
                                <summary><?php echo htmlspecialchars($document->title) ?></summary>
                                <p>
                                    <?php 
                                        echo htmlspecialchars($document->notes);
                                        $attached_rids = $document->records;
                                        if ($attached_rids[0] !== 0) { // if there are attached records
                                            echo "<br><br>Attached records: <br>";
                                            for ($j = 0; $j < count($attached_rids); $j++) {
                                                $attached_record = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/' . $attached_rids[$j]));
                                                echo ($j+1) . ". <a href='../file/viewdoc.php?rid=" . $attached_rids[$j] . "'><u>" . $attached_record->title . "</u></a><br>"; 
                                            }
                                        } 
                                    ?>
                                </p>
                            </details>
                        </td>
                        <td style="vertical-align:top"><?php echo htmlspecialchars($therapist->firstname . " " . $therapist->lastname) ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <div id="treatmentReqDialog"></div>

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
                    location.reload(); // refresh the notifications count
                }
            });

            $('#treatmentReqDialog').dialog({
                width: 650,
                height: 300,
                autoOpen: false,
                resizable: false,
                draggable: false,
                modal: true,
                buttons: [
                    {
                        text: "Accept Request",
                        click: function() {
                            $.ajax({
                                type: "POST",
                                url: "ajax-process.php",
                                data: { "acceptTreatmentId": $(this).data('treatmentId'),
                                        "csrf": <?php include_once $_SERVER['DOCUMENT_ROOT']."/util/csrf.php"; echo CSRFToken::generateToken($result->uid, "acceptTreatmentReq");?> }
                            }).done(function(response) {
                                if (response == 1) {
				    <?php Log::recordTX($user_json->uid, "Info", "Treatment request accepted") ?>;
                                    $('#acknowledgementDialog')
                                        .data('message', "Treatment request accepted")
                                        .dialog('open');
                                } else {
				   <?php Log::recordTX($user_json->uid, "Error", "Error when accepting treatment request") ?>;
                                    $('#acknowledgementDialog')
                                        .data('message', "Error in processing treatment request")
                                        .dialog('open');
                                }
                            });
                            $(this).dialog('close');
                        }
                    },
                    {
                        text: "Reject Request",
                        click: function() { 
                            $.ajax({
                                type: "POST",
                                url: "ajax-process.php",
                                data: { "rejectTreatmentId": $(this).data('treatmentId'),
                                        "csrf": <?php include_once $_SERVER['DOCUMENT_ROOT']."/util/csrf.php"; echo CSRFToken::generateToken($result->uid, "rejectTreatmentReq");?> }
                            }).done(function(response) {
                                if (response == 1) {
				<?php Log::recordTX($user_json->uid, "Info", "Treatment request rejected") ?>;
                                    $('#acknowledgementDialog')
                                        .data('message', "Treatment request rejected")
                                        .dialog('open');
                                } else {
					<?php Log::recordTX($user_json->uid, "Error", "Error when rejecting treatment request") ?>;
                                    $('#acknowledgementDialog')
                                        .data('message', "Error in processing treatment request")
                                        .dialog('open');
                                }
                            });
                            $(this).dialog('close'); 
                        }
                    }
                ],
                open: function(event, ui) {
                    $(this).load(
                        'treatment-req-dialog.php',
                        { "treatmentId": $(this).data('treatmentId') }
                    );
                }
            });

            $(document).on('click', '#treatmentReqDetails', function() {
                $('#treatmentReqDialog')
                    .data('treatmentId', $(this).val())
                    .dialog('option', 'title', "Treatment Request from " + $(this).attr('name'))
                    .dialog('open');
            });

        });

        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }

    </script>

</body>
</html>
