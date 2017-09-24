<?php
  if (isset($_POST["user_type"])) {
    $user_type = $_POST["user_type"];
  } else if (isset($_SESSION["user_type"])) {
    $user_type = $_SESSION["user_type"];
  }

  echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';

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

  if (substr_compare(getcwd(), "patient", -strlen("patient")) === 0) {
  	$patient_home = "../main.php";
  	$patient_therapist_list = "therapists.php";
  	$patient_documents = "viewdoc.php";
  	$patient_profile = "../update.php";
  	$patient_logout = "../login.php";
  } else {
  	$patient_home = "main.php";
  	$patient_therapist_list = "patient/therapists.php";
  	$patient_documents = "patient/viewdoc.php";
  	$patient_profile = "update.php";
  	$patient_logout = "login.php";
  }
  echo '';
  if ($user_type == "therapist") {
		echo
		'<div class="sidebar">
			<form class="sidebar-searchbar-form">
	  			<span class="searchbar-icon"><i class=\'fa fa-search\'></i></span>
	  			<input class="sidebar-searchbar" type="text" name="search" placeholder="Search..">
			</form><br>
			<a href="'.$therapist_home.'" style="text-decoration:none">Home</a><br><hr>
			<a href="'.$therapist_patient_list.'" style="text-decoration:none">Patient</a><br><hr>
			<a href="'.$therapist_compose_doc.'" style="text-decoration:none">Compose</a><br><hr>
			<a href="'.$therapist_manage_doc.'" style="text-decoration:none">Documents</a><br><hr>
			<a href="'.$therapist_profile.'" style="text-decoration:none">Profile</a><br><hr>
			<a href="'.$therapist_logout.'" style="text-decoration:none">Logout</a><br><hr>
		</div>';
	} else if ($user_type == "patient") { 
		echo
		'<div class="sidebar">
			<form class="sidebar-searchbar-form">
	  			<span class="searchbar-icon"><i class=\'fa fa-search\'></i></span>
	  			<input class="sidebar-searchbar" type="text" name="search" placeholder="Search..">
			</form><br>
			<a href="'.$patient_home.'" style="text-decoration:none">Home</a><br><hr>
			<a href="'.$patient_documents.'" style="text-decoration:none">Records</a><br><hr>
			<a href="'.$patient_profile.'" style="text-decoration:none">Profile</a><br><hr>
			<a href="'.$patient_therapist_list.'" style="text-decoration:none">Therapists</a><br><hr>
			<a href="'.$patient_logout.'" style="text-decoration:none">Logout</a><br><hr>
		</div>';
	}

?>
