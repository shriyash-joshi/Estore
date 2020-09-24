<div class="erf-wrapper wrap">
    <div class="erf-page-title">
        <h1 class="wp-heading-inline"><?php _e('Analytics', 'erforms') ?></h1>
    </div>
    <div class="erf_analytics_wrap erforms-admin-content">
        <div class="erf_chart_filters">
            <form id="erf_analytics_form" method="get">
                 <input type="hidden" name="page" value="erforms-analytics" />
                <?php echo erforms()->form->get_posts_dropdown(array('name'=>'form_id','selected'=>$form_id)); ?>

                <select name="period">
                    <option <?php echo $period=='7' ? 'selected' : ''; ?> value="7"><?php _e('Last 7 Days','erforms'); ?></option>
                    <option <?php echo $period=='30' ? 'selected' : ''; ?> value="30"><?php _e('Last 30 Days','erforms'); ?></option>
                    <option <?php echo $period=='60' ? 'selected' : ''; ?> value="60"><?php _e('Last 60 Days','erforms'); ?></option>
                </select>



            </form>    

        </div>
        <div id="erf_analytics_chart_container">
            <?php if(empty($chart_data)) : ?>
                <div class="erf_empty"><?php _e('No Data Available.','erforms'); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php if(!empty($chart_data)): ?>
    <script>
        function erf_draw_form_chart(){
            var data = google.visualization.arrayToDataTable([<?php echo $chart_data;  ?>]);

                var options = {
                  title: 'Submissions',
                  curveType: 'function',
                  legend: { position: 'bottom' }
                };



            // Instantiate and draw our chart, passing in some options.
            var chart = new google.visualization.LineChart(document.getElementById('erf_analytics_chart_container'));
            chart.draw(data,options);

            }

            jQuery(document).ready(function(){
                if(typeof google != 'undefined'){
                    google.charts.load('current', {'packages': ['corechart', 'bar']});
                    if (typeof erf_draw_form_chart == 'function'){
                        google.charts.setOnLoadCallback(erf_draw_form_chart);
                    }
                } 
                
                jQuery('#erf_analytics_form :input').change(function(){
                   jQuery('#erf_analytics_form').submit();
                });
        });
    </script>   
<?php endif; ?> 
    
    <script>
        jQuery(document).ready(function(){
            jQuery('#erf_analytics_form :input').change(function(){
                   jQuery('#erf_analytics_form').submit();
            });
        });
    </script>    