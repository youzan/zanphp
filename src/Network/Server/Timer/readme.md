
## namespace: Zan\Framework\Sdk\Timer

### Timer服务：

#### Timer::tick($interval, $jobName, $callback)

>  添加一个每隔 {$interval} 毫秒 执行一次的计时器任务

参数：

1. $interval 毫秒
2. $jobName 调用方传入的字符串，表示当前计时器任务
3. $callback 回调  function ($jobName) {}


#### Timer::after($interval, $jobName, $callback)

>  添加一个 {$interval} 毫秒后仅执行一次的计时器任务

参数：

1. $interval 毫秒
2. $jobName 调用方传入的字符串，表示当前计时器任务
3. $callback 回调  function ($params) {}


#### Timer::clearTickJob($jobName)

>  根据timer job name 清除一个计时器任务

参数：

1. $jobName 之前调用Timer::tick时候传入的jobName

#### Timer::clearAfterJob($jobName)

>  根据timer job name 清除一个计时器任务

参数：

1. $jobName 之前调用Timer::after时候传入的jobName


### Timer 管理服务:
#### TickTimerManager::show()

> 获取当前进程内所有tick 类型的 timer的列表

#### TickTimerManager::get($jobName)

> 根据job name获取对应tick timer，不存在则返回false

参数:

1. $jobName

#### AfterTimerManager::show()

> 获取当前进程内所有after 类型的 timer的列表

#### AfterTimerManager::get($jobName)

> 根据job name获取对应after timer，不存在则返回false

参数:

1. $jobName