<?php
/*
 * @author: Anjan
 * @date: 23-Feb-2016
 * @description: UserController for all basic user related activities Ex:Login,Register
 */

namespace app\modules\user\controllers;

use yii;
use yii\web\Controller;

use backend\modules\user\facades\UserFacade;

use common\models\Users;

class UserController extends Controller {    
    
    /*
     * Index action
     * Handles register view rendering
     */

    
    public function actionIndex() {
        $model = new Users;
        return $this->render('register', [
                    'model' => $model,
        ]);
    }
    
    public function actionRegister() {

        $facade = new UserFacade();
        $model = new Users;
        
        $request=Yii::$app->request->post();
        //$request=$request['ChangePassword'];
        if(!empty($request)){
            $response=$facade->register($request);             
            // give condition on different status basis
            Yii::$app->getSession()->setFlash('success', $response['MESSAGE']);
            return  $this->render('register',[
                           'model' =>$model,
            ]);
        }
       else{          
          return  $this->render('register',[
                           'model' =>$model,
            ]);
        }
    }

    /*
     * Register action controller
     * Handles formrendering, validation and register function
     */

   
}
