## Change Log

### 2016-12-05 nova服务支持追加参数,向后兼容

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

### 2016-12-12 

1. MysqliQueryTimeoutException上下文加入超时sql与超时时间
2. 添加异步DnsClient, $ip = (yield DnsClient::lookup("www.youzan.com"));

### 2016-12-12 FIX BUG

1. LZ4 大于1024bytes 解压失败
2. 强制关闭swoole worker自动重启(未考虑请求处理完), 使用zan框架重启机制
3. HttpClient dns查询加入超时机制(1s)