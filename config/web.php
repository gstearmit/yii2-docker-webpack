<?php

/**
 * Config for Web Application
 */

define('YII_ENV_MODE', 'web');

$params = require __DIR__ . '/params.php';

$config = [
    'id'             => 'iNCVrvPTpDQuWpdnqqz6NPXeUHsRQoV3',
    'basePath'       => dirname(__DIR__),
    'bootstrap'      => ['log'],
    'name'           => 'Bootstrap',
    'defaultRoute'   => 'index/index',
    'sourceLanguage' => 'en',
    'language'       => 'en',
    'timeZone'       => 'Asia/Ho_Chi_Minh',

    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],

    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
    ],

    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'mwsCLjohbWvqV8sLaHXebbZxDhmHEHF3',
            'enableCsrfValidation' => true,

            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,

            'rules' => [
                'signup' => 'index/signup',
                'login' => 'index/login',
                'logout' => 'index/logout',
                'reset-password' => 'index/reset-password',
                'confirm-email' => 'index/confirm-email',
                'confirm-request' => 'index/confirm-request',
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'sizeFormatBase' => 1000,
            'dateFormat' => 'php:d M Y',
            'datetimeFormat' => 'php:d M Y, H:i',
            'timeFormat' => 'php:H:i:s',
        ],
        'response' => [
            'formatters' => [
                \yii\web\Response::FORMAT_JSON => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                    'encodeOptions' => JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],
        'user' => [
            'class' => 'app\components\User',
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/index/login']
        ],
        'errorHandler' => [
            'errorAction' => YII_ENV_DEV ? null : '/index/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                    'except' => [
                        'yii\web\HttpException:400',
                        'yii\web\HttpException:401',
                        'yii\web\HttpException:403',
                        'yii\web\HttpException:404',
                        'yii\web\HttpException:422',
                    ],
                ],
            ],
        ],
        'assetManager' => [
            'linkAssets' => true,
            'bundles' => [
                'yii\web\YiiAsset' => false,
                'yii\web\JqueryAsset' => false,
                'yii\widgets\PjaxAsset' => false,
                'yii\widgets\ActiveFormAsset' => false,
                'yii\grid\GridViewAsset' => false,
                'yii\validators\ValidationAsset' => false,
                'yii\bootstrap\BootstrapAsset' => false,
                'yii\bootstrap\BootstrapThemeAsset' => false,
                'yii\bootstrap\BootstrapPluginAsset' => false,
                'yii\jui\JuiAsset' => false
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache',
        ],
        'settings' => [
            'class' => 'rkit\settings\Settings',
        ],
        'notify' => [
            'class' => 'app\components\Notify',
        ],
    ],
    'params' => $params,
];

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/local/main.php';

/**
 * Maintenance mode
 */

if (file_exists($config['basePath'] . '/runtime/maintenance')) {
    if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)) {
        $config['catchAll'] = ['index/maintenance'];
    }
}

return $config;
