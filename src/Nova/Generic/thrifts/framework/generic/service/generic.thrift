namespace nova com.youzan.nova.framework.generic.service

struct GenericRequest {
    1:optional string serviceName;
    2:optional string methodName;
    3:optional string methodParams;
}

service GenericService {
    string invoke(1:GenericRequest request);
}