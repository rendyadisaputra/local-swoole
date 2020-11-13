<?php

try {
    $MongoClient = new \MongoDB\Driver\Manager('mongodb://192.168.56.4:27017');
    $aggregate = ['aggregate' => 'Email',
        'pipeline' => [],
    'cursor' => new stdClass(),
  ];
    $command = new MongoDB\Driver\Command($aggregate);
    $result = $MongoClient->executeCommand('kita_sahabat_gresik', $command);
} catch (MongoDB\Driver\Exception\Exception $e) {
    var_dump($e);
}
