<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<?php if (!Yii::$app->user->isGuest && !Yii::$app->user->identity->isConfirmed()): ?>
<div class="alert alert-warning" role="alert">
  <?= Yii::t('app.msg', 'To complete the registration process, you must activate your account') ?><br>
  <?= Yii::t('app.msg', 'We sent you a letter on {email}', ['email' => Yii::$app->user->identity->email]) ?><br>
  <?= Html::a(Yii::t('app', 'Send again'), ['/confirm-request']) ?>
</div>
<?php endif?>

<?php $this->title = Yii::t('app', 'Index'); ?>
<div class="page-header">
  <h1>WESHOP FRONT-END v3 for Yii2 @08/2017</h1>
</div>

<p class="lead">Features Funtion</p>
<ul>
  <li>Users, Roles, Registration, Basic and social authorization</li>
  <li>Settings</li>
  <!li> File Manager</li>
  <li>Webpack for assets</li>
</ul>

<hr>
<?= Html::a(Yii::t('app', 'Control Panel'), ['/admin'], ['class' => 'btn btn-default']) ?>
