# Zan框架测试
##一、运行环境准备
1.mysql server

2.redis server

修改test/resource/config/{ENV}/connection下的mysql用户名和密码


##二、启动server
除mysql和redis之外的其他server采用mock方式实现,启动方法:

cd zan/mockServer

sh ./go.sh start  启动server


##三、执行测试
cd zan

phpunit

##四、关闭server
cd zan/mockServer

sh ./go.sh stop   关闭server