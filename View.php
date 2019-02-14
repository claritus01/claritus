<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\Breadcrumbs;

$this->title = \Yii::t('app', 'City');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
           <?=$this->title ;?>
            <small><?php echo \Yii::t('app', 'View')?></small>
        </h1>
        <?=
        Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ])
        ?>
    </section>
    
    
    <!-- Main content -->
    <section class="content">
        <div class="box box-default box-my ">
            <div class="box-header with-border">
            </div>
            <div class="box-body">
                <?= \Yii::$app->view->renderFile('@app/views/layouts/flash_message.php'); ?>
                <div class="row">
                   
                    <div class="col-lg-12">
                        <h3 class="profile-username text-center"><?php echo \yii::t('app', $model[0]['value']); ?></h3><br/>
                        <p class="text-muted text-center my-para"><strong class="light-grey"><?php echo \yii::t('app', 'Zip Code'); ?>:</strong><?php echo \yii::t('app', $model[0]['zip_code']); ?></p><br/>
                         <p class="text-muted text-center my-para"><strong class="light-grey"><?php echo \yii::t('app', 'Country'); ?>:</strong> <?php echo \yii::t('app', $model['country']); ?></p><br/>
                        <p class="text-muted text-center my-para"><strong class="light-grey"><?php echo \yii::t('app', 'State'); ?>:</strong> <?php echo \yii::t('app', $model[0]->state->value); ?></p><br/>
                        <p class="text-muted text-center my-para"><strong class="light-grey"><?php echo \yii::t('app', 'Type'); ?>:</strong> <?php echo \yii::t('app', $model[0]->status0->value); ?></p><br/>
                        
                    </div>
                   
                </div>
            </div><!-- /.box-body -->
            <div class="box-footer">
                <?php if($model[0]['id']){?>
<?php echo Html::a(\yii::t('app', 'Go Back'), Yii::$app->urlManager->createUrl(["city/"]), ['class' => 'btn btn-success btn-flat pull-left', 'name' => 'go-back']); ?>
                <?php echo Html::a(\yii::t('app', 'Edit'), Yii::$app->urlManager->createUrl(["city/update/",'id'=>$model[0]['id']]), ['class' => 'btn btn-primary btn-flat pull-right', 'name' => 'edit-city-button']); ?>
                <?php }?>
            </div>
        </div><!-- /.box -->
    </section><!-- /.content -->
<!-- </div> -->
