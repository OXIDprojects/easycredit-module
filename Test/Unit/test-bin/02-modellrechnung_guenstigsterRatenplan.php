<?php

require_once 'base/testclient.php';


$webshopId = '2.de.9999.10003';
$finanzierungsbetrag = 1000;

$queryParams = [
    'finanzierungsbetrag' => $finanzierungsbetrag,
    'webshopId' => $webshopId
];

$endpoint = '/v1/modellrechnung/guenstigsterRatenplan?' . http_build_query($queryParams);

$client = new TestClient($endpoint, 'GET');
echo($client->execute());