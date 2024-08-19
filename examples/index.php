<?php
require __DIR__. './../vendor/autoload.php';
use PassNinja\PassNinjaClient;

CONST ACCOUNTID = '**your-account-id**';
CONST APIKEY = '**your-api-key**';

$passninja = new PassNinjaClient(ACCOUNTID, APIKEY);

// Create a pass
$myPass = $passninja->pass['create']('ptk_0x2', [
    "icon-url" => "passninja.com",
    "nfc-message" => "blah",
    "member-name" => "Scott Tiger",
    "altitude" => "0",
    "latitude" => "25.7940827",
    "longitude" => "-80.2209766",
    "location-text" => "home",
    "max-distance" => "50",
    "relevant-date" => "2025-01-01T19:25:08+00:00",
    "expiration-date" => "2025-01-01T19:25:08+00:00",
]);
echo "<pre>================ Create Pass ======================";
print_r($myPass);
echo "</pre>";

// Finds issued passes for a given pass template key
$passInfo = $passninja->pass['get']($myPass['passType'], $myPass['serialNumber']);
echo "<pre>================ Get Pass ======================";
print_r($passInfo);
echo "</pre>";

// Fetch record to update the pass
$myPass = $passninja->pass['put']($myPass['passType'], $myPass['serialNumber'], [
    "icon-url" => "passninja.com",
    "nfc-message" => "blah",
    "member-name" => "Scott Tiger",
    "altitude" => "0",
    "latitude" => "25.7940827",
    "longitude" => "-80.2209766",
    "location-text" => "office",
    "max-distance" => "100",
    "relevant-date" => "2025-01-02T19:25:08+00:00",
    "expiration-date" => "2025-01-02T19:25:08+00:00",
]);
echo "<pre>================ Update Pass ======================";
print_r($myPass);
echo "</pre>";

// Delete the pass
$deletedPassSerialNumber = $passninja->pass['delete']($myPass['passType'], $myPass['serialNumber']);
echo 'Pass deleted. serial_number: '.$deletedPassSerialNumber;