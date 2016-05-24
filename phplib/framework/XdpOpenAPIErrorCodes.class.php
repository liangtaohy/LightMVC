<?php
/***************************************************************************
 *
 * Copyright (c) 2013 Xdp.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * Error codes and descriptions for the Xdp Open API.
 * If the developer is going to add his own error codes, to retain compatibility
 * with Xdp Open API, you may wish to begin your error codes at 10000 and above
 * 
 * @package	XdpOpenAPI
 * @author	zhujt(zhujianting@Xdp.com)
 * @version $Revision: 1.3 $
 **/

define('XDPAPI_EC_SUCCESS', 0);

/**
 * general errors
 **/
define('XDPAPI_EC_UNKNOWN', 1);
define('XDPAPI_EC_SERVICE', 2);
define('XDPAPI_EC_METHOD', 3);
define('XDPAPI_EC_TOO_MANY_CALLS', 4);
define('XDPAPI_EC_BAD_IP', 5);
define('XDPAPI_EC_PERMISSION', 6);
define('XDPAPI_EC_BAD_REFERER', 7);
define('XDPAPI_EC_NOT_LOGIN', 8);
define('XDPAPI_EC_BQL_SYNTAX', 9);
define('XDPAPI_EC_INVALID_IP', 10);
define('XDPAPI_EC_INVALID_DOMAIN', 11);
define('XDPAPI_EC_THIRD_SERVICE', 12);

/**
 * param errors
 **/
define('XDPAPI_EC_PARAM', 100);
define('XDPAPI_EC_PARAM_API_KEY', 101);
define('XDPAPI_EC_PARAM_SESSION_KEY', 102);
define('XDPAPI_EC_PARAM_CALL_ID', 103);
define('XDPAPI_EC_PARAM_SIGNATURE', 104);
define('XDPAPI_EC_PARAM_TOO_MANY', 105);
define('XDPAPI_EC_PARAM_SIGMETHOD', 106);
define('XDPAPI_EC_PARAM_TIMESTAMP', 107);
define('XDPAPI_EC_PARAM_USER_ID', 108);
define('XDPAPI_EC_PARAM_USER_FIELD', 109);
define('XDPAPI_EC_PARAM_ACCESS_TOKEN', 110);
define('XDPAPI_EC_PARAM_ACCESS_TOKEN_EXPIRED', 111);
define('XDPAPI_EC_PARAM_SESSION_KEY_EXPIRED', 112);
define('XDPAPI_EC_PARAM_USER_INFO', 113);
define('XDPAPI_EC_PARAM_IP', 114);
define('XDPAPI_EC_PARAM_RESPONSE_TYPE', 115);
define('XDPAPI_EC_PARAM_GRANT_TYPE', 116);
define('XDPAPI_EC_PARAM_MEDIA_TYPE', 117);
define('XDPAPI_EC_PARAM_REDIRECT_URI', 118);
define('XDPAPI_EC_PARAM_SECRET_KEY', 119);
define('XDPAPI_EC_PARAM_AUTHORIZATION_CODE', 120);
define('XDPAPI_EC_PARAM_XSTATE', 121);
define('XDPAPI_EC_PARAM_MEDIA_TOKEN', 122);
define('XDPAPI_EC_PARAM_BDUSS', 123);
define('XDPAPI_EC_UPLOAD_FILE_SIZE', 124);
define('XDPAPI_EC_UPLOAD_FILE_TYPE', 125);

define('XDPAPI_EC_AUTH_DENIED', 4100);
define('XDPAPI_EC_BIND_ALREADY_BINDED', 151);
define('XDPAPI_EC_BIND_NOT_BINDED', 152);

/**
 * user permission errors
 **/
define('XDPAPI_EC_PERMISSION_USER', 210);
define('XDPAPI_EC_PERMISSION_INVALID_PERM', 211);
define('XDPAPI_EC_PERMISSION_EMAIL', 212);
define('XDPAPI_EC_PERMISSION_MOBILE', 213);

/**
 * Pay API errors
 **/
define('XDPAPI_EC_PAY', 300);
define('XDPAPI_EC_PAY_ORDER_INVALID', 301);
define('XDPAPI_EC_PAY_ORDER_NOT_EXISTS', 302);
define('XDPAPI_EC_PAY_NOT_AUTHORIZED', 303);
define('XDPAPI_EC_PAY_STOPPED', 304);
define('XDPAPI_EC_PAY_PASSWORD_NEEDED', 305);
define('XDPAPI_EC_PAY_REPEATED', 306);
define('XDPAPI_EC_PAY_ORDER_CLOSED', 307);
define('XDPAPI_EC_PAY_ORDER_CANCEL', 308);
define('XDPAPI_EC_PAY_BALANCE_NOT_ENOUGH', 309);
define('XDPAPI_EC_PAY_GATEWAY_INVALID', 310);
define('XDPAPI_EC_PAY_ORDER_ADD_FAILED', 311);

/**
 * Bql errors
 */
define('XDPAPI_EC_BQL_TOO_MANY_ITEMS', 400);
define('XDPAPI_EC_BQL_TOO_MANY_SUBQUERY', 401);
define('XDPAPI_EC_BQL_SUBQUERY_COL', 402);
define('XDPAPI_EC_BQL_PARAM', 403);
define('XDPAPI_EC_BQL_PARAM_FORMAT', 404);
define('XDPAPI_EC_BQL_QUOTE', 405);
define('XDPAPI_EC_BQL_IN', 406);
define('XDPAPI_EC_BQL_COL', 407);

/**
 * SMS errors
 */

define('XDPAPI_EC_SMS_TOO_MANY_RECVS', 700);
define('XDPAPI_EC_SMS_INTERNAL_ERROR', 701);


/**
 * data store API errors
 **/
define('XDPAPI_EC_DATA_UNKNOWN_ERROR', 800); // should never happen
define('XDPAPI_EC_DATA_INVALID_OPERATION', 801);
define('XDPAPI_EC_DATA_QUOTA_EXCEEDED', 802);
define('XDPAPI_EC_DATA_OBJECT_NOT_FOUND', 803);
define('XDPAPI_EC_DATA_OBJECT_ALREADY_EXISTS', 804);
define('XDPAPI_EC_DATA_DATABASE_ERROR', 805);
define('XDPAPI_EC_MEMCACHE_ERROR', 806);
define('XDPAPI_EC_DATE_UPDATE_EMPTY', 807);     # update data

/**
 * application info errors
 **/
define('XDPAPI_EC_NO_SUCH_APP', 900);

/**
 * batch API errors
 **/
define('XDPAPI_EC_BATCH_ALREADY_STARTED', 950);
define('XDPAPI_EC_BATCH_NOT_STARTED', 951);
define('XDPAPI_EC_BATCH_TOO_MANY_ITEMS', 952);
define('XDPAPI_EC_BATCH_METHOD_NOT_ALLOWED_IN_BATCH_MODE', 953);

/**
 * auth query server error
 */
define('XDPAPI_EC_AUTHQUERY_PARAM', 960);

/**
 *  phone auth error
 */
define('XDPAPI_EC_PHONE_AUTH_UNAUTHORIZED_CLIENT',961);
define('XDPAPI_EC_PHONE_AUTH_LOGIN_FALSE',962);

/**
 * EMAIL ERROR
 */
define('XDPAPI_EC_EMAIL_SEND_FAILE', 1200);

/**
 * PASSWORD ERROR
 */
define('XDPAPI_EC_INCORRECT_PASSWORD', 1230);

define('XDPAPI_EC_COUPON_ERROR', 1300);

define('XDPAPI_EC_PAY_ERROR', 1330);

define('XDPAPI_EC_CHARGE_ERROR', 1360);

define('XDPAPI_EC_WALLET_ERROR', 1400);
define('XDPAPI_EC_NOT_OPEN_WALLET', 1401);
define('XDPAPI_EC_BALANCE_NOT_ENOUGH', 1402);
define('XDPAPI_EC_AUTOOPEN_WALLET_FAILED', 1403);

/**
 * 流控
 */
define('XDPAPI_EC_FLOW_LIMIT', 1401);

/**
 * Product specified error codes, each product has 2000 error codes,
 * following code area is reserved for some products:
 * Space	10000 ~ 11999
 * Iknow	12000 ~ 13999
 * Map		14000 ~ 15999
 * Shuoba	16000 ~ 17999
 * Tieba	18000 ~ 19999
 * Baike	20000 ~ 21999
 * Ting		22000 ~ 23999
 * hao123	24000 ~ 25999
 * ...
 *
 * We suggest product specified error codes named as XDPAPI_EC_XXX_YYY, error codes
 * and its description defined in XdpXXXOpenAPIErrorCodes.inc.php, while "XXX" is
 * the product name, such as Space or Iknow, and "YYY" is the the detail error code
 * name, ie:
 * define('XDPAPI_EC_SPACE_SPACE_NOT_EXIST', 10000);
 *
 **/
class XdpOpenAPIErrorDescs
{
    protected static $arrOpenAPIErrDescs = array(
        XDPAPI_EC_SUCCESS => 'Success',
        XDPAPI_EC_UNKNOWN => 'Unknown error', 
        XDPAPI_EC_SERVICE => 'Service temporarily unavailable', 
        XDPAPI_EC_METHOD => 'Unsupported XDP method',
        XDPAPI_EC_TOO_MANY_CALLS => 'XDP request limit reached',
        XDPAPI_EC_BAD_IP => 'Unauthorized client IP address:%s', 
        XDPAPI_EC_PERMISSION => 'No permission to access data', 
        XDPAPI_EC_BAD_REFERER => 'No permission to access data for this referer', 
        XDPAPI_EC_NOT_LOGIN => 'The user have not login', 
        XDPAPI_EC_BQL_SYNTAX => 'Bql Syntax error: %s', 
        XDPAPI_EC_INVALID_IP => 'Invalid client IP', 
        XDPAPI_EC_INVALID_DOMAIN => 'Invalid request domain', 
        XDPAPI_EC_THIRD_SERVICE  => 'Third openplatform request fail:%s', 
        
        XDPAPI_EC_PARAM => 'Invalid parameter %s',
        XDPAPI_EC_PARAM_API_KEY => 'Invalid API key', 
        XDPAPI_EC_PARAM_SESSION_KEY => 'Session key invalid or no longer valid', 
        XDPAPI_EC_PARAM_ACCESS_TOKEN => 'Access token invalid or no longer valid', 
        XDPAPI_EC_PARAM_ACCESS_TOKEN_EXPIRED => 'Access token expired', 
        XDPAPI_EC_PARAM_SESSION_KEY_EXPIRED => 'Session key expired', 
        XDPAPI_EC_PARAM_CALL_ID => 'Invalid/Used call_id parameter', 
        XDPAPI_EC_PARAM_SIGNATURE => 'Incorrect signature', 
        XDPAPI_EC_PARAM_TOO_MANY => 'Too many parameters', 
        XDPAPI_EC_PARAM_SIGMETHOD => 'Unsupported signature method', 
        XDPAPI_EC_PARAM_TIMESTAMP => 'Invalid/Used timestamp parameter', 
        XDPAPI_EC_PARAM_USER_ID => 'Invalid user id', 
        XDPAPI_EC_PARAM_USER_FIELD => 'Invalid user info field', 
        XDPAPI_EC_PARAM_USER_INFO => 'Invalid user info', 
        XDPAPI_EC_PARAM_IP => 'Invalid Ip', 
        XDPAPI_EC_PARAM_GRANT_TYPE => 'Invalid grant type', 
        XDPAPI_EC_PARAM_RESPONSE_TYPE => 'Invalid reponse type', 
        XDPAPI_EC_PARAM_MEDIA_TYPE => 'Invalid media_type', 
        XDPAPI_EC_PARAM_REDIRECT_URI => 'Invalid redirect uri', 
        XDPAPI_EC_PARAM_SECRET_KEY => 'Invalid Secret key',
        XDPAPI_EC_PARAM_AUTHORIZATION_CODE => 'Invalid authorization code',
        XDPAPI_EC_PARAM_XSTATE => 'Login session expired',
        XDPAPI_EC_PARAM_MEDIA_TOKEN => 'Invalid media_token or media_uid',
        XDPAPI_EC_PARAM_BDUSS => 'Invalid bduss',
        XDPAPI_EC_UPLOAD_FILE_SIZE => 'Invalid upload file size',
        XDPAPI_EC_UPLOAD_FILE_TYPE => 'Invalid upload file type',
            
        XDPAPI_EC_AUTH_DENIED => 'Authorization denied',
        XDPAPI_EC_BIND_ALREADY_BINDED => 'The uid has already been binded to your site',
        XDPAPI_EC_BIND_NOT_BINDED => 'The uid has not been binded yet',

        XDPAPI_EC_PERMISSION_USER => 'User not visible', 
        XDPAPI_EC_PERMISSION_INVALID_PERM => 'Unsupported permission:%s', 
        XDPAPI_EC_PERMISSION_EMAIL => 'No permission to access user email', 
        XDPAPI_EC_PERMISSION_MOBILE => 'No permission to access user mobile', 
        
        XDPAPI_EC_PAY => 'Unknown pay API error', 
        XDPAPI_EC_PAY_ORDER_INVALID => 'Order or amount format not match', 
        XDPAPI_EC_PAY_ORDER_NOT_EXISTS => 'Order not exist', 
        XDPAPI_EC_PAY_NOT_AUTHORIZED => 'App has not apply for the payment services', 
        XDPAPI_EC_PAY_STOPPED => 'Payment service for this app has been stopped',
        XDPAPI_EC_PAY_ORDER_ADD_FAILED  => 'Add Order Failed: %s',
        
        XDPAPI_EC_BQL_TOO_MANY_ITEMS => 'Each bql API can not contain more than %d items', 
        XDPAPI_EC_BQL_TOO_MANY_SUBQUERY => 'Sub-query less than %d', 
        XDPAPI_EC_BQL_SUBQUERY_COL => 'Sub-query field only one and can not contain *', 
        XDPAPI_EC_BQL_PARAM_FORMAT => 'Batch Bql `q` parameter must be json format', 
        XDPAPI_EC_BQL_PARAM => 'Invaild Bql', 
        XDPAPI_EC_BQL_QUOTE => 'single and double quote marks can not match', 
        XDPAPI_EC_BQL_IN => '`in` obtain item must be less than 500', 
        XDPAPI_EC_BQL_COL => 'Sub-query has invaild col name', 
        
        XDPAPI_EC_DATA_UNKNOWN_ERROR => 'Unknown data store API error', 
        XDPAPI_EC_DATA_INVALID_OPERATION => 'Invalid operation', 
        XDPAPI_EC_DATA_QUOTA_EXCEEDED => 'Data store allowable quota was exceeded', 
        XDPAPI_EC_DATA_OBJECT_NOT_FOUND => 'Specified object cannot be found', 
        XDPAPI_EC_DATA_OBJECT_ALREADY_EXISTS => 'Specified object already exists', 
        XDPAPI_EC_DATA_DATABASE_ERROR => 'A database error occurred. Please try again',

        XDPAPI_EC_DATE_UPDATE_EMPTY => 'Empty data input. Please add your info',
        
        XDPAPI_EC_NO_SUCH_APP => 'No such application exists', 
        
        XDPAPI_EC_BATCH_ALREADY_STARTED => 'begin_batch already called, please make sure to call end_batch first', 
        XDPAPI_EC_BATCH_NOT_STARTED => 'end_batch called before start_batch', 
        XDPAPI_EC_BATCH_TOO_MANY_ITEMS => 'Each batch API can not contain more than %s items', 
        XDPAPI_EC_BATCH_METHOD_NOT_ALLOWED_IN_BATCH_MODE => 'This method is not allowed in batch mode', 
        
        XDPAPI_EC_AUTHQUERY_PARAM => 'Invalid auth query parameter',
    	XDPAPI_EC_PHONE_AUTH_UNAUTHORIZED_CLIENT => 'phone auth without permission',
        XDPAPI_EC_PHONE_AUTH_LOGIN_FALSE => 'phone auth passport login false',
        XDPAPI_EC_SMS_TOO_MANY_RECVS    => 'too many sms receivers: %d',
        XDPAPI_EC_SMS_INTERNAL_ERROR    => 'sms internal error: %s',
        XDPAPI_EC_PAY_PASSWORD_NEEDED   => 'pay_password_needed',
        XDPAPI_EC_PAY_REPEATED          => 'pay_repeated: %s',
        XDPAPI_EC_PAY_ORDER_CLOSED      => 'order_closed: %s',
        XDPAPI_EC_PAY_ORDER_CANCEL      => 'order_canceled: %s',
        XDPAPI_EC_PAY_BALANCE_NOT_ENOUGH    => 'wallet balance not enough: %s',

        XDPAPI_EC_EMAIL_SEND_FAILE => 'email send faile',

        XDPAPI_EC_INCORRECT_PASSWORD => 'incorrect password',

        XDPAPI_EC_COUPON_ERROR => 'coupon error: %s',

        XDPAPI_EC_PAY_ERROR => 'pay error: %s',

        XDPAPI_EC_CHARGE_ERROR => 'charge error: %s',

        XDPAPI_EC_WALLET_ERROR => 'wallet error: %s',

        XDPAPI_EC_NOT_OPEN_WALLET => 'Open wallet first',
        XDPAPI_EC_BALANCE_NOT_ENOUGH => 'Balance is not enough',
        XDPAPI_EC_AUTOOPEN_WALLET_FAILED => 'auto open wallet error: %s',
        XDPAPI_EC_FLOW_LIMIT    => 'flow limit: %s',
    );

    public static function errmsg($errcode)
    {
        if (isset(self::$arrOpenAPIErrDescs)) {
            return self::$arrOpenAPIErrDescs[$errcode];
        } else {
            return self::$arrOpenAPIErrDescs[XDPAPI_EC_UNKNOWN];
        }
    }

    public static function register($arrErrDescs)
    {
        self::$arrOpenAPIErrDescs = self::$arrOpenAPIErrDescs + $arrErrDescs;
    }
}

class XdpOpenAPIException extends Exception
{

    public function __construct($errcode, $errmsg = null)
    {
        if (empty($errmsg)) {
            $errmsg = XdpOpenAPIErrorDescs::errmsg($errcode);
        }
        $argv = func_get_args();
        $errmsg_arg = empty($argv[2]) ? null : $argv[2];
        $errmsg = sprintf($errmsg, $errmsg_arg);
        parent::__construct($errmsg, $errcode);
    }
}


/* vim: set ts=4 sw=4 sts=4 tw=100 noet: */
