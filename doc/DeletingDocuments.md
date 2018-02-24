# 删除文档

通过指定文档的/index/type/id路径可以删除文档：

	$params = [
	    'index' => 'my_index',
	    'type' => 'my_type',
	    'id' => 'my_id'
	];
	
	// Delete doc at /my_index/my_type/my_id
	$response = $client->delete($params);