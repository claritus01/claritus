<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\Breadcrumbs;
use common\web\util\Codes\LookupTypeCodes;
use common\web\util\Codes\LookupCodes;

$this->title = \yii::t('app', 'City');
$this->params['breadcrumbs'][] = $this->title;
$smallTitle="Admin";
?>


<div class="container">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <?php echo $this->title;?>
           <?php if($model->id!=""){$smallTitle="Edit";}else{$smallTitle="Add";}?>
           <small><?= \yii::t('app', $smallTitle);?></small>
        </h1>
        <?=
        Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ])
        ?>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
            </div>
            <?php
            $form = ActiveForm::begin([
                        'id' => 'manageadd-form',
                        'action'=>'',
                        'options' => ['class' => 'form-horizontal',                            
                            ],
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-lg-2\">{input}</div>\n<div class=\"col-lg-6\">{error}</div>",
                            'labelOptions' => ['class' => 'col-lg-4'],
                        ],
            ]);
            ?> 
            <div class="box-body">
                <?= \Yii::$app->view->renderFile('@app/views/layouts/flash_message.php'); ?>

 <?php
    $stateList=array();
    $countryList = yii\helpers\ArrayHelper::map(\common\models\Countries::find()->where(['status'=>  LookupCodes::L_COMMON_STATUS_ENABLED,'is_delete'=>1])->all(), 'id', 'value');        
    $statusList= yii\helpers\ArrayHelper::map(common\models\Lookups::find()->where(['type'=>  LookupTypeCodes::LT_COMMON_STATUS])->all(), 'id', 'value');               
    if($model->id!=""){
        $stateList = yii\helpers\ArrayHelper::map(common\models\States::find()->where(['status'=>  LookupCodes::L_COMMON_STATUS_ENABLED,'country_id'=>$model->country_id])->all(), 'id', 'value');    
    }
    
    
    
    
    ?>

                <div class="row">
                    <div class="col-lg-2">

                    </div>
                    <div class="col-lg-8">                         
                                <?=
                                    $form->field($model, 'id', [
                                    'template' => "{label}\n<div class=\"col-sm-12\">{input}{error}{hint}</div>"])->hiddenInput()->label(FALSE);
                                ?>
                        
                        <div class="row">
                            <div class="col-lg-6">
                                <?=
                                $form->field($model, 'value', [
                                    'template' => "{label}\n<div class=\"col-sm-12\">{input}{error}{hint}</div>"])->textInput(['placeholder' => \yii::t('app', 'City name')])->label(\yii::t('app', 'City name'));
                                ?>
                            </div>
                            <div class="col-lg-6">
                                <?=
                                $form->field($model, 'zip_code', [
                                    'template' => "{label}\n<div class=\"col-sm-12\">{input}{error}{hint}</div>"])->textInput(['placeholder' => \yii::t('app', 'Zip Code')])->label(\yii::t('app', 'Zip Code'));
                                ?>
                            </div>
                                             
                        </div>
                        <div class="row">                            
                            <div class="col-lg-6">
                               <?= $form->field($model, 'country_id', [
                                    'template' => "{label}\n<div class=\"col-sm-12\">{input}{error}{hint}</div>"])->dropDownList($countryList, 
						['prompt'=>\yii::t('app', '--Select--'),
                                                 'onchange'=>'
             $.get("'.Yii::$app->urlManager->createUrl('city/loadstate?id=').
           '"+$(this).val(),function( data )                    {
                              $( "select#cityform-state_id" ).html( data );
                            });
                        '  ])->label(\yii::t('app', 'Country'));
                                ?>
                            </div>
                            <div class="col-lg-6">
                               <?= $form->field($model, 'state_id', [
                                    'template' => "{label}\n<div class=\"col-sm-12\">{input}{error}{hint}</div>"])->dropDownList( $stateList,
						['prompt'=>\yii::t('app', '--Select--')])->label(\yii::t('app', 'State'));
                                ?>
                            </div>
                        </div>
                        <?php if($statusList){
                                            foreach($statusList as $key=>$value){
                                                $statusList[$key]= \Yii::t('app', $value);
                                            }
                                        }?>
                        <div class="row">                            
                            <div class="col-lg-6">
                               <?= $form->field($model, 'status', [
                                    'template' => "{label}\n<div class=\"col-sm-12\">{input}{error}{hint}</div>"])->dropDownList($statusList, 
						['prompt'=>\yii::t('app', '--Select--')
                                                    
                                                    ])->label(\Yii::t('app', 'Status'));
                                ?>
                            </div>
                        </div>                      
                        
                        <div class="box-footer">
                            <?php echo Html::a(\Yii::t('app', 'Go Back'), Yii::$app->urlManager->createUrl(["city/"]), ['class' => 'btn btn-success btn-flat pull-left', 'name' => 'go-back']); ?>
<?= Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-primary btn-flat pull-right', 'name' => 'create-city-button']) ?>
                                </div>
                        
                                
                            </div>
                            <div class="col-lg-2">

                            </div>
                </div>
                        </div><!-- /.box-body -->
<?php ActiveForm::end(); ?>
                    </div><!-- /.box -->
                    </section><!-- /.content -->
                <!-- </div> -->
<script>
    /* used after server side validation */
    $.get("<?php echo Yii::$app->urlManager->createUrl('city/loadstate?id='.$model->country_id);?>",function( data ){
        $( "select#cityform-state_id" ).html( data );
        $("#cityform-state_id [value='<?php echo $model->state_id;?>']").attr("selected","selected");
    });
    /* code end */
</script>