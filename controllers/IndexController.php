<?php

namespace app\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\services\SocialAuth;
use app\models\forms\LoginForm;
use app\models\forms\SignupForm;
use app\models\forms\SignupProviderForm;
use app\models\forms\PasswordResetRequestForm;
use app\models\forms\ResetPasswordForm;
use app\models\forms\ConfirmEmailForm;

class IndexController extends \yii\web\Controller
{
    private $socialAuth;

    public function __construct($id, $module, SocialAuth $socialAuth, $config = [])
    {
        $this->socialAuth = $socialAuth;
        parent::__construct($id, $module, $config);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => [
                    'auth',
                    'logout',
                    'signup',
                    'signup-provider',
                    'confirm-request',
                    'request-password-reset',
                ],
                'rules' => [
                    [
                        'actions' => [
                            'auth',
                            'signup',
                            'signup-provider',
                            'request-password-reset',
                        ],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'logout',
                            'confirm-request'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successCallback'],
                'successUrl' => 'signup-provider'
            ],
        ];
    }

    public function successCallback($client)
    {
        Yii::$app->session['authClient'] = $client;
    }

    public function goHome()
    {
        Yii::$app->session['authClient'] = null;
        return Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t(
                        'app.msg',
                        'Please activate your account. A letter for activation was sent to {email}',
                        ['email' => $model->email]
                    )
                );
                return $this->goHome();
            }
            Yii::$app->session->setFlash(
                'error',
                Yii::t(
                    'app.msg',
                    'An error occurred while sending a message to activate account'
                )
            );
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionSignupProvider()
    {
        $session = Yii::$app->session;
        if ($session['authClient'] === null) {
            return $this->goHome();
        }

        $this->socialAuth->execute($session['authClient']);
        $user = $this->socialAuth->user();

        if ($user === null) {
            return $this->goHome();
        }

        $model = new SignupProviderForm($user, $this->socialAuth->email());

        if ($this->socialAuth->isExist() && $user->isActive() === false) {
            $session->setFlash('error', $user->getStatusDescription());
            return $this->goHome();
        }

        if ($this->socialAuth->isExist() && $user->isActive() && $model->login()) {
            return $this->goHome();
        }

        if ($this->socialAuth->isVerified() && $model->saveUser() && $model->login()) {
            return $this->goHome();
        }

        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            $model->login();

            if ($model->sendEmail()) {
                $session->setFlash(
                    'success',
                    Yii::t(
                        'app.msg',
                        'Please activate your account. A letter for activation was sent to {email}',
                        ['email' => $model->email]
                    )
                );
                return $this->goHome();
            }
            $session->setFlash(
                'error',
                Yii::t('app.msg', 'An error occurred while sending a message to activate account')
            );
            return $this->goHome();
        }

        return $this->render('signupProvider', [
            'model' => $model
        ]);
    }

    public function actionConfirmRequest()
    {
        $user = Yii::$app->user->identity;
        if ($user->isConfirmed()) {
            throw new ForbiddenHttpException(Yii::t('app.msg', 'Access Denied'));
        } // @codeCoverageIgnore

        $model = new ConfirmEmailForm();

        if ($model->sendEmail($user)) {
            Yii::$app->session->setFlash(
                'success',
                Yii::t('app.msg', 'A letter for activation was sent to {email}', ['email' => $user->email])
            );
            return $this->goHome();
        }
        Yii::$app->session->setFlash(
            'error',
            Yii::t('app.msg', 'An error occurred while sending a message to activate account')
        );
        return $this->goHome();
    }

    public function actionConfirmEmail($token)
    {
        $model = new ConfirmEmailForm();

        if (!$model->validateToken($token)) {
            Yii::$app->session->setFlash(
                'error',
                Yii::t('app.msg', 'Invalid link for activate account')
            );
            return $this->goHome();
        }

        if ($model->confirmEmail()) {
            Yii::$app->session->setFlash(
                'success',
                Yii::t('app.msg', 'Your account is successfully activated')
            );
        }
        return $this->goHome();
    }

    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t('app.msg', 'We\'ve sent you an email with instructions to reset your password')
                );
                return $this->goHome();
            }
            Yii::$app->session->setFlash(
                'error',
                Yii::t('app.msg', 'An error occurred while sending a message to reset your password')
            );
            return $this->goHome();
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    public function actionResetPassword($token)
    {
        $model = new ResetPasswordForm();

        if (!$model->validateToken($token)) {
            Yii::$app->session->setFlash(
                'error',
                Yii::t('app.msg', 'Invalid link for reset password')
            );
            return $this->goHome();
        }

        if ($model->load(Yii::$app->request->post()) &&
            $model->validate() &&
            $model->resetPassword()
        ) {
            Yii::$app->session->setFlash(
                'success',
                Yii::t('app.msg', 'New password was saved')
            );
            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /** @see commands/MaintenanceController **/
    public function actionMaintenance()
    {
        if (!Yii::$app->catchAll) {
            throw new NotFoundHttpException(Yii::t('app.msg', 'Page not found'));
        } // @codeCoverageIgnore

        $this->layout = 'maintenance';
        return $this->render('maintenance');
    }
}
