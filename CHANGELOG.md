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
