<?php

    session_start();

    $_SESSION['user_type'] = "patient";
    if (isset($_SESSION['therapists_list'])) {
        $therapists_list = $_SESSION['therapists_list'];
    }

    $num_therapists = count($therapists_list);

    // Retrieves the user JSON object based on the uid
    function getJsonFromUid($uid) {
        $user_json_tmp = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/'.$uid));
        return $user_json_tmp;
    }
?>

<html>
<meta charset="utf-8">

  <head>
    <title>Therapist List</title>
    <link href="../css/main.css" rel="stylesheet">
  </head>

  <body>
  	<?php include '../sidebar.php' ?>
	<div class="shifted">
    <h1>Therapist<?php if ($num_therapists != 1) { ?>s<?php } ?> you are assigned to (<?php echo $num_therapists ?>):</h1>
    <hr style="margin-top:-15px">

    <!--
    <table class="main-table">
      <tr>
        <th class = "first-col">S/N</th>
        <th>Name</th>
        <th>Contact Info</th>
        <th class="last-col">Remove</th>
      </tr>
      <?php for ($i = 0; $i < $num_therapists; $i++) { ?>
        <tr>
          <td class="first-col"><?php echo ($i + 1) . "." ?></td>
          <td><?php echo $therapists_list[$i] ?></td>
          <td></td>
          <td class="last-col"><button onclick="remove_therapist()" style="padding:0; border:none; background:none;">X</button></td>
        </tr>
      <?php } ?>
    </table>
    -->

    <table class="main-table">
      <tr>
        <th class = "first-col">S/N</th>
        <th>Name</th>
        <th>Contact Info</th>
        <th>D.O.B</th>
        <th class="last-col">Remove</th>
      </tr>
      <?php for ($i = 0; $i < $num_therapists; $i++) {
        $therapist_json = getJsonFromUid($therapists_list[$i]->therapistId);
        $therapist_name = $therapist_json->firstname." ".$therapist_json->lastname; ?>
            <input name="therapist_search" value="<?php echo $therapists_list[$i]->therapistId ?>" type="hidden">
            <tr>
                <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                <td valign="bottom"><button class="list-button"><?php echo $therapist_name ?></button></td>
                <td><?php echo $therapist_json->phone[0] ?></td>
                <td><?php echo $therapist_json->dob ?></td>
                <td class="last-col"><button onclick="remove_therapist()" style="padding:0; border:none; background:none;">X</button></td>
            </tr>
      <?php } ?>
    </table>


	</div>
  <script>
      function remove_therapist() {
        confirm("Are you sure you want to stop seeing this therapist?\nHe / She will no longer have access to your medical records.");
      }
  </script>
  </body>


</html>
