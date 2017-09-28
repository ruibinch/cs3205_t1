<?php
  session_start();
  if (isset($_POST["user_type"])) {
    $user_type = $_POST["user_type"];
    $_SESSION["user_type"] = $_POST["user_type"];
  } else if (isset($_SESSION["user_type"])) {
    $user_type = $_SESSION["user_type"];
  };

  if ($user_type == "patient") {
    $user = "Patient";
  } else if ($user_type == "therapist") {
    $user = "Therapist";
  }
  $therapists_list = array("John Smith", "Caitlyn Jenner", "Taylor Swift", "Robert Downey Jr.");
  $num_therapists = count($therapists_list);
  $documents_list = array("Document1", "Document2", "Document3", "Document4", "Document5", "Document6");
  $num_documents = count($documents_list);
  $notifications_list = array("Notification1", "Notification2", "Notification3", "Notification4", "Notification5", "Notification6");
  $num_notifications = count($notifications_list);

?>

<html>
<meta charset="utf-8">

  <head>
    <title>Healthcare System</title>
    <link href="css/main.css" rel="stylesheet">
  </head>

  <body>
    <?php include 'sidebar.php' ?>
  <div class="shifted">
    <h1>Welcome, <?php echo $user ?></h1>
    <hr style="margin-top:-15px">
    <div class="tab">
      <button class="tablinks active" onclick="openTab(event, 'Notifications')">Notifications</button>
      <?php if ($user_type == "patient") { ?>
      <button class="tablinks" onclick="openTab(event, 'Therapist')">Therapist</button>
      <?php } else { ?>
      <button class="tablinks" onclick="openTab(event, 'Patient')">Patient</button>
      <?php } ?>
      <button class="tablinks" onclick="openTab(event, 'Documents')">Documents</button>
    </div>

    <div id="Notifications" class="tabcontent" style="display: block;">
        <h3>You have <?php echo $num_notifications ?> notification(s).</h3>
        <table class="main-table">
          <?php for ($i = 0; $i < $num_notifications; $i++) { ?>
            <tr>
              <td><?php echo $notifications_list[$i] ?></td>
              <td class="last-col">X</td>
            </tr>
          <?php } ?>
        </table>
    </div>

    <div id="Therapist" class="tabcontent">
        <h3>You are assigned to <?php echo $num_therapists ?> therapist(s).</h3>

        <table class="main-table">
          <?php for ($i = 0; $i < $num_therapists; $i++) { ?>
            <tr>
              <td class="first-col"><?php echo ($i + 1) . "." ?></td>
              <td><?php echo $therapists_list[$i] ?></td>
            </tr>
          <?php } ?>
        </table>
    </div>

    <div id="Patient" class="tabcontent">
        <h3>You are assigned to <?php echo $num_therapists ?> patient(s).</h3>
        <ul class="main-list main-list-hoverable">
        <?php
          for ($i = 0; $i < $num_therapists; $i++) {
            echo "<li>" . $therapists_list[$i] . "</li>";
          }
        ?>
        </ul>
    </div>

    <div id="Documents" class="tabcontent">
        <h3>You have <?php echo $num_documents ?> documents.</h3>

        <table class="main-table">
          <tr>
            <th class="first-col">S/N</th>
            <th>Title</th>
            <th>Type</th>
            <th class="last-col">Date</th>
          </tr>
          <?php for ($i = 0; $i < $num_documents; $i++) { ?>
            <tr>
              <td class="first-col"><?php echo ($i + 1) . "." ?></td>
              <td><?php echo $documents_list[$i] ?></td>
              <td style="width:10%">.mp3</td>
              <td></td>
            </tr>
          <?php } ?>
        </table>
    </div>

    <script>
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
  </div>
  </body>

</html>
