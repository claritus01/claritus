<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title= 'Change Password';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php if (Yii::$app->session->getFlash('success')): ?>
    <div class="alert alert-success">
        <?php echo Yii::$app->session->getFlash('success'); ?>
    </div>
<?php endif; ?>

<?php if (Yii::$app->session->getFlash('error')): ?>
    <div class="alert alert-danger">
        <?php echo Yii::$app->session->getFlash('error'); ?>
    </div>

<?php endif; ?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>
</div>

<?php
    $form = ActiveForm::begin([
                'id' => 'changepassword-form',
                'options' => ['class' => 'form-horizontal',],
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
                    'labelOptions' => ['class' => 'col-lg-1 control-label'],
                ],
    ]);
 ?>   
    <?= $form->field($model, 'oldPassword')->passwordInput()->label('Old Password'); ?>    
    <?= $form->field($model, 'newPassword')->passwordInput()->label('New Password'); ?>    
    <?= $form->field($model, 'confirmNewPassword')->passwordInput()->label('Confirm Password'); ?>    

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('ChangePassword', ['class' => 'btn btn-primary', 'name' => 'changepassword-button']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

