# 安全

Elasticsearch-PHP客户端支持两种安全设置方式：HTTP认证和SSL加密。

## HTTP认证

如果你的Elasticsearch是通过HTTP认证来维持安全，你就要为Elasticsearch-PHP客户端提供身份凭证（credentials），这样服务端才能认证客户端请求。在实例化客户端时，身份凭证（credentials）需要配置在host数组中：

	$hosts = [
	    'http://user:pass@localhost:9200',       // HTTP Basic Authentication
	    'http://user2:pass2@other-host.com:9200' // Different credentials on different host
	];
	
	$client = ClientBuilder::create()
	                    ->setHosts($hosts)
	                    ->build();

每个host都要添加身份凭证（credentials），这样的话每个host都拥有自己的身份凭证（credentials）。所有发送到集群中的请求都会根据访问节点来使用相应的身份凭证（credentials）。

## SSL加密

配置SSL会有些复杂。你要去识别Certificate Authority (CA)签名的证书或者自签名证书。

	注意：libcurl版本注意事项
	如果你觉得客户端已经正确配置SSL，但是没有起效，请检查你的libcurl版本。在某些平台上，一些设置可能有效也可能无效，这取决于
	libcurl版本号。例如直到libcurl 7.37.1，OSX平台的libcurl才添加--cacert选项。--cacert选项对应PHP的CURLOPT_CAINFO常量，
	这就意味着自定义的证书在低版本下是无法使用的。

	如果你现在正面临这个问题，请更新你的libcurl，然后查看curl changelog有无增加该选项。

### 公共CA证书

如果你的证书是公共CA签名证书，且你的服务器用的是最新的根证书，你只需要在host中使用https。客户端会自动识别SSL证书：

	$hosts = [
	    'https://localhost:9200' 
	];
	
	$client = ClientBuilder::create()
	                    ->setHosts($hosts)
	                    ->build();

如果服务器的根证书已经过期，你就要用证书bundle。对于客户端来说，最好的方法是使用composer/ca-bundle。一旦安装好ca-bundle，你要告诉客户端使用你提供的证书来替代系统的bundle：

	$hosts = ['https://localhost:9200'];
	$caBundle = \Composer\CaBundle\CaBundle::getBundledCaBundlePath();
	
	$client = ClientBuilder::create()
	                    ->setHosts($hosts)
	                    ->setSSLVerification($caBundle)
	                    ->build();

### 自签名证书

自签名证书是指没有被公共CA签名的证书。自签名证书由你自己的组织来签名。在你确保安全发送自己的根证书前提下，自签名证书可用作内部使用的。当自签名证书暴露给公众客户时就不应该使用了，因为客户端容易受到中间人攻击。

如果你正使用自签名证书，你要给客户端提供证书路径。这与指定一个根bundle的语法一致，只是把根bundle替换为自签名证书：

	$hosts = ['https://localhost:9200'];
	$myCert = 'path/to/cacert.pem';
	
	$client = ClientBuilder::create()
	                    ->setHosts($hosts)
	                    ->setSSLVerification($myCert)
	                    ->build();

## 同时使用认证与SSL

同时使用认证与SSL也是有可能的。在URI中指定https与身份凭证（credentials），同时提供SSL所需的自签名证书。例如下面的代码段就同时使用了HTTP认证和自签名证书：
	
	$hosts = ['https://user:pass@localhost:9200'];
	$myCert = 'path/to/cacert.pem';
	
	$client = ClientBuilder::create()
	                    ->setHosts($hosts)
	                    ->setSSLVerification($myCert)
	                    ->build();