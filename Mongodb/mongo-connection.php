<?php

$GLOBALS['MongoDBManager'] = null;
try {
    $GLOBALS['MongoDBManager'] = new MongoDB\Driver\Manager('mongodb://192.168.56.4:27017');
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo 'connection to Database was failed';
    var_dump($e);
}
