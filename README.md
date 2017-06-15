<p>
<a href="https://github.com/youzan/"><img alt="有赞logo" width="36px" src="https://img.yzcdn.cn/public_files/2017/02/09/e84aa8cbbf7852688c86218c1f3bbf17.png" alt="youzan">
</a>
</p>
<p align="center">
    <img src="https://github.com/youzan/zanphp.io/blob/master/src/img/zan-logo-small@2x.png?raw=true" alt="zanphp logo" srcset="https://github.com/youzan/zanphp.io/blob/master/src/img/zan-logo-small.png?raw=true 1x, https://github.com/youzan/zanphp.io/blob/master/src/img/zan-logo-small@2x.png?raw=true 2x, https://github.com/youzan/zanphp.io/blob/master/src/img/zan-logo-small.png?raw=true" width="210" height="210">
</p>

<p align="center">基于 PHP 协程的网络服务框架，提供最简单的方式开发面向 C10K+ 的高并发SOA服务和RPC服务。</p>
<p align="center">每天为2,000+个服务提供300,000,000+次访问量支持，广泛应用于有赞各项业务。</p>


## 核心特性
1. 基于 `yield` 实现了独立堆栈的协程
2. 类似于 Golang 的并发编程模型实现
3. 基于 [zan](https://github.com/youzan/zan) 提供异步非阻塞I/O服务
4. 连接池支持（内置 MySQL、Redis、syslog 等多种组件）
5. 类似 Golang 的 defer 机制解决由于异常导致的资源未释放、锁未释放的问题
6. 可继承的View布局及组件化支持，方便完成 bigPipe/bigRender/ 首屏加载优化等不同的渲染方式
7. 基于模型驱动的 SQLMap，实现了 SQL 的快速定位及方便的 sharding、cache 支持
8. 提供类似于 [Laravel](https://github.com/laravel/laravel) 的 middleware(Filters & Terminators) 机制
9. Di及单元测试的良好支持
10. 完整的RPC远程服务调用方案

## 框架定位
ZanPHP 的定位是高并发 Web 服务或业务中间件。

ZanPHP 参考了很多 Golang 特性，不过目的绝不是为了替换掉 Golang。

PHP 在业务系统开发上的优势明显，而 Golang 相信会是将来系统编程的霸主。

ZanPHP 和 Golang 的边界是：ZanPHP做业务系统；Golang
做平台系统（中间件或基础服务组件）。

而 ZanPHP 和 Golang 编程模型的驱近，是希望能给PHP程序员一个更好的桥梁到Golang。

理想的技术栈是：ZanPHP + Go + 少量的C/C++。

当然对于致力于终身coding的码农来说：Java依然是很难跨过去的坎。


## 官方文档

Zan PHP 的文档仓库地址：[zanphp-doc](https://github.com/youzan/zanphp-doc)。

在线查看文档 [http://zanphpdoc.zanphp.io/ ✈](http://zanphpdoc.zanphp.io/)


## 常用链接
- [zan-doc](https://github.com/youzan/zanphp-doc) - Zan PHP 开发者文档
- [zan-installer](https://github.com/youzan/zan-installer) - Zan PHP 脚手架工具
- [zanhttp](https://github.com/youzan/zanhttpdemo) - Zan PHP HTTP demo
- [zantcp](https://github.com/youzan/zantcpdemo) - Zan PHP TCP demo
- [PHP异步编程: 手把手教你实现co与Koa](https://github.com/youzan/php-co-koa) 


## 官方交流渠道
官网：[点我进入](http://zanphp.io)
QQ群：115728122

## 捐赠我们
[捐赠通道](http://zanphp.io/donate)

## License

[Zan PHP 框架](https://github.com/youzan/zan)基于 [MIT license](https://opensource.org/licenses/MIT) 进行开源。
