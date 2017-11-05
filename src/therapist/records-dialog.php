<?php

    include_once '../util/ssl.php';

    if (isset($_POST['patientId'])) {
        $patientId = $_POST['patientId'];
    }
    if (isset($_POST['therapistId'])) {
        $therapistId = $_POST['therapistId'];
    }
    if (isset($_POST['attachRecordsList'])) {
        $attached_records_list = $_POST['attachRecordsList'];
    }

    // Gets the list of consents associated with this therapist
    $consents_list_json = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/consent/user/' . $therapistId));
    $consents_list_status = array();
    if (isset($consents_list_json->consents)) {
        $consents_list = $consents_list_json->consents;
        for ($i = 0; $i < count($consents_list); $i++) {
            $consent = $consents_list[$i];
            $consents_list_status[$consent->rid] = $consent->status;
        }
    }
    
    // Gets the list of records assigned to the specified patient
    $records_list_json = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/record/all/' . $patientId));
    if (isset($records_list_json->records)) {
        $records_list = $records_list_json->records;
    }
    if (isset($records_list)) {
        $num_records = count($records_list);
    } else {
        $num_records = 0;
    }

?>

<table class="main-table">
    <tr>
        <th class = "first-col">S/N</th>
        <th>Last Modified</th>
        <th>Type</th>
        <th>Title</th>
        <th>Actions</th>
        <th class="last-col">Select</th>
    </tr>
    <?php for ($i = 0; $i < $num_records; $i++) {
        $record = $records_list[$i];
        // Only display records which the therapist has consent to view
        if ($consents_list_status[$record->rid]) {
            $checked_status = "";
            if (isset($attached_records_list[$record->rid])) {
                if ($attached_records_list[$record->rid]) {
                    $checked_status = "checked";
                } 
            }
            ?>
            <tr>
                <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                <td><?php echo $record->modifieddate ?></button></td>
                <td><?php echo $record->type ?></td>
                <td><?php echo $record->title ?></td>
                <td><input type="button" value="Details"/></td>;
                <td><input type="checkbox" class="selectRecordCheckbox" value="<?php echo $record->rid ?>" <?php echo $checked_status ?>/></td>
            </tr>
        <?php }
    } ?>
</table>