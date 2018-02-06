<?php

require 'vendor/autoload.php';

use Elasticsearch\ClientBuilder;

// 开启日志
$logger = ClientBuilder::defaultLogger('D:/Elasticsearch-PHP5.0/log.txt');

// 设置批量大小为500
$handlerParams = [
    'max_handles' => 500
];

$defaultHandler = ClientBuilder::defaultHandler($handlerParams);

// 开启Future模式
$client = ClientBuilder::create()       // Instantiate a new ClientBuilder
            ->setLogger($logger)        // Set the logger with a default logger
            ->setHandler($defaultHandler)
            ->build();                  // Build the client object

$params = [
    'index' => 'test',
    'type' => 'test',
    'id' => 1,
    'client' => [
        'future' => 'lazy'
    ]
];

$future = $client->get($params);

// 发送请求并得到数据
// $doc = $future['_source'];

// 发送请求，暂时不用数据
$future->wait();

$future = $client->get($params);
$future->wait();
// 强制发送请求
/*$futures = [];

for ($i = 1; $i < 500; $i++) {
    $params = [
        'index' => 'test',
        'type' => 'test',
        'id' => $i,
        'client' => [
            'future' => 'lazy'
        ]
    ];

    $futures[] = $client->get($params);     //queue up the request
}*/

// resolve the future, and therefore the underlying batch
// $body = $futures[100]['_source'];

echo '<pre>';
    print_r($future);
echo '</pre>';
