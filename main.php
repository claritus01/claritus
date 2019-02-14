<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log',
        'common\base\Configurations',                 

    ],
    'defaultRoute' => 'user/login',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=test',
            'username' => 'test',
            'password' => 'test',
            'charset' => 'utf8',
            'tablePrefix' => 'cc_',
            
        ]
        
        
    ],
];
