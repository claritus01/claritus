<?php

namespace backend\facades;

/*
 * @author: Prachi
 * @date: 02-March-2016
 * @description: AdminFacade interacts with models for all basic user related activities Ex:Login,Register
 */

use Yii;

use common\facades\CommonFacade;
use common\facades\MailFacade;

use common\web\util\Codes\Codes;
use common\web\util\Codes\LookupCodes;
use common\web\util\Codes\LookupTypeCodes;

use common\models\Users;
use common\models\AdminPersonal;
use common\models\UserPersonalDetails;
use common\models\ChangePasswordForm;
use common\models\LoginForm;
use common\models\PasswordHistory;
use common\models\Permissions;
use common\models\RolePermissions;
use common\models\EmailTemplates;
use common\models\Lookups;
use common\models\LookupTypes;

use yii\web\UploadedFile;



class AdminFacade {

    public $messages;           //stores an instance of the messages XML file.
    public $isGuestAdmin;        //decides user is logged in(=0) or not(=1)
    public $adminId;             //returns looged in user's id. 

    public function __construct() {

        $session = Yii::$app->session;
        $this->messages = CommonFacade::getMessages();

        if ($session->get('user')) {

            $user = $session->get('user');
            $this->isGuestAdmin = 0;
            $this->adminId = $user->id;
        } else {

            $this->isGuestAdmin = 1;
            $this->adminId = null;
        }
    }

    public function createUser($data,$image=array()) {        
        
        $CODES = new Codes;    //To get Code
        $model = new Users();
        $modelPersonal = new AdminPersonal();

        $userFormData = new \common\models\ManageuseraddForm();
        $userFormData->attributes = $data['ManageuseraddForm'];
        
        
        $facade = new CommonFacade;
        $CODES = new Codes;
        
        $userdata = array();
        
        $userdata['is_delete'] = 1;
        $userdata['role'] = $data['ManageuseraddForm']['role'];
        $userdata['created_on'] = date('Y-m-d h:i:s');
        $userdata['modified_on'] = date('Y-m-d h:i:s');
        $userdata['user_type'] = CommonFacade::getParentLookup($data['ManageuseraddForm']['role']);
        //$userdata['password'] = $data['onetimePassword']?md5(md5($data['onetimePassword'])):(string)rand(10000001,99999999);
        $userdata['password'] = (string)rand(10000001,99999999);
        
        $adminpersonal['first_name'] = $data['ManageuseraddForm']['firstName'];
        $adminpersonal['last_name'] = $data['ManageuseraddForm']['lastName'];
        $adminpersonal['email'] = $data['ManageuseraddForm']['email'];
        $adminpersonal['phone'] = $data['ManageuseraddForm']['phone'];
        //$adminpersonal['is_delete'] = 1;
        $adminpersonal['created_on'] = date('Y-m-d h:i:s');
        $adminpersonal['modified_on'] = date('Y-m-d h:i:s');
        $userdata['status'] = LookupCodes::L_COMMON_STATUS_ENABLED;
        
        $model->attributes = $userdata;
        $modelPersonal->attributes = $adminpersonal;
        
         $path=$save_path="";
        if(!empty($image)){
         // store the source file name            
         // $ext = end((explode(".", $image->name)));
          $arr = explode(".", $image->name);
          $ext = end($arr);
          // generate a unique file name
          $avatar = Yii::$app->security->generateRandomString().".{$ext}";

          // the path to save file, you can set an uploadPath           
          $path = Yii::$app->params['UPLOAD_PATH'].'admin_users/profile/' . $avatar;
          $save_path="admin_users/profile/".$avatar;
          $modelPersonal->image_path=$save_path;
        }
             

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->save()) {
                $user_id = $model->getPrimaryKey();
                $modelPersonal->user_id = $user_id;
                $modelPersonal->created_by = $user_id;
                $modelPersonal->modified_by = $user_id;
                if ($modelPersonal->save()) {                    
                    //update User table for create_by and modified_by field
                    $model->created_by = $user_id;
                    $model->modified_by = $user_id;
                    $model->save();
                    $transaction->commit();
                    if($path!=""){$image->saveAs($path);}
                    $STATUS = 1;
                    $CODE = $CODES::SUCCESS;
                    $MSG = $this->messages->M101;
                    $MSG=str_replace("{1}", $adminpersonal['email'], $MSG);
                    $data = array('STATUS' => 'success', 'SUBDATA' => array(), 'DATA'=>$userFormData);
                    
                    
                    
                    /**generate token for reset password**/
                    $expiryTimeShortcode="FORGOT_PASSWORD_EXPIRY_MINUTES";
                    $operation=  LookupCodes::L_SYSTEM_OPERATIONS_CREATE_PASSWORD;
                    
                $systemResponse=CommonFacade::generateSystemToken($expiryTimeShortcode,$user_id,$operation);
                if($systemResponse['STATUS']==1){
                    $token=$systemResponse['TOKEN'];                    
                    $link = Yii::$app->urlManager->getHostInfo().Yii::$app->urlManager->createUrl("admin/resetpassword?id=".$token);
                    
                    $emailObj = array(
                            'LINK'=>$link, 
                            'SUBSCRIBER.FIRSTNAME'=>$adminpersonal['first_name'], 
                            'SUBSCRIBER.LASTNAME'=>$adminpersonal['last_name']
                        );
                    
                    $eventId = LookupCodes::L_EMAIL_TEMPLATES_WELCOME_EMAIL;//email events
                    $langId = LookupCodes::L_LANGUGAGE_ENGLISH;

                    $recepient =   $adminpersonal['email'];
                    $mailfacade = new \common\facades\MailFacade();
                    $mail = $mailfacade->sendEmail($emailObj, $recepient, $eventId, $langId, Yii::$app->params['configurations']['ADMIN_EMAIL']);                 
                    $STATUS = 1;
                    $CODE = $CODES::SUCCESS;
                    $MSG = $this->messages->M113;
                    $MSG=str_replace("{1}", $recepient, $MSG);
                    $MSG=str_replace("{2}", "added", $MSG);                   
                    
                }
                    
                   /* 
                    
                    $usrdata['id'] = $model->id;
                    $usrdata['validTill'] = strtotime("+15 minutes", strtotime(date('Y-m-d H:i:s')));

                    $facade = new CommonFacade();
                    $forgotKey = $facade->encryptToken($usrdata);
                    $MSG = urlencode($forgotKey);
                   */ 
                    return array('CODE' => $CODE, 'MESSAGE' =>$MSG, 'DATA' => $data);
                } else {
                    $transaction->rollBack();
                    $errorAll = array_merge(array_values($model->firstErrors), array_values($modelPersonal->firstErrors));
                    $error = $errorAll;
                    $MSG = $error[0];
                    $CODE = $CODES::VALIDATION_ERROR;
                    $STATUS = 0;
                    $data = array('STATUS' => 'error', 'SUBDATA' => array(), 'DATA'=>$userFormData);
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
                }
            } else { 
                $transaction->rollBack();
                $errorAll = array_merge(array_values($model->firstErrors), array_values($modelPersonal->firstErrors));
                $error = $errorAll;
                $MSG = $error[0];
                $CODE = $CODES::VALIDATION_ERROR;
                $STATUS = 0;
                $data = array('STATUS' => 'error', 'SUBDATA' => array(), 'DATA'=>$userFormData);
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            $errorAll = array_merge(array_values($model->firstErrors), array_values($modelPersonal->firstErrors));
            $error = $errorAll;
            $MSG = $error[0];
            $CODE = $CODES::VALIDATION_ERROR;
            $STATUS = 0;
            $data = array('STATUS' => 'error', 'SUBDATA' => array(), 'DATA'=>$userFormData);
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
        }
        
    }
    
    public function listUser($userObj) {
        
        $CODES = new Codes;    //To get Code
        $model = new Users();
        $modelPersonal = new AdminPersonal();

        $facade = new AdminFacade;
        $CODES = new Codes;
         
        if($userObj->user_type == 150003){ 
            
            $userdata = Users::find()
                //->andWhere(['!=', 'id', $this->adminId])
                ->where(['is_delete'=>1,'user_type'=>  LookupCodes::L_USER_TYPE_ADMIN])
                ->orderBy(['id'=>SORT_DESC])->all();
            
           
        } else {
            $userdata = Users::find()
                ->where(['!=','cc_users.id', $this->adminId])
                ->where(['user_type'=>$userObj->user_type,'user_type'=>  LookupCodes::L_USER_TYPE_ADMIN, 'is_delete'=>1])
                ->andWhere(['>=', 'role', $userObj->role])
                ->orderBy(['id'=>SORT_DESC])->all();
        }
                
        
       
        if ($userdata) {

                $MSG = $this->messages->M108;
                $CODE = $CODES::SUCCESS;

                $data = array('STATUS' => 'success', 'SUBDATA' => $userdata);
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
                
            } else {

                $error = array_values($userdata->firstErrors);
                $MSG = $this->messages->M104;
                $CODE = $CODES::VALIDATION_ERROR;

                $data = array('STATUS' => 'error', 'SUBDATA' => array());
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
            }
        
    }

    public function Login($data) {

        $CODES = new Codes;    //To get Code 
        $user = new Users;
        $loginInstance = new LoginForm;
        $adminDetailsModel = new AdminPersonal;

        $loginInstance->setAttributes($data);

        if ($loginInstance->validate()) {

            $user = Users::find()->joinWith('adminPersonals')->Where(['email' => $data['username']])->orWhere(['phone' => $data['username']])->andWhere(['password' => md5(md5($data['password']))])->one();
            
            if ($user) {
                
                $session = Yii::$app->session;
                $session->set('user', $user);
   
                Yii::$app->user->login($user, 3600*24*30);
                
                $MSG = $this->messages->M108;
                $CODE = $CODES::SUCCESS;

                $data = array('STATUS' => 'success', 'SUBDATA' => array());
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
            } else {

                $error = array_values($loginInstance->firstErrors);
                $MSG = $this->messages->M104;
                $CODE = $CODES::VALIDATION_ERROR;

                $data = array('STATUS' => 'error', 'SUBDATA' => array());
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
            }
        } else {

            $error = array_values($loginInstance->firstErrors);
            $MSG = $error[0];
            $CODE = $CODES::VALIDATION_ERROR;
            $data = array('STATUS' => 'error', 'SUBDATA' => array());
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
        }
    }

    public function Logout() {

        $CODES = new Codes;    //To get Code 
        $session = Yii::$app->session->destroy('user');

        $MSG = $this->messages->M107;
        $CODE = $CODES::VALIDATION_ERROR;

        $data = array('STATUS' => 'success', 'SUBDATA' => array());
        return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
    }

    //To be edited.

    public function Changepassword($data) {

        $CODES = new Codes;    //To get Code 
        $user = new Users;
        $changeInstance = new ChangePasswordForm;
        $adminDetailsModel = new AdminPersonal;

        $changeInstance->setAttributes($data);

        if ($changeInstance->validate()) {

            $user = Users::find()->Where(['id' => Yii::$app->admin->adminId, 'password' => md5(md5($data['oldPassword']))])->one();

            if ($user) {
                $history = new PasswordHistory();
                $hdata['user_id'] = Yii::$app->admin->adminId;
                $hdata['value'] = $user->password;
                $hdata['type_of_operation'] = LookupCodes::L_SYSTEM_OPERATIONS_CHANGE_PASSWORD;
                $hdata['is_delete'] = 1;
                $hdata['created_on'] = date('Y-m-d h:i:s');
                $hdata['created_by'] = Yii::$app->admin->adminId;
                $hdata['modified_on'] = date('Y-m-d h:i:s');
                $hdata['modified_by'] = Yii::$app->admin->adminId;

                $history->setAttributes($hdata);
                if ($history->save()) {
                    $newPassword = md5(md5($data['newPassword']));
                    $user->setAttribute('password', $newPassword);
                    if ($user->save(false)) {

                        $MSG = $this->messages->M106;
                        $CODE = $CODES::SUCCESS;

                        $data = array('STATUS' => 'success', 'SUBDATA' => $user);
                        return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
                    } else {
                        $error = array_values($user->firstErrors);
                        $MSG = $error[0];
                        $CODE = $CODES::DB_TECH_ERROR;
                        Yii::error($error);
                        $data = array('STATUS' => 'error', 'SUBDATA' => $user);
                        return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
                    }
                } else {
                    $error = array_values($history->firstErrors);
                    $MSG = $error[0];
                    $CODE = $CODES::DB_TECH_ERROR;
                    Yii::error($error);
                    $data = array('STATUS' => 'error', 'SUBDATA' => $user);
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
                }
            } else {

                $MSG = $this->messages->M105;
                $CODE = $CODES::VALIDATION_ERROR;

                $data = array('STATUS' => 'error', 'SUBDATA' => array());
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
            }
        } else {

            $error = array_values($changeInstance->firstErrors);
            $MSG = $error[0];
            $CODE = $CODES::VALIDATION_ERROR;
            $data = array('STATUS' => 'error', 'SUBDATA' => array());
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
        }
    }

    public function Forgotpassword($data) {

        $CODES = new Codes;    //To get Code 
        $user = new AdminPersonal;
        $forgotInstance = new \common\models\ForgotpasswordForm();
        $adminDetailsModel = new AdminPersonal;

        $forgotInstance->setAttributes($data);

        if ($forgotInstance->validate()) {

            $user = AdminPersonal::find()->Where(['email' => $data['email']])->one();

            if($user){
                //if ($user->is_delete == 0) {
                    $data = array('STATUS' => 'error', 'SUBDATA' => array());
                    $CODE = $CODES::ERROR;
                    $MSG = $this->messages->M111;
                //} else {
                    $userId = $user->user_id;
                    
                    $config = \common\models\Configurations::find()->select(['value'])->where(['short_code'=>'FORGOT_PASSWORD_EXPIRY_MINUTES'])->one();
                    if($config){
                        $mins = $config->value;
                    } else {
                        $mins = 15;
                    }
                    
                    $data['id'] = $userId;
                    $data['validTill'] = strtotime("+$mins minutes", strtotime(date('Y-m-d H:i:s')));

                    $facade = new CommonFacade();
                    $forgotKey = $facade->encryptToken($data);
                    $token = urlencode($forgotKey);
                    
                    
                    
                    $systemToken = new \common\models\SystemTokens();
                    $systemToken->type = LookupCodes::L_SYSTEM_OPERATIONS_FORGOT_PASSWORD;
                    $systemToken->value = $token;
                    $systemToken->creation_date_time = date('Y-m-d H:i:s');
                    $systemToken->expiration_date_time = date('Y-m-d h:i:s', strtotime(date('Y-m-d H:i:s'). " + $mins minutes"));
                    $systemToken->user_id = $userId; 
                    $systemToken->status = LookupCodes::L_COMMON_STATUS_ENABLED;        
                    $systemToken->is_delete = 1;      
                    $systemToken->created_on = date('Y-m-d H:i:s');
                    $systemToken->created_by = $userId;      
                    $systemToken->modified_on = date('Y-m-d H:i:s');
                    $systemToken->modified_by = $userId;
                    
                    $link = Yii::$app->urlManager->getHostInfo().Yii::$app->urlManager->createUrl("admin/resetpassword?id=".$token);

                    if($systemToken->save()){
                        
                        $emailObj = array('LINK'=>$link, 'USER.FIRSTNAME'=>$user->first_name, 'USER.LASTNAME'=>$user->last_name, 'SUBSCRIBER.FIRSTNAME'=>$user->first_name, 'SUBSCRIBER.LASTNAME'=>$user->last_name);
                        $eventId = LookupCodes::L_EMAIL_TEMPLATES_FORGOT_PASSWORD_MAIL;//email events
                        $langId = LookupCodes::L_LANGUGAGE_ENGLISH;
                        
                        $recepient = $user->email;
                        $mailfacade = new MailFacade();
                        //
                        $mail = $mailfacade->sendEmail($emailObj, $recepient, $eventId, $langId);
                        if($mail){
                            $STATUS = 1;
                            $CODE = $CODES::SUCCESS;
                            $MSG = $this->messages->M112;
                        }
                    } else {
                        $STATUS = 0;
                        $CODE = $CODES::ERROR;
                        $MSG = $systemToken->getErrors();
                    }
                //}
            } else {
                $CODE = $CODES::ERROR;
                $MSG = $this->messages->M110;
                $data = array('STATUS' => 'error', 'SUBDATA' => array());
            }
        } else {
            $error = array_values($forgotInstance->firstErrors);            
            $MSG = $this->messages->M110;
            $CODE = $CODES::VALIDATION_ERROR;
            $data = array('STATUS' => 'error', 'SUBDATA' => array());
            //return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
        }
        return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
    }

    public function authorizeResetToken($data) {

        $CODES = new Codes;
        $token = $data['secretHash'];
        $data = CommonFacade::decryptToken($token);

        if ($data && isset($data->id)) { 
            
            $system_token = \common\models\SystemTokens::find()->Where(['value'=>urlencode($token)])->one(); 
            
            if ($system_token->status == LookupCodes::L_COMMON_STATUS_DISABLED) {
                $MSG = $this->messages->M135;
                $CODE = $CODES::VALIDATION_ERROR;

                $data = array('STATUS' => 'error', 'SUBDATA' => array());
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
            }elseif ($data->validTill < strtotime(date('Y-m-d H:i:s'))) {
                $MSG = $this->messages->M114;
                $CODE = $CODES::VALIDATION_ERROR;

                $data = array('STATUS' => 'error', 'SUBDATA' => array());
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
            }
            $MSG = '';
            $CODE = $CODES::SUCCESS;

            $data = array('STATUS' => 'success', 'SUBDATA' => array());
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
        } else {
            $MSG = '';
            $CODE = $CODES::VALIDATION_ERROR;

            $data = array('STATUS' => 'error', 'SUBDATA' => array());
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
        }
    }

    public function resetPassword($data) { 
    
        $CODES = new Codes;
        $token = $data['secretHash'];
        $decr = CommonFacade::decryptToken($token);
        $newPassword="";

        if ($decr && isset($decr->id)) {
            
            $system_token = \common\models\SystemTokens::find()->Where(['value'=>urlencode($token)])->one(); 

            $user = Users::find()->Where(['id'=>$decr->id])->one();      
            if ($user) {                
                $history = new PasswordHistory();
                $hdata['user_id'] = $decr->id;
                $hdata['value'] = $user->password;
                $hdata['type_of_operation'] = LookupCodes::L_SYSTEM_OPERATIONS_CREATE_PASSWORD;
                $hdata['is_delete'] = 1;
                $hdata['created_on'] = CommonFacade::getCurrentDateTime();
                $hdata['created_by'] = Yii::$app->admin->adminId;
                $hdata['modified_on'] = CommonFacade::getCurrentDateTime();
                $hdata['modified_by'] = Yii::$app->admin->adminId;

                $history->setAttributes($hdata);
                if ($history->save()) {
                    $newPassword = md5(md5($data['newPassword']));
                    $user->setAttribute('password', $newPassword);
                    if ($user->save(false)) {
                        
                        /* disable system token link */
                        $system_token->setAttribute('status', LookupCodes::L_COMMON_STATUS_DISABLED);
                        $system_token->save(false);

                        /* code end */
                        
                        $MSG = $this->messages->M106;
                        $CODE = $CODES::SUCCESS;

                        $data = array('STATUS' => 'success', 'SUBDATA' => $user);
                        return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
                    } else {
                        $error = array_values($user->firstErrors);
                        $MSG = $error[0];
                        $CODE = $CODES::DB_TECH_ERROR;
                        Yii::error($error);
                        $data = array('STATUS' => 'error', 'SUBDATA' => $user);
                        return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
                    }
                } else {
                    $error = array_values($history->firstErrors);
                    $MSG = $error[0];
                    $CODE = $CODES::DB_TECH_ERROR;
                    Yii::error($error);
                    $data = array('STATUS' => 'error', 'SUBDATA' => $user);
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
                }
            } else {
                
                $MSG = $this->messages->M105;
                $CODE = $CODES::VALIDATION_ERROR;

                $data = array('STATUS' => 'error', 'SUBDATA' => array());
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
            }
        } else {
            $MSG = '';
            $CODE = $CODES::VALIDATION_ERROR;

            $data = array('STATUS' => 'error', 'SUBDATA' => array());
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
        }
    }

    public function getProfile($id=null) {
        
        $CODES = new Codes;
        $userRole = 0;
        $user = null;
        $userId = $id?$id:Yii::$app->admin->adminId;
        $userData = Users::find()->where(['id'=>$userId])->one();        
        if($userData)
            $userRole = $userData->user_type;
        
        if($userRole && ($userRole == LookupCodes::L_USER_TYPE_ADMIN || $userRole == LookupCodes::L_USER_TYPE_DEVELOPERS)){
            $user = AdminPersonal::find()->where(['user_id' => $userId])->one();
        } else if($userRole&&$userRole==LookupCodes::L_USER_TYPE_SUBSCRIBER){
            $user = UserPersonalDetails::find()->where(['user_id' => $userId])->one();
        }
        
        if ($user) {
            $MSG = '';
            $CODE = $CODES::SUCCESS;

            $data = array('STATUS' => 'success', 'SUBDATA' => $user);
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
        } else {
            $MSG = "";
            $CODE = $CODES::VALIDATION_ERROR;

            $data = array('STATUS' => 'error', 'SUBDATA' => array());
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
        }
    }

    public function updateProfile($data, $id=null) {
        
        $CODES = new Codes;
        $userRole = 0;
        $user = null;
        $userId = $id?$id:Yii::$app->admin->adminId;
        $userData = Users::find()->where(['id'=>$userId])->one();
        $oldImg="";
       
        if($userData)
            $userRole = $userData->user_type;
        if($userRole && ($userRole == LookupCodes::L_USER_TYPE_ADMIN || $userRole == LookupCodes::L_USER_TYPE_DEVELOPERS))
            $user = AdminPersonal::find()->where(['user_id' => $userId])->one();
        else if($userRole&&$userRole==LookupCodes::L_USER_TYPE_SUBSCRIBER){
            $user = UserPersonalDetails::find()->where(['user_id' => $userId])->one();
        }
        
        $image = UploadedFile::getInstance($user, 'image_path'); 
                
        $path=$save_path="";
        if(!empty($image)){ 
            
            // store the source file name  
            $arr = explode(".", $image->name);
            $ext = end($arr);

          // generate a unique file name
          $avatar = Yii::$app->security->generateRandomString().".{$ext}";

          // the path to save file, you can set an uploadPath           
          $path = Yii::$app->params['UPLOAD_PATH'].'admin_users/profile/' . $avatar;
          $save_path="admin_users/profile/".$avatar;          
        }
       
        
        if ($user) {            
            $oldImg=$user->image_path;              
            $user->first_name=$data['first_name'];
            $user->last_name=$data['last_name'];           
            if($save_path!=""){
               $user->image_path=$save_path;
                if($oldImg!="" && file_exists(Yii::$app->params['UPLOAD_PATH'].$oldImg)){
                    unlink(Yii::$app->params['UPLOAD_PATH'].$oldImg);
                }
            }            
            
            if ($user->save(false)) {
                 if($path!=""){$image->saveAs($path);}
                $MSG = $this->messages->M109;
                $CODE = $CODES::SUCCESS;
                $user->id=$data['id'];
                $data = array('STATUS' => 'success', 'SUBDATA' => $user);
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
            } else {
                $MSG = $this->messages->M105;
                $CODE = $CODES::VALIDATION_ERROR;

                $data = array('STATUS' => 'error', 'SUBDATA' => array());
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
            }
        } else {
            $MSG = $this->messages->M105;
            $CODE = $CODES::VALIDATION_ERROR;

            $data = array('STATUS' => 'error', 'SUBDATA' => array());
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $data);
        }
    }
    
    
    public function createPermission($data, $image=array()){
        $CODES = new Codes;
        
        
        $path = "";
        $save_path = "";
        
        if(!empty($image)){
            $arr = explode(".", $image->name);
            $ext = end($arr);
            //$ext = end((explode(".", $image->name)));
            $avatar = Yii::$app->security->generateRandomString().".{$ext}";
            $path = Yii::$app->params['UPLOAD_PATH'].'permissionIcon/' . $avatar;
            $save_path = "permissionIcon/".$avatar;
        }
        
        
        if($data['Permissions']['id'] != ''){
            if($save_path != ""){
                $temp = $save_path;                  
            } else {
                unset($temp);
            }
            
            $id = $data['Permissions']['id'];
            $model = Permissions::find()->where(['id'=>$id])->one();
            if($save_path == ""){
                $temp = $model->image;
            }
            
            $model->modified_by = Yii::$app->admin->adminId;
            $model->modified_on = date('Y-m-d H:i:s');
        } else {
            if($save_path != ""){
                $temp = $save_path;                  
            } else {
                unset($temp);
            }
            $model = new Permissions();
            $model->created_by = Yii::$app->admin->adminId;
            $model->created_on = date('Y-m-d H:i:s');
        }
        $model->status = LookupCodes::L_COMMON_STATUS_ENABLED;
        $model->is_delete = 1;
        $model->attributes = $data['Permissions'];
        
        if(isset($temp)){
            $model->image = $temp;
        } 
        
        //print_r($data['Permissions']);die;
        if($model->permission_type != LookupCodes::L_PERMISSION_TYPES_MENU_LEVEL){
            $model->sort_order = '';
            $model->parent_id = '';
            $model->url = '';
        }
        
        if ($model->save()) {
            if($path!=""){$image->saveAs($path);}
            $MSG = $this->messages->M115;
            $CODE = $CODES::SUCCESS;
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
        } else {
            
            $MSG = $this->messages->M116;
            $CODE = $CODES::ERROR;
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
        }
    }
    
    public function editPermission($id){
        $CODES = new Codes;
        if($id != ''){
            $model = Permissions::find()->where(['id'=>$id])->one();
            if($model){
                $MSG = $this->messages->M117;
                $CODE = $CODES::SUCCESS;
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
            } else {
                $MSG = $this->messages->M103;
                $CODE = $CODES::ERROR;
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' =>'');
            }
        }
    }
    
    
    public function deletePermission($id){
        $CODES = new Codes;
        if($id != ''){
            $model = Permissions::find()->where(['id'=>$id])->one();
            if($model){
                $model->is_delete = 0;
                $model->modified_by = Yii::$app->admin->adminId;
                $model->modified_on = date('Y-m-d H:i:s');

                if($model->save()){
                    $MSG = $this->messages->M120;
                    $CODE = $CODES::SUCCESS;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
                } else {
                    $MSG = $this->messages->M103;
                    $CODE = $CODES::ERROR;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
                }
            }
            
        }
    }
    
    public function deletePermissionImage($id){        
        $CODES = new Codes;   
        $commonFacade= new CommonFacade();
        $currentTime=$commonFacade->getCurrentDateTime();        
        if($id != ''){
            $model = Permissions::find()->where(['id'=>$id])->one();
            if($model){
                
                if(file_exists(Yii::$app->params['UPLOAD_PATH'].$model->image) && $model->image!=""){
                    unlink(Yii::$app->params['UPLOAD_PATH'].$model->image);
                }
                
                $model->image = '';
                $model->modified_by = Yii::$app->admin->adminId;
                $model->modified_on = $currentTime;

                if($model->save(false)){
                    $MSG=$this->messages->M113; //To get Message
                    $MSG= str_replace("{1}","Image", $MSG);
                    $MSG= str_replace("{2}","deleted", $MSG);
                    $CODE = $CODES::SUCCESS;

                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' =>array());
                } else {
                    $MSG = $this->messages->M103;
                    $CODE = $CODES::ERROR;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => array());
                }
            }
            
        }
    }
    
    
    public function deleteUser($id){
        $CODES = new Codes;
        if($id != ''){
            
            $model = Users::find()->where(['id'=>$id])->one();
            if($model){
                $model->is_delete = 0;
                $model->modified_by = Yii::$app->admin->adminId;
                $model->modified_on = date('Y-m-d H:i:s');

                if($model->save()){
                    $MSG = $this->messages->M120;
                    $CODE = $CODES::SUCCESS;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
                } else {
                    $MSG = $this->messages->M103;
                    $CODE = $CODES::ERROR;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
                }
            }
            
        }
    }
    
    
    public function activateDeactivate($model, $status){
        $CODES = new Codes;
        
        if($model != ''){
            //$model = Permissions::find()->where(['id'=>$id])->one();
            if($model){
                $model->status = $status;

                $model->modified_by = Yii::$app->admin->adminId;
                $model->modified_on = date('Y-m-d H:i:s');
                
                if($model->save()){
                    $MSG = $this->messages->M119;
                    $CODE = $CODES::SUCCESS;
                    $DATA = $model->status0->value;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' =>$DATA);
                } else {
                    $MSG = $this->messages->M103;
                    $CODE = $CODES::ERROR;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $status);
                }
            }
            
        }
    }
    
    
    public function getPlaceholders(){
        $CODES = new Codes;
        
        $lookup = Lookups::find()->andWhere(['type'=>  LookupTypeCodes::LT_EMAIL_TEMPLATES, 'is_delete'=>1])->all();
        
        if($lookup){
            $MSG = $this->messages->M119;
            $CODE = $CODES::SUCCESS;
            $DATA = $model->status0->value;
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' =>$lookup);
        } else {
            $status = 0;
            $MSG = $this->messages->M103;
            $CODE = $CODES::ERROR;
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $status);
        }
    }
    
    
    
    
    public function createMapping($data){
        
        $CODES = new Codes;
        $roleId = '';
        foreach($data['finalArray'] as $list){
            
            if($list[0] != ''){
                $id = $list[0];
                $model = RolePermissions::find()->where(['id'=>$id])->one();
                $model->modified_by = Yii::$app->admin->adminId;
                $model->modified_on = date('Y-m-d H:i:s');
            } else {
                $model = new RolePermissions();
                $model->created_by = Yii::$app->admin->adminId;
                $model->created_on = date('Y-m-d H:i:s');
            }
            
            $roleId = $list[1];
            $model->status = LookupCodes::L_COMMON_STATUS_ENABLED;
            $model->is_delete = 1;
            $model->role_id = $list[1];
            $model->permission_id = $list[2];
            $model->add = $list[3];
            $model->edit = $list[4];
            $model->delete = $list[5];
            $model->view = $list[6];
            $model->list = $list[7];
            $model->change_status = $list[8];
            $model->default = $list[9];
            
            if ($model->save()) {
                $MSG = $this->messages->M115;
                $CODE = $CODES::SUCCESS;
            } else {
                //$MSG = $this->messages->M102;
                $MSG = $model->getErrors();
                $CODE = $CODES::ERROR;
            }
        }
        
        
        return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $roleId);
        
        /*
        $CODES = new Codes;
        if($data['RolePermissions']['id'] != ''){
            $id = $data['RolePermissions']['id'];
            $model = RolePermissions::find()->where(['id'=>$id])->one();
            $model->modified_by = Yii::$app->admin->adminId;
            $model->modified_on = date('Y-m-d H:i:s');
        } else {
            $model = new RolePermissions();
            $model->created_by = Yii::$app->admin->adminId;
            $model->created_on = date('Y-m-d H:i:s');
        }
        $model->status = 550001;
        $model->is_delete = 1;
        $model->attributes = $data['RolePermissions'];
        
        $isExists = RolePermissions::find()->where(['role_id'=>$model->role_id, 'permission_id'=>$model->permission_id, 'is_delete'=>1])->exists();
        
        if(!$isExists){
            if ($model->save()) {
                $MSG = $this->messages->M115;
                $CODE = $CODES::SUCCESS;
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
            } else {
                $MSG = $this->messages->M116;
                $CODE = $CODES::ERROR;
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
            }
        } else {
            $MSG = $this->messages->M121;
            $CODE = $CODES::ERROR;
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
        }
        */
    }
    
    public function editMapping($id){
        $CODES = new Codes;
        if($id != ''){
            $model = RolePermissions::find()->where(['id'=>$id])->one();
            if($model){
                $MSG = $this->messages->M117;
                $CODE = $CODES::SUCCESS;
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
            } else {
                $MSG = $this->messages->M103;
                $CODE = $CODES::ERROR;
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' =>'');
            }
        }
    }
    
    
     public function changeMappingStatus($id, $status){
        $CODES = new Codes;
        
        if($id != ''){
            $model = RolePermissions::find()->where(['id'=>$id])->one();
            if($model){
                $model->status = $status;

                $model->modified_by = Yii::$app->admin->adminId;
                $model->modified_on = date('Y-m-d H:i:s');

                if($model->save()){
                    $MSG = $this->messages->M119;
                    $CODE = $CODES::SUCCESS;
                    $DATA = $model->status0->value;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' =>$DATA);
                } else {
                    $MSG = $this->messages->M103;
                    $CODE = $CODES::ERROR;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $status);
                }
            }
            
        }
    }
    
    
    
    public function deleteMapping($id){
        $CODES = new Codes;
        if($id != ''){
            $model = RolePermissions::find()->where(['id'=>$id])->one();
            if($model){
                $model->is_delete = 0;
                $model->modified_by = Yii::$app->admin->adminId;
                $model->modified_on = date('Y-m-d H:i:s');

                if($model->save()){
                    $MSG = $this->messages->M120;
                    $CODE = $CODES::SUCCESS;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
                } else {
                    $MSG = $this->messages->M103;
                    $CODE = $CODES::ERROR;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
                }
            }
            
        }
    }
    
    
    public function createTemplate($data, $image=array()){
        $CODES = new Codes;
        
        $path = "";
        $save_path = "";
        
        if(!empty($image)){
            //$ext = end((explode(".", $image->name)));
            $arr = explode(".", $image->name);
            $ext = end($arr);
            $avatar = Yii::$app->security->generateRandomString().".{$ext}";
            $path = Yii::$app->params['UPLOAD_PATH'].'attachment/' . $avatar;
            $save_path="attachment/".$avatar;
        }
        
        if($data['EmailTemplates']['id'] != ''){
            if($save_path != ""){
                $temp = $save_path;                  
            } else {
                unset($temp);
            }
            
            $id = $data['EmailTemplates']['id'];
            $model = EmailTemplates::find()->where(['id'=>$id])->one();
            if($save_path == ""){
                $temp = $model->attachment;
            }
            $model->modified_by = Yii::$app->admin->adminId;
            $model->modified_on = date('Y-m-d H:i:s');
        } else {
            if($save_path != ""){
                $temp = $save_path;                  
            } else {
                unset($temp);
            }
            $model = new EmailTemplates();
            $model->created_by = Yii::$app->admin->adminId;
            $model->created_on = date('Y-m-d H:i:s');
            
            //if($data['EmailTemplates']['event_id'] && $data['EmailTemplates']['language']){
            //$model2 = EmailTemplates::find()->where(['language'=>$data['EmailTemplates']['language'], 'event_id'=>$data['EmailTemplates']['event_id']])->exists();
            //    if($model2){
            //        $MSG = $this->messages->M123;
            //        $CODE = $CODES::ERROR;
            //        return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
            //    }
            //}
        }
        
        /* added by komal garg on 07-Sep-2017 */
        $data['EmailTemplates']['content'] = str_replace('[Click and drag to move]','', $data['EmailTemplates']['content']);
        
        $model->status = LookupCodes::L_COMMON_STATUS_ENABLED;
        $model->is_delete = 1;
        $model->attributes = $data['EmailTemplates'];

        if(isset($temp)){
            $model->attachment = $temp;
        }
        
        
        
        if ($model->save()) {
            
            if($path!=""){$image->saveAs($path);}
            $MSG = $this->messages->M134;
            $CODE = $CODES::SUCCESS;
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
        } else {
            
            $MSG = $this->messages->M116;
            $CODE = $CODES::ERROR;
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
        }
    }
    
    public function editTemplate($event, $lang){
        $CODES = new Codes;
        $model = EmailTemplates::find()->where(['event_id'=>$event, 'language'=>$lang, 'is_delete'=>1])->one();
        if($model){
            $MSG = $this->messages->M117;
            $CODE = $CODES::SUCCESS;
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
        } else {
            $model = new EmailTemplates();
            $model->event_id = $event;
            $model->language = $lang;
            
            $MSG = $this->messages->M103;
            $CODE = $CODES::ERROR;
            return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' =>$model);
        }
    }
    
    
    public function editTemplate_old($id){
        $CODES = new Codes;
        if($id != ''){
            $model = EmailTemplates::find()->where(['id'=>$id])->one();
            if($model){
                $MSG = $this->messages->M117;
                $CODE = $CODES::SUCCESS;
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
            } else {
                $MSG = $this->messages->M103;
                $CODE = $CODES::ERROR;
                return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' =>'');
            }
        }
    }
    
    
    public function deleteTemplate($id){
        $CODES = new Codes;
        if($id != ''){
            $model = EmailTemplates::find()->where(['id'=>$id])->one();
            if($model){
                $model->is_delete = 0;
                $model->modified_by = Yii::$app->admin->adminId;
                $model->modified_on = date('Y-m-d H:i:s');

                if($model->save()){
                    $MSG = $this->messages->M120;
                    $CODE = $CODES::SUCCESS;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
                } else {
                    $MSG = $this->messages->M103;
                    $CODE = $CODES::ERROR;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
                }
            }
            
        }
    }
    
    public function removeAttachment($event, $lang){
        $CODES = new Codes;
        if($event != ''){
            $model = EmailTemplates::find()->where(['event_id'=>$event, 'language'=>$lang])->one();
            if($model){
                $model->attachment = '';
                $model->modified_by = Yii::$app->admin->adminId;
                $model->modified_on = date('Y-m-d H:i:s');

                if($model->save()){
                    $MSG = $this->messages->M133;
                    $CODE = $CODES::SUCCESS;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
                } else {
                    $MSG = $this->messages->M103;
                    $CODE = $CODES::ERROR;
                    return array('CODE' => $CODE, 'MESSAGE' => $MSG, 'DATA' => $model);
                }
            }
            
        }
    }
    
    public function dashboardData(){
        $CODES = new Codes;
        
        $data['totalLookupTypes']= LookupTypes::find()->where(['is_delete'=>1])->count();
        $data['totalLookups']= Lookups::find()->where(['is_delete'=>1])->count();
        $data['totalSubscribers']= Users::find()->where(['is_delete'=>1,'user_type'=>  LookupCodes::L_USER_TYPE_SUBSCRIBER])->count();
        $data['totalAdminUsers']=  Users::find()->where(['is_delete'=>1,'user_type'=> LookupCodes::L_USER_TYPE_ADMIN])->count();    
        
        // get current month users
        $query="
            SELECT count(id) AS curent_month_subscriber
            FROM `cc_users`
            WHERE user_type=".LookupCodes::L_USER_TYPE_SUBSCRIBER.
            " AND YEAR( `created_on` ) = YEAR( NOW( ) )
            AND MONTH( `created_on` ) = MONTH( NOW( ) )
            AND is_delete='1'
            "; 
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand($query);
        $countUser = $command->queryAll(); 
        
        $data['totalCurrentMonthUsers']=$countUser[0]['curent_month_subscriber'];
        
        
        
        $data['totalEnableLookupTypes']= LookupTypes::find()->where(['is_delete'=>1,'status'=>1])->count();
        $data['totalEnableLookups']= Lookups::find()->where(['is_delete'=>1,'status'=>  LookupCodes::L_COMMON_STATUS_ENABLED])->count();
        $data['totalEnableSubscribers']=  Users::find()->where(['is_delete'=>1,'user_type'=>  LookupCodes::L_USER_TYPE_SUBSCRIBER,'status'=>  LookupCodes::L_USER_STATUS_VERIFIED])->count();
        $data['totalEnableAdminUsers']=  Users::find()->where(['is_delete'=>1,'user_type'=>  LookupCodes::L_USER_TYPE_ADMIN,'status'=>LookupCodes::L_COMMON_STATUS_ENABLED])->count();        
        
        $query1="
            SELECT count(id) AS curent_month_subscriber
            FROM `cc_users`
            WHERE user_type=".LookupCodes::L_USER_TYPE_SUBSCRIBER.
            " AND YEAR( `created_on` ) = YEAR( NOW( ) )
            AND MONTH( `created_on` ) = MONTH( NOW( ) )
            AND is_delete='1'
            AND status=".LookupCodes::L_USER_STATUS_VERIFIED;
            
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand($query1);
        $countUser1 = $command->queryAll(); 
        
        $data['totalEnableCurrentMonthUsers']=$countUser1[0]['curent_month_subscriber'];
        
        
        /*percentage*/
        
        if($data['totalLookupTypes'] == 0){
            $data['totalLookupTypes'] = 1;
        }
        if($data['totalLookups'] == 0){
            $data['totalLookups'] = 1;
        }
        if($data['totalSubscribers'] == 0){
            $data['totalSubscribers'] = 1;
        }
        if($data['totalAdminUsers'] == 0){
            $data['totalAdminUsers'] = 1;
        }
        if($data['totalCurrentMonthUsers'] == 0){
            $data['totalCurrentMonthUsers'] = 1;
        }
        
        $data['lookupTypePercentage']=($data['totalEnableLookupTypes']/$data['totalLookupTypes'])*100;        
        $data['lookupPercentage']=($data['totalEnableLookups']/$data['totalLookups'])*100;
        $data['subscriberPercentage']=($data['totalEnableSubscribers']/$data['totalSubscribers'])*100;
        $data['adminUserPercentage']=($data['totalEnableAdminUsers']/$data['totalAdminUsers'])*100;
        $data['currentMonthUsersPercentage']=($data['totalEnableCurrentMonthUsers']/$data['totalCurrentMonthUsers'])*100;
        
        return array('CODE' => $CODES::SUCCESS, 'MESSAGE' => '', 'DATA' => $data);
    }
    
}
