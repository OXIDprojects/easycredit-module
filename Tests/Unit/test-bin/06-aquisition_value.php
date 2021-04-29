<?php

require_once 'base/testclient.php';


$webshopId = '2.de.9999.10003';
$webshopToken = 'ybz-3v9-e4w';

$headers = [
    "Content-Type: application/json",
    "tbk-rk-shop: $webshopId",
    "tbk-rk-token: $webshopToken"
];

$endpoint = '/v1/webshop/' . $webshopId . '/restbetragankaufobergrenze';

$client = new TestClient($endpoint, 'get', null, $headers);
echo($client->execute());