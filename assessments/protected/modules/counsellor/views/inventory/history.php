<?php
/**
 * @var $this InventoryController
 * @var $model InventoryModel
 */

$this->pageTitle = 'Order History';

$all_products = $model->getOrderHistoryAllProducts();
$allowcated_products = $model->getOrderHistoryAllowcatedProducts();

?>

<div class="container" style="min-height: 450px;">
    <h4>Order Summary</h4>
    <div class="row">
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading clearfix">
                    <div class="row">
                        <div class="col-sm-6">
                            All Products <i class="fa ml-2" id="date-all-loader"></i>
                        </div>
                        <div class="col-sm-6">
                            <div class="col-xs-6 p-0 pr-2">
                                <input readonly type="text" style="background: #ffffff;" name="date_from" id="date-all-from" class="form-control"
                                       placeholder="Date From" />
                            </div>
                            <div class="col-xs-6 p-0 pl-2">
                                <input readonly type="text" style="background: #ffffff;"
                                       name="date_from" id="date-all-to" class="form-control" placeholder="Date To"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body table-responsive">
                    <table class="table table-bordered table-condensed table-center">
                        <thead>
                        <tr>
                            <th>Total MRP</th>
                            <th>Cost of buying</th>
                            <th>Potential profit</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <span id="mrp_all">
                                <?php
                                echo '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $all_products['mrp']);
                                ?>
                                </span>
                            </td>
                            <td>
                                <span id="paid_all">
                                <?php
                                echo '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $all_products['paid']);
                                ?>
                                </span>
                            </td>
                            <td>
                                <span id="profit_all">
                                <?php
                                echo '₹' . Yii::app()->numberFormatter->format('#,##,##,###', ($all_products['mrp'] - $all_products['paid']));
                                ?>
                                </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading clearfix">
                    <div class="row">
                        <div class="col-sm-6">
                            Allocated Products <i class="fa ml-2" id="date-allowcated-loader"></i>
                        </div>
                        <div class="col-sm-6">
                            <div class="col-xs-6 p-0 pr-2">
                                <input readonly type="text" name="date_from"
                                       id="date-allowcated-from" class="form-control"
                                       style="background: #ffffff;"
                                       placeholder="Date From" />
                            </div>
                            <div class="col-xs-6 p-0 pl-2">
                                <input readonly type="text" style="background: #ffffff;"
                                       name="date_from" id="date-allowcated-to" class="form-control" placeholder="Date To" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body table-responsive">
                    <table class="table table-bordered table-condensed table-center">
                        <thead>
                        <tr>
                            <th>Total MRP</th>
                            <th>Cost of buying</th>
                            <th>Realized profit</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <span id="allowcated_mrp">
                                <?php
                                echo '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $allowcated_products['mrp']);
                                ?>
                                </span>
                            </td>
                            <td>
                                <span id="allowcated_paid">
                                <?php
                                echo '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $allowcated_products['paid']);
                                ?>
                                </span>
                            </td>
                            <td>
                                <span id="allowcated_profit">
                                <?php
                                echo '₹' . Yii::app()->numberFormatter->format('#,##,##,###', ($allowcated_products['mrp'] - $allowcated_products['paid']));
                                ?>
                                </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <h4>Order History</h4>

    <div class="no-more-tables">
        <?php
        $this->widget('zii.widgets.grid.CGridView', [
            'id' => 'inventory-grid',
            'dataProvider' => $model->getOrderHistory(),
            'filter' => $model,
            'ajaxUpdate' => true,
            'afterAjaxUpdate' => 'js:function(){$(\'.uni-tooltip\').tooltip();}',
            'columns' => [
                ['name' => 'order_id',
                    'header' => 'Order_id <i class="fa fa-sort"></i>',
                    'htmlOptions' => ['data-title' => 'Order Id'],
                    'filter' => CHtml::activeTextField($model, 'order_id', ['class' => 'form-control grid-filter', 'placeholder' => 'Order Id'])
                ],
                ['name' => 'date_added',
                    'header' => 'Date <i class="fa fa-sort"></i>',
                    'htmlOptions' => ['data-title' => 'Date'],
                    'filter' => CHtml::activeTextField($model, 'date_from', [
                            'class' => 'form-control grid-filter grid-filter-date', 'placeholder' => 'Date From',
                            'readonly' => 'readonly',
                            'style' => 'float: left; width: 110px; margin-right: 5px; background: #ffffff;'
                        ]) .
                        CHtml::activeTextField($model, 'date_to', [
                            'class' => 'form-control grid-filter grid-filter-date',
                            'readonly' => 'readonly',
                            'placeholder' => 'Date To', 'style' => 'float: left; width: 110px; background: #ffffff;'
                        ]),
                    'value' => function($row){
                        return date('Y-m-d', strtotime($row['date_added']));
                    }
                ],
                ['name' => 'product_name',
                    'header' => 'Product Name <i class="fa fa-sort"></i>',
                    'htmlOptions' => ['data-title' => 'Product Name'],
                    'filter' => CHtml::activeDropDownList($model, 'product_id',
                        $model->distinctProducts, [
                            'class' => 'form-control grid-filter', 'empty' => 'Select'
                        ])
                ],
                ['name' => 'quantity',
                    'header' => 'Quantity',
                    'htmlOptions' => ['data-title' => 'Quantity', 'align' => 'right'],
                    'filter' => false,
                    'value' => function($row) {
                        return Yii::app()->numberFormatter->format('#,##,##,###', $row['quantity']);
                    }
                ],
                ['name' => 'paid',
                    'header' => 'Amount Paid',
                    'htmlOptions' => ['data-title' => 'Amount Paid', 'align' => 'right'],
                    'filter' => false,
                    'value' => function($row) {
                        return '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $row['paid']);
                    }
                ],
                ['name' => 'mrp',
                    'header' => 'MRP',
                    'htmlOptions' => ['data-title' => 'MRP', 'align' => 'right'],
                    'filter' => false,
                    'value' => function($row) {
                        return '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $row['mrp']);
                    }
                ],
                ['name' => 'discount',
                    'header' => 'Discount',
                    'htmlOptions' => ['data-title' => 'Discount', 'align' => 'right'],
                    'filter' => false,
                    'value' => function($row) {
                        return '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $row['discount']);
                    }
                ],
            ],
            'pagerCssClass' => 'pagination-pager',
            'itemsCssClass' => 'table table-bordered table-striped',
            'pager' => [
                'htmlOptions' => ['class' => 'pagination'],
                'header' => false,
                'nextPageLabel' => '<i class="fa fa-step-forward"></i>',
                'prevPageLabel' => '<i class="fa fa-step-backward"></i>',
                'lastPageLabel' => '<i class="fa fa-fast-forward"></i>',
                'firstPageLabel' => '<i class="fa fa-fast-backward"></i>'
            ]
        ]);
        ?>
    </div>

</div>

<script type="text/javascript">
    $(function () {
        $('.uni-tooltip').tooltip();

        $('#date-all-from, #date-all-to').datetimepicker({
            format:'Y-m-d',
            formatDate: 'Y-m-d',
            maxDate:'<?php echo date('Y-m-d') ?>',
            timepicker:false,
            autoclose:true,
            closeOnDateSelect:true,
            onClose: updateAllProducts
        });

        $('#date-allowcated-from, #date-allowcated-to').datetimepicker({
            format:'Y-m-d',
            formatDate: 'Y-m-d',
            maxDate:'<?php echo date('Y-m-d') ?>',
            timepicker:false,
            autoclose:true,
            closeOnDateSelect:true,
            onClose: updateAllowcatedProducts
        });
    });

    $('body').on('click', '.grid-filter-date', function(){
        $(this).datetimepicker({
            format:'Y-m-d',
            formatDate: 'Y-m-d',
            maxDate:'<?php echo date('Y-m-d') ?>',
            timepicker:false,
            autoclose:true,
            closeOnDateSelect:true
        });
        $(this).datetimepicker('show');
    });

    function updateAllProducts(){
        $('#date-all-loader').addClass('fa-spin fa-spinner');
        $.ajax({
            dataType: "json",
            type: 'post',
            url: '<?php echo $this->createUrl("history"); ?>',
            data: {type:'all', date_from:$('#date-all-from').val(), date_to:$('#date-all-to').val()},
            success: function(json){
                $('#date-all-loader').removeClass('fa-spin fa-spinner');
                $('#mrp_all').text(json.mrp);
                $('#paid_all').html(json.paid);
                $('#profit_all').html(json.profit);
            }
        });
    }

    function updateAllowcatedProducts(){
        $('#date-allowcated-loader').addClass('fa-spin fa-spinner');
        $.ajax({
            dataType: "json",
            type: 'post',
            url: '<?php echo $this->createUrl("history"); ?>',
            data: {type:'allowcated', date_from:$('#date-allowcated-from').val(), date_to:$('#date-allowcated-to').val()},
            success: function(json){
                $('#date-allowcated-loader').removeClass('fa-spin fa-spinner');
                $('#allowcated_mrp').text(json.mrp);
                $('#allowcated_paid').html(json.paid);
                $('#allowcated_profit').html(json.profit);
            }
        });
    }

</script>