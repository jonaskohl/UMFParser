<?php
    require("../UMFParser.php");

    $file = file_get_contents("sample.umf");

    $parser = new UMF_Parser;
    $parser->parse($file);
    $parsed_object = $parser->parseResult;

    header("Content-Type: application/json");
    print(json_encode($parsed_object));
?>