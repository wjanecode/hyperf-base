<?php
declare(strict_types=1);

namespace WJaneCode\HyperfBase\Constants;

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
    const SERVER_ERROR = 500;

    /**
     * @Message("param validate fail!")
     */
    const PARAM_ERROR = 9999;

    /**
     * @Message("DB error!")
     */
    const DB_ERROR = 9998;

    /**
     * @Message("ZGW request body error!")
     */
    const ZGW_REQUEST_BODY_ERROR = 9997;

    /**
     * @Message("ZGW request auth appId not exist!")
     */
    const ZGW_AUTH_APP_ID_NOT_EXIST = 9996;

    /**
     * @Message("ZGW request auth signature error!")
     */
    const ZGW_AUTH_SIGNATURE_ERROR = 9995;

    /**
     * @Message("Request rate reach limit!")
     */
    const REQUEST_RATE_LIMIT = 9994;

    /**
     * @Message("Record not exist!")
     */
    const RECORD_NOT_EXIST = 10000;

    /**
     * @Message("Token not validate!")
     */
    const TOKEN_NOT_VALIDATE = 10001;

    /**
     * @Message("Record did exist!")
     */
    const RECORD_DID_EXIST = 10002;

    /**
     * @Message("Logout fail!")
     */
    const LOGOUT_FAIL = 10003;

    /**
     * @Message("Auth fail!")
     */
    const AUTH_FAIL = 10004;

    /**
     * @Message("User not approved!")
     */
    const USER_NOT_APPROVED = 10005;

    /**
     * @Message("Update record fail!")
     */
    const UPDATE_RECORD_FAIL = 10006;

    /**
     * @Message("Action need admin role!")
     */
    const ACTION_REQUIRE_ADMIN = 10008;

    /**
     * @Message("Module call fail!")
     */
    const MODULE_CALL_FAIL = 10011;

    /**
     * @Message("USER HAS NO PERMISSION DO THIS ACTION!")
     */
    const PERMISSION_ERROR = 10012;

    /**
     * @Message ("Captcha expired")
     */
    const SYSTEM_ERROR_CAPTCHA_EXPIRED = 10013;

    /**
     * @Message ("Captcha check invalidate")
     */
    const SYSTEM_ERROR_CAPTCHA_INVALIDATE = 10014;

    /**
     * @Message ("Create captcha dir fail")
     */
    const SYSTEM_ERROR_CAPTCHA_DIR_CREATE_FAIL = 10015;

    /**
     * @Message ("Qiniu upload config not set")
     */
    const SYSTEM_ERROR_QINIU_UPLOAD_CONFIG_NOT_SET = 10016;

    /**
     * @Message ("No upload file found")
     */
    const SYSTEM_ERROR_NO_UPLOAD_FILE_FOUND = 10017;

    /**
     * @Message ("Upload move file fail!")
     */
    const SYSTEM_ERROR_UPLOAD_MOVE_FILE_FAIL = 10018;

    /**
     * @Message ("Upload file size too big!")
     */
    const SYSTEM_ERROR_UPLOAD_FILE_SIZE_TOO_BIG = 10019;

    /**
     * @Message ("Upload file mime type is not allowed!")
     */
    const SYSTEM_ERROR_UPLOAD_FILE_MIME_NOT_ALLOWED= 10020;

    /**
     * @Message("content is not json")
     */
    const NOT_JSON = 10021;

    /**
     * @Message("business code error")
     */
    const BUSINESS_CODE_NOT_SUCCESS = 10022;


}