<?php

$userListConnectedIDs = [];
$server = new Swoole\Websocket\Server('0.0.0.0', 9502);
$userList = [
    'token01' => ['name' => 'nurdiana', 'user_id' => 9],
    'token02' => ['name' => 'aisyah', 'user_id' => 10],
    'token03' => ['name' => 'Rendy', 'user_id' => 11],
];
$server->on('open', function ($ser, $req) use (&$userListConnectedIDs, &$userList) {
    // if ($req->fd == 1) {
    //     $userListConnectedIDs[x] = [
    //         'time' => time(),
    //         'user' => $userList['0001'],
    //         'id' => '0001',
    //     ];
    // } else {
    //     $userListConnectedIDs[$req->fd] = [
    //         'time' => time(),
    //         'user' => $userList['0002'],
    //         'id' => '0002',
    //     ];
    // }
    // echo "connection open: {$req->fd}\n";

    $parseUrl = explode('/', $req->server['request_uri'], 4);
    $api = isset($parseUrl[1]) ? $parseUrl[1] : null;
    $apiKey = isset($parseUrl[2]) ? $parseUrl[2] : null;
    $token = isset($parseUrl[3]) ? $parseUrl[3] : null;

    if ($api == 'api' && $apiKey == 'abcdefgh' && isset($userList[$token])) {
        $userListConnectedIDs[$userList[$token]['user_id']] = [
            'time' => time(),
            'user' => $userList[$token],
            'ws_fd' => $req->fd,
    ];
    } else {
        $ser->push($req->fd, json_encode(
            ['error' => 1, 'message' => 'URI Request not found', 'code' => 404]));
        $ser->disconnect($req->fd);
    }
    var_dump('dumping ', $userListConnectedIDs);
});

$server->on('message', function ($server, $frame) use (&$userListConnectedIDs, &$userList) {
    echo "received message: {$frame->data}\n";

    $json = json_decode($frame->data, 1);
    var_dump($json);
    if (!is_null($json)) {
        if ($json['to'] != 'all') {
            $server->push($frame->fd, json_encode(
                ['type' => 'message', 'data' => [
                    'author' => $frame->fd,
                    'text' => $json['message'],
                    'color' => 'yellow',
                    'time' => time(),
                ]]));

            $wsID = $userListConnectedIDs[intval($json['to'])]['ws_fd'];

            $server->push($wsID, json_encode(
                ['type' => 'message', 'data' => [
                    'author' => $frame->fd,
                    'text' => $json['message'],
                    'color' => 'green',
                    'time' => time(),
                ]]));
        }

        if ($json['to'] == 'all') {
            foreach ($json['pointing'] as $p) {
                sleep(2);
                foreach ($userListConnectedIDs as $authID => $user) {
                    $wsID = $user['ws_fd'];
                    $server->push($wsID, json_encode(
                            ['type' => 'message', 'data' => [
                                'author' => $frame->fd,
                                'text' => json_encode($p),
                                'color' => 'yellow',
                                'time' => time(),
                            ]])
                        );
                }
            }
        }

        sleep(15);
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
});

$server->start();
