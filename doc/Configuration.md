# 配置

几乎所有的client都可以配置。大多数用户只需配置一些参数来满足他们的需求，但是也有可能配置所有的参数来满足需求。

在client实例化前就应该通过ClientBuilder来完成自定义配置。我们会概述一下所有的配置参数，并且展示一些代码示例。

## Inline Host配置方法

最常见的配置是告诉client集群的信息：有多少个节点，节点的ip地址和端口号。如果没有指定主机名，client会连接localhost:9200。

利用ClientBuilder的setHosts()方法可以改变client的默认连接方式。setHosts()方法接收一个一维数组，数组里面每个值都代表集群里面的节点信息。值的格式多种多样，主要看你的需求：

	$hosts = [
	    '192.168.1.1:9200',         // IP + Port
	    '192.168.1.2',              // Just IP
	    'mydomain.server.com:9201', // Domain + Port
	    'mydomain2.server.com',     // Just Domain
	    'https://localhost',        // SSL to localhost
	    'https://192.168.1.3:9200'  // SSL to IP + Port
	];
	$client = ClientBuilder::create()           // Instantiate a new ClientBuilder
	                    ->setHosts($hosts)      // Set the hosts
	                    ->build();              // Build the client object

注意ClientBuilder对象允许链式操作。当然也可以分别调用上述的方法：

	$hosts = [
	    '192.168.1.1:9200',         // IP + Port
	    '192.168.1.2',              // Just IP
	    'mydomain.server.com:9201', // Domain + Port
	    'mydomain2.server.com',     // Just Domain
	    'https://localhost',        // SSL to localhost
	    'https://192.168.1.3:9200'  // SSL to IP + Port
	];
	$clientBuilder = ClientBuilder::create();   // Instantiate a new ClientBuilder
	$clientBuilder->setHosts($hosts);           // Set the hosts
	$client = $clientBuilder->build();          // Build the client object

## Extended Host配置方法

client也支持键值对配置语法。Inline Host配置方法依赖PHP的filter_var()函数和parse_url()函数来验证和提取一个URL的各个部分。然而，这些php函数在一些特定的场景下会出错。例如，filter_var()函数不接收有下划线的URL。同样，如果Basic Auth的密码含有特定字符（如#、?），那么parse_url()函数会报错。

因而client支持键值对的语法，从而使client实例化更加可控。（对每个节点来说，配置参数就是一些键值对）：

	$hosts = [
	    // This is effectively equal to: "https://username:password!#$?*abc@foo.com:9200/"
	    [
	        'host' => 'foo.com',
	        'port' => '9200',
	        'scheme' => 'https',
	        'user' => 'username',
	        'pass' => 'password!#$?*abc'
	    ],
	
	    // This is equal to "http://localhost:9200/"
	    [
	        'host' => 'localhost',    // Only host is required
	    ]
	];
	$client = ClientBuilder::create()           // Instantiate a new ClientBuilder
	                    ->setHosts($hosts)      // Set the hosts
	                    ->build();              // Build the client object

每个节点只需要配置host参数。如果其它参数不指定，那么默认的端口是9200，默认的scheme是http。

## 认证和加密

想了解HTTP认证和SSL加密的内容，请查看[Authorization and SSL](https://www.elastic.co/guide/en/elasticsearch/client/php-api/5.0/_security.html)(后面补上中文)

## 设置重发次数

在一个集群中，client默认重发n（n=节点数）次。如果操作抛出如下异常：connection refusal, connection timeout, DNS lookup timeout等等（不包括4xx和5xx），client便会重发。

如果你不想重发，或者想更改重发次数。你可以使用setRetries()方法：

	$client = ClientBuilder::create()
	                    ->setRetries(2)
	                    ->build();

假如client重发次数超过设定值，便会抛出最后接收到的异常。例如，如果你有10个节点，setRetries(5)，client便会发送5次命令。如果5个节点返回的结果都是connection timeout，那么client会抛出OperationTimeoutException。由于连接池处于使用状态，这些节点也可能会被标记为死节点。

为了识别是否为死节点，抛出的异常会包含一个MaxRetriesException。例如，你可以在catch内使用getPrevious()来捕获一个特定的curl异常，以便查看是否包含MaxRetriesException。

	$client = Elasticsearch\ClientBuilder::create()
	    ->setHosts(["localhost:1"])
	    ->setRetries(0)
	    ->build();
	
	try {
	    $client->search($searchParams);
	} catch (Elasticsearch\Common\Exceptions\Curl\CouldNotConnectToHost $e) {
	    $previous = $e->getPrevious();
	    if ($previous instanceof 'Elasticsearch\Common\Exceptions\MaxRetriesException') {
	        echo "Max retries!";
	    }
	}

或者，所有的curl 抛出的异常(CouldNotConnectToHost, CouldNotResolveHostException, OperationTimeoutException)都归为TransportException。这样你就能够用TransportException来替代如上3种异常：

	$client = Elasticsearch\ClientBuilder::create()
	    ->setHosts(["localhost:1"])
	    ->setRetries(0)
	    ->build();
	
	try {
	    $client->search($searchParams);
	} catch (Elasticsearch\Common\Exceptions\TransportException $e) {
	    $previous = $e->getPrevious();
	    if ($previous instanceof 'Elasticsearch\Common\Exceptions\MaxRetriesException') {
	        echo "Max retries!";
	    }
	}

