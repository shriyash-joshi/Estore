<style type="text/css">
    div.well input, div.question-template textarea {background: #ffffff;}
    input[type=checkbox], input[type=radio] {margin: 6px 0 0;}
</style>

<div class="container">
    <h1 class="text-center">Welcome to the Curriculum Evaluator!</h1>
</div>

<div class="container">
    <div class="well well-lg">
        <h3 class="text-center">Parent Information</h3>
        
        <?php
            $form = $this->beginWidget('CActiveForm', [
                'id' => 'ChooseTest',
                'enableAjaxValidation' => false,
                'enableClientValidation' => false,
                'clientOptions' => ['validateOnSubmit' => true, 'validateOnChange' => false, 'validateOnType' => false],
            ]);
        ?>

        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
                
                <div class="form-group ">
                    <label class="control-label">Parent Name</label>
                    <?php
                        echo $form->textField($model, "full_name", ['Placeholder' => "Parent full name", 'class' => 'form-control']);
                        echo $form->error($model, "full_name", ["class" => "errorMessage text-danger"]);
                    ?>
                </div>
                
                <div class="form-group ">
                    <label class="control-label">Parent contact Number (Optional)</label>
                    <?php
                        echo $form->textField($model, "contact_number", ['Placeholder' => "Contact number with country code", 'class' => 'form-control']);
                        echo $form->error($model, "contact_number", ["class" => "errorMessage text-danger"]);
                    ?>
                </div>
                
                <div class="form-group ">
                    <label class="control-label">Parent Email ID</label>
                    <?php
                        echo $form->textField($model, "email", ['Placeholder' => "Parent email", 'class' => 'form-control']);
                        echo $form->error($model, "email", ["class" => "errorMessage text-danger"]);
                    ?>
                </div>
                
                <button type="submit" class="btn btn-success btn-block choose-test">Submit</button>
            </div>
        </div>
        
        <?php $this->endWidget(); ?>
    </div>
    
    <script type="text/javascript">
        
    </script>
    
</div>