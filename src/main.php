<?php
include_once 'util/jwt.php';
ini_set("allow_url_fopen", 1); // allow connection to DB server

/*
 * // Get JSON object of user
 * if (isset($_SESSION['user_json'])) {
 * $user_json = $_SESSION['user_json'];
 * $_SESSION['user_json'] = $user_json;
 * }
 *
 * // Get user type
 * if (isset($_SESSION['user_type'])) {
 * $user_type = $_SESSION['user_type'];
 * }
 */

// TODO: change the dummy key here to the real key
$result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
$user_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $result->uid));
$user_type = $result->istherapist ? "therapist" : "patient";

if ($user_type === "patient") {
    $user = "Patient";
    // Populate notifications list; TODO this part
    $treatment_pending_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/patient/' . $user_json->uid . '/false'));
    if (isset($treatment_pending_json->treatments)) {
        $treatment_pending = $treatment_pending_json->treatments;
    }
    if (isset($treatment_pending)) {
        $num_notifications = count($treatment_pending);
    } else {
        $num_notifications = 0;
    }
    
    // Populate therapist list
    $therapists_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/patient/' . $user_json->uid . '/true'));
    $therapists_list = $therapists_list_json->treatments;
    $_SESSION['therapists_list'] = $therapists_list;
    $num_therapists = count($therapists_list);
} else if ($user_type === "therapist") {
    $user = "Therapist";
    // Populate notifications list; TODO - include notifications for documents
    $treatment_reqs_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/therapist/' . $user_json->uid . '/false'));
    if (isset($treatment_reqs_json->treatments)) { // there are pending treatment requests
        $treatment_reqs = $treatment_reqs_json->treatments;
    }
    if (isset($treatment_reqs)) {
        $num_notifications = count($treatment_reqs);
    } else {
        $num_notifications = 0;
    }
    
    // Populate patients list
    $patients_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/therapist/' . $user_json->uid . '/true'));
    $patients_list = $patients_list_json->treatments;
    $_SESSION['patients_list'] = $patients_list;
    $num_patients = count($patients_list);
}

// TODO - update from DB API
$documents_list = array(
    "Document1",
    "Document2",
    "Document3",
    "Document4",
    "Document5",
    "Document6"
);
$num_documents = count($documents_list);

// Retrieves the user JSON object based on the uid
function getJsonFromUid($uid)
{
    $user_json_tmp = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $uid));
    return $user_json_tmp;
}

?>

<html>
<meta charset="utf-8">

<head>
<title>Healthcare System</title>
<link href="css/main.css" rel="stylesheet">
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>

<body>
        <?php include 'sidebar.php' ?>
        <div class="shifted">
		<h1>Welcome, <?php echo (isset($user_json->firstname) ? $user_json->firstname : $user) ?></h1>
		<hr style="margin-top: -15px">
		<div class="tab">
			<button class="tablinks active"
				onclick="openTab(event, 'Notifications')">Notifications</button>
                    <?php if ($user_type === "patient") { ?>
                        <button class="tablinks"
				onclick="openTab(event, 'Therapist')">Therapists</button>
                    <?php } else { ?>
                        <button class="tablinks"
				onclick="openTab(event, 'Patient')">Patients</button>
                    <?php } ?>
                    <button class="tablinks"
				onclick="openTab(event, 'Documents')">Documents</button>
		</div>

		<div id="Notifications" class="tabcontent" style="display: block;">
			<h3>You have <?php echo $num_notifications ?> notification<?php if ($num_notifications != 1) { ?>s<?php } ?>.</h3>
			<table class="main-table">
                        <?php
                        
if ($user_type === "therapist" && isset($treatment_reqs)) {
                            for ($i = 0; $i < count($treatment_reqs); $i ++) {
                                $patient_json = getJsonFromUid($treatment_reqs[$i]->patientId);
                                ?>
                                <tr>
					<td><?php echo "Treatment request from ".$patient_json->firstname." ".$patient_json->lastname ?></td>
					<td class="last-col"><button id="acceptTreatmentReq"
							value="<?php echo $treatment_reqs[$i]->id ?>">Accept</button></td>
					<td class="last-col"><button id="rejectTreatmentReq"
							value="<?php echo $treatment_reqs[$i]->id ?>">Reject</button></td>
				</tr>
                            <?php
                            
}
                        } else if ($user_type === "patient" && isset($treatment_pending)) { // user is a patient
                            for ($i = 0; $i < count($treatment_pending); $i ++) {
                                $therapist_json = getJsonFromUid($treatment_pending[$i]->therapistId);
                                ?>
                                    <tr>
					<td><?php echo "Treatment request pending approval from ".$therapist_json->firstname." ".$therapist_json->lastname ?></td>
				</tr>
                                <?php
                            
}
                        }
                        ?>
                    </table>
		</div>

		<div id="Therapist" class="tabcontent">
			<h3>You are assigned to <?php echo $num_therapists ?> therapist<?php if ($num_therapists != 1) { ?>s<?php } ?>.</h3>
			<table class="main-table">
                        <?php
                        
for ($i = 0; $i < $num_therapists; $i ++) {
                            $therapist_json = getJsonFromUid($therapists_list[$i]->therapistId);
                            ?>
                            <tr>
					<td class="first-col"><?php echo ($i + 1)."." ?></td>
					<td><?php echo $therapist_json->firstname." ".$therapist_json->lastname ?></td>
				</tr>
                        <?php } ?>
                    </table>
		</div>

		<div id="Patient" class="tabcontent">
			<h3>You are assigned to <?php echo $num_patients ?> patient<?php if ($num_patients != 1) { ?>s<?php } ?>.</h3>
			<table class="main-table">
                        <?php
                        
for ($i = 0; $i < $num_patients; $i ++) {
                            $patient_json = getJsonFromUid($patients_list[$i]->patientId);
                            ?>
                            <tr>
					<td class="first-col"><?php echo ($i + 1)."." ?></td>
					<td><?php echo $patient_json->firstname." ".$patient_json->lastname ?></td>
				</tr>
                        <?php } ?>
                    </table>
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
					<td class="first-col"><?php echo ($i + 1)."." ?></td>
					<td><?php echo $documents_list[$i] ?></td>
					<td style="width: 10%">.mp3</td>
					<td></td>
				</tr>
                        <?php } ?>
                    </table>
		</div>
		</hr>
	</div>

	<script>

            $(document).ready(function () {

                $('#acceptTreatmentReq').click(function() {
                    $.ajax({
                        type: "POST",
                        url: "util/ajax-process.php",
                        data: { "acceptTreatmentId": $(this).val() }
                    }).done(function(response) {
                        if (response == 1) {
                            alert("Treatment request accepted"); // TODO - change this to a nicer dialog box
                        } else {
                            alert("Error in processing treatment request");
                        }
                        location.reload();
                    });
                });

                $('#rejectTreatmentReq').click(function() {
                    $.ajax({
                        type: "POST",
                        url: "util/ajax-process.php",
                        data: { "rejectTreatmentId": $(this).val() }
                    }).done(function(response) {
                        if (response == 1) {
                            alert("Treatment request rejected");
                        } else {
                            alert("Error in processing treatment request");
                        }
                        location.reload();
                    });
                });
            });

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

</body>
</html>