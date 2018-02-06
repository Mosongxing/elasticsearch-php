# Future模式

client对象提供future模式（或叫异步模式）。future模式允许批量发送请求（并行发送到集群），这对于提高执行效率有着重大帮助。

PHP是单线程的，然而libcurl提供multi interface功能。这使得像PHP这种单线程的语言可以批量发送请求，从而获得并发性特征。批量请求通过底层的多线程libcurl库并行的发送请求到Elasticsearch，而批量的相应也随之返回给PHP。

在单线程环境下，执行n个请求的时间等于n个请求时间相加。在multi interface功能下，执行n个请求的时间等于最慢的那个请求的时间。

还有就是，multi-interface功能允许请求同时发送到不同的主机，这意味着Elasticsearch-PHP可以更有效的使用集群。

## 使用Future模式

使用这种模式相对简单，只是你要写多一点代码。为了开启future模式，在请求参数的client选项中增加future参数，并设置为'lazy'状态：

	$client = ClientBuilder::create()->build();
	
	$params = [
	    'index' => 'test',
	    'type' => 'test',
	    'id' => 1,
	    'client' => [
	        'future' => 'lazy'
	    ]
	];
	
	$future = $client->get($params);

这里会返回一个future对象，而不是真正的存储数据。future对象是待处理对象，它看起来就像是个占位符。你可以把future对象当成是普通对象在代码中传递使用。当你需要存储数据时，你可以解析future对象。如果future对象已经被解析，存储数据可以立即使用。如果future对象还没被解析，那么在解析过程中会阻塞直到解析完成。

在实际应用中，你可以通过设置future: lazy参数构造一个请求队列，而返回的future对象直到解析完成，程序才会继续执行。无论什么时候，全部的请求都是以并行方式发送到集群，以异步方式返回给curl。

这听起来好复杂，但由于RingPHP的FutureArray接口，这些操作则变得很简单。它让future对象看起来像是一个关联数组。例如：

	$client = ClientBuilder::create()->build();
	
	$params = [
	    'index' => 'test',
	    'type' => 'test',
	    'id' => 1,
	    'client' => [
	        'future' => 'lazy'
	    ]
	];
	
	$future = $client->get($params);
	
	$doc = $future['_source'];    // This call will block and force the future to resolve

就像通常的响应数据那样，future对象可以用迭代关联数组的方式解析特定的值（轮流解析挂起来的请求结果）。这样就可以写成如下形式：

	$client = ClientBuilder::create()->build();
	$futures = [];
	
	for ($i = 0; $i < 1000; $i++) {
	    $params = [
	        'index' => 'test',
	        'type' => 'test',
	        'id' => $i,
	        'client' => [
	            'future' => 'lazy'
	        ]
	    ];
	
	    $futures[] = $client->get($params);     //queue up the request
	}
	
	
	foreach ($futures as $future) {
	    // access future's values, causing resolution if necessary
	    echo $future['_source'];
	}

请求队列会并行执行，执行后赋值给futures数组。每批请求默认为100个。

如果你想强制future解析，但又不立刻获取存储数据。你可以用future对象的wait()方法来强制解析：

	$client = ClientBuilder::create()->build();
	$futures = [];

	for ($i = 0; $i < 1000; $i++) {
	    $params = [
	        'index' => 'test',
	        'type' => 'test',
	        'id' => $i,
	        'client' => [
	            'future' => 'lazy'
	        ]
	    ];
	
	    $futures[] = $client->get($params);     //queue up the request
	}
	
	//wait() forces future resolution and will execute the underlying curl batch
	$futures[999]->wait();

## 更改批量大小

默认的批量大小为100个，这意味着在client对象强制future对象解析前（执行curl_multi调用），队列可以容纳100个请求。批量大小可以更改，取决于你的需求。批量大小通过设置handler时调整max_handles参数修改：
	
	$handlerParams = [
	    'max_handles' => 500
	];
	
	$defaultHandler = ClientBuilder::defaultHandler($handlerParams);
	
	$client = ClientBuilder::create()
	            ->setHandler($defaultHandler)
	            ->build();

上面的设置会更改批量发送数量为500。注意：不管队列数量是否为最大批量值，强制解析future对象都会引起底层的curl执行批量操作。在如下的示例中，只有499个对象加入队列，但是结果会强制解析future对象：

	$handlerParams = [
	    'max_handles' => 500
	];
	
	$defaultHandler = ClientBuilder::defaultHandler($handlerParams);
	
	$client = ClientBuilder::create()
	            ->setHandler($defaultHandler)
	            ->build();
	
	$futures = [];
	
	for ($i = 0; $i < 499; $i++) {
	    $params = [
	        'index' => 'test',
	        'type' => 'test',
	        'id' => $i,
	        'client' => [
	            'future' => 'lazy'
	        ]
	    ];
	
	    $futures[] = $client->get($params);     //queue up the request
	}
	
	// resolve the future, and therefore the underlying batch
	$body = $future[499]['body'];

## 各种批量执行

队列里面允许存在各种请求。比如，你可以把get请求、index请求和search请求放到队列里面：

	$client = ClientBuilder::create()->build();
	$futures = [];
	
	$params = [
	    'index' => 'test',
	    'type' => 'test',
	    'id' => 1,
	    'client' => [
	        'future' => 'lazy'
	    ]
	];
	
	$futures['getRequest'] = $client->get($params);     // First request
	
	$params = [
	    'index' => 'test',
	    'type' => 'test',
	    'id' => 2,
	    'body' => [
	        'field' => 'value'
	    ],
	    'client' => [
	        'future' => 'lazy'
	    ]
	];
	
	$futures['indexRequest'] = $client->index($params);       // Second request
	
	$params = [
	    'index' => 'test',
	    'type' => 'test',
	    'body' => [
	        'query' => [
	            'match' => [
	                'field' => 'value'
	            ]
	        ]
	    ],
	    'client' => [
	        'future' => 'lazy'
	    ]
	];
	
	$futures['searchRequest'] = $client->search($params);      // Third request
	
	// Resolve futures...blocks until network call completes
	$searchResults = $futures['searchRequest']['hits'];
	
	// Should return immediately, since the previous future resolved the entire batch
	$doc = $futures['getRequest']['_source'];

## 注意事项

使用future模式时需要注意几点。最大也是最明显的问题是：你要自己去解析future对象。这挺麻烦的，而且偶尔会引起一些意料不到的状况。

例如，假如你手动使用wait()方法解析，在需要重新构建future对象并解析的情况下，你也许要调用好几次wait()方法。这是因为每次重新构造future对象都会覆盖解析结果，所以每个future对象都要解析获取结果。

如果你使用ArrayInterface返回的结果（$response['hits']['hits']）则不用进行额外处理，因为FutureArrayInterface会全面自动地解析future对象。

另外一点是一些方法会失效。比如exists方法（$client->exists(), $client->indices()->exists, $client->indices->templateExists()等）在正常情况下会返回true或false。

当使用future模式时，future对象是未封装的，这代表client对象无法检测响应结果和返回true或false。所以你会得到从Elasticsearch返回的原始响应数据，不得不对这些数据进行处理。

这些注意事项也适用于ping()方法。