
<?php

$secret = 'f2d86911929e3445d0191fcebc488972';
$url = 'http://127.0.0.1:8000/api/callback';

// Payload
$payload = [
    'event_id' => 'evt_' . bin2hex(random_bytes(8)),
    'order_id' => 1,
    'status' => 'refunded', // paid, failed, refunded
    'amount' => '1000.00',
    'currency' => 'USD',
];


$json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

$signature = hash_hmac('sha256', $json, $secret);


$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $json,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Signature: ' . $signature,
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

//curl_close($ch); //Deprecated починаючи з 8.5 якщо не помиляюсь.
//Якщо версія пхп нижче, то повернути.


echo "Payload:\n";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n\nSignature:\n";
echo $signature;

echo "\n\nHTTP Code: {$httpCode}\n";

if ($error) {
    echo "cURL Error: {$error}\n";
} else {
    echo "Response:\n{$response}\n";
}
