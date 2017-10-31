<?php

    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");

    $user_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $result->uid));
    $user_type = $result->istherapist ? "therapist" : "patient";
    
    $documents_list = json_decode(file_get_contents('http://172.25.76.76/api/team1/record/all/'.$user_json->uid))->records;
    $num_documents = count($documents_list);

    function sanitise($input) {
        $input = trim($input);
        $input = stripcslashes($input);
        $input = htmlspecialchars($input);
        return $input;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['action'])) {
            $rid = $_POST['rid'];
            $delete = json_decode(file_get_contents('http://172.25.76.76/api/team1/record/delete/'.$rid."/".$user_json->uid));
            $documents_list = json_decode(file_get_contents('http://172.25.76.76/api/team1/record/all/'.$user_json->uid))->records;
            $num_documents = count($documents_list);
        } else {
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
            $url = 'http://172.25.76.76/api/team1/record/document/create';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $document_json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_exec($ch);
            $documents_list = json_decode(file_get_contents('http://172.25.76.76/api/team1/record/all/'.$user_json->uid))->records;
            $num_documents = count($documents_list);

            // if permission had been granted to the patient to view the document, create a corresponding consent
            if ($allow_patient_viewdoc === "on") {
                $added_document = $documents_list[count($documents_list)-1];
                $added_document_rid = $added_document->rid;
                $associated_patient_id = json_decode(file_get_contents('http://172.25.76.76/api/team1/record/get/' . $added_document_rid))->patientId;
                $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/consent/create/' . $associated_patient_id . '/' . $added_document_rid));
                
                // set the consent status to true
                $consents_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/consent/record/' . $added_document_rid));
                if (isset($consents_json->consents)) {
                    $consents = $consents_json->consents;
                    for ($i = 0; $i < count($consents); $i++) {
                        if ($consents[$i]->uid === $associated_patient_id) {
                            $consentId = $consents[$i]->consentId;
                            $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/consent/update/' . $consentId));
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
        return json_decode(file_get_contents('http://172.25.76.76/api/team1/record/get/' . $rid));
    }

    function getJsonFromUid($uid) {
        $user_json_tmp = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/'.$uid));
        return $user_json_tmp;
    }

?>

<html>
<meta charset="utf-8">
    <head>
        <title>Manage Documents</title>
        <link href="../css/main.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    </head>

    <body>
        <?php include '../sidebar.php' ?>

        <div class="shifted">
            <h1>You have <?php echo $num_documents ?> documents.</h1>  
            <hr style="margin-top:-15px">
            <table class="main-table">
                <tr>
                    <th class="first-col">S/N</th>
                    <th>Title</th>
                    <th>Patient</th>
                    <th>Shared with Patient</th>
                    <th>Last modified</th>
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
                ?>
                    <tr>
                        <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                        <td style="width:60%">
                            <details>
                                <summary>
                                    <?php echo $record->title; ?>
                                </summary>
                                <p><?php echo $record->notes; ?></p>
                                <p>
                                    <?php 
                                        $attached_rids = $record->records;
                                        $output = "";
                                        if ($attached_rids[0] !== 0) { // if there are attached records
                                            echo "Attached RIDs:";
                                            foreach($attached_rids as $value) {
                                                if ($output === "") {
                                                    $output .= $value;
                                                } else {
                                                    $output .= ", " . $value;
                                                }
                                            }
                                        }
                                        echo $output;
                                    ?>
                                </p>
                            </details>
                        </td>
                        <td>
                            <?php 
                                if (isset($patient->firstname) && isset($patient->lastname)) { 
                                    echo $patient->firstname . " " . $patient->lastname; 
                                } else {
                                    echo "-";
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                                if (!isset($patient->uid)) { // if document has no associated patient
                                    echo "-";
                                } else {
                                    $shared_with_patient = "No";
                                    $consents_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/consent/owner/' . $result->uid . '/' . $record->patientId));
                                    if (isset($consents_json->consents)) {
                                        $consents = $consents_json->consents;
                                        for ($j = 0; $j < count($consents); $j++) {
                                            if ($consents[$j]->rid === $document->rid) {
                                                $shared_with_patient = "Yes";
                                            }
                                        }
                                    }
                                    echo $shared_with_patient;
                                }
                            ?>
                        </td>
                        <td><?php echo substr($record->modifieddate, 0, 10); ?></td>
                        <td>
                            <form id="deleteDocument" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                              <input type="hidden" name="action" value="delete-document" />
                              <input type="hidden" name="rid" value="<?php echo $record->rid; ?>">
                            </form>
                            <button name="delete-document" style="border:none; background:none"><i class="fa fa-times fa-lg" aria-hidden="true"></i></button>
                        </td>

                    </tr>
                <?php 
                    $i++;
                    }
                }
                ?>
            </table>
        </div>

        <script>
          $(document).ready(function() {
            $("button[name='delete-document'").click(function() {
              var to_delete = confirm("Are you sure you want to delete this document?");
              if (to_delete) {
                $("#deleteDocument").submit();
              }
            });
          });
        </script>
    </body>
</html>
