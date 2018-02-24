# 更新文档

更新文档既可以完全覆盖现存文档全部字段，又可以部分更新字段（更改现存字段，或添加新字段）。

## 部分更新

如果你要部分更新文档（如更改现存字段，或添加新字段），你可以在body参数中指定一个doc参数。这样doc参数内的字段会与现存字段进行合并。

	$params = [
	    'index' => 'my_index',
	    'type' => 'my_type',
	    'id' => 'my_id',
	    'body' => [
	        'doc' => [
	            'new_field' => 'abc'
	        ]
	    ]
	];
	
	// Update doc at /my_index/my_type/my_id
	$response = $client->update($params);

## script更新

有时你要执行一个脚本来进行更新操作，如对字段进行自增操作或添加新字段。为了执行一个脚本更新，你要提供脚本命令和一些参数：

	$params = [
	    'index' => 'my_index',
	    'type' => 'my_type',
	    'id' => 'my_id',
	    'body' => [
	        'script' => 'ctx._source.counter += count',
	        'params' => [
	            'count' => 4
	        ]
	    ]
	];
	
	$response = $client->update($params);

## Upsert更新

Upsert操作是指“更新或插入”操作。这意味着一个upsert操作会先执行script更新，如果文档不存在（或是你更新的字段不存在），则会插入一个默认值。

	$params = [
	    'index' => 'my_index',
	    'type' => 'my_type',
	    'id' => 'my_id',
	    'body' => [
	        'script' => 'ctx._source.counter += count',
	        'params' => [
	            'count' => 4
	        ],
	        'upsert' => [
	            'counter' => 1
	        ]
	    ]
	];
	
	$response = $client->update($params);