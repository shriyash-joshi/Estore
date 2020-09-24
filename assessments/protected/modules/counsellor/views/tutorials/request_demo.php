<?php
/**
 * @var $this TutorialsController
 * @var $form CActiveForm
 * @var $model RequestDemo
 */
?>

<?php
$form = $this->beginWidget('CActiveForm', [
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
    'clientOptions' => ['validateOnSubmit' => true, 'validateOnChange' => false, 'validateOnType' => false],

]);
?>
<hr />
<div class="row mb-5 mt-5">
    <div class="col-sm-12">
<!--        <h3 class="mt-5">Request a Demo</h3>-->

<!--        <div class="form-group">-->
<!--            <label class="control-label">-->
<!--                What are the benefits that I will enjoy as a Supercounsellor?-->
<!--            </label>-->
<!--            --><?php
//            echo $form->textArea($model, 'my_benifits', ['class' => 'form-control', 'rows' => '4', 'placeholder' => '']);
//            echo $form->error($model, 'my_benifits', ['class' => 'errorMessage text-danger'])
//            ?>
<!--        </div>-->
<!---->
<!--        <div class="form-group">-->
<!--            <label class="control-label">-->
<!--                How will my students benefit from the technology?-->
<!--            </label>-->
<!--            --><?php
//            echo $form->textArea($model, 'student_benifits', ['class' => 'form-control', 'rows' => '4', 'placeholder' => '']);
//            echo $form->error($model, 'student_benifits', ['class' => 'errorMessage text-danger'])
//            ?>
<!--        </div>-->

        <div class="text-center">
            <button type="submit" class="btn btn-success text-center">Request a Demo</button>
        </div>
    </div>
</div>
<?php $this->endWidget(); ?>
