<?php

date_default_timezone_set('Europe/Vilnius');

header('Content-Type: application/json; charset=UTF-8');

function array_get(&$var, $default=null) {
    return isset($var) ? $var : $default;
}

function error_message($message)
{
    http_response_code(400);
    $error_json = array('error' => $message);
    echo json_encode($error_json);
    exit(0);
}

function success_message($message)
{
    http_response_code(200);
    $error_json = array('success' => $message);
    echo json_encode($error_json);
    exit(0);
}

if (array_get($_SERVER['REQUEST_METHOD']) !== 'POST') {
    error_message('Request is not POST.');
}

$json_request_string = file_get_contents('php://input');
if (! $json_request_string ) {
    error_message('Request is empty.');
}

$json_request = @json_decode($json_request_string);
if (! $json_request) {
    error_message('Unable to decode JSON request.');
}

$report_name = array_get($json_request->{'name'});
$report_email = array_get($json_request->{'email'});
$report_message = array_get($json_request->{'message'});
$report_device_model = array_get($json_request->{'device_model'});
$report_device_os = array_get($json_request->{'device_os'});
$report_app_name = array_get($json_request->{'app_name'});
$report_app_version = array_get($json_request->{'app_version'});
$report_build_number = array_get($json_request->{'build_number'});

if (! ($report_name and $report_email and $report_message and $report_device_model
    and $report_device_os and $report_app_version and $report_app_version and $report_build_number )) {

    error_message('One or more of required fields is not set.');
}

define('EMAIL_FROM', 'bugreport@altshiftkeyboard.com');
define('EMAIL_TO', 'info@altshiftkeyboard.com');
define('EMAIL_SUBJECT', 'Alt+Shift bug report: ' . date(DATE_ISO8601));
$additional_headers = 'From: ' . EMAIL_FROM . "\r\n" . 'Reply-To: ' . $report_email;

$message = <<<EOF
Hi,

Here goes a new bug report:

* Name: $report_name
* Email: $report_email
* App name: $report_app_name
* App version: $report_app_version
* Build number: $report_build_number
* Device model: $report_device_model
* Device OS: $report_device_os
* Message:

--
$report_message
--

Thanks,
EOF;

$success = mail(EMAIL_TO, EMAIL_SUBJECT, $message, $additional_headers);
if (! $success) {
    error_message('Unable to save bug report.');
}

success_message('Report sent!');
