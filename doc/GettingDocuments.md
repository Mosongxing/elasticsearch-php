# 获取文档

Elasticsearch提供获取文档的实际时间。这意味着只要文档被索引且客户端收到消息确认后，你就可以立即在任何的分片中检索文档。Get操作通过index/type/id方式请求一个文档信息：

	$params = [
	    'index' => 'my_index',
	    'type' => 'my_type',
	    'id' => 'my_id'
	];
	
	// Get doc at /my_index/my_type/my_id
	$response = $client->get($params);