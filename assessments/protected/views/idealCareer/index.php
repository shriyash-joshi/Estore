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
                <h1 class="white-text mb-0"> Ideal Career Assessment</h1>
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
               <img src="https://univariety.sgp1.digitaloceanspaces.com/img/achievement.png"/>
            </div>
        </div>
        <div class="col-md-8">
            <h3>About Assessment</h3>
            <p>The assessment helps you better understand your strengths and personality. It helps you identify your best fit career. More than 200+ career professionals have worked to design, test and update this career assessment. Thousands of students have been giving this assessment each year. The testing algorithm is constantly updated with emerging careers options</p>
            <h3>Assessment Output</h3>
            <p>The assessment output is in the form of a 14 page detailed report assessing and identifying your natural inclination for further studies, areas of improvement, skills, values and personal characteristics. The report is divided into 4 sections. The motivation section identifies the top 3 factors that help you perform better at work. The aptitude section evaluates you on verbal, numerical, spatial, critical dissection and acuteness aptitude. Interest section utilises the RIASEC theory for suggestion that careers that will be of interest to you and keep you motivated. The personality section evaluates your personality type.</p>
            <h3>Instructions</h3>
            <ol class="pl-3">
                <li>We advise you to complete the assessment in 25 minutes.</li>
                <li>The assessment has 7 sections with around 20 questions in each section. The aptitude section focuses on your problem solving skills. All other questions require a ‘yes’ or a ‘no’ as answers.   </li>
                <li>At the end of the assessment you will get a detailed report which will give you a clear direction of where you should be going.</li>
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