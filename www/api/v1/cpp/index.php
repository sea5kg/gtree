<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    $data = array("error" => "Expected only POST request");
    $response_data = json_encode($data);
    echo json_encode($response_data, JSON_PRETTY_PRINT);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$payload = json_encode($data, JSON_PRETTY_PRINT);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://web-cpp:10555/api/');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload)
));
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$response = curl_exec($ch);

$http_code = 500;

if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    // Get information about the transfer
    $info = curl_getinfo($ch);
    $http_code = $info['http_code']; // or curl_getinfo($ch, CURLINFO_HTTP_CODE);
}

http_response_code($http_code);

curl_close($ch);
echo $response;
exit();
