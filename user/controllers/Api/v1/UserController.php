<?php
/*
 * @date: 07-Feb-2016
 * @description: UserController for all basic user related activities
 */

namespace backend\modules\user\controllers\Api\v1;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use backend\controllers\parentapi\ApiController;
use common\web\util\Codes\LookupCodes;
use common\web\util\Codes\Codes;

use common\models\Users;
use backend\modules\user\models\UserPersonal;

use common\facades\CommonFacade;
use backend\modules\user\facades\UserFacade;



class UserController extends ApiController{
    
 /**
 * @author: prachi
 * @date: 07-Feb-2016
 * @description: Registration
 */
    
    
  
    public function actionCreate(){        
        $facade = new UserFacade();
        $commonFacade= new CommonFacade();    
        $currentTime=$commonFacade->getCurrentDateTime();
         
        $params=$_REQUEST;
        
        $request['User']['role']=isset($params['role']) ? $params['role']:'';
        $request['User']['user_type']=isset($params['user_type'])?$params['user_type']:'';
        $request['User']['password']= ($params['password'] !='')?md5(md5($params['password'])):'';
        $request['UserPersonal']['email']=isset($params['email'])?$params['email']:'';
        $request['UserPersonal']['phone']=isset($params['phone'])?$params['phone']:'';
        if(isset($params['is_social']) && isset($params['social_id']) && $params['is_social']!=0 && $params['social_id']!=0){
            $request['UserPersonal']['is_social']=$params['is_social'];
            $request['UserPersonal']['social_id']=$params['social_id'];
            $request['User']['password']="";
        }
        
        if(isset($params['first_name']) && isset($params['last_name'])){
            $request['UserPersonal']['first_name']=$params['first_name'];
            $request['UserPersonal']['last_name']=$params['last_name'];
        }
        
        /* code is added by komal garg on 12 Sep 2017 
        check user verification require or not
        */
        $requireUserVerification = Yii::$app->params['configurations']['User_Verification'];
        if($requireUserVerification == LookupCodes::L_USER_VERIFICATION_YES){
            $status = LookupCodes::L_USER_STATUS_NON_VERIFIED;
        }else{
            $status = LookupCodes::L_USER_STATUS_VERIFIED;
        }
        /* code end */
        
        $request['User']['status']= $status;
        $request['User']['is_delete']=1;
        $request['User']['created_on']=$request['User']['modified_on']=$request['UserPersonal']['created_on']=$request['UserPersonal']['modified_on']=$currentTime;

        $response=$facade->Register($request);
        //$status=0,$error_code=400,$message='Method not allowed'
        $STATUS=$CODE=$MESSAGE="";$DATA=array();
        if(!empty($response)){
            $STATUS=$response['STATUS'];
            $CODE=$response['CODE'];
            $MESSAGE=$response['MESSAGE'];
            $DATA=$response['DATA'];
        }
        //if($STATUS==1){$DATA=array();}                  
        ApiController::response($STATUS,$CODE,$MESSAGE,$DATA);
       
        
    }
    
    public function actionLogin(){
        $STATUS = $CODE = $MESSAGE = "";
        $DATA = array();
        
        /**
         *@changes : By prachi to get device Id
         */
        $commonFacade = new CommonFacade();
        $deviceId=$commonFacade->getDeviceIdFromHeaders();
        
        $params = $_REQUEST;
        $request['username'] = $params['username'];
        $request['password'] = $params['password'];
        $request['deviceId'] = $deviceId;
        
        $facade = new UserFacade();
        $response = $facade->Login($request);
        
        if(!empty($response)){
            $STATUS = $response['STATUS'];
            $CODE = $response['CODE'];
            $MESSAGE = $response['MESSAGE'];
            if(isset($response['DATA'])){
                $DATA=$response['DATA'];
            }
        }
                   
        ApiController::response($STATUS, $CODE, $MESSAGE, $DATA);
    }
    
    
    public function actionForgotpassword(){
        $STATUS = $CODE = $MESSAGE = "";
        $DATA = array();
        
        $params = $_REQUEST;
        $request['email'] = $params['email'];
        
        $facade = new UserFacade();
        $response = $facade->forgotPassword($request);
        
        if(!empty($response)){
            $STATUS = $response['STATUS'];
            $CODE = $response['CODE'];
            $MESSAGE = $response['MESSAGE'];
        }
        if($STATUS==1){
            $DATA = array();
        }      
        ApiController::response($STATUS, $CODE, $MESSAGE, $DATA);
    }
    
    
    public function actionUserverification(){ 
        $STATUS = $CODE = $MESSAGE = "";
        $DATA = array();
        
        $request = $_REQUEST;
        
        $facade = new UserFacade();
        $response = $facade->userVerification($request);
        
        if(!empty($response)){
            $STATUS = $response['STATUS'];
            $CODE = $response['CODE'];
            $MESSAGE = $response['MESSAGE'];
        }
        if($STATUS==1){
            $DATA = array();
        }      
        ApiController::response($STATUS, $CODE, $MESSAGE, $DATA);
    }
    
    
    public function actionResendverification(){
        $STATUS = $CODE = $MESSAGE = "";
        $DATA = array();
        
        $request = $_REQUEST;
        
        $facade = new UserFacade();
        $response = $facade->resendVerification($request);
        
        if(!empty($response)){
            $STATUS = $response['STATUS'];
            $CODE = $response['CODE'];
            $MESSAGE = $response['MESSAGE'];
        }
        if($STATUS==1){
            $DATA = array();
        }      
        ApiController::response($STATUS, $CODE, $MESSAGE, $DATA);
    }
    
       
 /**
 * @author: prachi
 * @date: 07-Feb-2016
 * @description: set profile
 */
    
    public function  actionUpdate(){
        
       $DATA=array();
       $STATUS=$CODE=$MESSAGE=$REASON="";
       $facade = new UserFacade();
       $commomFacade= new CommonFacade();
       
       //get token from header
       $globalAuthorizedToken=ApiController::setAuthorizeToken();
       //get user id from authorizedToken
       $user_id=$commomFacade->getUserIdFromAuthorizedToken($globalAuthorizedToken);              
       if($user_id==""){
           $STATUS=0;$CODE=400;$MESSAGE="Bad Request";$REASON="Unable to get user details.";
           ApiController::response($STATUS,$CODE,$MESSAGE,$DATA,$REASON);
       }
       
        $model = UserPersonal::find()->where(['user_id'=>$user_id])->one();
        $model->load ( \Yii::$app->getRequest ()->getBodyParams (), '' );
        $image = UploadedFile::getInstanceByName ( 'image' );
        
        
       $params= $_REQUEST;       
       $request['UserPersonal']['user_id']=$user_id;
       $request['UserPersonal']['first_name']=isset($params['first_name'])?$params['first_name']:'';
       $request['UserPersonal']['last_name']=isset($params['last_name'])?$params['last_name']:'';
       $request['UserPersonal']['gender']=isset($params['gender'])?$params['gender']:'';       
       $request['UserPersonal']['marital_status']=isset($params['marital_status'])?$params['marital_status']:'';
       $request['UserPersonal']['modified_on']=  $commomFacade->getCurrentDateTime();
       $request['UserPersonal']['modified_by']=$user_id;       
       $response=$facade->setProfile($request,$image);        
       if(!empty($response)){
            $STATUS = $response['STATUS'];
            $CODE = $response['CODE'];
            $MESSAGE = $response['MESSAGE'];
            if(isset($response['DATA'])){
                $DATA=$response['DATA'];
            }
            if(isset($response['REASON'])){
                $REASON=$response['REASON'];
            }
        }        
        ApiController::response($STATUS,$CODE,$MESSAGE,$DATA,$REASON);
   }
   
    public function actionChangepassword(){
        $STATUS = $CODE = $MESSAGE = "";
        $DATA = array();
        
        $commomFacade = new CommonFacade();
        $globalAuthorizedToken = ApiController::setAuthorizeToken();
        $user_id = $commomFacade->getUserIdFromAuthorizedToken($globalAuthorizedToken);       
        if($user_id == ""){
            $STATUS=0;$CODE=400;$MESSAGE="Bad Request";$REASON="Unable to get user details.";
            ApiController::response($STATUS,$CODE,$MESSAGE,$DATA,$REASON);
        }
        
        $params = $_REQUEST;
        $request['oldPassword'] = $params['oldPassword'];
        $request['newPassword'] = $params['newPassword'];
        $request['repeatNewPassword'] = $params['repeatNewPassword'];
        $request['userId'] = $user_id;
        
        $facade = new UserFacade();
        $response = $facade->changePassword($request);
        
        if(!empty($response)){
            $STATUS = $response['STATUS'];
            $CODE = $response['CODE'];
            $MESSAGE = $response['MESSAGE'];
        }
        if($STATUS==1){
            $DATA = array();
        }
        ApiController::response($STATUS, $CODE, $MESSAGE, $DATA);
    }
    
    public function actionView(){
        /*$configFacade= new \backend\facades\configuration\ConfigurationFacade();
        $val=$configFacade->setApiStaticKey();*/
        $STATUS = $CODE = $MESSAGE = "";
        $DATA = array();
        
        $commomFacade = new CommonFacade();
        $globalAuthorizedToken = ApiController::setAuthorizeToken();
        $user_id = $commomFacade->getUserIdFromAuthorizedToken($globalAuthorizedToken);       
        if($user_id == ""){
            $STATUS=0;$CODE=400;$MESSAGE="Bad Request";$REASON="Unable to get user details.";
            ApiController::response($STATUS,$CODE,$MESSAGE,$DATA,$REASON);
        }
        
        $params = $_REQUEST;
        $request['userId'] = $user_id;
        
        $facade = new UserFacade();
        $response = $facade->getProfile($request);
        
        if(!empty($response)){
            $STATUS = $response['STATUS'];
            $CODE = $response['CODE'];
            $MESSAGE = $response['MESSAGE'];
            $DATA = $response['DATA'];
        }
        ApiController::response($STATUS, $CODE, $MESSAGE, $DATA);
    }
    
    
    public function actionLogout(){
        //flush data of devide
        $STATUS = $CODE = $MESSAGE = "";
        $DATA =$request= array();
        $deviceId=CommonFacade::getDeviceIdFromHeaders();
        $request['deviceId'] = $deviceId;
        
        $facade = new UserFacade();
        $response = $facade->Logout($request);
        
        if(!empty($response)){
            $STATUS = $response['STATUS'];
            $CODE = $response['CODE'];
            $MESSAGE = $response['MESSAGE'];
            if(isset($response['DATA'])){
                $DATA=$response['DATA'];
            }
        }
                   
        ApiController::response($STATUS, $CODE, $MESSAGE, $DATA);
    }
    
    public function actionSociallogin(){
        $facade = new UserFacade();
        $commonFacade= new CommonFacade();    
        $currentTime=$commonFacade->getCurrentDateTime();
         
        $params=$_REQUEST;
        $user_id="";
        // check user exists or not
        $user = UserPersonal::find()->where(['email'=>$params['username']])->orWhere(['phone' =>$params['username']])->one();
        
        if(!$user){ 
            //call registration
            $request['User']['role']=isset($params['role']) ? $params['role']:'';
            $request['User']['user_type']=  LookupCodes::L_USER_TYPE_SUBSCRIBER;
            $request['User']['password']="";

            $request['UserPersonal']['email']=isset($params['email'])?$params['email']:'';
            $request['UserPersonal']['phone']=isset($params['phone'])?$params['phone']:'';
            if(isset($params['is_social']) && isset($params['social_id']) && $params['is_social']!=0 && $params['social_id']!=0){
                $request['UserPersonal']['is_social']=$params['is_social'];
                $request['UserPersonal']['social_id']=$params['social_id'];            
            }


            $request['UserPersonal']['first_name']=isset($params['first_name'])?$params['first_name']:'';
            $request['UserPersonal']['last_name']=isset($params['last_name'])?$params['last_name']:'';
            $request['UserPersonal']['gender']=isset($params['gender'])?$params['gender']:'';


            $request['User']['status']=  LookupCodes::L_USER_STATUS_VERIFIED;
            $request['User']['is_delete']=1;
            $request['User']['created_on']=$request['User']['modified_on']=$request['UserPersonal']['created_on']=$request['UserPersonal']['modified_on']=$currentTime;

            $response=$facade->Register($request);
            $user_id=$response['AUTO_ID'];
            
        }else{
            $User = Users::find()->select(['password','id','is_delete'])->where(['id'=>$user->user_id,'user_type'=>  LookupCodes::L_USER_TYPE_SUBSCRIBER])->one();
            if ($User->is_delete == 0) {
                $STATUS = 0;
                $CODE = Codes::ERROR;
                $MESSAGE = CommonFacade::getMessages()->M111;
                $DATA = array();
            } else {
                $user_id=$user->user_id;
            }
            
        }

        if($user_id){
            
            $deviceId=$commonFacade->getDeviceIdFromHeaders();      
            $facade->embedUserToDevice($deviceId,$user_id);
            $token = CommonFacade::swapDeviceAuthorizedTokens($deviceId,$user_id);
            
            $STATUS = 1;
            $CODE = Codes::SUCCESS;
            $MESSAGE = CommonFacade::getMessages()->M108;
            $DATA['API_CURRENT_TOKEN']=$token;
            $DATA['login_type']='Social';     
        }
             
        ApiController::response($STATUS, $CODE, $MESSAGE, $DATA);
    }

    
    public function actionGetcmspageslist(){
        $STATUS = $CODE = $MESSAGE = "";
        $DATA = array();
        
        
        $commomFacade = new CommonFacade();
        $globalAuthorizedToken = ApiController::setAuthorizeToken();
        $user_id = $commomFacade->getUserIdFromAuthorizedToken($globalAuthorizedToken);       
        if($user_id == ""){
            $STATUS=0;$CODE=400;$MESSAGE="Bad Request";$REASON="Unable to get user details.";
            ApiController::response($STATUS,$CODE,$MESSAGE,$DATA,$REASON);
        }
        
        $facade = new UserFacade();
        $response = $facade->getCmsPagesList();
        
        if(!empty($response)){
            $STATUS = $response['STATUS'];
            $CODE = $response['CODE'];
            $MESSAGE = $response['MESSAGE'];
        }
        if($STATUS==1){
            $DATA = array();
        }
        
        ApiController::response($STATUS, $CODE, $MESSAGE, $DATA);
    }
    
    
    public function actionGetcmspage(){
        $STATUS = $CODE = $MESSAGE = "";
        $DATA = array();
        
        $commomFacade = new CommonFacade();
        $globalAuthorizedToken = ApiController::setAuthorizeToken();
        $user_id = $commomFacade->getUserIdFromAuthorizedToken($globalAuthorizedToken);       
        if($user_id == ""){
            $STATUS=0;$CODE=400;$MESSAGE="Bad Request";$REASON="Unable to get user details.";
            ApiController::response($STATUS,$CODE,$MESSAGE,$DATA,$REASON);
        }
        
        $params = $_REQUEST;
        $request['id'] = $params['id'];
        
        $facade = new UserFacade();
        $response = $facade->getCmsPage($request);
        
        if(!empty($response)){
            $STATUS = $response['STATUS'];
            $CODE = $response['CODE'];
            $MESSAGE = $response['MESSAGE'];
        }
        if($STATUS==1){
            $DATA = array();
        }
        ApiController::response($STATUS, $CODE, $MESSAGE, $DATA);
    }
}


