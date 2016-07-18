<<<<<<< Updated upstream
# Youzan Zan PHP framework
=======
#Overview##
----
Zan是基于协程的PHP网络服务框架，以最简单的方式开发C10K+并发的web服务或SOA服务

##Features##
----
1. 基于"yield"实现了独立堆栈的协程
2. 类似于Golang的并发编程模型实现
3. 基于swoole提供非阻塞IO服务
4. 内置连接池组件（内置mysql,redis,syslog,...多种组件）
5. 类似Golang的defer机制解决由于异常导致的资源未释放、锁未释放的问题
6. 可继承的view布局及组件化支持，方便完成bigPipe/bigRender/首屏加载优化等不同的渲染方式
7. 基于模型驱动的SQLMap，实现了SQL的快速定位及方便的sharding、cache支持
8. 提供类似于laravel的middleware(Filters & Terminators)机制
9. Di及单元测试的良好支持
10. 良好的服务化对接支持

##DOC##
----
https://github.com/youzan/zan-doc/blob/master/zh/SUMMARY.md
>>>>>>> Stashed changes

基于Swoole和PHP协程构建的高性能服务框架。路由、过滤器、SQL Map、单元测试（实现了对yield的支持）、调用链、可无限继承的View、服务监控等特性，便于构建大型 SOA 应用。

<<<<<<< Updated upstream
## 官方文档

Zan PHP 框架的文档我们托管在了 [GitBook/zan-doc](https://agalwood.gitbooks.io/zan-doc/content/zh/)。


## 常用链接

- [zan-doc](https://github.com/youzan/zan-doc) - Zan PHP 开发者文档
- [zan-installer](https://github.com/youzan/zan-installer) - Zan PHP 脚手架工具
- [zanhttp](https://github.com/youzan/zanhttp) - Zan PHP HTTP demo
- [zan-hign-performance-mysql](https://github.com/youzan/zan_high_performance_mysql) - Zan PHP 高性能MySQL实践


## 开发交流

QQ群：115728122


## License

我们[有赞](https://youzan.com/)的 [Zan PHP框架](https://github.com/youzan/zan)基于 [Apache-2.0 license](https://opensource.org/licenses/Apache-2.0) 进行开源。
=======
##Demo##
----
Demo: https://github.com/youzan/zan-installer

##交流方式##
----
QQ群: 115728122
>>>>>>> Stashed changes
