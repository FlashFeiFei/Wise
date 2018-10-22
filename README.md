# Wise
智能小程序
仿照easywechat 4.x版本写的

微信响应的格式和智能小程序的不一样，需要修改解析基类的方法，现在暂时没修改

OpenPlatform 中的 HttpClient并没有获取小程序信息的接口，因为获取小程序的接口需要的token是授权用户的token，所以这个接口必须在别的地方写