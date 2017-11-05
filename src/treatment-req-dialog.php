<?php

    include_once 'util/ssl.php';

    if (isset($_POST['treatmentId'])) {
        $treatmentId = $_POST['treatmentId'];
    }

    $treatment = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/treatment/' . $treatmentId));
    $patient = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/user/uid/public/' . $treatment->patientId));

?>

<table width="50%" style="margin-left:20px">
    <tr>
        <td width="25%">Sex:</td>
        <td><?php echo ($patient->sex === "M") ? "Male" : "Female" ?></td>
    </tr>
    <tr>
        <td width="25%">Phone:</td>
        <td><?php echo htmlspecialchars($patient->phone) ?></td>
    </tr>
</table>
<br/><br/><br/>
<div>
    <?php if ($treatment->currentConsent) {
        echo "The patient has <b>granted consent</b> to view his/her <b>current records</b>.";
    } else {
        echo "The patient has <b>not granted consent</b> to view his/her <b>current records</b>.";
    } ?>
</div><br/>
<div>
    <?php if ($treatment->futureConsent) {
        echo "The patient has <b>granted consent</b> to view his/her <b>future records</b> by default.";
    } else {
        echo "The patient has <b>not granted consent</b> to view his/her <b>future records</b> by default.";
    } ?>
</div>