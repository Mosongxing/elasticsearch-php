# 快速开始

这一章会大概描述一下client以及client内的一些主要方法的使用。

## 安装

* 在composer.json中引入elasticsearch-php：
* 
		{
		    "require": {
		        "elasticsearch/elasticsearch": "~5.0"
		    }
		}

* 用composer安装client：

		curl -s http://getcomposer.org/installer | php
		php composer.phar install --no-dev

* 在项目中引入自动加载文件，并且实例化一个client：

		require 'vendor/autoload.php';
		
		use Elasticsearch\ClientBuilder;
		
		$client = ClientBuilder::create()->build();

## 索引一个文档

在elasticsearch-php中，几乎一切都是用关联数组的写法。REST方法、文档和参数——所有都是关联数组。

为了索引一个文档，我们要指定4部分信息：index，type，id和一个body。构建一个键值对的关联数组就可以完成上面的4部分。请求提的键值对格式与文档的键值对格式保持一致性。（如['testField' => 'abc']在文档中则为{"testField" : "abc"}）：

	$params = [
	    'index' => 'my_index',
	    'type' => 'my_type',
	    'id' => 'my_id',
	    'body' => ['testField' => 'abc']
	];
	
	$response = $client->index($params);
	print_r($response);

返回的数据则表面文档已被创建。返回的数据是一个关联数组，包含了Elasticsearch返回的被decode过的Json信息：

	Array
	(
	    [_index] => my_index
	    [_type] => my_type
	    [_id] => my_id
	    [_version] => 1
	    [created] => 1
	)

## 获取一个文档

现在获取刚刚索引的文档：
	
	$params = [
	    'index' => 'my_index',
	    'type' => 'my_type',
	    'id' => 'my_id'
	];
	
	$response = $client->get($params);
	print_r($response);

返回的信息包含一些元信息（如index，type及其它的信息）和_source 属性，
这些也是你发送给Elasticsearch的最初数据。

	Array
	(
	    [_index] => my_index
	    [_type] => my_type
	    [_id] => my_id
	    [_version] => 1
	    [found] => 1
	    [_source] => Array
	        (
	            [testField] => abc
	        )
	
	)

## 搜索一个文档

搜索是elasticsearch的一大特色，所以我们试一下执行一个搜索。我们准备用Match方法作为示范：

	$params = [
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
	
	$response = $client->search($params);
	print_r($response);

这个返回的信息与前面的例子返回的有所不同。这里有一些元数据（如took, timed_out及其它的信息）和一个hits的数组。hts内部也有一个hits，内部的hits包含一些特定的搜索结果：

	Array
	(
	    [took] => 1
	    [timed_out] =>
	    [_shards] => Array
	        (
	            [total] => 5
	            [successful] => 5
	            [failed] => 0
	        )
	
	    [hits] => Array
	        (
	            [total] => 1
	            [max_score] => 0.30685282
	            [hits] => Array
	                (
	                    [0] => Array
	                        (
	                            [_index] => my_index
	                            [_type] => my_type
	                            [_id] => my_id
	                            [_score] => 0.30685282
	                            [_source] => Array
	                                (
	                                    [testField] => abc
	                                )
	                        )
	                )
	        )
	)


## 删除一个文档

现在我们看一下如何把之前添加的文档删除掉：

	$params = [
	    'index' => 'my_index',
	    'type' => 'my_type',
	    'id' => 'my_id'
	];
	
	$response = $client->delete($params);
	print_r($response);

你会注意到删除文档的语法与获取文档的语法是一样的。唯一不同的是操作方法：delete方法替代了get方法。下面的返回信息代表文档已被删除：

	Array
	(
	    [found] => 1
	    [_index] => my_index
	    [_type] => my_type
	    [_id] => my_id
	    [_version] => 2
	)

## 删除一个索引

由于elasticsearch动态特性，我们创建的第一个文档会自动创建一个索引，同时也会把settings里面的参数配置为默认值。由于我们在后面要指定特定的settings，所以现在要删除掉这个索引：

	$deleteParams = [
	    'index' => 'my_index'
	];
	$response = $client->indices()->delete($deleteParams);
	print_r($response);

返回的信息是：

	Array
	(
	    [acknowledged] => 1
	)

## 创建一个索引

我们可以重新开始了，现在要添加一个索引，同时配置一下settings：

	$params = [
	    'index' => 'my_index',
	    'body' => [
	        'settings' => [
	            'number_of_shards' => 2,
	            'number_of_replicas' => 0
	        ]
	    ]
	];
	
	$response = $client->indices()->create($params);
	print_r($response);

Elasticsearch创建了一个索引，并且配置了特定的settings，返回如下信息：

	Array
	(
	    [acknowledged] => 1
	)

## 本章结语

这里只是概述了一下client以及它的语法。如果你很熟悉elasticsearch，你会注意到这些方法的命名跟REST方法是一样的。

你也注意到了client的命名从某种程度上讲也是方便你的IDE易于搜索。$client对象下的所有核心方法都是可用的。索引管理和集群管理分别在$client->indices()和$client->cluster()中。

请查询文档的其余内容以便知道整个client是如何使用的。