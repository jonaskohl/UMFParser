<?php
    require("../UMFParser.php");

    $file = file_get_contents("sample.json");
    $object = json_decode($file);

    $builder = new UMF_Builder;
    $UMF = $builder->build($object);

    header("Content-Type: text/plain");
    print($UMF);
?>