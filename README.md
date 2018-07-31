## 企鹅号(腾讯开放平台)的接口封装
> 对open.om.qq.com的接口封装

## 使用
```
$config = new Config($clientId, $clientSecret);

/* 获取AccessToken */
$tokenApi = new Token($config);
redirectTo($tokenApi->authorizeUrl('your_return_url', '自定义STATE'));
// when receive
$tokenApi->accessToken($code);

/* 使用AccessToken调用API */
$tokenApi->openid = $openid;
$api = new Api($tokenApi);
$responseArr = $api->articleList();
```

## 奇怪的问题
> 提交都是用POST，但是文档中参数都是从URL传的，发图文也是。我试了只把content通过POST传，不能成功。