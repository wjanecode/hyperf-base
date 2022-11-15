# 基于hyperf的扩展基础包
## 简介
### 使用：hyperf项目
### 封装功能：
- 跨域中间件
- 限频中间件 (需要session中间件)
- wgw请求协议认证
- 验证码
- auth认证
- Redis锁服务
- 发送邮箱
- 上传文件到七牛云
- 日记服务

### 安装使用
```
composer require wjanecode/hyperf-base
```

```
//发布配置文件
php bin/hyperf.php vendor:publish wjanecode/hyperf-base
```
### wgw协议
请求内容防篡改
- 签名算法: 在hyperf-common.php配置好appId和appSecret 
- 第一步:生成当前时间戳timestamp和随机字符串nonce 
- 第二步:取出协议中的interface.name和param, php eg. $name = $reqBody['interface']['name']; 
- 第三步:将第一步取出的参数按照如下加入到param, php eg. $param['interfaceName'] = $name; 
- 第四步:将第二步的param参数按照首字母升序 
- 第五步:将第四部参数数组json编码后进行md5编码得到参数字符串paramString,注意这里json编码不要主动编码为Unicode,不转义/字符 
- 第六步:按照下面的格式拼接参数: appId=$appId&appSecret=$appSecret&nonce=$nonce&timestamp=$timestamp&$paramString; 
- 第七步:用appSecret和第六步字符串采用sha256算法算出签名 
- 第八步:将得到的签名使用参数名signature加入到auth

如果是接口带文件上传，需要将上述得到的auth和interface字段进行json编码,后端会在获取到请求的时候自动解码
#### 请求格式
```json
{
    "timestamp": 1668454867,
    "auth": {
        "timestamp": 1668454867,
        "nonce": "1668454867",
        "signature": "4bf1ecf2966b1d6cda8be5715bb1aae7edb77852495e84c58469318c65a775d3",
        "appId": "test1"
    },
    "version": "1.0",
    "eventId": 1668454867,
    "caller": "vue-admin",
    "seqId": "1668454867",
    "interface": {
        "name": "common.about.info",
        "param": {}
    }
}
```
#### 前端加密示例
```js
export function callSafeService(interfaceName, params) 
{
    let AppReqAuthAppId = process.env.APP_REQ_ID;
    let AppReqAuthSecret = process.env.APP_REQ_SECRET;

    let timestamp = new Date().getTime()
    timestamp = parseInt(timestamp/1000)
    let nonce = String(timestamp);
    let signParam = deepCopyObject(params);
    signParam['interfaceName'] = interfaceName;
    signParam = ksort(signParam);
    let signParamString = JSON.stringify(signParam);
    console.log('step 1:'+signParamString);
    let MD5 = new Hashes.MD5({utf8:true});
    signParamString = MD5.hex(signParamString);
    console.log('sign param md5:'+signParamString);
    let base = "appId="+AppReqAuthAppId+"&appSecret="+AppReqAuthSecret+"&nonce="+nonce+"&timestamp="+timestamp+"&"+signParamString
    console.log("sign base:"+base)
    let SHA256 = new Hashes.SHA256;
    let signature = SHA256.hex_hmac(AppReqAuthSecret,base)
    let auth = {
      'timestamp':timestamp,
      'nonce':nonce,
      'signature':signature,
      'appId':AppReqAuthAppId
    }
    const postData = {
        'token':getToken(),
        'timestamp':timestamp,
        'auth': auth,
        'version':'1.0',
        'eventId':timestamp,
        'caller':'vue-admin',
        'seqId':String(timestamp),
        'interface':{
            'name':interfaceName,
            'param':params
        }
    }

    console.log(JSON.stringify(postData))

    return request({
        url: '',
        method: 'post',
        data: postData,
        headers: {
            'content-type':'application/json',
            'Access-Control-Allow-Origin':'*'
        }
    })
}
```

#### 后端示例代码
```php
 private function checkSign(array $requestBody)
    {
        $auth = $requestBody["auth"];
        $timestamp = $auth["timestamp"];
        $ttl = $this->config->get("hyperf-base.wgw.sign_ttl", 10);
        $secondDidPass = Carbon::now()->diffInRealSeconds(Carbon::createFromTimestamp($timestamp));
        Log::info("sign time did pass $secondDidPass seconds!");
        if ($secondDidPass > $ttl) {
            Log::info("sign has expired!");
            throw new HyperfBaseException(ErrorCode::WGW_AUTH_SIGNATURE_ERROR, "sign expire!");
        }

        $appId = $auth["appId"];
        //能否找到对应的秘钥
        if (!isset($this->appIdSecretList[$appId])) {
            throw new HyperfBaseException(ErrorCode::WGW_AUTH_APP_ID_NOT_EXIST);
        }
        $appSecret = $this->appIdSecretList[$appId];

        $param = Arr::get($requestBody, 'interface.param');
        $interfaceName = Arr::get($requestBody, 'interface.name');
        $param["interfaceName"] = $interfaceName;
        ksort($param);
        $paramJson = json_encode($param, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        Log::info("param json:$paramJson");
        $paramString = md5($paramJson);

        $nonce = $auth["nonce"];
        $base = "appId=$appId&appSecret=$appSecret&nonce=$nonce&timestamp=$timestamp&$paramString";

        Log::info("sign base:".$base);
        $paramSignature = $auth["signature"];
        $signature = hash_hmac("sha256", $base, $appSecret);
        if ($signature != $paramSignature) {
            Log::error("signature check fail!");
            Log::info("server sign:$signature");
            Log::info("client sign:$paramSignature");

            throw new HyperfBaseException(ErrorCode::WGW_AUTH_SIGNATURE_ERROR);
        }
   }
```


借鉴自https://github.com/zyprosoft/hyperf-common