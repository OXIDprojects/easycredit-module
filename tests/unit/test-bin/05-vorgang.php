<?php

require_once 'base/testclient.php';


$webshopId = '2.de.9999.10003';
$webshopToken = 'ybz-3v9-e4w';

$headers = [
    "Content-Type: application/json",
    "tbk-rk-shop: $webshopId",
    "tbk-rk-token: $webshopToken"
];

$endpoint = '/v1/vorgang';

$data = file_get_contents(__DIR__ . '/data/05-vorgang.json');

$client = new TestClient($endpoint, 'POST', $data, $headers);
echo($client->execute());