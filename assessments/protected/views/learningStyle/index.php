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
                <h1 class="white-text mb-0"> Learning Style Assessment</h1>
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
               <img src="https://univariety.sgp1.digitaloceanspaces.com/img/meditation.png"/>
            </div>
        </div>
        <div class="col-md-8">
            <h3>About Assessment</h3>
            <p>The assessment is based on The Learning Style Questionnaire developed by Rita and Kenneth Dunn. The questionnaire was studied by their colleague Gary E. Price who did a content analysis to discover the consistent factors in the questionnaire. As a result of this analysis the Learning Style assessment was made. The assessment reveals how you prefer to study, concentrate and learn. The assessment analyses your learning preferences for immediate environment, emotionality, sociological needs and physical needs.</p>
            <h3>Assessment Output</h3>
            <p>The assessment output will be in the form of a report containing score on 16 preferences classified under Sensory, Environmental and Mindset preferences. The report will contain recommendations that will help you improve on various learning styles. The report will also help you learn about new learning styles.</p>
            <h3>Instructions</h3>
            <ol class="pl-3">
                <li>We advise you to complete the assessment in 20 minutes. </li>
                <li>The assessment will consist of 69 questions. Based on the given situation, you will be asked to choose an option on a Likert-type scale. </li>
                <li>At the end of the assessment you will get an interactive report containing actionable tips.</li>
            </ol>
            <div class="row">
                <div class="col-md-3 col-sm-4 mb-3 pr-0"> <?php echo $button; ?></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('body').on('click', '.take-the-test', function(){$.colorbox({href: "<?php echo $this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []) ?>", width: '60%', height: '60%', iframe: true, onClosed: function(){window.location.reload();}});});

    window.addEventListener('message', receiveMessage, false);
    
    function receiveMessage(event) {
        if(event.origin !== '<?php echo $this->testServer; ?>')return;
        if (event.data === Object(event.data) && event.data.hasOwnProperty('completed_at')) {
            $.post("<?php echo $this->createUrl('testCompleted', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []); ?>", event.data,function(data){});
        }
    }
    
    if(window.addEventListener){
        window.addEventListener("message", receiveSize, false);
    }else{
        window.attachEvent("onmessage", receiveSize);
    }
    
    function receiveSize(e)    {
        if ((e.origin === "https://api.staging.humanesources.com" || e.origin === 'https://api.humanesources.com' || e.origin === 'api.keystosucceed.cn') && (!isNaN(e.data))){
            $.fn.colorbox.resize({height: e.data});
        }
    }

</script>