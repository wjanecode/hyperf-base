<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Constants;

use Hyperf\Constants\AbstractConstants;

class Constants extends AbstractConstants
{

    //用户是管理员角色
    const USER_ROLE_ADMIN = 1;

    //框架运行时设置请求协议
    const WGW = "WGW";

    //框架请求处理时设置请求唯一标记
    const WJANE_REQ_ID = "WJANE-ReqId";

    //框架请求处理识别为上传请求的处理
    const WJANE_UPLOAD = "WJANE-Upload";

    //上传文件系统使用本地标记
    const UPLOAD_SYSTEM_TYPE_LOCAL = 'local';

    //上传文件系统使用七牛标记
    const UPLOAD_SYSTEM_TYPE_QINIU = 'qiniu';
}