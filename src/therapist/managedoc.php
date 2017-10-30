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
    $current_date = new DateTime();
    $current_date->setTimeZone(new DateTimeZone('Singapore'));
    $associated_patient = sanitise($_POST['document-associated-patient']);
    $title = sanitise($_POST['document-title']);
    $notes = sanitise($_POST['document-notes']);
    $creation_date = strval($current_date->format("Y-m-d"));
    $modified_date = $creation_date;
    $document_json = json_array($title, $associated_patient, $user_json->uid, $creation_date, $modified_date, $notes, array(1));
    $url = 'http://172.25.76.76/api/team1/record/document/create';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $document_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_exec($ch);
    $documents_list = json_decode(file_get_contents('http://172.25.76.76/api/team1/record/all/'.$user_json->uid))->records;
    $num_documents = count($documents_list);
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
              </details>
            </td>
            <td><?php echo $patient->firstname." ".$patient->lastname ?></td>
            <td><?php echo substr($record->modifieddate, 0, 10); ?></td>
            <td><button id="<?php echo $record->rid; ?>" onclick="delete_doc(this.id, <?php echo $user_json->uid?>)" style="border:none; background:none"><i class="fa fa-times fa-lg" aria-hidden="true"></i></button></td>
          </tr>
        <?php 
              $i++;
            }
          }
        ?>
      </table>
    </div>
    <script>
      function delete_doc(rid, uid) {
        var to_delete = confirm("Are you sure you want to delete this document?");
        if (to_delete) {
          alert("Okay");
          /*
          $.get(
            "http://172.25.76.76/api/team1/record/delete/" + rid + "/" + uid,
            function() { 
              window.location.href='managedoc.php';
            }
          );
          */
        }
      }
    </script>
  </body>
</html>
