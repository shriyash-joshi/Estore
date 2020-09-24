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
                <h1 class="white-text mb-0"> Multiple Intelligences Assessment</h1>
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
               <img src="https://univariety.sgp1.digitaloceanspaces.com/img/multiple-layer.png"/>
            </div>
        </div>
        <div class="col-md-8">
            <h3>About Assessment</h3>
            <p>The assessment is based on the Theory of Multiple Intelligences developed by Dr. Howard Gardner in 1983. The theory has been tested under Project SUMIT and found to improve student performance in 78% of the cases. The assessment uses proprietary technology to match your intelligences to your best fit careers.</p>
            <h3>Assessment Output</h3>
            <p>An interactive report will be displayed to you at the end of the assessment. The report will contain an analysis of your emotional intelligence and nine other intelligences. The report will consist of actionable points that you can follow to improve on your intelligences. The report will also give you careers and pathways based on matches on strongest intelligences.</p>
            <h3>Instructions</h3>
            <ol class="pl-3">
            <li>We advise you to complete the assessment in 20 minutes.</li>
            <li> The assessment will consist of 54 questions. Based on the given situation, you will be asked to choose an option on a 7-point Likert-type scale. </li>
            <li>At the end of the assessment you will get an interactive report containing the list of careers best suited based on your intelligence scores.</li>
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