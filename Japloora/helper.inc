<?php

define('JAPLOORA_DOC_ROOT', $_SERVER['DOCUMENT_ROOT']);

/**
 * Write message to Core.log file
 * @param string $message
 * @param string $type
 */
function watchdog($message, $type = NULL) {
    $string = ($type != NULL) ? $type . " :: " . $message : $message;
    return error_log($string);
}

/**
 * Return 404 page
 * @return string
 */
function core_404() {
    json_output(array('page'=>'404'), 404);
}

/**
 * Return 403 page
 * @return string
 */
function core_403() {
    json_output(array('page'=>'403'), 403);
}

/**
 * Magic Function which transform string type "aa:cc,vv:dd" in assiociative array
 * array(aa => cc, vv => dd)
 * @param string $string
 * @param string $line_marker
 * @param string $key_value_separator
 * @return array
 */
function stringToAssiociativ($string, $line_marker, $key_value_separator) {
    $output = array();
    $string_fragments = explode($line_marker, $string);

    foreach ($string_fragments as $string_fragment) {
        $datas = explode($key_value_separator, $string_fragment);

        $output[$datas[0]][] = $datas[1];
    }

    return $output;
}

/**
 * Kill process on error
 * @param array $json_data
 * @param number $http_code
 */
function json_output($json_data = array(), $http_code = 200) {
    http_response_code($http_code);
    die(json_encode($json_data));
}
