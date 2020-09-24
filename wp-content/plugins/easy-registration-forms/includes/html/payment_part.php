<div class="erf-payment-wrapper">
    
        <?php if(!empty($form['payment_header'])): ?>
        <div class="erf_payment_header"><h5><?php echo $form['payment_header']; ?></h5></div>
        <?php endif; ?>
        <div class="erf-plans erf-plans-required">
            <?php 
                $initial_amount= 0; // To store initial amount for required product(s)
                $enabled_plan_ids= $form['plans']['enabled']; 
                $req_plan_ids= $form['plans']['required'];  
                foreach($req_plan_ids as $index=>$plan_id):
                    if(!in_array($plan_id,$enabled_plan_ids)) // Skip non required plans
                            continue;
                    $plan= erforms()->plan->get_plan($plan_id);
                    if(empty($plan)){
                        continue;
                    }
            ?>        
            <?php if($plan['type']=='user'): ?>
                <div class="fb-checkbox-group form-group form-group-plans">
                    <div class="checkbox erf-donation-field">  
                        <label class="fb-text-label"><?php echo $plan['name']; ?><span class="erf-required">*</span></label>
                        <input data-plan-type="user" type="number" value="" class="form-control erf-price" required placeholder="<?php _e('Amount','erforms'); ?>" name="plan_id_<?php echo $plan['id']; ?>" id="plan_id_<?php echo $plan['id']; ?>" />  
                    </div>
                        <?php if(!empty($plan['description'])): ?>
                                <div class="erf-plan-description"><?php echo $plan['description']; ?></div>
                        <?php endif; ?>

                </div>
            <?php elseif($plan['type']=='product'): ?>
                <div class="fb-radio-group fb-checkbox-group form-group form-group-plans">
                    <div class="radio checkbox">
                        <?php if(!empty($form['allow_single_plan'])): ?>
                        <input class="erf-price" required   data-plan-type="product" data-erf-price="<?php echo esc_attr($plan['price']); ?>" type="radio" value="<?php echo esc_attr($plan['id']); ?>" name="plan_id" id="plan_id_<?php echo $plan['id']; ?>" />
                        <?php else: $initial_amount += $plan['price']; ?>
                        <input class="erf-price" checked required  data-plan-type="product" data-erf-price="<?php echo esc_attr($plan['price']); ?>" type="checkbox" value="<?php echo esc_attr($plan['id']); ?>" name="plan_id_<?php echo $plan['id']; ?>" id="plan_id_<?php echo $plan['id']; ?>"/>
                        <?php endif; ?>
                        <label class="fb-text-label"><?php echo $plan['name']; ?><span class="erf-required">*</span></label>
                    </div>
                    <?php if(!empty($plan['description'])): ?>
                        <div class="erf-plan-description"><?php echo $plan['description']; ?></div>
                    <?php endif; ?>
                </div> 
            <?php endif; ?>    
            <?php  endforeach; ?>
        </div>
        <div class="erf-plans erf-plans-non-required">  
            <?php   foreach($enabled_plan_ids as $index=>$plan_id): 
                        if(in_array($plan_id,$req_plan_ids)) // Skip required plans
                                continue;
                        $plan= erforms()->plan->get_plan($plan_id);
                        if(empty($plan)){
                            continue;
                        }
            ?>

                    <?php if($plan['type']=='user'): ?>
                        <div class="fb-checkbox-group form-group form-group-plans">
                            <div class="checkbox erf-donation-field">   
                                <label class="fb-text-label"><?php echo $plan['name']; ?></label>
                                <input placeholder="<?php _e('Amount','erforms'); ?>" id="plan_id_<?php echo $plan['id']; ?>" name="plan_id_<?php echo $plan['id']; ?>" data-plan-type="user" <?php echo in_array($plan['id'],$req_plan_ids) ? 'required' : ''; ?> type="number" value="" class="form-control erf-price" <?php _e('Amount','erforms'); ?> id="plan_id_<?php echo $plan['id']; ?>"/>
                            </div>    
                            <?php if(!empty($plan['description'])): ?>
                                <div class="erf-plan-description"><?php echo $plan['description']; ?></div>
                            <?php endif; ?>
                        </div>
                    <?php elseif($plan['type']=='product'): ?>
                        <div class="fb-checkbox-group form-group form-group-plans">
                            <div class="checkbox">
                                <label class="fb-text-label"><?php echo $plan['name']; ?></label>
                                <input name="plan_id_<?php echo $plan['id']; ?>" class="erf-price" data-plan-type="product" data-erf-price="<?php echo $plan['price']; ?>" type="checkbox" value="<?php echo $plan['id']; ?>" id="plan_id_<?php echo $plan['id']; ?>" />
                            </div>
                            <?php if(!empty($plan['description'])): ?>
                                <div class="erf-plan-description"><?php echo $plan['description']; ?></div>
                            <?php endif; ?>
                        </div> 
                <?php endif; ?>
            <?php endforeach; ?>  
        </div>
        <div class="erf-price-total">
            <p>
                <span class="erf-total-title"><?php _e('Total','erforms'); ?></span> <?php echo erforms_currency_symbol($this->options['currency'],false); ?><span class="erf-total-payment">
                    <?php echo $initial_amount; ?>
                </span>
            </p>
        </div>

            <div class="erf-payment-methods" style="<?php echo $initial_amount<=0 ? 'display:none' : ''; ?>">  
            <?php if(is_array($this->options['payment_methods'])) : ?>
                <?php foreach($this->options['payment_methods'] as $payment_method): 
                    $payment_method_label= apply_filters('erf_'.$payment_method.'_front_label', strtoupper($payment_method)); 
                    $active= $payment_method=='offline' ? true : apply_filters('erf_'.$payment_method.'_plugin_active',false);
                    if(empty($active))
                        continue;
                ?>
                    <input type="radio" checked name="payment_method" value="<?php echo esc_attr($payment_method); ?>"/> <?php echo $payment_method_label; ?>
                <?php endforeach; ?>
            <?php endif; ?> 
        </div>
</div>  
        
