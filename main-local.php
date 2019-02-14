<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=clarituscore',
            'username' => 'clarituscore',
            'password' => 'core123#',
            'charset' => 'utf8',
            'tablePrefix' => 'cc_',
            
        ],
        /*'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],*/
        'smtp' => 'yii\smtp\Mail',
        'ses' => 'yii\ses\Mailer',
    ],
];
