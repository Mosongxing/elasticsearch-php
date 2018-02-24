# 索引管理操作

索引管理操作可以让你管理集群中的索引，例如创建、删除和更新索引和索引的映射/配置。

## 创建一个索引

索引操作包含在一个特定的命名空间内，与其它直接从属于客户端对象的方法隔离开来。让我们创建一个索引作为示例：

	$client = ClientBuilder::create()->build();
	$params = [
	    'index' => 'my_index'
	];
	
	// Create the index
	$response = $client->indices()->create($params);

你可以在一个创建索引API中指定任何参数。所有的参数通常会注入请求体中的body参数下：

	$client = ClientBuilder::create()->build();
	$params = [
	    'index' => 'my_index',
	    'body' => [
	        'settings' => [
	            'number_of_shards' => 3,
	            'number_of_replicas' => 2
	        ],
	        'mappings' => [
	            'my_type' => [
	                '_source' => [
	                    'enabled' => true
	                ],
	                'properties' => [
	                    'first_name' => [
	                        'type' => 'string',
	                        'analyzer' => 'standard'
	                    ],
	                    'age' => [
	                        'type' => 'integer'
	                    ]
	                ]
	            ]
	        ]
	    ]
	];
	
	
	// Create the index with mappings and settings now
	$response = $client->indices()->create($params);

## 创建一个索引（复杂示例）

这是一个以更为复杂的方式创建索引的示例，示例中展示了如何定义analyzers，tokenizers，filters和索引的settings。虽然创建方式与之前的示例本质一样，但是这个复杂示例对于理解客户端的使用方法具有莫大帮助，因为这种特定的语法结构很容易被混淆。

	$params = [
	    'index' => 'reuters',
	    'body' => [
	        'settings' => [ 
	            'number_of_shards' => 1,
	            'number_of_replicas' => 0,
	            'analysis' => [ 
	                'filter' => [
	                    'shingle' => [
	                        'type' => 'shingle'
	                    ]
	                ],
	                'char_filter' => [
	                    'pre_negs' => [
	                        'type' => 'pattern_replace',
	                        'pattern' => '(\\w+)\\s+((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\b',
	                        'replacement' => '~$1 $2'
	                    ],
	                    'post_negs' => [
	                        'type' => 'pattern_replace',
	                        'pattern' => '\\b((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\s+(\\w+)',
	                        'replacement' => '$1 ~$2'
	                    ]
	                ],
	                'analyzer' => [
	                    'reuters' => [
	                        'type' => 'custom',
	                        'tokenizer' => 'standard',
	                        'filter' => ['lowercase', 'stop', 'kstem']
	                    ]
	                ]
	            ]
	        ],
	        'mappings' => [ 
	            '_default_' => [    
	                'properties' => [
	                    'title' => [
	                        'type' => 'string',
	                        'analyzer' => 'reuters',
	                        'term_vector' => 'yes',
	                        'copy_to' => 'combined'
	                    ],
	                    'body' => [
	                        'type' => 'string',
	                        'analyzer' => 'reuters',
	                        'term_vector' => 'yes',
	                        'copy_to' => 'combined'
	                    ],
	                    'combined' => [
	                        'type' => 'string',
	                        'analyzer' => 'reuters',
	                        'term_vector' => 'yes'
	                    ],
	                    'topics' => [
	                        'type' => 'string',
	                        'index' => 'not_analyzed'
	                    ],
	                    'places' => [
	                        'type' => 'string',
	                        'index' => 'not_analyzed'
	                    ]
	                ]
	            ],
	            'my_type' => [  
	                'properties' => [
	                    'my_field' => [
	                        'type' => 'string'
	                    ]
	                ]
	            ]
	        ]
	    ]
	];
	$client->indices()->create($params);

## 删除一个索引

删除一个索引十分简单：

	$params = ['index' => 'my_index'];
	$response = $client->indices()->delete($params);

## Put Settings API

Put Settings API允许你更改索引的配置参数:

	$params = [
	    'index' => 'my_index',
	    'body' => [
	        'settings' => [
	            'number_of_replicas' => 0,
	            'refresh_interval' => -1
	        ]
	    ]
	];
	
	$response = $client->indices()->putSettings($params);

## Get Settings API

Get Settings API可以让你知道一个或多个索引的当前配置参数：
	
	// Get settings for one index
	$params = ['index' => 'my_index'];
	$response = $client->indices()->getSettings($params);
	
	// Get settings for several indices
	$params = [
	    'index' => [ 'my_index', 'my_index2' ]
	];
	$response = $client->indices()->getSettings($params);

## Put Mappings API

Put Mappings API允许你更改或增加一个索引的映射。

	// Set the index and type
	$params = [
	    'index' => 'my_index',
	    'type' => 'my_type2',
	    'body' => [
	        'my_type2' => [
	            '_source' => [
	                'enabled' => true
	            ],
	            'properties' => [
	                'first_name' => [
	                    'type' => 'string',
	                    'analyzer' => 'standard'
	                ],
	                'age' => [
	                    'type' => 'integer'
	                ]
	            ]
	        ]
	    ]
	];
	
	// Update the index mapping
	$client->indices()->putMapping($params);

## Get Mappings API

Get Mappings API返回索引和类型的映射细节。你可以指定一些索引和类型，取决于你希望检索什么映射。
	
	// Get mappings for all indexes and types
	$response = $client->indices()->getMapping();
	
	// Get mappings for all types in 'my_index'
	$params = ['index' => 'my_index'];
	$response = $client->indices()->getMapping($params);
	
	// Get mappings for all types of 'my_type', regardless of index
	$params = ['type' => 'my_type' ];
	$response = $client->indices()->getMapping($params);
	
	// Get mapping 'my_type' in 'my_index'
	$params = [
	    'index' => 'my_index'
	    'type' => 'my_type'
	];
	$response = $client->indices()->getMapping($params);
	
	// Get mappings for two indexes
	$params = [
	    'index' => [ 'my_index', 'my_index2' ]
	];
	$response = $client->indices()->getMapping($params);

## 索引命名空间下的其他API

索引命名空间下还有一些API允许你管理你的索引（add/remove templates, flush segments, close indexes等）。

如果你使用一个自动化的IDE，你应该可以轻易发现索引的命名空间：

	$client->indices()->

这里可以查看可用方法清单。而浏览\Elasticsearch\Namespaces\Indices.php文件则会看到所有可调用的方法清单。