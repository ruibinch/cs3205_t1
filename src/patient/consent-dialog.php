<?php

    if (isset($_POST['recordId'])) {
        $recordId = $_POST['recordId'];
    }
    if (isset($_POST['patientId'])) {
        $patientId = $_POST['patientId'];
    }

    // Retrieves the user JSON object based on the uid
    function getJsonFromUid($uid) {
        $user_json_tmp = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $uid));
        return $user_json_tmp;
    }

?>

<table width="100%">
    <tr>
        <th style="text-align:center">Name of Therapist</th>
        <th style="text-align:center">Permission</th>
    </tr>
    <?php 
        $therapists_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/patient/' . $patientId . '/true'));
        if (isset($therapists_list_json->treatments)) {
            $therapists_list = $therapists_list_json->treatments;
            for ($i = 0; $i < count($therapists_list); $i++) {
                $therapistId = $therapists_list[$i]->therapistId;
                $therapist_json = getJsonFromUid($therapistId);
                $therapist_name = $therapist_json->firstname . " " . $therapist_json->lastname;

                $therapist_consents_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/consent/user/' . $therapistId));
                $checked_status = "";
                $consentId = "";
                if (isset($therapist_consents_json->consents)) {
                    $therapist_consents = $therapist_consents_json->consents;
                    for ($j = 0; $j < count($therapist_consents); $j++) {
                        $consent = $therapist_consents[$j];
                        if ($consent->rid == $recordId) {
                            $consentId = $consent->consentId;
                            if ($consent->status) {
                                $checked_status = "checked";
                            }
                        }
                    }
                }
                
                echo "<tr>";
                echo "<td style='text-align:center'>" . $therapist_name . "</td>";
                echo "<td style='text-align:center'><input type='checkbox' value='" . $consentId . "' " . $checked_status . "/></td>";
                echo "</tr>";
            }
        }
    ?>
</table>