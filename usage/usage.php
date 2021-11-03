<?php

use MirazMac\PclZip\PclZip;

require_once '../vendor/autoload.php';

try {

$pclzip = new PclZip(__DIR__ . '/sample.zip');
$pclzip->extract();
} catch (\Exception $e) {
    r($e);
}
