<div id="erforms-plan" class="erf-wrapper wrap">
    <div class="erf-page-title">
        <h1 class="wp-heading-inline"><?php _e('Plans', 'erforms'); ?></h1>
        <div class="erf-page-menu">
            <ul class="erf-nav clearfix">
                <li class="erf-nav-item active">
                    <a href="<?php echo admin_url('admin.php?page=erforms-plan'); ?>"><?php _e('Add New', 'erforms'); ?></a>
                </li>
            </ul>
        </div>
    </div>
    
    <?php
    $plan_table = new ERForms_Plan_Table;
    $plan_table->prepare_items();
    ?>

    <div class="erforms-admin-content">

        <form id="erforms-plan-table" method="get" action="<?php echo admin_url('admin.php?page=erforms-plan'); ?>">

            <input type="hidden" name="post_type" value="erforms" />

            <input type="hidden" name="page" value="erforms-plan" />

            <?php $plan_table->views(); ?>
            <?php $plan_table->display(); ?>

        </form>

    </div>

</div>
