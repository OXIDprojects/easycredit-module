<?php

require_once 'base/testclient.php';


$webshopId = '2.de.9999.10003';

$endpoint = "/v1/texte/repraesentativesBeispiel/$webshopId";

$client = new TestClient($endpoint, 'GET');
echo($client->execute());