# 安装

Elasticsearch-php需要满足以下4个条件：

* PHP 5.6.6或更高版本
* Composer
* ext-curl:PHP的Libcurl扩展
* Native JSON扩展 (ext-json) 1.3.7或更高版本

其余的依赖会由Composer自动安装加载。Composer是一个PHP包管理和依赖管理工具，使用Composer安装elasticsearch-php非常简单。

## 版本信息

Elasticsearch-PHP的版本要和Elasticsearch版本适配，适配信息如下：

	Elasticsearch Version	Elasticsearch-PHP Branch
	>= 5.0                  5.0
	
	>= 1.0, ⇐ 5.0          1.0, 2.0
	
	⇐ 0.90.*               0.4

## Composer安装

在composer.json文件中增加elasticsearch-php。如果你是新建项目，那么把以下的代码复制粘贴到composer.json就行了。如果是在现有项目中添加elasticsearch-php，那么把"elasticsearch/elasticsearch": "~5.0"添加到其它的包名后面即可：

	{
	    "require": {
	        "elasticsearch/elasticsearch": "~5.0"
	    }
	}

打开黑窗口，进入项目根目录，使用以下命令安装elasticsearch-php：

	composer install --no-dev

最后，加载autoload.php。如果你现有项目是用Composer安装的，那么autoload.php也许已经在某处加载了，你就不用再加载了。然后就可以实例化一个client了：

	require 'vendor/autoload.php';
	
	$client = Elasticsearch\ClientBuilder::create()->build();

Client的实例化主要是使用静态方法create()，这里会创建一个ClientBuilder对象，主要是用来设置一些配置。如果你配置完了，你就可以调用build()方法来生产一个Client对象。我们会在<b>配置</b>章节详细说明配置方法。

## --no-dev标志

你会注意到安装命令行指定了--no-dev。这里是防止Composer 安装测试和开发依赖包。对于普通用户没有必要安装测试包。尤其是开发包包含了Elasticsearch的一套源码，主要是用来测试REST的。这对于非开发者来说太大了，因此要使用--no-dev。