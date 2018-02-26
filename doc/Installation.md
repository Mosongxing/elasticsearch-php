# 安装

Elasticsearch-php的安装需要满足以下4个需求：

* PHP 7.0.0或更高版本
* Composer
* ext-curl:PHP的Libcurl扩展（详情查看下方注意事项）
* 原生JSON扩展 (ext-json) 1.3.7或更高版本

其余的依赖会由Composer自动安装。Composer是一个PHP包管理和依赖管理工具，使用Composer安装elasticsearch-php非常简单。

	注意：Libcurl是可替代的
	与Elasticsearch-php绑定的默认HTTP handlers需要PHP的Libcurl扩展，但客户端也并非一定要用Libcurl扩展。如果你有
	一台主机没有安装Libcurl扩展，你可以使用基于PHP streams的HTTP handler来替代。但是性能会变差，因为Libcurl扩展要
	快得多。

## 版本矩阵

Elasticsearch-PHP的版本要和Elasticsearch版本适配。

Elasticsearch-PHP的master分支总是与Elasticsearch的master分支相一致，但不建议在生产环境代码中使用dev-master分支。

	Elasticsearch Version	Elasticsearch-PHP Branch
	>= 6.0                   6.0
	>= 5.0,⇐ 6.0            5.0
	>= 1.0,⇐ 5.0            1.0, 2.0
	⇐ 0.90.*                0.4

## Composer安装

* 在composer.json文件中增加elasticsearch-php。如果你是新建项目，那么把以下的代码复制粘贴到composer.json就行了。如果是在现有项目中添加elasticsearch-php，那么把elasticsearch-php添加到其它的包名后面即可：

		{
		    "require": {
		        "elasticsearch/elasticsearch": "~6.0"
		    }
		}

* 要使用composer安装客户端。首先要用下面第一个命令来安装composer.phar，然后使用第二个命令来执行安装程序。composer会自动下载所有的依赖，把下载的依赖存储在/vendor/目录下，并且创建一个autoloader：
	
		curl -s http://getcomposer.org/installer | php
		php composer.phar install --no-dev
关于Composer的详情请查看[Composer 中文网](https://www.phpcomposer.com/)

* 最后加载autoload.php。如果你现有项目是用Composer安装的，那么autoload.php也许已经在某处加载了，你就不必再加载。最后实例化一个客户端对象：
	
		require 'vendor/autoload.php';
		
		$client = Elasticsearch\ClientBuilder::create()->build();
客户端对象的实例化主要是使用静态方法create()，这里会创建一个ClientBuilder对象，主要是用来设置一些自定义配置。如果你配置完了，你就可以调用build()方法来创建一个Client对象。我们会在配置一节中详细说明配置方法。

## --no-dev标志

你会注意到安装命令行指定了--no-dev。这里是防止Composer安装各种测试依赖包和开发依赖包。对于普通用户没有必要安装测试包。特别是开发依赖包包含了Elasticsearch的一套源码，这是为了以REST API的方式进行测试。然而这对于非开发者来说太大了，因此要使用--no-dev。

如果你想帮助完善这个客户端类库，那就删掉--no-dev标志来进行测试吧。