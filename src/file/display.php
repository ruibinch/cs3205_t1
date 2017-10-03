<?php 
    //TODO: Add file type checking
    $fileType = $result->type;
    //TODO: Add different display method
    if ($fileType == "image") {
        echo 'An image';
    } elseif ($fileType == "movie") {
        echo 'A movie';
    } elseif ($fileType == "timeseries") {
        echo 'A chart';
    } elseif ($fileType == "document") {
        echo 'A document';
    } else {
        echo 'Error: unknown file type!';
    }
?>>