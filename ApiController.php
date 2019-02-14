<?php

namespace backend\controllers\parentapi;
/**
 *@AUTHOR:Prachi
 *@DATE:07-03-2016
 * @DESCRIPTION: common functions for api
 * 
*/
use Yii;
use yii\web\Controller;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\AccessRule;

use common\web\util\Codes\LookupCodes;

use backend\facades\api\ApiFacade;
use common\facades\CommonFacade;

use backend\modules\user\facades\UserFacade;

class ApiController extends Controller {

    /**
     *@author:prachi
     * @var representing deviceid,device authorized token     
     */
    public $globalDeviceId="",$globalAuthorizedToken="";
  
    /**
     *@author:prachi
     * @array pass array representing allowed request to guest user
     */
  
    public $allowGuestRequest=array(
        'parentapi/authorize/index',
        'parentapi/deviceprofile/update',
        'parentapi/master/index',
        'parentapi/master/lookups',
        'parentapi/master/lookuptype',
        'parentapi/master/city',
        'parentapi/master/state',
        'parentapi/master/country',
        'user/Api/v1/user/create',
        'user/Api/v1/user/login',
        'user/Api/v1/user/sociallogin',
        'user/Api/v1/user/forgotpassword',
        'user/Api/v1/user/userverification',
        'user/Api/v1/user/resendverification',
    );
  
    protected function setAuthorizeToken() {
        $headers=apache_request_headers();       
        $this->globalAuthorizedToken="";
        if(isset($headers['AuthorizedToken'])){
         $this->globalAuthorizedToken=$headers['AuthorizedToken'];
        }
        return $this->globalAuthorizedToken;
    }

    /**
    *@author:prachi
    * behaviours to validate http request and allowed actions
    */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'index'=>['get'],
                    'view'=>['get'],
                    'create'=>['post'],
                    'update'=>['post'],
                    'delete' => ['delete'],
                    'deleteall'=>['post'],                     
                    'login'=>['post'],
                    'sociallogin'=>['post'],
                    'logout'=>['get'],
                    'forgotpassword'=>['post'],
                    'changepassword'=>['post'],
                    'lookups'=>['get'],
                    'city'=>['get'],
                    'state'=>['get'],
                    'country'=>['get'],
                    'lookuptype'=>['get'],
                    'userverification'=>['post'],
                    'resendverification'=>['post']
                ], 
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ], 
        ];
    }
    
    
    public function beforeAction($event)
    {  
        $action = $event->id;
        
        if (isset($this->actions[$action])) {
            $verbs = $this->actions[$action];
        } elseif (isset($this->actions['*'])) { 
            $verbs = $this->actions['*'];
        } else { 
            $this->response($status=0,$error_code=400,$message='Bad Request','',$reason='Invalid method name'); 
        }
        $verb = Yii::$app->getRequest()->getMethod(); //return http method
       
        $allowed = array_map('strtoupper', $verbs);
       
        if (!in_array($verb, $allowed)) {           
            $this->response($status=0,$error_code=405,$message='Method not allowed');              
        }
        // Authenticate User Static key         
        self::authenticate();  
         
        return true;  
    }
    
    /**
    *@author:prachi
    * to check wether the user is guest or authorized using @var globalAuthorizedToken
    */
    
    private function isGuestUser(){ 
        /*
        echo Yii::$app->apiuser->isGuestApiUser;die;
         *  /*$controllerArr=explode("/",$contoller);
                        $base_controller=end($controllerArr);                        
                        if($base_controller=="user" && $action=="login"){
                            return true;
                         }*/
        
        $token=$this->setAuthorizeToken();      
        $commonFacade= new CommonFacade();
        $tokenArray=$commonFacade->decryptToken($token);
        
        //if(@array_key_exists('userId', $tokenArray)){
        if(isset($tokenArray->userId)){
            // authenticated user                                  
            $userData=\common\models\Users::find()->select('id,status')->where(['id'=>$tokenArray->userId,'is_delete'=>'1'])->one();            
            if($userData){
                if($userData->status!=LookupCodes::L_USER_STATUS_BLOCKED){
                    return true;
                }else{
                    $this->response(0, 100, 'User has been blocked');     
                }
            } else{
                $this->response(0, 100, 'Inavlid User');     
            }           
            return false;
        }else{ 
            $module=$controller=$action=$request="";
            $module= Yii::$app->controller->module->id;
            $contoller= Yii::$app->controller->id;
            $action= Yii::$app->controller->action->id;
            if($module!=Yii::$app->id){$request=$module.'/';}
            $request.=$contoller.'/'.$action;
            if(in_array($request,$this->allowGuestRequest)){
                return true;
            }else{
                $this->response(0, 400, 'Bad request');                                                
            }
            
            return false;
        }
        
    }

    /**
    *@author:prachi
    * to authenticate mobile request with different headers on each stage 
    */
    
    public function authenticate(){
        $facade= new ApiFacade();        
        $headers=apache_request_headers();
        //print_r($headers);die;//
        //  $this->response($status=0,$error_code=4011,$message=$headers['DeviceId']); 
        if(isset($headers['DeviceId'])){
            $this->globalDeviceId=$headers['DeviceId'];
            $contoller= Yii::$app->controller->id;
            $action= Yii::$app->controller->action->id;
            if($contoller=="parentapi/authorize" && $action=="index"){ //register new device
                $res=$facade->getApiKey();  
                //  $DEVICE_TOKEN_OPTIONAL=Yii::$app->params['configurations']['DEVICE_TOKEN_OPTIONAL'];   
                $DEVICE_TOKEN_OPTIONAL=CommonFacade::getLookupValueFromConfig(Yii::$app->params['configurations']['DEVICE_TOKEN_OPTIONAL']);
                //if($DEVICE_TOKEN_OPTIONAL==='FALSE'){
                if(Yii::$app->params['configurations']['DEVICE_TOKEN_OPTIONAL']==LookupCodes::L_DEVICE_TOKEN_OPTIONS_FALSE){
                    // add one more header
                   $valid=isset($headers['ApiKey']) && isset($headers['DeviceType']) && isset($headers['DeviceToken']); 
                }else{$valid=isset($headers['ApiKey']) && isset($headers['DeviceType']);}
                
                if($valid){
                    $globalApiKey=$headers['ApiKey'];
                    $deviceType=$headers['DeviceType'];
                    if($globalApiKey===$res->value){
                        return true;
                    }
                    else{
                        $this->response($status=0,$error_code=401,$message='Unauthorized request2','',$reason="Incorrect api key");                           
                    }   
                }else{
                    $this->response($status=0,$error_code=400,$message='Bad request','',$reason="Missing arguments");                           
                }
            }
            else{ 
                if(isset($headers['AuthorizedToken'])){
                    $this->isGuestUser(); 
                    $result=$facade->getDeviceData($this->globalDeviceId);
                    if(empty($result)){                        
                        $this->response($status=0,$error_code=401,$message='Unauthorized request3','','Unable to get device details');
                    }
                    
                    // if login request then return true in else cond;
                   
                    if($this->globalAuthorizedToken===$result->current_token){
                        return true;
                    }elseif($this->globalAuthorizedToken===$result->previous_token){
                        return true;
                    }else{                     
                        $this->response($status=0,$error_code=401,$message='Unauthorized request4','',$reason='Invalid authorized token');
                    }
                }
                else{
                    $this->response($status=0,$error_code=400,$message='Bad request','',$reason="Authorized token is missing");
                }
            }
            
        }else{
            $this->response($status=0,$error_code=400,$message='Bad request','',$reason='Device id is blank.');                           
        }
        
    }
    
   /**
     *@author:prachi
     * to set http header 
     */
    
    private function setHeader($status)
    {	  
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        $content_type="application/json; charset=utf-8";

        header($status_header);
        header('Content-type: ' . $content_type);
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: AuthorizedToken, DeviceId, ApiKey, DeviceType, DeviceToken, Content-Type, Accept");
	header('X-Powered-By: ' . "Claritus <claritusconsulting.com>");
    }
    
    /**
     *@author:prachi
     * define http status code 
     */
      
    private function _getStatusCodeMessage($status)
    {
	// these could be stored in a .ini file and loaded
	// via parse_ini_file()... however, this will suffice
	// for an example
      $codes = Array(
	100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    
	);
	return (isset($codes[$status])) ? $codes[$status] : '';
    }
    
    /*
     *@author:prachi
     *@provide response to mobile request
     * pass the following params
     * status as 0 (in case of failure) or 1 (in case of success)
     * code can be http status code or Codes from web/utils/codes
     * message representing messages
     * data can be array or object
     */
    
    public function response($status,$code=NULL,$message,$data=NULL,$reason=NULL){       
        //get current token of requested device
        if($status==1){  
            $api_token=self::authenticateTokenTimeStamp($code,$data); 
            $response=array('status'=>$status,'code'=>$code,'data'=>$data,'message'=>$message,'api_current_token'=>$api_token,'reason'=>$reason);            
        }  else {
            $data=array();       
            $codeArr=array('400','401','405');
            if(!in_array($code, $codeArr)){
                $api_token=self::authenticateTokenTimeStamp($code,$data); 
                $response=array('status'=>$status,'code'=>$code,'data'=>$data,'message'=>$message,'api_current_token'=>$api_token,'reason'=>$reason);
            }else{
                if($code==401){
                    $api_current_token=$this->setAuthorizeToken();
                    if($api_current_token!=""){
                        $tokenArray=CommonFacade::decryptToken($api_current_token);    
                        //if(array_key_exists('userId',$tokenArray)){                    
                        if(isset($tokenArray->userId)){
                            $request['deviceId'] = $this->globalDeviceId;
                            $facade = new UserFacade();            
                            $facade->Logout($request); 
                        }
                    }    
                }
                $response=array('status'=>$status,'code'=>$code,'data'=>$data,'message'=>$message,'reason'=>$reason);
            }
        }
        //$this->setHeader($code);
        $this->setHeader(200);
        echo json_encode($response,JSON_PRETTY_PRINT);            
        exit;
    }
    
    
    public function authenticateTokenTimeStamp($code=NULL,$data){ //generate new token on time expire
        
            $api_current_token=$this->setAuthorizeToken();
            Yii::error('Token1'.$api_current_token); 
         
       //  if(!array_key_exists('API_CURRENT_TOKEN',$data) && $api_current_token!=""){
           if(!isset($data['API_CURRENT_TOKEN']) && $api_current_token!=""){
             
                $tokenArray=CommonFacade::decryptToken($api_current_token);         
                //if(array_key_exists('currentTime', $tokenArray)){
                if(isset($tokenArray->currentTime)){
                    $curentTime=  CommonFacade::getCurrentDateTime();
                    $tokenTime=$tokenArray->currentTime;             
                    $diffMins=\common\models\Configurations::find()->select('value')->where(['short_code'=>'API_TOKEN_EXPIRY_MINUTES'])->one();          
                    if ($diffMins->value!="" && is_numeric($diffMins->value)){
                    $endTime = strtotime("+ ".$diffMins->value." minutes", strtotime($tokenTime));}
                    $endTime=date("Y-m-d H:i:s",$endTime);
                    $newTokenTimeStr=  strtotime($endTime);
                    $currTokenTimeStr =  strtotime($curentTime);
                    if($currTokenTimeStr>$newTokenTimeStr){
                        //generate new and update and returns                 
                        //if(array_key_exists('userId',$tokenArray)){
                        if(isset($tokenArray->userId)){

                            if($code==401){
                                $request['deviceId'] = $this->globalDeviceId;
                                $facade = new UserFacade();            
                                $facade->Logout($request);                        
                               // $response=array('status'=>$response['STATUS'],'code'=>$response['CODE'],'data'=>array(),'message'=>$response['MESSAGE'],'api_current_token'=>$api_token);                   
                            }else{
                                $user_id=$tokenArray->userId;
                                Yii::error('Token2'.$api_current_token);
                                CommonFacade::swapDeviceAuthorizedTokens($this->globalDeviceId,$user_id);
                                Yii::error('Token3'.$api_current_token);
                            }                     
                        }else{
                            Yii::error('Token4'.$api_current_token);
                            CommonFacade::swapDeviceAuthorizedTokens($this->globalDeviceId);
                            Yii::error('Token5'.$api_current_token);
                        }                
                    }
                }
         
                 }
          $api_current_token=CommonFacade::getDeviceCurrentToken($this->globalDeviceId);
          Yii::error('Token7'.$api_current_token);
         return $api_current_token;
    }
  
}
