<?php

$wsConfigs = [
    'mongoDB' => [
        'host' => 'mongodb://192.168.56.4:27017',
        'selectedDB' => 'kita_sahabat_gresik',
    ],
];
$userListConnectedIDs = [];
$userListConnectedFDIDs = [];
$server = new Swoole\Websocket\Server('0.0.0.0', 9502);
$userList = [
    'token01' => ['name' => 'nurdiana', 'user_id' => 9],
    'token02' => ['name' => 'aisyah', 'user_id' => 10],
    'token03' => ['name' => 'Rendy', 'user_id' => 11],
    'token04' => ['name' => 'dd', 'user_id' => 12],
    'token05' => ['name' => 'tt', 'user_id' => 13],
];
function readF($fileName)
{
    $myfile = fopen($fileName, 'r') or die('Unable to open file!');
    $fc = fread($myfile, filesize($fileName));
    fclose($myfile);

    return $fc;
}

$server->on('open', function ($ser, $req) use (&$userListConnectedIDs, &$userList) {
    $parseUrl = explode('/', $req->server['request_uri'], 5);
    $api = isset($parseUrl[1]) ? $parseUrl[1] : null;
    $apiKey = isset($parseUrl[3]) ? $parseUrl[3] : null;
    $token = isset($parseUrl[4]) ? $parseUrl[4] : null;
    // var_dump($api, $apiKey, $token, $req->server['request_uri']);
    if ($api == 'wsocket' && $apiKey == 'abcdefgh' && isset($userList[$token])) {
        $userListConnectedIDs[$userList[$token]['user_id']] = [
            'time' => time(),
            'user' => $userList[$token],
            'ws_fd' => $req->fd,
            'token' => $token,
        ];

        $userListConnectedFDIDs[$req->fd] = $token;

        $ser->push($req->fd, json_encode(
        ['error' => 0,
        'message' => "u're connected",
         'code' => 200,
         ]));
    // echo 'connected '.$req->fd;
    } else {
        $ser->push($req->fd, json_encode(
            ['error' => 1, 'message' => 'URI Request not found', 'code' => 404]));
        $ser->disconnect($req->fd);
    }
});

$server->on('message', function ($server, $frame) use (&$userListConnectedIDs, &$userListConnectedFDIDs, &$userList, &$wsConfigs) {
    // echo "received message: {$frame->data}\n";

    $file = readF('processor/ws-onGaming.php');
    $file = str_replace('<?php', '', $file);
    // var_dump($file);
    eval($file);
    // $server->push($frame->fd, json_encode(
    //     ['type' => 'message', 'data' => [
    //         'author' => $frame->fd,
    //         'text' => 'hello back here',
    //         'color' => 'yellow',
    //         'time' => time(),
    //     ]]));

    return false;
    $json = json_decode($frame->data, 1);

    if (!is_null($json)) {
        if ($json['actionPath']) {
        }
        if ($json['to'] != 'all') {
            $server->push($frame->fd, json_encode(
                ['type' => 'message', 'data' => [
                    'author' => $frame->fd,
                    'text' => $json['message'],
                    'color' => 'yellow',
                    'time' => time(),
                ]]));

            $wsID = $userListConnectedIDs[intval($json['to'])]['ws_fd'];
        }
        echo 'saving to database';
    } else {
        foreach ($userListConnectedIDs as $authID => $user) {
            $server->push($authID, json_encode(
                ['type' => 'message', 'data' => [
                    'author' => $frame->fd,
                    'text' => $frame->data,
                    'color' => 'yellow',
                    'time' => time(),
                ]])
            );
        }
    }

    // $server->push($frame->fd, json_encode(
    //     ['type' => 'message', 'data' => [
    //         'author' => $frame->fd,
    //         'text' => 'coba lagi yuk',
    //         'color' => 'yellow',
    //         'time' => time(),
    //     ]]));
});

$server->on('close', function ($server, $fd) use (&$userListConnectedIDs) {
    echo "connection close: {$fd}\n";
    unset($userListConnectedIDs[$fd]);
    unset($userListConnectedFDIDs[$fd]);
});

$server->start();
