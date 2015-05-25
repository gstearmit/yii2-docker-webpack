<?php
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Tags');
?>
<?= $this->render('/shared/forms/header', ['title' => $model->title, 'model' => $model]) ?>

<?php $form = ActiveForm::begin(['options' => ['class' => 'form']]); ?>

  <!-- title -->
  <?= $form->field($model, 'title')->textInput() ?>

  <!-- count -->
  <?= $form->field($model, 'count') ?>

<?= $this->render('/shared/forms/bottom', ['model' => $model]) ?>
