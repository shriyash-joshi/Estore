<div class="erf-password-protected erf-container">
    <form method="POST" class="erf-form">
        <?php if(!empty($form['pwd_res_description'])): ?>
                <div class="erf-description form-group">
                    <h4><?php echo $form['pwd_res_description']; ?></h4>
                </div>
        <?php endif; ?>
        
        
        <div class="erf-question form-group">
            <label><?php echo $form['pwd_res_question']; ?></label>
        </div>

         <div class="erf-answer form-group">
             <input type="text" class="form-control" name="erf_answer" />
             <input type="hidden" name="erform_id" value="<?php echo esc_attr($form['id']); ?>" />
        </div>
        
        <?php if($password_error): ?>
                <div class="erf-error-row erf-error form-group">
                    <?php echo $form['pwd_res_err']; ?>
                </div> 
        <?php endif; ?>
        <div class="erf-submit-button clearfix">   
            <div class="form-group">
                <button type="submit">Submit</button>
            </div>
        </div>
    </form>    
    
</div>