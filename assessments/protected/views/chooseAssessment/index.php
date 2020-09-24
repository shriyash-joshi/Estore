<?php
    
?>
<div class="blue_bg">
    <div class="container">
        <h1 class="white-text text-center">Choose Assessment</h1>
    </div>
</div>

<div class="container">
    <div class="well well-lg">
        <h3 class="text-center">Please select the assessment you want to take</h3>
        
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
                        echo $form->dropDownList($model, "assessment_name", $model->testNames, ['empty' => "Choose Assessment", "class" => "", 'onclick' => '$(#ChooseAssessmentForm_assessment_name_em_).hide("slow");']);
                        echo $form->error($model, "assessment_name", ["class" => "errorMessage text-danger"]);
                    ?>
                </div>
                <button type="button" class="btn btn-success btn-block choose-test">Submit</button>
            </div>
        </div>
        
        <?php $this->endWidget(); ?>
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Choose Assessment</h4>
                </div>
                <div class="modal-body">
                    This is the second of the two tests that you can take. Please select a relevant test, 
                    because you will not be able to change to another later.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="$('#ChooseTest').submit();">Confirm</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>    
    
    <script type="text/javascript">
        
        $('body').on('click', '.choose-test', function(){
            var v = $('#ChooseAssessmentForm_assessment_name').val();
            if(!v){
                $('#ChooseAssessmentForm_assessment_name_em_').html('Please choose a assessment').show();
                return;
            }
            
            $('#myModal').modal('show');
        });
    </script>
    
</div>