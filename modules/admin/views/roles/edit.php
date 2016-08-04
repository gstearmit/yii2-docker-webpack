<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('app', 'Roles') . ' / ';
$this->title .= !empty($model->description) ? $model->description : Yii::t('app', 'Create');
?>
<?= Html::a(Yii::t('app', 'List'), ['index'], ['class' => 'btn btn-default']) ?>&nbsp;
<?= Html::a(Yii::t('app', 'Add'), ['edit'], ['class' => 'btn btn-default']) ?><hr>
<?= $this->render('/shared/flash') ?>

<?php $form = ActiveForm::begin(['options' => ['id' => 'roles-form', 'class' => 'ajax-form']]); ?>

    <!-- name -->
  <?= $form->field($model, 'name')->textInput(['maxlength' => true])->hint(Yii::t('app.messages', 'Only latin letters')) ?>

  <!-- description -->
  <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

  <?php if ($model->isSuperUser()):?>
  <div class="alert alert-warning" role="alert">
      <?= Yii::t('app.messages', 'This role has all privileges by default, it can not be deleted') ?>
  </div>
  <?php endif?>

  <!-- permissions -->
  <?= $form->field($model, 'permissions')
      ->dropDownList(ArrayHelper::map($permissions, 'name', function ($row) {
          return Yii::t('app', $row->description);
      }), [
          'multiple' => true,
          'size' => 15,
          'disabled' => $model->isSuperUser()
      ]) ?>

  <!-- roles -->
  <?= $form->field($model, 'roles')
      ->dropDownList(ArrayHelper::map($roles, 'name', function ($row) {
          return Yii::t('app', $row->description);
      }), [
          'multiple' => true,
          'size' => 15,
          'disabled' => $model->isSuperUser()
      ])
      ->hint(Yii::t('app', 'The role will receive all the privileges of the selected role')) ?>

  <hr>
  <div class="form-controls">
    <div class="form-group pull-left">
      <?= Html::submitButton(Yii::t('app', 'Save'), [
          'name' => 'submit',
          'class' => 'btn btn-info',
          'data-loading-text' => Yii::t('app.messages', 'Please wait…')
      ]) ?>
      <?php if ($model->primaryKey) : ?>
      <?= Html::a(
          Yii::t('app', 'Delete'),
          ['delete', 'id' => $model->primaryKey, 'reload' => true],
          [
              'title' => Yii::t('app', 'Delete'),
              'class' => 'btn btn-danger',
              'data-pjax' => 1,
              'data-method' => 'post',
              'data-confirm' => Yii::t('app.messages', 'Are you sure you want to delete this record?')
          ]
      ); ?>
      <?php endif?>
    </div>

    <div class="form-inline pull-right text-right text-muted small">
      <div class="form-group">
        <?= Yii::t('app', 'Date create') ?><br>
        <?= Yii::$app->formatter->asDatetime($model->created_at, 'short') ?>
      </div>&nbsp;&nbsp;&nbsp;
      <div class="form-group">
        <?= Yii::t('app', 'Date update') ?><br>
        <?= Yii::$app->formatter->asDatetime($model->updated_at, 'short') ?>
      </div>
    </div>
  </div>

<?php ActiveForm::end(); ?>
