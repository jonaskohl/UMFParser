<?php
class UMF_InvalidFormatException extends Exception {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

class UMF_Parser {

    private const fileVersion = "1.0";
    public const version = "1.0.0";

    public function getParserFileVersion() {
        return self::fileVersion;
    }

    private function _boolval($str) {
        if (strtolower($str) == "true") return true;
        if (strtolower($str) == "false") return false;
    }

    private function getFileVersion($str) {
        $re = '/^#!%UMF File Format ([0-9]+)\.([0-9]+)\n/';
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        return implode(".", array($matches[0][1], $matches[0][2]));
    }

    private function getDataType($line) {
        if (trim($line) == "") {
            return "ignore";
        }
        $re = '/^(number|string|boolean|array<number>|array<string>|array<boolean>|#)/';
        preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
        if ($matches) {
            return $matches[0][1];
        }
        return "invalid";
    }

    private function parseStringLine($line) {
        $re = '/^string\s+"(.*)"\s+"(.*)"\s*$/';
        preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
        return array("name"=>$matches[0][1], "value"=>$matches[0][2], "type"=>"string");
    }

    private function parseNumberLine($line) {
        $re = '/^number\s+"(.*)"\s+([0-9.]+)\s*$/';
        preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
        return array("name"=>$matches[0][1], "value"=>floatval($matches[0][2]), "type"=>"number");
    }

    private function parseBooleanLine($line) {
        $re = '/^boolean\s+"(.*)"\s+(true|false)\s*$/';
        preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
        return array("name"=>$matches[0][1], "value"=>$this->_boolval($matches[0][2]), "type"=>"boolean");
    }

    private function parseArrayNumberLine($line) {
        $re = '/^array<number>\s+"(.*)"\s+\((([0-9.]+\s*,\s*)*[0-9.]+)\)$/';
        preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
        $val = json_decode("[".$matches[0][2]."]");
        return array("name"=>$matches[0][1], "value"=>$val, "type"=>"array");
    }

    private function parseArrayStringLine($line) {
        $re = '/^array<string>\s+"(.*)"\s+\((("(.*)"\s*,\s*)*"(.*)")\)$/';
        preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
        $val = json_decode("[".$matches[0][2]."]");
        return array("name"=>$matches[0][1], "value"=>$val, "type"=>"array");
    }

    private function parseArrayBooleanLine($line) {
        $re = '/^array<boolean>\s+"(.*)"\s+\((((true|false)\s*,\s*)*(true|false))\)$/';
        preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);
        $val = json_decode("[".$matches[0][2]."]");
        return array("name"=>$matches[0][1], "value"=>$val, "type"=>"array");
    }

    private function parseCommands($str, &$parsedObject) {
        $parsedArray = array();
        $parsedObject = array();
        $lines = explode("\n", $str);
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $datatype = $this->getDataType($line);
            switch ($datatype) {
                case "string":
                    $v = $this->parseStringLine($line);
                    if (defined("UMF_PARSER_DEBUG_ARRAY")) array_push($parsedArray, $v);
                    $parsedObject[$v["name"]] = $v["value"];
                    break;
                case "number":
                    $v = $this->parseNumberLine($line);
                    if (defined("UMF_PARSER_DEBUG_ARRAY")) array_push($parsedArray, $v);
                    $parsedObject[$v["name"]] = $v["value"];
                    break;
                case "boolean":
                    $v = $this->parseBooleanLine($line);
                    if (defined("UMF_PARSER_DEBUG_ARRAY")) array_push($parsedArray, $v);
                    $parsedObject[$v["name"]] = $v["value"];
                    break;
                case "array<number>":
                    $v = $this->parseArrayNumberLine($line);
                    if (defined("UMF_PARSER_DEBUG_ARRAY")) array_push($parsedArray, $v);
                    $parsedObject[$v["name"]] = $v["value"];
                    break;
                case "array<string>":
                    $v = $this->parseArrayStringLine($line);
                    if (defined("UMF_PARSER_DEBUG_ARRAY")) array_push($parsedArray, $v);
                    $parsedObject[$v["name"]] = $v["value"];
                    break;
                case "array<boolean>":
                    $v = $this->parseArrayBooleanLine($line);
                    if (defined("UMF_PARSER_DEBUG_ARRAY")) array_push($parsedArray, $v);
                    $parsedObject[$v["name"]] = $v["value"];
                    break;
                case "#":
                case "ignore":
                    // line empty or comment, do nothing
                    break;
                case "invalid":
                default:
                    // unknown type, throw error
                    throw new UMF_InvalidFormatException("The type in line $i is invalid!");
                    break;
            }
        }
        if (defined("UMF_PARSER_DEBUG_ARRAY"))
            return $parsedArray;
    }

    public function parse($string) {
        $fv = $this->getFileVersion($string);
        if ($fv) {
            if ($fv == self::fileVersion) {
                $this->fileVersion = $fv;
                $this->parseCommands($string, $o);
                $this->parseResult = $o;
            } else {
                throw new UMF_InvalidFormatException("The version of the UMF file is incompatible with the parser [{$fv} != " . self::fileVersion . "].");
            }
        } else {
            throw new UMF_InvalidFormatException("The format of the UMF file is incorrect.");
        }
    }

}


class UMF_Builder {

    private const types = array(
        "boolean"=>"boolean",
        "integer"=>"number",
        "double"=>"number",
        "string"=>"string",
        "array"=>"array"
    );

    private function getArrayType($array) {
        $lastType = null;
        foreach ($array as $val) {
            $type = UMF_Builder::types[gettype($val)];
            if ($lastType != null) {
                if ($lastType != $type) {
                    throw new UMF_InvalidFormatException("Unknown type [$type]");
                }
            }
            $lastType = $type;
        }
        return $lastType;
    }

    private function boolToString($bool) {
        return $bool ? "true" : "false";
    }

    private function boolArrayToStringArray($array) {
        $outArray = array();
        foreach ($array as $value) {
            array_push($outArray, $this->boolToString($value));
        }
        return $outArray;
    }

    public function build($object) {
        $outputString = "";

        $parser = new UMF_Parser;

        $ver = $parser::version;
        $fver = $parser->getParserFileVersion();
        $outputString .= "#!%UMF File Format $fver\n";
        $outputString .= "\n# Generated by UMFParser $ver on " . date("c") . "\n\n";

        foreach ($object as $key => $value) {
            $type = UMF_Builder::types[gettype($value)];
            if ($type == "array") {
                $arrayType = $this->getArrayType($value);
                $type = "array<$arrayType>";
            }

            $valstring = "";

            switch ($type) {
                case "string":
                    $valstring = "\"$value\"";
                    break;
                case "number":
                    $valstring = $value;
                    break;
                case "boolean":
                    $valstring = $this->boolToString($value);
                    break;
                case "array<number>":
                    $valstring = "(". implode(", ", $value) . ")";
                    break;
                case "array<boolean>":
                    $valstring = "(". implode(", ", $this->boolArrayToStringArray($value)) . ")";
                    break;
                case "array<string>":
                    $valstring = "(\"". implode("\", \"", $value) . "\")";
                    break;
                default:
                    break;
            }

            $outputString .= "$type \"$key\" $valstring\n";
        }

        return $outputString;
    }
}


if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    // called directly
    if (!defined("UMF_PARSER_FORCE_DISABLE_INFO_MESSAGE")) {
        if (!headers_sent()) {
            header("Content-Type: text/plain");
            print("UMFParser " . UMF_Parser::version . "\nCopyright (c) 2017 Jonas Kohl. All rights reserved.\nhttp://jonaskohl.de\n");
        }
    }
} else {
    // included/required
}
