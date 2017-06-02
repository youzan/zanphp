namespace * com.youzan.novaTcpDemo.service

include '../entity/Demo.thrift'

exception NovaTcpDemoException {
1: string message
2: i32 code
}

service DemoService {
    string echoBack(1:string name),
    Demo.Demo hello(1:string name),
    string testException() throws (1: NovaTcpDemoException ouch),
    Demo.Demo returnNullResult(),
    list<i32> returnEmptyArray() throws (1: NovaTcpDemoException ouch)
}
