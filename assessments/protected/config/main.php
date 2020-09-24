<?php

return [
    'basePath' => dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>'Univariety',
    'preload' => ['log'],
    'import' => [
        'application.models.*',
        'application.components.*',
    ],

    'modules' => [
        'counsellor' => ['defaultController' => 'inventory']
    ],
    'components' => [
        'urlManager' => [
            'urlFormat'=>'path',
            'showScriptName' => false,
            'rules' => [
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ],
        ],
        'db' => require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'database.php',
        'errorHandler' => [
            'errorAction'=>'site/error',
        ],
        'log' => [
            'class'=>'CLogRouter',
                'routes' => [
                    [
                        'class'=>'CFileLogRoute',
                        'levels'=>'error, warning',
                    ],
                    //['class'=>'CWebLogRoute'],
                ],
        ],
    ],
    'params' => [],
];