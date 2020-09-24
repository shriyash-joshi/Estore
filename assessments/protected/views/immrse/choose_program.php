<?php
    
?>
<div class="blue_bg">
    <div class="container">
        <h1 class="white-text text-center">Choose Program</h1>
    </div>
</div>

<div class="container">
    <div class="well well-lg">
        <h3 class="text-center">Please select the Internship Program you want to take</h3>
        
        <?php
            $form = $this->beginWidget('CActiveForm', [
                'id' => 'ChooseTest',
                'enableAjaxValidation' => true,
                'enableClientValidation' => false,
                'clientOptions' => ['validateOnSubmit' => true, 'validateOnChange' => false, 'validateOnType' => false],
            ]);
        ?>

        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
                <div class="form-group ">
                    <?php
                        echo $form->dropDownList($model, "assessment_name", $model->testNames, ['empty' => "Choose Program", "class" => "", 'onclick' => '$(#ChooseProgramForm_assessment_name_em_).hide("slow");']);
                        echo $form->error($model, "assessment_name", ["class" => "errorMessage text-danger"]);
                    ?>
                </div>
                <button type="button" class="btn btn-success btn-block choose-test">Submit</button><br>
                <p class="text-danger"><strong>Program works best on Windows and Android devices</strong></p>
            </div>
        </div>
        
        <?php $this->endWidget(); ?>
    </div>
    
    <script type="text/javascript">
        
        $('body').on('click', '.choose-test', function(){
            var v = $('#ChooseProgramForm_assessment_name').val();
            if(!v){
                $('#ChooseProgramForm_assessment_name_em_').html('Please choose a assessment').show();
                return;
            }
            
            $('#ChooseTest').submit();
        });
    </script>
    
</div>