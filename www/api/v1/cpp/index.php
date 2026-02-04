<?php

header('Content-Type: application/json; charset=utf-8');
http_response_code(200);

echo "111";
$data = json_decode(file_get_contents('php://input'), true);
print_r($data);
// echo $data["operacion"];



// $data = array("method" => "Hagrid", "age" => "36");
// $payload = json_encode($data);
// 
// $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, 'http://web-cpp:10555/api/');
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//     'Content-Type: application/json',
//     'Content-Length: ' . strlen($payload)
// ));
// 
// $response = curl_exec($ch);
// curl_close($ch);


// echo $response;
exit();
