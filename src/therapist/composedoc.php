<?php

  include_once '../util/jwt.php';
  $result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
  $user_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $result->uid));
  $user_type = $result->istherapist ? "therapist" : "patient";

  $patients_list = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/therapist/'.$user_json->uid.'/true'))->treatments;
  $num_patients = count($patients_list);

  function getUserFromUid($uid) {
    return json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $uid));
  }

?>

<html>
<meta charset="utf-8">
  <head>
    <title>Compose Document</title>
    <link href="../css/main.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  </head>

  <body class="newdoc">
    <?php include '../sidebar.php' ?>

    <div class="shifted">
      <h1>Compose a document</h1>
      <hr style="margin-top:-15px">
      <form id="documentForm" class="profile-form" name="profile-form" method="post" action="managedoc.php">
        <table class="newdoc-table">
          <tr>
            <td class="first-col">Associated Patient:</td>
            <td>
              <select name="document-associated-patient">
                <?php for ($i = 0; $i < $num_patients; $i++) { 
                  $patient = getUserFromUid($patients_list[$i]->patientId); ?>
                  <option value="<?php echo $patient->uid ?>">
                    <?php
                      echo $patient->firstname." ".$patient->lastname;
                    ?>
                  </option>
                <?php } ?>
              </select>
            </td>
          </tr>
          <tr>
            <td class="first-col">Title:</td>
            <td><input name="document-title" type="text"/></td>
          </tr>
          <tr>
            <td class="first-col">Notes:</td>
            <td><textarea name="document-notes" class="newdoc-text"></textarea></td>
          </tr>
          <tr>
            <td class="first-col">Attach Records:</td>
            <td><a href="">View Records</a></td>
          </tr>
        </table>
    </form>
      <button name="save-form">Save</button>
      <button onclick="window.location.href='../main.php'">Cancel</button>
    </div>

    <script>
      $(document).ready(function() {
        $("button[name='save-form'").click(function() {
          var title = $('input[name="document-title"]').val().trim();
          var notes = $('textarea[name="document-notes"]').val().trim();
          
          if (title === "" || notes === "") {
            alert("Please check for any empty fields");
          } else {
            $("#documentForm").submit();
          }
        });
      });
    </script>
    
  </body>

</html>
