<?php

    if (isset($_POST['acceptTreatmentId'])) {
        echo acceptTreatmentReq($_POST['acceptTreatmentId']);
    } else if (isset($_POST['rejectTreatmentId'])) {
        echo rejectTreatmentReq($_POST['rejectTreatmentId']);
    }

    //TODO: add CSRF validation
    function acceptTreatmentReq($treatmentId) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/update/'.$treatmentId));
        return $response->result;
    }

    function rejectTreatmentReq($treatmentId) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/delete/'.$treatmentId));
        return $response->result;
    }
    
?>