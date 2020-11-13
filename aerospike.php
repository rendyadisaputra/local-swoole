<?php

$config = ['hosts' => [['addr' => '192.168.56.4', 'port' => 3000]]];
$db = new Aerospike($config, false);
if (!$db->isConnected()) {
    echo fail("Could not connect to host $HOST_ADDR:$HOST_PORT [{$db->errorno()}]: {$db->error()}");
    exit(1);
}
echo "SUCCESS.\n";
$namespace = 'infosphere';
$table = 'characters';

include_once 'aero-functions/aero-put.php';
