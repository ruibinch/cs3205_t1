<?php
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';

    // Checks if the current working directory is the therapist subfolder
    if (substr_compare(getcwd(), "therapist", -strlen("therapist")) === 0) {
        $jwt_link = "../util/jwt.php";
        $therapist_home = "../main.php";
        $therapist_patient_list = "patients.php";
        $therapist_compose_doc = "composedoc.php";
        $therapist_manage_doc = "managedoc.php";
        $therapist_profile = "../update.php";
        $therapist_logout = "../logout.php";
    } else {
        $jwt_link = "util/jwt.php";
        $therapist_home = "main.php";
        $therapist_patient_list = "therapist/patients.php";
        $therapist_compose_doc = "therapist/composedoc.php";
        $therapist_manage_doc = "therapist/managedoc.php";
        $therapist_profile = "update.php";
        $therapist_logout = "logout.php";
    }

    if (substr_compare(getcwd(), "patient", -strlen("patient")) === 0) {
        $jwt_link = "../util/jwt.php";
        $patient_home = "../main.php";
        $patient_therapist_list = "therapists.php";
        $patient_documents = "records.php";
        $patient_profile = "../update.php";
        $patient_logout = "../logout.php";
    } else {
        $jwt_link = "util/jwt.php";
        $patient_home = "main.php";
        $patient_therapist_list = "patient/therapists.php";
        $patient_documents = "patient/records.php";
        $patient_profile = "update.php";
        $patient_logout = "logout.php";
    }

    include_once $jwt_link;
    $result = WebToken::verifyToken($_COOKIE["jwt"]);
    $user_type = $result->istherapist ? "therapist" : "patient";


    if ($user_type === "therapist") {
		echo
        '<div class="sidebar">
			<a href="'.$therapist_home.'" style="text-decoration:none">Home</a><br><hr>
			<a href="'.$therapist_patient_list.'" style="text-decoration:none">Patients</a><br><hr>
			<a href="'.$therapist_compose_doc.'" style="text-decoration:none">Compose</a><br><hr>
			<a href="'.$therapist_manage_doc.'" style="text-decoration:none">Documents</a><br><hr>
			<a href="'.$therapist_profile.'" style="text-decoration:none">Profile</a><br><hr>
			<a href="'.$therapist_logout.'" style="text-decoration:none">Logout</a><br><hr>
		</div>';
	} else if ($user_type === "patient") { 
		echo
		'<div class="sidebar">
			<a href="'.$patient_home.'" style="text-decoration:none">Home</a><br><hr>
			<a href="'.$patient_therapist_list.'" style="text-decoration:none">Therapists</a><br><hr>
			<a href="'.$patient_documents.'" style="text-decoration:none">Records</a><br><hr>
			<a href="'.$patient_profile.'" style="text-decoration:none">Profile</a><br><hr>
			<a href="'.$patient_logout.'" style="text-decoration:none">Logout</a><br><hr>
		</div>';
	}

?>
