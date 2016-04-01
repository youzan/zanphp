
## namespace: Zan\Framework\Sdk\Timer

### Timer服务：

#### Timer::tick($interval, $callback, $params)

>  添加一个每隔 {$interval} 毫秒 执行一次的计时器任务

参数：

1. $interval 毫秒
2. $callback 回调  function ($hash, $params) {}
3. $params metadata数据 可以在上面的回调中通过$params拿到



#### Timer::after($interval, $callback, $params)

>  添加一个 {$interval} 毫秒后仅执行一次的计时器任务

参数：

1. $interval 毫秒
2. $callback 回调  function ($params) {}
3. $params metadata数据 可以在上面的回调中通过$params拿到


#### Timer::clear($hash)

>  根据timer hash 清除一个计时器任务

参数：

1. $hash 32位16进制数的字符串 通过Timer::tick 或者 Timer::after 返回获取


### Timer 管理服务:
#### TimerManager::show($type)

> 获取当前进程内所有timer的列表

参数:

1. $type 字符串 "tick" 或者 "after" 或者 null，当传null时返回所有定时器任务

#### TimerManager::get($hash)

> 根据hash获取对应timer，不存在则返回false

参数:

1. $hash 