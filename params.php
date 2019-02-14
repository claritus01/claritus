<?php
return [
    'ADMIN_EMAIL' => 'admin@example.com',
    'PATH_XML_EMAIL' => __DIR__ . '/../../common/web/util/Xml/Email.xml',
    'PATH_XML_MESSAGES' => __DIR__ . '/../../common/web/util/Xml/Messages.xml',
    'FORGOT_PASSWORD_EXPIRY_MINUTES'=>'15',
    'MCRYPT_SALT'=>'c0r3ap#p',
    //'DEVICE_TOKEN_OPTIONAL'=>FALSE, //FALSE in case of Push Notification else TRUE
    'FORGOT_PASSWORD_URL'=>'http://localhost/test/web/index.php/user/Api/v1/user/login',
    'UPLOAD_PATH'=>realpath(Yii::$app->basePath).'/uploads/',    
    'UPLOAD_URL'=>Yii::$app->request->baseUrl.'/uploads/',
    'HTTP_UPLOAD_URL'=>'http://localhost/test/web/uploads/',    
];
