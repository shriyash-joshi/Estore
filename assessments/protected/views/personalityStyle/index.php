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
                <h1 class="white-text mb-0"> Personality Type Assessment</h1>
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
               <img src="https://univariety.sgp1.digitaloceanspaces.com/img/personality.png"/>
            </div>
        </div>
        <div class="col-md-8">
            <h3>About Assessment</h3>
            <p>This assessment is based on personality type – the innate way people naturally see the world and make decisions – a set of basic drives and motivations that remain constant throughout a person’s life. The model of personality type is non-judgmental. There are no types that are better or worse, or healthier or more frail. Each type has its own inherent strengths and potential challenges. Personality type does not predict intelligence; rather it identifies important natural predispositions and tendencies.</p>
            <h3>Assessment Output</h3>
            <p>At the end of the assessment you will receive an interactive report with a four-letter personality code and their related strengths, challenges and recommendations. You will also receive a list of careers you are likely to succeed in based on your personality type. You will be able to read through the recommendations and able to mark if the report fits your personality correctly.</p>
            <h3>Instructions</h3>
            <ol class="pl-3">
                <li>We advise you to complete the assessment in 20 minutes.</li>
                <li> The assessment will consist of 36 questions. There is no right or wrong answer. Each question will ask you to choose between different options based on the given situation.</li>
                <li>At the end of the assessment you will get an interactive report containing the list of careers best suited for your personality.</li>
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
