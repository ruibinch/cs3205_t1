<?php
  $user = "User";
	$therapists_list = array("John Smith", "Caitlyn Jenner", "Taylor Swift", "Robert Downey Jr.");
  $num_therapists = count($therapists_list);
  $user_type = "patient";
?>

<html>
<meta charset="utf-8">

  <head>
    <title>Welcome, <?php echo $user ?></title>
    <link href="../css/main.css" rel="stylesheet">
  </head>

  <body>
  	<?php include '../sidebar.php' ?>
	<div class="shifted">
    <h1>Therapists you are assigned to (<?php echo $num_therapists ?>):</h1>
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
        <th>Contact</th>
        <th class="last-col">Remove</th>
      </tr>
      <?php for ($i = 0; $i < $num_therapists; $i++) { ?>
        <tr>
          <td class="first-col"><?php echo ($i + 1) . "." ?></td>
          <td valign="bottom">
            <form method="post" action="managet.php">
              <input name="therapist_search" value="<?php echo $therapists_list[$i] ?>" type="hidden">
              <button class="list-button"><?php echo $therapists_list[$i] ?></button>
            </form>
          </td>
          <td></td>
          <td></td>
          <td></td>
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
