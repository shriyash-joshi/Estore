<style type="text/css">
    div.well input, div.question-template textarea {background: #ffffff;}
    input[type=checkbox], input[type=radio] {margin: 6px 0 0;}
</style>

<div class="container pt-5">
    <h2 class="text-center mt-5">
        <i class="fa fa-check-circle text-success"></i> Congratulations! Your personalized report is ready to be generated
    </h2>
    
    <div class="mt-5 text-center" style="min-height: 350px;">
        <form method="post" class="horizontal">
            <button type="submit" class="btn btn-success btn-sm mr-3">Generate Report</button>
            
            <a href="<?php echo $this->createUrl('index', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []) ?>" 
               class="btn btn-success btn-sm">
                Cancel
            </a>
        </form>
        <p class="text-warning mt-3">
            Please note you will be able to generate the report only once.
        </p>
        
    </div>
    
</div>