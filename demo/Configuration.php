<?php

require 'vendor/autoload.php';

use Elasticsearch\ClientBuilder;

// Inline Host配置方法
/*$hosts = [
    'localhost:9200',        // SSL to localhost
];
$client = ClientBuilder::create()           // Instantiate a new ClientBuilder
                    ->setHosts($hosts)      // Set the hosts
                    ->build();              // Build the client object*/

// Extended Host配置方法
/*$hosts = [
    // This is effectively equal to: "https://username:password!#$?*abc@foo.com:9200/"
    [
        'host' => 'localhost',
        'port' => '9200',
        'scheme' => 'https'
    ]
];
$client = ClientBuilder::create()           // Instantiate a new ClientBuilder
                    ->setHosts($hosts)      // Set the hosts
                    ->build();              // Build the client object*/

// 设置重发次数
/*$client = ClientBuilder::create()
    ->setHosts(["localhost:9200"])
    ->setRetries(0)
    ->build();

$searchParams = [
    'index' => 'my_index',
    'type' => 'my_type',
    'body' => [
        'query' => [
            'match' => [
                'testField' => 'abc'
            ]
        ]
    ]
];

try {
    $response = $client->search($searchParams);
    print_r($response);
} catch (Elasticsearch\Common\Exceptions\TransportException $e) {
    $previous = $e->getPrevious();
    if ($previous instanceof Elasticsearch\Common\Exceptions\MaxRetriesException) {
        echo "Max retries!";
    }
}*/

// 开启日志
$logger = ClientBuilder::defaultLogger('D:\Elasticsearch-PHP5.0\demo\log.txt');

$client = ClientBuilder::create()       // Instantiate a new ClientBuilder
            ->setLogger($logger)        // Set the logger with a default logger
            ->build();                  // Build the client object