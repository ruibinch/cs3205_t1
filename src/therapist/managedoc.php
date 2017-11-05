<?php

    include_once '../util/ssl.php';
    include_once '../util/jwt.php';
    include_once '../util/logging.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"]);

    $user_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/uid/' . $result->uid));
    $user_type = $result->istherapist ? "therapist" : "patient";
    
    $documents_list = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/all/'.$user_json->uid))->records;
    $num_documents = count($documents_list);

    $shared_documents_list = array();

    if (isset(json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/user/'.$user_json->uid))->consents)) {
        $consented_documents_list = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/user/'.$user_json->uid))->consents;
        $shared_documents_list = array();
        foreach($consented_documents_list AS $consented_document) {
            $shared_document = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/'.$consented_document->rid));
            if (strcmp($shared_document->type, "File") == 0 && strcmp($shared_document->subtype, "document") == 0 && $consented_document->status) {
                array_push($shared_documents_list, get_record($consented_document->rid));
            }
        }
    }

    $num_shared_documents = count($shared_documents_list);

    function sanitise($input) {
        $input = trim($input);
        $input = stripcslashes($input);
        $input = htmlspecialchars($input);
        return $input;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['action'])) {
            $rid = $_POST['rid'];
            $record = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/get/' . $rid));
            $patientId = $record->patientId;
            $therapistId = $record->therapistId;

            // delete consent related to the document, if itexists
            if ($therapistId != 0) {
                $consents_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/owner/' . $therapistId . '/' . $patientId));
                if (isset($consents_json->consents)) {
                    $consents_list = $consents_json->consents;
                    for ($j = 0; $j < count($consents_list); $j++) {
                        if ($consents_list[$j]->rid === $rid) {
                            $delete_consent = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/delete/' . $consents_list[$j]->consentId));
                        }
                    }
                }
            }

            // delete document
            $delete_document = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/delete/'.$rid."/".$user_json->uid));
            Log:: recordTx($user_json->uid, "Info", "Deleted a document");
            $documents_list = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/all/'.$user_json->uid))->records;
            $num_documents = count($documents_list);
        } else {
            // get CSRF token
            $csrf = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/csrf/".$_POST['csrf']));
            if (isset($csrf->result) || $csrf->expiry < time() || $csrf->description != "composedoc" || $csrf->uid != $user_json->uid) {
                //invalid csrf token
                Log::recordTX($user_json->uid, "Warning", "Invalid csrf when accessing managedoc.php");
                header('HTTP/1.0 400 Bad Request.');
                die();
            }

            $current_date = new DateTime();
            $current_date->setTimeZone(new DateTimeZone('Singapore'));
            $associated_patient = sanitise($_POST['document-associated-patient']);
            if (isset($_POST['allow-patient-viewdoc'])) {
                $allow_patient_viewdoc = $_POST['allow-patient-viewdoc'];
            } else {
                $allow_patient_viewdoc = "";
            }
            $title = sanitise($_POST['document-title']);
            $notes = sanitise($_POST['document-notes']);
            $attached_records = $_POST['attached-records'];
            $attached_rids_array = array();
            if ($attached_records !== null) {
                $attached_rids_array = explode(",", $attached_records);
            }
            $creation_date = strval($current_date->format("Y-m-d"));
            $modified_date = $creation_date;
            
            $document_json = json_array($title, $associated_patient, $user_json->uid, $creation_date, $modified_date, $notes, $attached_rids_array);
            $url = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/document/create';
            ssl::post_content($url, $document_json, array('Content-Type: application/json'));
            /*
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            ssl::setSSL($ch);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $document_json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_exec($ch);
            */
            if (empty($associated_patient)) {
                Log::recordTX($user_json->uid, "Info", "Composed a new document with no associated patient");
            } else {
                Log::recordTX($user_json->uid, "Info", "Composed a new document associated with patient ". $associated_patient);
            }
            $documents_list = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/all/'.$user_json->uid))->records;
            $num_documents = count($documents_list);

            // create a corresponding consent between this document and the associated patient
            $added_document = $documents_list[count($documents_list)-1];
            $added_document_rid = $added_document->rid;
            $associated_patient_id = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/get/' . $added_document_rid))->patientId;
            if ($associated_patient_id != 0) { // if there is an associated patient
                $response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/create/' . $associated_patient_id . '/' . $added_document_rid));
                
                // if option to allow patient to view is set, update the consent status to true
                if ($allow_patient_viewdoc === "on") {
                    $consents_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/record/' . $added_document_rid));
                    if (isset($consents_json->consents)) {
                        $consents = $consents_json->consents;
                        for ($i = 0; $i < count($consents); $i++) {
                            if ($consents[$i]->uid === $associated_patient_id) {
                                $consentId = $consents[$i]->consentId;
                                $response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/update/' . $consentId));
                            }
                        }
                    }
                }
            }
        }
    }

    function json_array($title, $patientId, $therapistId, $creationDate, $modifiedDate, $notes, $records) {
        $arr = array(
            'title' => $title,
            'patientId' => $patientId,
            'therapistId' => $therapistId,
            'creationdate' => $creationDate,
            'modifieddate' => $modifiedDate,
            'notes' => $notes,
            'records' => $records
        );
        return json_encode($arr);
    }

    function get_record($rid) {
        return json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/get/' . $rid));
    }

    function getJsonFromUid($uid) {
        $user_json_tmp = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/uid/'.$uid));
        return $user_json_tmp;
    }

?>

<html>
<meta charset="utf-8">
    <head>
        <title>Manage Documents</title>
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
        <h1>You have <?php echo $num_documents + $num_shared_documents ?> document<?php if ($num_documents + $num_shared_documents != 1) { ?>s<?php } ?>.</h1>  
            <hr style="margin-top:-15px">
            <details open>
                <summary><b>Your Notes (<?php echo $num_documents ?>)</b></summary>
                <table class="main-table">
                <tr>
                    <th class="first-col">S/N</th>
                    <th>Title</th>
                    <th>Patient</th>
                    <th>Shared with Patient</th>
                    <th>Last modified</th>
                    <th></th>
                    <th class="last-col"></th>
                </tr>
                <?php 
                    $i = 0;
                    foreach($documents_list AS $document) {
                        $record = get_record($document->rid);
                        if (isset($record->result)) {
                            continue;
                        } else {
                            $patient = getJsonFromUid($record->patientId);
                            $consents_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/owner/' . $result->uid . '/' . $record->patientId));
                            
                ?>
                    <tr>
                        <td class="first-col" style="vertical-align:top"><?php echo ($i + 1) . "." ?></td>
                        <td style="width:60%; vertical-align:top">
                            <details>
                                <summary>
                                    <?php echo $record->title; ?>
                                </summary>
                                <p><?php echo $record->notes; ?></p>
                                <p>
                                    <?php 
                                        $therapist_consents_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/user/' . $result->uid . '/true'));
                                        if (isset($therapist_consents_json->consents)) {
                                            $records_viewable_by_therapist = $therapist_consents_json->consents;
                                        }
                                        $rids_viewable_by_therapist = array();
                                        if (isset($records_viewable_by_therapist)) {
                                            for ($k = 0; $k < count($records_viewable_by_therapist); $k++) {
                                                array_push($rids_viewable_by_therapist, $records_viewable_by_therapist[$k]->rid);
                                            }
                                        }

                                        $attached_rids = $record->records;
                                        if ($attached_rids[0] !== 0) { // if there are attached records
                                            echo "Attached records: <br>";
                                            for ($j = 0; $j < count($attached_rids); $j++) {
                                                $attached_record = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/' . $attached_rids[$j]));
                                                if (in_array($attached_rids[$j], $rids_viewable_by_therapist)) {
                                                    echo ($j+1) . ". <a href='../file/viewdoc.php?rid=" . $attached_rids[$j] . "'><u>" . $attached_record->title . "</u></a><br>"; 
                                                } else {
                                                    echo ($j+1) . ". " . $attached_record->title . "<br>";
                                                }
                                            }
                                        }
                                    ?>
                                </p>
                            </details>
                        </td>
                        <td style="vertical-align:top">
                            <?php 
                                if (isset($patient->firstname) && isset($patient->lastname)) { 
                                    echo $patient->firstname . " " . $patient->lastname; 
                                } else {
                                    echo "-";
                                }
                            ?>
                        </td>
                        <td style="vertical-align:top">
                            <?php
                                if (!isset($patient->uid)) { // if document has no associated patient
                                    echo "-";
                                } else {
                                    $shared_with_patient = "No";
                                    if (isset($consents_json->consents)) {
                                        $consents = $consents_json->consents;
                                        for ($j = 0; $j < count($consents); $j++) {
                                            if (($consents[$j]->rid === $document->rid) && $consents[$j]->status) {
                                                $shared_with_patient = "Yes";
                                            }
                                        }
                                    }
                                    echo $shared_with_patient;
                                }
                            ?>
                        </td>
                        <td style="vertical-align:top"><?php echo substr($record->modifieddate, 0, 10); ?></td>
                        <td style="vertical-align:top">
                            <form id="deleteDocument" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                              <input type="hidden" name="action" value="delete-document" />
                              <input type="hidden" name="rid" value="<?php echo $record->rid; ?>"/>
                            </form>
                            <button name="delete-document" style="border:none; background:none"><i class="fa fa-times fa-lg" aria-hidden="true"></i></button>
                        </td>
                        <td style="vertical-align:top">
                            <button data-patientid="<?php echo $record->patientId ?>" data-rid="<?php echo $record->rid ?>" name="share-document" style="border:none; background:none"><i class="fa fa-share-square-o fa-lg" aria-hidden="true"></i></button>
                        </td>
                    </tr>
                <?php 
                    $i++;
                    }
                }
                ?>
                </table>
            </details>
            <br>
            <details open>
                <summary><b>Other Therapists' Notes (<?php echo $num_shared_documents ?>)</b></summary>
                <table class="main-table">
                    <tr>
                        <th class="first-col">S/N</th>
                        <th>Title</th>
                        <th>Therapist</th>
                        <th>Patient</th>
                        <th>Last modified</th>
                    </tr>
                    <?php for($i=0; $i < count($shared_documents_list); $i++) { ?>
                        <tr>
                            <td><?php echo $i + 1 ?></td>
                            <td style="width:60%">
                                <details>
                                    <summary><?php echo $shared_documents_list[$i]->title ?></summary>
                                    <p><?php echo $shared_documents_list[$i]->notes ?></p>
                                    <p>
                                        <?php 
                                            $therapist_consents_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/user/' . $user_json->uid . '/true'));
                                            if (isset($therapist_consents_json->consents)) {
                                                $records_viewable_by_therapist = $therapist_consents_json->consents;
                                            }
                                            $rids_viewable_by_therapist = array();
                                            if (isset($records_viewable_by_therapist)) {
                                                for ($k = 0; $k < count($records_viewable_by_therapist); $k++) {
                                                    array_push($rids_viewable_by_therapist, $records_viewable_by_therapist[$k]->rid);
                                                }
                                            }

                                            $attached_rids = $record->records;
                                            if ($attached_rids[0] !== 0) { // if there are attached records
                                                echo "Attached records: <br>";
                                                for ($j = 0; $j < count($attached_rids); $j++) {
                                                    $attached_record = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/' . $attached_rids[$j]));
                                                    if (in_array($attached_rids[$j], $rids_viewable_by_therapist)) {
                                                        echo ($j+1) . ". <a href='../file/viewdoc.php?rid=" . $attached_rids[$j] . "'><u>" . $attached_record->title . "</u></a><br>"; 
                                                    } else {
                                                        echo ($j+1) . ". " . $attached_record->title . "<br>";
                                                    }
                                                }
                                            }
                                        ?>
                                    </p>
                                </details>
                                
                            </td>
                            <td>
                                <?php
                                $t_id = $shared_documents_list[$i]->therapistId;
                                $therapist = getJsonFromUid($t_id);
                                echo $therapist->firstname." ".$therapist->lastname;
                                ?>
                            </td>
                            <td>
                                <?php
                                $p_id = $shared_documents_list[$i]->patientId;
                                if ($p_id === 0) {
                                    echo "-";
                                } else {
                                    $patient = getJsonFromUid($p_id);
                                    echo $patient->firstname." ".$patient->lastname;
                                }
                                ?>
                            </td>
                            <td><?php echo substr($shared_documents_list[$i]->modifieddate, 0, 10) ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </details>
        </div>

        <div id="therapistsListDialog"></div>

        <script>

        var therapistArray = [];
        $(document).ready(function() {

            $("button[name='delete-document']").click(function() {
              var to_delete = confirm("Are you sure you want to delete this document?");
              if (to_delete) {
                $("#deleteDocument").submit();
              }
            });

            $("button[name='share-document']").click(function() {
                var patientId = $(this).data('patientid');
                var rid = $(this).data('rid');
                $('#therapistsListDialog')
                    .data('patientId', patientId)
                    .data('therapistId', <?php echo $user_json->uid ?>)
                    .data('rid', rid)
                    .dialog('open');
                
            });

            $('#therapistsListDialog').dialog({
                width: 400,
                height: 600,
                autoOpen: false,
                resizable: false,
                draggable: true,
                modal: true,
                title: 'Share Documents',
                buttons: [
                    {
                        text: "Share",
                        click: function() {
                            var rid = $(this).data('rid');
                            therapistArray = [];
                            $(".selectDocumentCheckbox").each(function() {
                                var t_id = $(this).val();
                                var isChecked = $(this).is(':checked');
                                var t_json = {"therapist": t_id, "isChecked": isChecked, "owner": <?php echo $user_json->uid ?>, "rid": rid};
                                therapistArray.push(JSON.stringify(t_json));
                            });
                            $.ajax({
                                type: "POST",
                                url: "../ajax-process.php",
                                data: { "therapistArray": therapistArray }
                            }).done(function(response) {
                                alert(response);
                            });
                            $(this).dialog('close');
                        }
                    },
                    {
                        text: "Cancel",
                        click: function() {
                            therapistArray = [];
                            $(this).dialog('close');
                        }
                    }
                ],
                open: function(event, ui) {
                    $(this).load(
                        'therapists-list-dialog.php', 
                        { 
                            "patientId": $(this).data('patientId'), 
                            "therapistId": $(this).data('therapistId'),
                            "rid": $(this).data('rid')
                        }
                    );
                }
            });

          });
        </script>
    </body>
</html>
