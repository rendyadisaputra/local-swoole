<?php

require_once __DIR__.'/vendor/autoload.php';

require_once __DIR__.'/Mongodb/mongo-connection.php';

use FastRoute\RouteCollector;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

// handle get index requests
function get_index_handler(array $vars)
{
    global $MongoDBManager;

    $query = new MongoDB\Driver\Query([]);

    $rows = $MongoDBManager->executeQuery('test.collection_test', $query);

    return [
        'status' => 200,
        'message' => 'Hello world!',
        'values' => $rows->toArray(),
        'vars' => [
            'vars' => $vars,
            '$_GET' => $_GET,
            '$_POST' => $_POST,
        ],
    ];
}

// handle get index requests
function post_index_handler(array $vars)
{
    $message = json_encode($vars);

    return [
        'status' => 200,
        'message' => 'Hello world!'.$vars,
        'vars' => [
            'vars' => $vars,
            '$_GET' => $_GET,
            '$_POST' => $_POST,
        ],
    ];
}

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET', '/[{title}]', 'get_index_handler');
    $r->addRoute('POST', '/[{title}]', 'post_index_handler');
});

function handleRequest($dispatcher, string $request_method, string $request_uri)
{
    // list($code, $handler, $vars) = $dispatcher->dispatch($request_method, $request_uri);
    $rem = $dispatcher->dispatch($request_method, $request_uri);
    $code = isset($rem[0]) ? $rem[0] : null;
    $handler = isset($rem[1]) ? $rem[1] : null;
    $vars = isset($rem[2]) ? $rem[2] : null;

    // var_dump($handler);
    switch ($code) {
        case FastRoute\Dispatcher::NOT_FOUND:
            $result = [
                'status' => 404,
                'message' => 'Not Found',
                'errors' => [
                    sprintf('The URI "%s" was not found', $request_uri),
                ],
            ];
            break;
        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $handler;
            $result = [
                'status' => 405,
                'message' => 'Method Not Allowed',
                'errors' => [
                    sprintf('Method "%s" is not allowed', $request_method),
                ],
            ];
            break;
        case FastRoute\Dispatcher::FOUND:
            $result = call_user_func($handler, $vars);
            break;
    }

    return $result;
}

$host = '0.0.0.0';
$port = 9501;

$GLOBALS['config'] = $config = ['hosts' => [['addr' => '192.168.56.4', 'port' => 3000]]];
$GLOBALS['db'] = $db = new Aerospike($config, false);
if (!$db->isConnected()) {
    echo fail("Could not connect to host $HOST_ADDR:$HOST_PORT [{$db->errorno()}]: {$db->error()}");
    exit(1);
}

$server = new Server($host, $port);

// a swoole server is evented just like express
$server->on('start', function (Server $server) use ($port) {
    echo sprintf('Swoole http server is started at http://%s:%s'.json_encode($server, JSON_PRETTY_PRINT), PHP_EOL, $port);
});

// handle all requests with this response
$server->on('request', function (Request $request, Response $response) use ($dispatcher) {
    $request_method = $request->server['request_method'];
    $request_uri = $request->server['request_uri'];

    // populate the global state with the request info
    $_SERVER['REQUEST_URI'] = $request_uri;
    $_SERVER['REQUEST_METHOD'] = $request_method;
    $_SERVER['REMOTE_ADDR'] = $request->server['remote_addr'];

    $_GET = $request->get ?? [];
    $_FILES = $request->files ?? [];

    // form-data and x-www-form-urlencoded work out of the box so we handle JSON POST here
    if ($request_method === 'POST' && $request->header['content-type'] === 'application/json') {
        $body = $request->rawContent();
        $_POST = empty($body) ? [] : json_decode($body);
    } else {
        $_POST = $request->post ?? [];
    }

    // global content type for our responses
    $response->header('Content-Type', 'application/json');

    $result = json_encode(handleRequest($dispatcher, $request_method, $request_uri));
    _log($result);
    // write the JSON string out
    $response->end($result);
});

$server->start();

function _log($text)
{
    global $db;

    $text .= uniqid();
    // return false;
    $namespace = 'infosphere';
    $table = 'logtablekedua';
    $primary_key = uniqid();
    $key = $db->initKey($namespace, $table, $primary_key); // $i is Primary key
    $data = ['log_data' => $text, 'pk' => $primary_key];
    $status = $db->put($key, $data);

    // openlog('phperrors', LOG_PID | LOG_INFO, LOG_LOCAL0);
    // syslog(LOG_INFO, $text);
    // closelog();
}
