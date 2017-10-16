<?php
    include_once '../util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
    $user_type = $result->istherapist ? "therapist" : "patient";

    // Gets the list of records assigned to the specified patient
    $records_list_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/records/all/' . $result->uid));
    if (isset($records_list_json->records)) {
        $records_list = $records_list_json->records;
    }
    if (isset($records_list)) {
        $num_records = count($records_list);
    } else {
        $num_records = 0;
    }

?>

<html>
<meta charset="utf-8">

    <head>
        <title>View Records</title>
        <link href="../css/main.css" rel="stylesheet">
    </head>

    <body>
        <?php include '../sidebar.php' ?>
        <div class="shifted">
            <h1>View Record<?php if ($num_records != 1) { ?>s<?php } ?> (<?php echo $num_records ?>):</h1>
            <hr style="margin-top:-15px">

            <table class="main-table" id="recordsTable">
                <tr>
                    <th class = "first-col">S/N</th>
                    <th>Last Modified</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th class="last-col">Actions</th>
                </tr>
                <?php for ($i = 0; $i < $num_records; $i++) {
                    $record = $records_list[$i];
                    $record_id = $record->rid; ?>
                    <tr>
                        <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                        <td><?php echo $record->modifieddate ?></button></td>
                        <td><?php echo $record->type ?></td>
                        <td><?php echo $record->title ?></td>
                        <td><a href="" style="color:blue">View</a></td>
                    </tr>
                <?php } ?>
            </table>

	    </div>

    </body>
</html>
