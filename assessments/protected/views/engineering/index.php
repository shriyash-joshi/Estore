<?php
    if ($this->OrderAssessment->completed_on) {
        $button = CHtml::button('View Report', ['class' => 'breadcum_btn btn-sm btn-green take-the-test']);
    }else{
        if(!$this->OrderAssessment->started_on){
            $button = CHtml::button('Take The Test', ['class' => 'breadcum_btn btn-sm btn-green take-the-test']);
        }else{
            $button = CHtml::button('Continue The Test', ['class' => 'breadcum_btn btn-sm btn-green take-the-test']);
        }
    }
?>
<div class="blue_bg">
    <div class="container">
        <div class="row pt-4 pb-4">
            <div class="col-md-7">
                <h1 class="white-text mb-0"> Engineering Assessment</h1>
            </div>
            <div class="col-md-3 pt-4 white-text col-xs-12">&nbsp;</div>
            
            <div class="col-md-2 col-xs-12 pl-md-0">
                <ul class="list-none white-text list-inline  pt-3 pr-3">
                    <li>
                        <?php echo $button; ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container pt-4 pb-4">
    <div class="row pt-4 pb-4">
        <div class="col-md-4 text-center">
            <div class="round-circle">
               <img src="https://univariety.sgp1.digitaloceanspaces.com/img/configuration.png"/>
            </div>
        </div>
        <div class="col-md-8">
            <h3>About Assessment</h3>
            <p>This test is for students who have decided to pursue career in engineering. It will help choose your preferred engineering branch among the plethora of engineering branches that exist today. The test will evaluate you on your interest and capabilities. It will evaluate you on four fundamental aptitudes of an engineer namely spatial, closure, mechanical and logical. The test has been prepared with the help of psychologists, academic counselors and psychiatrist.</p>
            <h3>Assessment Output</h3>
            <p>The test output is in the form of a report which shares a brief about all the engineering branches. The report will consist of scores on engineering interests with the highest score indicating the best engineering area for you. The report will also consist of an engineering interest graph which will plot all your engineering interests on a single graph for you to visualize clearly.</p>
            <h3>Instructions</h3>
            <ol class="pl-3">
                <li>The test will consist of questions with three option. Choose the option that you feel is the best for you.</li>
                <li>The questions do not have any right or wrong answers to the questions.</li>
                <li>At the end of the test you will get a detailed report which will give you a clear direction of where you should be going.</li>
            </ol>
            <div class="row">
                <div class="col-md-3 col-sm-4 mb-3 pr-0"> <?php echo $button; ?></div>
            </div>
         </div>
    </div>
</div>
<script type="text/javascript">
$('body').on('click', '.take-the-test', function(){ window.open("<?php echo $this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []) ?>", '_blank');});
</script>