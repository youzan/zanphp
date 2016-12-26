## Change Log

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

### 2016-12-12 Fix

1. LZ4 大于1024bytes 解压失败
2. 强制关闭swoole worker自动重启(未考虑请求处理完), 使用zan框架重启机制
3. HttpClient dns查询加入超时机制(1s)


### 2016-12-12 Feature

1. MysqliQueryTimeoutException上下文加入超时sql与超时时间
2. 添加异步DnsClient, $ip = (yield DnsClient::lookup("www.youzan.com"));


### 2016-12-13 Fix

1. 修复nova协议编码bug
    1. encode过程抛出异常, 重新encode异常时buffer没有清除, 导致序列化二进制数据错误;
    2. 影响zan与iron, 已同时修复nova同步与异步版本

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
        'host' => '10.9.17.150',
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

### 2016-12-14 Feature

添加 getRpcContext(k) setRpcContext(k, v) 系统调用, 通过nova协议上下文透传消息;

### 2016-12-14 Fix

修复ParallelException被Throw到父Task的BUG;


### 2016-12-16 Feature

Store 添加 `del`, `hDel`, `incr`, `incrBy`, `hIncrBy` 5个接口

KV::incr 需要使用 Store::hIncrBy($configKey, $fmtArgs, Store::DEFAULT_BIN_NAME, $value) 进行兼容替换


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


### 2016-12-23

网络错误添加code用以与iron区分, 格式: 网络错误(code)


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