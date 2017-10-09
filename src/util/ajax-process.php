<?php

    if (isset($_POST['patientId']) && isset($_POST['therapistId'])) {
        //echo $_POST['patientId'] . " " . $_POST['therapistId']; 
        echo createTreatmentReq($_POST['patientId'], $_POST['therapistId']);
    } else if (isset($_POST['acceptTreatmentId'])) {
        echo acceptTreatmentReq($_POST['acceptTreatmentId']);
    } else if (isset($_POST['rejectTreatmentId'])) {
        echo removeTreatmentReq($_POST['rejectTreatmentId']);
    } else if (isset($_POST['removeTreatmentId'])) {
        echo removeTreatmentReq($_POST['removeTreatmentId']);
    }

    function createTreatmentReq($patientId, $therapistId) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/create/' . $patientId . '/' . $therapistId));
        return $response->result;
    }

    //TODO: add CSRF validation
    function acceptTreatmentReq($treatmentId) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/update/' . $treatmentId));
        return $response->result;
    }

    function removeTreatmentReq($treatmentId) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/delete/' . $treatmentId));
        return $response->result;
    }

?>