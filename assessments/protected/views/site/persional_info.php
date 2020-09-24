<div class="container pt-4 pb-4">
    <?php
        $form = $this->beginWidget('CActiveForm', [
            'enableAjaxValidation' => true,
            'enableClientValidation' => false,
            'clientOptions' => ['validateOnSubmit' => true, 'validateOnChange' => false, 'validateOnType' => false],
        ]);
    ?>

    <h2 class="text-center">Please provide the below information</h2>
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <div class="form-group ">
                <label for="PersonalInfoForm_test_user_name">Full Name &nbsp;<small class="text-danger">(The same will reflect in reports)</small></label>
                <?php
                    echo $form->textField($model, "test_user_name", ['placeholder' => "Full name", "class" => "form-control"]);
                    echo $form->error($model, "test_user_name", ["class" => "errorMessage text-danger"]);
                ?>
            </div>
            <button type="submit" class="btn btn-success btn-block">Submit</button>
        </div>
    </div>
    

    <?php $this->endWidget(); ?>
</div>

<script>
    $(function () {
        parent.$.fn.colorbox.resize({height: '60%', width: '60%'});
    });
</script>