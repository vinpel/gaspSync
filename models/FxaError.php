<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
* LoginForm is the model behind the login form.
*/
class FxaError extends Model{
  /*
  any status code, errno 999: unknown error

  The follow error responses include additional parameters:

  errno 111: a serverTime parameter giving the current server time in seconds.
  errno 114: a retryAfter parameter indicating how long the client should wait before re-trying.
  errno 120: a email parameter indicating the case used to create the account
  errno 201: a retryAfter parameter indicating how long the client should wait before re-trying
  */
  private static  $FxaError=[
    101=>[400,"attempt to create an account that already exists"],
    102=>[400,"attempt to access an account that does not exist"],
    103=>[400,"incorrect password"],
    104=>[400,"attempt to operate on an unverified account"],
    105=>[400,"invalid verification code"],
    106=>[400,"request body was not valid json"],
    107=>[400,"request body contains invalid parameters"],
    108=>[400,"request body missing required parameters"],
    109=>[401,"invalid request signature"],
    110=>[401,"invalid authentication token"],
    111=>[401,"invalid authentication timestamp"],
    112=>[411,"content-length header was not provided"],
    113=>[413,"request body too large"],
    114=>[429,"client has sent too many requests (see backoff protocol)"],
    115=>[401,"invalid authentication nonce"],
    116=>[410,"endpoint is no longer supported"],
    117=>[400,"incorrect login method for this account"],
    118=>[400,"incorrect key retrieval method for this account"],
    119=>[400,"incorrect API version for this account"],
    120=>[400,"incorrect email case"],
    201=>[503,"service temporarily unavailable to due high load (see backoff protocol)"],
    998=>[500,""],  //not in the spec
    999=>[400,"unknown error"],
    
  ];
  private static $http_status_codes = array(100 => "Continue"
  , 101 => "Switching Protocols"
  , 102 => "Processing"
  , 200 => "OK"
  , 201 => "Created"
  , 202 => "Accepted"
  , 203 => "Non-Authoritative Information"
  , 204 => "No Content"
  , 205 => "Reset Content"
  , 206 => "Partial Content"
  , 207 => "Multi-Status"
  , 300 => "Multiple Choices"
  , 301 => "Moved Permanently"
  , 302 => "Found"
  , 303 => "See Other"
  , 304 => "Not Modified"
  , 305 => "Use Proxy"
  , 306 => "(Unused)"
  , 307 => "Temporary Redirect"
  , 308 => "Permanent Redirect"
  , 400 => "Bad Request"
  , 401 => "Unauthorized"
  , 402 => "Payment Required"
  , 403 => "Forbidden"
  , 404 => "Not Found"
  , 405 => "Method Not Allowed"
  , 406 => "Not Acceptable"
  , 407 => "Proxy Authentication Required"
  , 408 => "Request Timeout"
  , 409 => "Conflict"
  , 410 => "Gone"
  , 411 => "Length Required"
  , 412 => "Precondition Failed"
  , 413 => "Request Entity Too Large"
  , 414 => "Request-URI Too Long"
  , 415 => "Unsupported Media Type"
  , 416 => "Requested Range Not Satisfiable"
  , 417 => "Expectation Failed"
  , 418 => "I'm a teapot"
  , 419 => "Authentication Timeout"
  , 420 => "Enhance Your Calm"
  , 422 => "Unprocessable Entity"
  , 423 => "Locked"
  , 424 => "Failed Dependency"
  , 424 => "Method Failure"
  , 425 => "Unordered Collection"
  , 426 => "Upgrade Required"
  , 428 => "Precondition Required"
  , 429 => "Too Many Requests"
  , 431 => "Request Header Fields Too Large"
  , 444 => "No Response"
  , 449 => "Retry With"
  , 450 => "Blocked by Windows Parental Controls"
  , 451 => "Unavailable For Legal Reasons"
  , 494 => "Request Header Too Large"
  , 495 => "Cert Error"
  , 496 => "No Cert"
  , 497 => "HTTP to HTTPS"
  , 499 => "Client Closed Request"
  , 500 => "Internal Server Error"
  , 501 => "Not Implemented"
  , 502 => "Bad Gateway"
  , 503 => "Service Unavailable"
  , 504 => "Gateway Timeout"
  , 505 => "HTTP Version Not Supported"
  , 506 => "Variant Also Negotiates"
  , 507 => "Insufficient Storage"
  , 508 => "Loop Detected"
  , 509 => "Bandwidth Limit Exceeded"
  , 510 => "Not Extended"
  , 511 => "Network Authentication Required"
  , 598 => "Network read timeout error"
  , 599 => "Network connect timeout error");
  static  function onError($message) {
    user_error($message);
    return false;
  }
  /*
  "code": 400, // matches the HTTP status code
  "errno": 107, // stable application-level error number
  "error": "Bad Request", // string description of the error type
  "message": "the value of salt is not allowed to be undefined",
  "info": "https://docs.dev.lcip.og/errors/1234" // link to more info on the error

  */
  static function fxaAuthError($errno,$additionalParameters=null){

    if (!isset(self::$FxaError[$errno])){
      $errno=999;
    }
    $code=self::$FxaError[$errno][0];
    $text=self::$http_status_codes[self::$FxaError[$errno][0]];
    $message=self::$FxaError[$errno][1]	;
    if ($additionalParameters!==null){
      $message.=' ['.$additionalParameters.']';
    }
    $jsonReturn =[
      'errno'=>$errno,
      'code'=>$code,
      'error'=>$text,
      'message'=>$message
    ];
    \Yii::$app->response->setStatusCode($code);
    //\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    header("Content-Type: application/json; charset=UTF-8");
    print  json_encode($jsonReturn);
    \Yii::$app->response->send();
    die();
  }
}
?>
