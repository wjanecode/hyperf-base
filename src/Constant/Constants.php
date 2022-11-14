<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Constant;

use Hyperf\Constants\AbstractConstants;

class Constants extends AbstractConstants
{
    // 用户是管理员角色
    public const USER_ROLE_ADMIN = 1;

    // 框架运行时设置请求协议
    public const WGW = 'WGW';

    // 框架请求处理时设置请求唯一标记
    public const WJANE_REQ_ID = 'WJANE-ReqId';

    // 框架请求处理识别为上传请求的处理
    public const WJANE_UPLOAD = 'WJANE-Upload';

    // 上传文件系统使用本地标记
    public const UPLOAD_SYSTEM_TYPE_LOCAL = 'local';

    // 上传文件系统使用七牛标记
    public const UPLOAD_SYSTEM_TYPE_QINIU = 'qiniu';
}
