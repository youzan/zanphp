## Change Log

### 20170501 

1. 加入swoole内部连接池兼容代码
2. 加入websocket支持
3. 移除对旧版本swoole两个旧版驱动的支持
4. 移除所有aerospike相关代码
5. FIX worker重启时候获取nova连接失败问题，加入重试逻辑
6. FIX 连接池在dns解析回调初始化导致worker启动时获取不懂连接的问题，加入重试逻辑


### 20170511 修复请求filter流程定时器没有清除BUG


### 20170320 Redis调用加入超时，默认2000ms，可在连接中配置 (影响Store + Cache) 

```php
<?php 
return [
    'codis' => [
        'engine'=> 'redis',
        'host' => '127.0.0.1',
        'port' => 6602,
        'pool'  => [],
        'timeout' => 1000, // ms
    ],
];
```
------------------------------------------------------------------------------------------


### 20170315 添加可选配置文件 config/env/apiconf.php, 配置优先于ApiConfig 

------------------------------------------------------------------------------------------

### 20170314 HTTP Server 自定义异常处理注册

resource\middleware目录下增加exceptionHandler.php配置,示例如下:

```php
<?php

return [
     'match' => [
         [
            "index/index/index",  "all",
         ],
//         [
//             ".*", "all"
//         ]
     ],
     'group' => [
         "all" => [
             TestExceptionHandler::class,
         ],
     ],
];
 
```

```php
<?php
class TestExceptionHandler implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        // 针对异常自行判断决定是否
        return new Response("网络错误");
        // or 
        return null; // 不做处理
    }
}

```

------------------------------------------------------------------------------------------
### TCP Server 自定义异常处理注册
resource\middleware目录下增加exceptionHandler.php配置,示例如下:
```php
<?php
use Zan\Framework\Network\Tcp\Exception\Handler\GenericExceptionHandler;

return [
    'match' => [
        [
            "/com/youzan/nova/framework/generic/service/GenericService/invoke", "genericExceptionHandlerGroup",
        ],
        [
            "/Com/Youzan/Nova/Framework/Generic/Php/Service/GenericTestService/ThrowException", "genericExceptionHandlerGroup",
        ],
        [
            ".*", "all"
        ]
    ],
    'group' => [
        "genericExceptionHandlerGroup" => [
            GenericExceptionHandler::class
        ],
        "all" => [
            GenericExceptionHandler::class
        ],
    ],
];
```

match下添加serviceName和对应的handler group,框架自动根据serviceName匹配,成功后执行对应group下的handler,
handler class示例为:
```php
<?php
class GenericExceptionHandler implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        sys_error("GenericExceptionHandler handle: ".$e->getMessage());
        throw new \Exception("网络错误", 0);
    }
}
```
转换成自定义异常抛出即可。

------------------------------------------------------------------------------------------

### 2017-02-27 Feature 

#### haunt.php 添加可选配置项 "app_configs"

```php
return [
     // 拉取需要的服务列表，此处填写注册到注册中心的的app name，如果无需拉去任何服务，app_names为空array即可
    'app_names' => [
        'scrm-api',
        'pf-api',
    ],

    // 拉取app配置 
    'app_configs' => [
        // 从 com.youzan.service 域拉取scrm-api 服务
        'scrm-api' => [
            'protocol' => 'nova',
            'namespace' => 'com.youzan.service',
        ],
        // 从 com.youzan.test 域拉取pf-api 服务
        'pf-api' => [
            'protocol' => 'nova',
            'namespace' => 'com.youzan.test',
        ],
    ],
];
```

#### nova.php "novaApi" 配置项支持发布多个thrift package


```php
// 兼容旧的配置方式
return [
    'novaApi' => [
        'path'  => 'vendor/nova-service/pf/gen-php',
        'namespace' => 'Com\\Youzan\\Pf\\',
    ],
];
```

```php
// 多组 package 配置方式
// 注意: 多package 需要 'namespace' 相同
// 注意: haunt agent 对 同ip+port发布多个虚拟应用做了限制, 不可用
return [
    'novaApi' => [
        [
            'path'  => 'vendor/nova-service/scrm-base/gen-php',
            'namespace' => 'Com\\Youzan\\Scrm\\',
            'domain' => 'com.youzan.service', // 可选, 默认 com.youzan.service, 配置服务发布到 具体的域
            'appName'   => 'scrm', // 可选, 默认Application::getName(), 配置服务发布的 应用名
            'protocol'   => 'nova', // 可选, 目前恒等于 nova
        ],
        [
            'path'  => 'vendor/nova-service/scrm-core/gen-php',
            'namespace' => 'Com\\Youzan\\Scrm\\',
            'domain' => 'com.youzan.service', // 可选, 默认 com.youzan.service, 配置服务发布到 具体的域
            'appName'   => 'scrm', // 可选, 默认Application::getName(), 配置服务发布的 应用名
            'protocol'   => 'nova', // 可选, 目前恒等于 nova
        ],
    ],
];
```

#### 服务注册一些说明:

1. 服务注册时会将 Protocol + Namespace + SrvName + IP + Port 做为 etcdKey;
2. srvList 中etcdKey同名, 后面的一组服务会覆盖前面注册的服务;
3. srvList 是用来注册多组 不同namespace, 或者不同SrvName的 的服务
4. zan中protocol === nova
5. zan中namespace 可在 novaApi 中配置, 默认 com.youzan.service
6. zan中SrvName 可在 novaApi 中配置, 默认 Application::getName()
7. 多个thrift package 按etcdKey分组注册, 每组追加泛化调用方法

#### 服务拉取的说明:

1. 从etcd服务拉取是以app为维度的, 返回app实例的n个节点

#### thrift 与 app

1. thrift 文件中 namespace nova com.youzan.a.b.c 中 a 与 appName 没有必然联系, 可以不相同


------------------------------------------------------------------------------------------

### 2017-02-20 Feature 支持多App配置

添加 root/resource/config_{{appName}} 配置路径, 注意: appName "-"替换为"_"

root/resource/config_{{appName}}/ 配置优先于 root/resource/config

------------------------------------------------------------------------------------------

### 2017-02-04 Feature

mysql 添加异步事务支持

------------------------------------------------------------------------------------------
### 2017-01-17 Fix

Url::site添加https作为默认scheme

------------------------------------------------------------------------------------------

### 2017-01-16 Feature

1. 支持动态权重变更、软负载
2. 框架错误记录到log
3. hawk SDK重构，更全的监控信息上报

------------------------------------------------------------------------------------------

### 2017-01-02 Fix

修复async_mysql回调内与swoole代码不匹配造成的Notice错误与异常信息缺失;

------------------------------------------------------------------------------------------

### 2016-12-28 Fix

修复调度器在发生异常情况下调度Async任务的bug;

------------------------------------------------------------------------------------------

### 2016-12-23 Feature

Tcp连接与Redis增加对Unix Socket支持; 添加 path 配置项;

配置:

```
<?php

return [
    'kv_redis' => [
        'engine'=> 'redis',
	    'path' => "/var/run/yz-tether/redis2aerospike.sock",
        'pool'  => [ ... ],
    ],
];
```

------------------------------------------------------------------------------------------

### 2016-12-23

网络错误添加code用以与iron区分, 格式: 网络错误(code)

------------------------------------------------------------------------------------------

### 2016-12-22 Feature

#### set Cookie时, 如果没有指定Domain, 根据Request Host自动匹配; 规则:

#### a. 内置Host列表,

```
[
    '.koudaitong.com',
    '.youzan.com',
    '.qima-inc.com',
    '.kdt.im',
]
```

#### b. 可以通过cookie.php配置文件添加该列表

```php
<?php
return [
    'domain' => 'foo.youzan.com',
    // or
    'domain' => ["127.0.0.1", 'foo.youzan.com'],
    // ... 其他配置
];
````

#### c. 优先匹配子域

当request host为 bar.foo.youzan.com, domainList 中同时有 ['.youzan.com', 'foo.youzan.com', ...] 

优先匹配 foo.youzan.com;

------------------------------------------------------------------------------------------

### 2016-12-16 Feature

Store 添加 `del`, `hDel`, `incr`, `incrBy`, `hIncrBy` 5个接口

KV::incr 需要使用 Store::hIncrBy($configKey, $fmtArgs, Store::DEFAULT_BIN_NAME, $value) 进行兼容替换

------------------------------------------------------------------------------------------

### 2016-12-14 Fix

修复ParallelException被Throw到父Task的BUG;

------------------------------------------------------------------------------------------

### 2016-12-14 Feature

添加 getRpcContext(k) setRpcContext(k, v) 系统调用, 通过nova协议上下文透传消息;

------------------------------------------------------------------------------------------

### 2016-12-13 Feature

#### 添加Store类, 通过Redis协议访问KV , 解决KV连接池与coredump问题

1. 使用KV接口存储的字符串, 可以使用Store接口取出;
2. 使用KV接口存储的非字符串类型, 必须做数据迁移;
3. 使用Store接口存储的值，使用KV接口无法取出;

#### 配置
```
<?php
// connection/kvstore.php

return [
    'kv_redis' => [
        'engine'=> 'redis',
        'host' => 'xx.xx.xx.xx',
        'port' => 6666,
        'pool'  => [
            'maximum-connection-count' => 50,
            'minimum-connection-count' => 10,
            'init-connection'=> 10,
        ],
    ],
]
```

#### 兼容修改

为避免数据迁移, 修改接口兼容数据:

1. KV::set  替换为 Store::hSet;
2. KV::hSet  替换为 Store::hSet;
3. KV::set  使用 Store::hGet 获取数据;
4. KV::hSet  使用 Store::hGet 获取数据;


```
<?php

yield KV::set("scrm_kv.customer", $fmt, $value);
// 替换为
yield Store::hSet("scrm_kv2.customer", $fmt, Store::DEFAULT_BIN_NAME, $value);
// 获取数据
yield Store::hGet("scrm_kv2.customer", $fmt, Store::DEFAULT_BIN_NAME);

//////////////////////////////////////////////////

yield KV::hSet("scrm_kv.customer", $fmt, $bin, $randStr);
// 替换为
yield Store::hSet("scrm_kv2.customer", $fmt, $bin, $value);
// 获取数据
yield Store::hGet("scrm_kv2.customer", $fmt, $bin);
```


#### 备注

AS与REDIS协议映射关系参考 :

AS                  | REDIS
--------------------|------------
namespace:set:{key} | hash key
bin                 | hash field
{value}             | hash value

set ns:set:key def_bin value
get ns:set:key def_bin

hset ns:set:key bin value
hget ns:set:key bin 

------------------------------------------------------------------------------------------

### 2016-12-13 Fix

1. 修复nova协议编码bug
    1. encode过程抛出异常, 重新encode异常时buffer没有清除, 导致序列化二进制数据错误;
    2. 影响zan与iron, 已同时修复nova同步与异步版本

------------------------------------------------------------------------------------------

### 2016-12-12 Feature

1. MysqliQueryTimeoutException上下文加入超时sql与超时时间
2. 添加异步DnsClient, $ip = (yield DnsClient::lookup("www.youzan.com"));

------------------------------------------------------------------------------------------

### 2016-12-12 Fix

1. LZ4 大于1024bytes 解压失败
2. 强制关闭swoole worker自动重启(未考虑请求处理完), 使用zan框架重启机制
3. HttpClient dns查询加入超时机制(1s)

------------------------------------------------------------------------------------------

### 2016-12-05 Feature 

nova服务支持追加参数,向后兼容

```
service ArgTestService {
    string func(1:i32 arg1);
}
```

thrift定义修改,命名空间与方法名不变,追加参数;

```
struct ObjArg {
    1:optional i32 intArg
}

service ArgTestService {
    string func(1:i32 arg1, 2:ObjArg objArg);
}
```

服务提供者func服务实现方法需配置`参数默认值`, 即可支持旧SDK调用;

```
public function func1($arg1, ObjArg $objArg = null);
```