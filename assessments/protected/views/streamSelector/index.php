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
                <h1 class="white-text mb-0"> Stream Selection Assessment</h1>
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
               <img src="https://univariety.sgp1.digitaloceanspaces.com/img/instruction.png"/>
            </div>
        </div>
        <div class="col-md-8">
            <h3>About Assessment</h3>
            <p>The assessment helps you determine which stream to take after 10th class. It is based on the RIASEC theory and determines your verbal, numerical, clerical, spatial and reasoning aptitude. Based on your aptitude and personality, the assessment determines the most suitable stream for you. The aptitude section calculates your strengths and weaknesses to determine abilities required to study the four streams. The interest section helps to determine your feeling of wanting to know or learn about something.</p>
            <h3>Assessment Output</h3>
            <p>The assessment output is in the form of a report containing your aptitude and interest scores on the commerce, humanities, science(math) and science(bio) streams. The report gives a brief about each stream and what benefits you would receive from taking the stream. For your visualization, the stream selector graph will plot the aptitude and interest scores for comparison among various branches.</p>
            <h3>Instructions</h3>
            <ol class="pl-3">
                <li>The assessment consists of 76 questions out of which 36 questions measure interest and 40 questions measure aptitude.</li>
                <li>There are no right or wrong answers for the questions.  </li>
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