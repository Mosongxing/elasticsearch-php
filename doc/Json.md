# 用PHP处理JSON对象集合和JSON对象

client对象在关于JSON对象集合和JSON对象的处理和定义方面总是让人找不着北。尤其是由空对象和空对象集合引起的问题。本章会展示一些Elasticsearch JSON API常见的数据格式，还会说明如何以PHP的语法来表达这些数据格式。

## 空对象

Elasticsearch API在几个地方使用了空对象，这会对PHP造成影响。不像其它的语言，PHP没有一个简便的符号来表示空对象，而许多开发者还不知道如何指定一个空对象。

设想在查询中增加Highlight：

	{
	    "query" : {
	        "match" : {
	            "content" : "quick brown fox"
	        }
	    },
	    "highlight" : {
	        "fields" : {
	            "content" : {} // 这个空对象便会引起问题
	        }
	    }
	}

问题就在于PHP会自动把"content" : {}转换成"content" : []，在Elasticsearch DSL中这样的数据格式是非法的。我们需要告诉PHP那个空对象就是一个空对象，不是一个空数组。为了在查询中定义空对象，你需要这样做：

	$params['body'] = array(
	    'query' => array(
	        'match' => array(
	            'content' => 'quick brown fox'
	        )
	    ),
	    'highlight' => array(
	        'fields' => array(
	            'content' => new \stdClass()
	        )
	    )
	);
	$results = $client->search($params);

通过使用一个stdClass对象，我们可以强制json_encode正确的解析为空对象，而不是空数组。然而，这种冗余的写法是唯一解决PHP空对象的方法，没有简便的方法可以表示空对象。

## 对象集合

Elasticsearch DSL的另一种常见的数据格式是对象集合。例如，假设在你的查询中增加排序：

	{
	    "query" : {
	        "match" : { "content" : "quick brown fox" }
	    },
	    "sort" : [  
	        {"time" : {"order" : "desc"}},
	        {"popularity" : {"order" : "desc"}}
	    ]
	}

这种形式很常见，但是在PHP中构建就稍微有些繁琐，因为这需要嵌套数组。用PHP写这种冗余的结构就让人读起来有点晦涩。为了构建对象集合，你要在数组中添加数组：

	$params['body'] = array(
	    'query' => array(
	        'match' => array(
	            'content' => 'quick brown fox'
	        )
	    ),
	    'sort' => array( // 这里encode为"sort" : []
	        array('time' => array('order' => 'desc')), // 这里encode为{"time" : {"order" : "desc"}}
	        array('popularity' => array('order' => 'desc')) // 这里encode为{"popularity" : {"order" : "desc"}}
	    )
	);
	$results = $client->search($params);

如果你用的是PHP5.4及以上版本，我强烈要求你使用[]构建数组。这会让多维数组看起来易读些：

	$params['body'] = [
	    'query' => [
	        'match' => [
	            'content' => 'quick brown fox'
	        ]
	    ],
	    'sort' => [
	        ['time' => ['order' => 'desc']],
	        ['popularity' => ['order' => 'desc']]
	    ]
	];
	$results = $client->search($params);

## 空对象集合

偶尔你会看到DSL需要上述两种数据格式。score查询便是一个很好的例子，该查询有时需要一个对象集合，而有一些对象可能是一个空的JSON对象。

请看如下查询：

	{
	   "query":{
	      "function_score":{
	         "functions":[
	            {
	               "random_score":{}
	            }
	         ],
	         "boost_mode":"replace"
	      }
	   }
	}

我们用下面的PHP代码来构建这个查询：

	$params['body'] = array(
	    'query' => array(
	        'function_score' => array(
	            'functions' => array(  
	                array(  
	                    'random_score' => new \stdClass() 
	                )
	            )
	        )
	    )
	);
	$results = $client->search($params);
