# 配置

几乎所有应用（如mysql、redis等）的client都可以配置。大多数用户只需配置一些参数来满足他们的需求，但是也有可能配置所有的参数来满足需求。

在client对象实例化前就应该通过ClientBuilder方法来完成自定义配置。我们会概述一下所有的配置参数，并且展示一些代码示例。

## Inline Host配置方法

最常见的配置是告诉client集群的信息：有多少个节点，节点的ip地址和端口号。如果没有指定host名，client会连接localhost:9200。

利用ClientBuilder的setHosts()方法可以改变client对象的默认连接方式。setHosts()方法接收一个一维数组，数组里面每个值都代表集群里面的节点信息。值的格式多种多样，主要看你的需求：

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

为了识别是否为重发异常（重发次数超过设定值），抛出的异常会包含一个MaxRetriesException。例如，你可以在catch内使用getPrevious()来捕获一个特定的curl异常，以便查看是否包含MaxRetriesException。

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

或者，所有curl 抛出的异常(CouldNotConnectToHost, CouldNotResolveHostException, OperationTimeoutException)都归为TransportException。这样你就能够用TransportException来替代如上3种异常：

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

## 开启日志

Elasticsearch-PHP支持日志写入，但由于性能原因，所以没有默认开启。如果你希望开启日志，你就要选择一个日志记录工具并安装日志记录工具，然后在Client中开启日志。推荐使用Monolog，不过任何实现PSR/Log接口的日志记录工具都可以使用。

为了使用Monolog，请把它加入composer.json：

	{
	    "require": {
	        ...
	        "elasticsearch/elasticsearch" : "~5.0",
	        "monolog/monolog": "~1.0"
	    }
	}

然后用composer更新：

	composer update

一旦安装好Monolog（或其他日志记录工具），你就要创建一个日志对象并且注入到client对象中。ClientBuilder对象有一个静态方法来构建一个通用的Monolog-based日志对象。你只需要提供存放日志路径就行：

	$logger = ClientBuilder::defaultLogger('path/to/your.log');
	
	$client = ClientBuilder::create()       // Instantiate a new ClientBuilder
	            ->setLogger($logger)        // Set the logger with a default logger
	            ->build();                  // Build the client object

你也可以指定记录的日志级别：

	// set severity with second parameter
	$logger = ClientBuilder::defaultLogger('/path/to/logs/', Logger::INFO);
	
	$client = ClientBuilder::create()       // Instantiate a new ClientBuilder
	            ->setLogger($logger)        // Set the logger with a default logger
	            ->build();                  // Build the client object

defaultLogger()方法只是一个辅助方法，不要求你使用它。你可以自己创建日志对象，然后注入：

	use Monolog\Logger;
	use Monolog\Handler\StreamHandler;
	
	$logger = new Logger('name');
	$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));
	
	$client = ClientBuilder::create()       // Instantiate a new ClientBuilder
	            ->setLogger($logger)        // Set your custom logger
	            ->build();                  // Build the client object

## 配置HTTP Handler

Elasticsearch-PHP使用可替代的HTTP传输层——RingPHP。这允许client构建一个普通的HTTP请求，然后通过传输层发送出去。真正的请求细节隐藏在client中，并且这是模块化的，因此你可以根据你的需求来选择HTTP handlers。

client使用的默认handler是结合型handler。当以同步模式发送请求，handler使用CurlHandler来发送单个curl请求。这种方式对于单一请求来说特别迅速。当异步（future）模式开启，handler就转换成使用CurlMultiHandler，CurlMultiHandler以curl_multi方式来发送请求。这样会消耗更多性能，但是允许批量HTTP请求并行执行。

你可以从以下一些助手函数中选择一个来配置HTTP handler，或者你也可以自定义HTTP handler：

	$defaultHandler = ClientBuilder::defaultHandler();
	$singleHandler  = ClientBuilder::singleHandler();
	$multiHandler   = ClientBuilder::multiHandler();
	$customHandler  = new MyCustomHandler();
	
	$client = ClientBuilder::create()
	            ->setHandler($defaultHandler)
	            ->build();

想要了解自定义Ring handler的细节，请查看[RingPHP Documentation](http://ringphp.readthedocs.io/en/latest/)

在所有的client中都是使用默认的handler。这不仅可以以同步模式快速发送请求，而且也保留了异步模式来实现并行请求。 如果你觉得你永远不会用到future模式，你可以考虑用singleHandler，这样会间接节省一些性能。

## 设置连接池

client会维持一个连接池，每个连接代表集群的一个节点。这里有好几类连接池可供使用，每个的行为都有些细微差距。连接池可通过setConnectionPool()来配置：

	$connectionPool = '\Elasticsearch\ConnectionPool\StaticNoPingConnectionPool';
	$client = ClientBuilder::create()
	            ->setConnectionPool($connectionPool)
	            ->build();

更多细节请查询[连接池配置](https://www.elastic.co/guide/en/elasticsearch/client/php-api/5.0/_connection_pool.html)（以后翻译）

## 设置选择器

连接池是用来管理集群的连接，但是选择器则是用来确定下一个API请求要用哪个连接。这里有几个选择器可供选择。选择器可通过setSelector()来更改：

	$selector = '\Elasticsearch\ConnectionPool\Selectors\StickyRoundRobinSelector';
	$client = ClientBuilder::create()
	            ->setSelector($selector)
	            ->build();

更多细节请查询[选择配置](https://www.elastic.co/guide/en/elasticsearch/client/php-api/5.0/_selectors.html)（以后翻译）

## 设置序列化

client得到的请求数据是关联数组，但是Elasticsearch接受JSON数据。序列化是指把PHP数组序列化为JSON数据。当然Elasticsearch返回的JSON数据也会反序列化为PHP数组。

大部分人不会更改默认的序列方式（SmartSerializer），但你真的想改变，那可以通过setSerializer()方法：

	$serializer = '\Elasticsearch\Serializers\SmartSerializer';
	$client = ClientBuilder::create()
	            ->setSerializer($serializer)
	            ->build();

更多细节请查询[序列化配置](https://www.elastic.co/guide/en/elasticsearch/client/php-api/5.0/_serializers.html)（以后翻译）

## 设置自定义ConnectionFactory

当连接池发送请求时，ConnectionFactory就会实例化连接对象。一个连接对象代表一个节点。因为handler（通过RingPHP）才是真正的执行网络请求，那么连接对象的主要工作就是维持连接：节点挂了吗？ping的通吗？host和端口是什么啦？

很少会去自定义ConnectionFactory，但是如果你想做，那么你要提供一个完整的ConnectionFactory对象作为setConnectionFactory()方法的参数。这个自定义对象需要实现ConnectionFactoryInterface接口。

	class MyConnectionFactory implements ConnectionFactoryInterface
	{
	
	    public function __construct($handler, array $connectionParams,
	                                SerializerInterface $serializer,
	                                LoggerInterface $logger,
	                                LoggerInterface $tracer)
	    {
	       // Code here
	    }
	
	
	    /**
	     * @param $hostDetails
	     *
	     * @return ConnectionInterface
	     */
	    public function create($hostDetails)
	    {
	        // Code here...must return a Connection object
	    }
	}
	
	
	$connectionFactory = new MyConnectionFactory(
	    $handler,
	    $connectionParams,
	    $serializer,
	    $logger,
	    $tracer
	);
	
	$client = ClientBuilder::create()
	            ->setConnectionFactory($connectionFactory);
	            ->build();

你也看到啦，如果你想注入自定义的ConnectionFactory，你自己就要写好它。ConnectionFactory需要HTTP handler，序列化，日志和追踪。

## 设置Endpoint闭包

client对象使用Endpoint闭包来发送API请求到Elasticsearch的Endpoint对象。一个命名空间对象会通过闭包构建一个新的Endpoint，这个意味着如果你想扩展API的方法，你可以很方便的做到。（以上翻译有误，后期更正）

例如，我们可以新增一个endpoint：

	$transport = $this->transport;
	$serializer = $this->serializer;
	
	$newEndpoint = function ($class) use ($transport, $serializer) {
	    if ($class == 'SuperSearch') {
	        return new MyProject\SuperSearch($transport);
	    } else {
	        // Default handler
	        $fullPath = '\\Elasticsearch\\Endpoints\\' . $class;
	        if ($class === 'Bulk' || $class === 'Msearch' || $class === 'MPercolate') {
	            return new $fullPath($transport, $serializer);
	        } else {
	            return new $fullPath($transport);
	        }
	    }
	};
	
	$client = ClientBuilder::create()
	            ->setEndpoint($newEndpoint)
	            ->build();

很明显，如果你这样做的话，那么你就要负责对现存的endpoint进行维护，以确保所有的方法都能正常运行。同时你也要确保端口和序列化都写入每个endpoint。

## 从hash配置中构建client对象

为了更加容易的构建client对象，所有的配置都可以用hash形式来替代逐步配置。这种配置方法可以通过静态方法ClientBuilder::FromConfig()来完成，它接收一个数组，返回一个配置好的client对象。

数组键对应方法名（如retries 对应 setRetries()方法）：

	$params = [
	    'hosts' => [
	        'localhost:9200'
	    ],
	    'retries' => 2,
	    'handler' => ClientBuilder::singleHandler()
	];
	$client = ClientBuilder::fromConfig($params);

为了帮助用户找出潜在的问题，没有的参数则会抛出异常。如果你不想要抛出异常，你可以在fromConfig()中设置$quiet = true：

	$params = [
	    'hosts' => [
	        'localhost:9200'
	    ],
	    'retries' => 2,
	    'imNotReal' => 5
	];
	
	// Set $quiet to true to ignore the unknown `imNotReal` key
	$client = ClientBuilder::fromConfig($params, true);