<?php

declare(strict_types=1);
/**
 * @link     https://51coode.com
 * @contact  https://51coode.com
 */
namespace WJaneCode\HyperfBase\Constant;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * 错误码分层
 * @Constants
 */
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Server Error!")
     */
    public const SERVER_ERROR = 500;

    /**
     * @Message("param validate fail!")
     */
    public const PARAM_ERROR = 9999;

    /**
     * @Message("DB error!")
     */
    public const DB_ERROR = 9998;

    /**
     * @Message("WGW request body error!")
     */
    public const WGW_REQUEST_BODY_ERROR = 9997;

    /**
     * @Message("WGW request auth appId not exist!")
     */
    public const WGW_AUTH_APP_ID_NOT_EXIST = 9996;

    /**
     * @Message("WGW request auth signature error!")
     */
    public const WGW_AUTH_SIGNATURE_ERROR = 9995;

    /**
     * @Message("Request rate reach limit!")
     */
    public const REQUEST_RATE_LIMIT = 9994;

    /**
     * @Message("Record not exist!")
     */
    public const RECORD_NOT_EXIST = 10000;

    /**
     * @Message("Token not validate!")
     */
    public const TOKEN_NOT_VALIDATE = 10001;

    /**
     * @Message("Record did exist!")
     */
    public const RECORD_DID_EXIST = 10002;

    /**
     * @Message("Logout fail!")
     */
    public const LOGOUT_FAIL = 10003;

    /**
     * @Message("Auth fail!")
     */
    public const AUTH_FAIL = 10004;

    /**
     * @Message("User not approved!")
     */
    public const USER_NOT_APPROVED = 10005;

    /**
     * @Message("Update record fail!")
     */
    public const UPDATE_RECORD_FAIL = 10006;

    /**
     * @Message("Action need admin role!")
     */
    public const ACTION_REQUIRE_ADMIN = 10008;

    /**
     * @Message("Module call fail!")
     */
    public const MODULE_CALL_FAIL = 10011;

    /**
     * @Message("USER HAS NO PERMISSION DO THIS ACTION!")
     */
    public const PERMISSION_ERROR = 10012;

    /**
     * @Message("Captcha expired")
     */
    public const SYSTEM_ERROR_CAPTCHA_EXPIRED = 10013;

    /**
     * @Message("Captcha check invalidate")
     */
    public const SYSTEM_ERROR_CAPTCHA_INVALIDATE = 10014;

    /**
     * @Message("Create captcha dir fail")
     */
    public const SYSTEM_ERROR_CAPTCHA_DIR_CREATE_FAIL = 10015;

    /**
     * @Message("Qiniu upload config not set")
     */
    public const SYSTEM_ERROR_QINIU_UPLOAD_CONFIG_NOT_SET = 10016;

    /**
     * @Message("No upload file found")
     */
    public const SYSTEM_ERROR_NO_UPLOAD_FILE_FOUND = 10017;

    /**
     * @Message("Upload move file fail!")
     */
    public const SYSTEM_ERROR_UPLOAD_MOVE_FILE_FAIL = 10018;

    /**
     * @Message("Upload file size too big!")
     */
    public const SYSTEM_ERROR_UPLOAD_FILE_SIZE_TOO_BIG = 10019;

    /**
     * @Message("Upload file mime type is not allowed!")
     */
    public const SYSTEM_ERROR_UPLOAD_FILE_MIME_NOT_ALLOWED = 10020;

    /**
     * @Message("content is not json")
     */
    public const NOT_JSON = 10021;

    /**
     * @Message("business code error")
     */
    public const BUSINESS_CODE_NOT_SUCCESS = 10022;
}
