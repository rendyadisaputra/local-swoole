<?php

foreach ($userListConnectedIDs as $key => $fc) {
    // if()
    if ($fc['token'] != $userListConnectedFDIDs[$frame->fd]) {
        $server->push(($fc['ws_fd']), $frame->data);
    }
    // var_dump($fc['token']);
    //  $ser->push(($fc['ws_fd']));
}
// echo $frame->data;
