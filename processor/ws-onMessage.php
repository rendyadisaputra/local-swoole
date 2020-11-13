<?php

// $server is WS-SERVER Variable
// $frame is WS-Frame Requester.
// $json is data
$MongoModel = null;
// var_dump($wsConfigs);
if (!isset($wsConfigs['MongoDB-Connector'])) {
    include_once __DIR__.'/processor/Models/MongoModel.php';

    $MongoModel = $wsConfigs['MongoDB-Connector'] = new MongoModel($wsConfigs['mongoDB']);
} else {
    $MongoModel = $wsConfigs['MongoDB-Connector'];
}

$mongoTable = $MongoModel->useTable('marketplace_logs');

$json = json_decode($frame->data, 1);
$sendError = function ($message = 'error', $errorCode = 402) use ($server, $frame) {
    $data = [
        'error' => 1,
        'message' => $message,
        'error_code' => $errorCode,
    ];
    $server->push($frame->fd, json_encode($data));
};

if (is_null($json)) {
    $sendError('invalid json Format', 403);

    return false;
}

// var_dump($json);
if (!isset($json['routing'])) {
    $sendError('no routing found', 403);

    return false;
}
// Find from the existing first based on the URL
$findData = $mongoTable->DBfind(['url' => $json['data']['url']]);
if (count($findData['result']) == 0) {
    $response = $mongoTable->DBinsert($json['data']);
    $server->push($frame->fd, json_encode($response));
} else {
    $server->push($frame->fd, json_encode(
        ['message' => 'data already exists', 'error' => 1, 'error_code' => 405]
    ));
}
