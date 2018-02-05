<?php

require 'vendor/autoload.php';

use Elasticsearch\ClientBuilder;

// 开启日志
$logger = ClientBuilder::defaultLogger('D:/Elasticsearch-PHP5.0/log.txt');

// 每个请求的配置
$client = ClientBuilder::create()       // Instantiate a new ClientBuilder
            ->setLogger($logger)        // Set the logger with a default logger
            ->build();                  // Build the client object

/*$params = [
    'index'  => 'test_missing',
    'type'   => 'test',
    'id'     => 1,
    'client' => [ 'ignore' => [404, 405] ] 
];

$response = $client->get($params);*/

// 自定义查询参数
/*$params = [
    'index' => 'test',
    'type' => 'test',
    'id' => 1,
    'parent' => 'abc', // white-listed Elasticsearch parameter
    'client' => [
        'custom' => [
            'customToken' => 'abc', // user-defined, not white listed, not checked
            'otherToken' => 123
        ]
    ]
];
$exists = $client->exists($params);
print_r($exists);*/

// 增加返回冗余
$params = [
    'index' => 'my_index',
    'type' => 'my_type',
    'id' => 'my_id',
    'client' => [
        'verbose' => true
    ]
];
$response = $client->get($params);
print_r($response);