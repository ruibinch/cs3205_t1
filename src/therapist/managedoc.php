<?php

    $user_type = "therapist";
    $documents_list = array("Document1", "Document2", "Document3", "Document4", "Document5", "Document6");
    $num_documents = count($documents_list);

?>

<html>
<meta charset="utf-8">

    <head>
        <title>Manage Documents</title>
        <link href="../css/main.css" rel="stylesheet">
    </head>

    <body>
        <?php include '../sidebar.php' ?>

        <div class="shifted">
            <h1>You have <?php echo $num_documents ?> documents.</h1>  
            <hr style="margin-top:-15px">

            <table class="main-table">
                <tr>
                    <th class="first-col">S/N</th>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Patient</th>
                    <th>Owner</th>
                    <th class="last-col">Date</th>
                </tr>
                <?php for ($i = 0; $i < $num_documents; $i++) { ?>
                    <tr>
                        <td class="first-col"><?php echo ($i + 1) . "." ?></td>
                        <td></td>
                        <td><?php echo $documents_list[$i] ?></td>
                        <td style="width:10%">.mp3</td>
                        <td></td>
                        <td></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </body>
</html>
