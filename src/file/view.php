<?php
    //TODO: Add error warnings
    $oneTimeToken = $_GET[filetoken];
    $curl = curl_init();
    //TODO: save server url to somewhere else
    $serverurl = "cs3205-4-i.comp.nus.edu.sg";
    //TODO: update URL when API is up
    curl_setopt($curl, CURLOPT_URL, $serverurl);
    curl_setopt($curl, CURLOPT_PORT , 80);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    $title = $result->title;
?>


<html>
<meta charset="utf-8">
  <head>
    <title><?php $title?></title>
    <link href="../css/main.css" rel="stylesheet">
  </head>

  <body>
    <?php include '../sidebar.php' ?>
    <?php include 'display.php'?>
  </body>
</html>