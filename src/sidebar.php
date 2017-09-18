<?php
  if (isset($_POST["user_type"])) {
    $user_type = $_POST["user_type"];
  } else if (isset($_SESSION["user_type"])) {
    $user_type = $_SESSION["user_type"];
  }

  // Checks if the current working directory is the therapist subfolder
  if (substr_compare(getcwd(), "therapist", -strlen("therapist")) === 0) {
  	$therapist_home = "../main.php";
  	$therapist_patient_list = "patients.php";
  	$therapist_compose_doc = "composedoc.php";
  	$therapist_manage_doc = "managedoc.php";
  	$therapist_profile = "../update.php";
  	$therapist_logout = "../login.php";
  } else {
  	$therapist_home = "main.php";
  	$therapist_patient_list = "therapist/patients.php";
  	$therapist_compose_doc = "therapist/composedoc.php";
  	$therapist_manage_doc = "therapist/managedoc.php";
  	$therapist_profile = "update.php";
  	$therapist_logout = "login.php";
  }

  if ($user_type == "therapist") {
		echo
		'<div class="sidebar">
			Menu<br>
			<form>
	  			<input type="text" name="search" placeholder="Search..">
			</form>
			<a href="'.$therapist_home.'" style="text-decoration:none">Home</a><br>
			<a href="'.$therapist_patient_list.'" style="text-decoration:none">Patient List</a><br>
			<a href="'.$therapist_compose_doc.'" style="text-decoration:none">Compose Document</a><br>
			<a href="'.$therapist_manage_doc.'" style="text-decoration:none">Manage Documents</a><br>
			<a href="'.$therapist_profile.'" style="text-decoration:none">Profile</a><br>
			<a href="'.$therapist_logout.'" style="text-decoration:none">Logout</a><br>
		</div>';
	} else if ($user_type == "patient") { 
		echo
		'<div class="sidebar">
			Menu<br>
			<form>
	  			<input type="text" name="search" placeholder="Search..">
			</form>
			<a href="main.php" style="text-decoration:none">Home</a><br>
			<a href="viewdoc.php" style="text-decoration:none">Records</a><br>
			<a href="update.php" style="text-decoration:none">Profile</a><br>
			<a href="managet.php" style="text-decoration:none">Therapists</a><br>
			<a href="login.php" style="text-decoration:none">Logout</a><br>
			<a href="searcht.php" style="text-decoration:none">Search</a><br>
		</div>';
	}

?>