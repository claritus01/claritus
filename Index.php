<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\Breadcrumbs;
use common\web\util\Codes\LookupCodes;
use common\web\util\Codes\LookupTypeCodes;


$this->title = \yii::t('app', 'Manage City');
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('@web/js/listing.js');
$this->registerJsFile('@web/js/common.js');
?>
<script>
    var expires = new Date();
    expires.setTime(expires.getTime() + (1 * 24 * 60 * 60 * 1000));
    document.cookie = 'language' + '=' + '<?php echo $lang; ?>' + ';expires=' + expires.toUTCString();
</script>

<!-- <div class="container"> -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
           <?php echo $this->title;?>
            <small><?php echo \yii::t('app', 'List'); ?></small>
        </h1>
        <?=
        Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ])
        ?>
    </section>
 <?php
    $statusList = yii\helpers\ArrayHelper::map(\common\models\Lookups::find()->where(['type'=>  LookupTypeCodes::LT_COMMON_STATUS])->all(), 'id', 'value');
      $countStatusList=  count($statusList);
?>
    <!-- Main content -->
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <?php 
                if($permission->add == 1){ 
                echo Html::a(\yii::t('app', 'Add'), Yii::$app->urlManager->createUrl(["city/create"]), ['class' => 'btn btn-success btn-flat pull-left col-lg-1', 'name' => 'create']); 
                }?>
            </div>
            <div class="box-body">
                <?= \Yii::$app->view->renderFile('@app/views/layouts/flash_message.php'); ?>
               <table id="listing-table" class="table table-hover">
                <thead><tr>
                  <th><?php echo \yii::t('app', 'ID'); ?></th>
                  <th><?php echo \yii::t('app', 'Name'); ?></th>                  
                  <th><?php echo \yii::t('app', 'Zip Code'); ?></th>
                  <th><?php echo \yii::t('app', 'State'); ?></th>
                  <th><?php  echo \yii::t('app', 'Status'); ?></th> 
                
                <?php if($permission->view == 1 || $permission->edit == 1 || $permission->delete == 1){ ?> 
                  <th><?php echo \yii::t('app', 'Action'); ?></th>
                <?php } ?>  
                </tr>
                </thead><tbody>
                <?php foreach($data AS $k=>$v){
                    
                    ?>
                <tr id="tr_<?= $v['id'];?>">
                  <td><?=$v['id'];?></td>
                  <td><?=$v['value'];?></td>                  
                  <td><?=$v['zip_code'];?></td>                 
                  <td><span class=""><?=$v->state->value;?></span></td>                    
                  <?php if($permission->change_status == 1 && $countStatusList>2){ ?>
                  <td>
                      <label style="display: none;"><?php echo $v->status0->value;?></label>
                      <select class="form-control" onchange="activateDeactivate(<?php echo $v['id'];?>, '<?php echo Yii::$app->getUrlManager()->createUrl(["city/changestatus"]); ?>',  this.value,'dd')">
                        <?php 
                        if($statusList) {
                                echo "<option value=''>".\Yii::t('app', '-- Select Status --')."</option>";
                                 foreach($statusList as $key=>$post){?>
                                      <option value='<?=$key?>' <?php if($key==$v['status']){echo 'selected';}?>><?=\Yii::t('app', $post)?></option>
                                 <?php }
                            } else {
                                 echo "<option>-</option>";
                            }
                        ?>
                    </select>
                  </td>
                  <?php }
                  elseif($permission->change_status == 1 && $countStatusList==2){ 
                      $changeTo=  LookupCodes::L_COMMON_STATUS_DISABLED;
                      if($v['status']==LookupCodes::L_COMMON_STATUS_DISABLED){$changeTo=  LookupCodes::L_COMMON_STATUS_ENABLED;}
                      ?>
                  <td><label style="display: none;"><?php echo $v->status0->value;?></label><input id="toggle-event_<?= $v['id'];?>" type="checkbox" <?=($v['status']=='550001')?'checked':''?> id="toggle-event" data-toggle="toggle" data-on="Enabled" data-off="Disabled" data-style="ios" onchange="activateDeactivate(<?php echo $v['id'];?>, '<?php echo Yii::$app->getUrlManager()->createUrl(["city/changestatus"]); ?>', <?=$changeTo?>,'')"></td>
                  <?php }
                  else{?>
                  <td id="status_<?= $v['id'];?>"><span class=""><?= \yii::t('app', $v->status0->value);?></span></td>
                  <?php } ?>
                  
                  <?php if($permission->view == 1 || $permission->edit == 1 || $permission->delete == 1){ ?>
                  <td>
                     <?php if($permission->view == 1){ ?>                     
                      <?= Html::a(\yii::t('app', 'View'), Yii::$app->urlManager->createUrl(["city/view",'id'=>$v['id']])); ?>                                   
                       <?php } ?>
                     <?php if($permission->edit == 1){ ?>    &nbsp;|&nbsp;
                     <?= Html::a(\yii::t('app', 'Edit'), Yii::$app->urlManager->createUrl(["city/update",'id'=>$v['id']])); ?>                                      
                     <?php } ?>
                      <?php if($permission->delete == 1){ ?>&nbsp;|&nbsp;
                      <a style="cursor: pointer;" onclick="permanentDelete(<?php echo $v['id']?>, '<?php echo Yii::$app->getUrlManager()->createUrl(["city/delete"]); ?>', this);">  
                         <?php echo \yii::t('app', 'Delete'); ?> 
                    </a>
                      <?php } ?>
                  </td>
                  <?php } ?>
                </tr>
                <?php } ?>
              </tbody></table>
            </div><!-- /.box-body -->
            <div class="box-footer">

            </div>
        </div><!-- /.box -->
    </section><!-- /.content -->
<!-- </div> -->
