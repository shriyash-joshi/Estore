<?php
/**
 * @var $this InventoryController
 * @var $inventory_summary array
 * @var $model InventoryModel
 * @var $this InventoryController
 * @var $AllowcateForm AllowcateForm
 * @var $form CActiveForm
 */

$this->pageTitle = 'Products Inventory';
$inventory_summary = $model->getInventorySummary();

?>

<div class="container">
    <h4 class="text-center"><?php echo $this->pageTitle; ?></h4>
    <div class="text-center no-more-tables">
        <table class="table table-condensed table-bordered">
            <thead>
            <tr>
                <th class="text-center">Total MRP value of Inventory (un-allocated)</th>
                <th class="text-center">What you have paid (for un-allocated products)</th>
                <th class="text-center">Profit potential</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td data-title="Total unallocated MRP">
                    <?php echo '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $inventory_summary['mrp']); ?>
                </td>
                <td data-title="Total paid unallocated">
                    <?php echo '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $inventory_summary['paid']); ?>
                </td>
                <td data-title="Profit potential">
                    <?php echo '₹' . Yii::app()->numberFormatter->format('#,##,##,###', ($inventory_summary['mrp'] - $inventory_summary['paid'])); ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <h4 class="text-center">Unallocated Inventory</h4>

    <div class="no-more-tables">
        <?php

        $this->widget('zii.widgets.grid.CGridView', [
            'id' => 'inventory-grid',
            'ajaxUrl' => $this->createurl('index'),
            'dataProvider' => $model->getInventoryDetails(),
            'filter' => $model,
            'ajaxUpdate' => true,
            'afterAjaxUpdate' => 'js:function(){$(\'.uni-tooltip\').tooltip();}',
            'columns' => [
                ['name' => 'product_name',
                    'header' => 'Product name <i class="fa fa-sort"></i>',
                    'htmlOptions' => ['data-title' => 'Product name'],
                    'filter' => CHtml::activeDropDownList($model, 'product_id', $model->distinctProducts, ['class' => 'form-control', 'empty' => 'Select'])
                ],
                ['name' => 'qty',
                    'header' => 'Total <i class="fa fa-sort"></i>',
                    'htmlOptions' => ['data-title' => 'Total'],
                    'filter' => false,
                ],
                ['name' => 'unused',
                    'header' => 'Unallocated <i class="fa fa-sort"></i>',
                    'htmlOptions' => ['data-title' => 'Unallocated'],
                    'filter' => false,
                ],
                [
                    'header' => 'MRP value',
                    'htmlOptions' => ['data-title' => 'MRP value'],
                    'filter' => false,
                    'value' => function($row) {

                        $mrp = Yii::app()->db->createCommand("(SELECT SUM(b.totals) FROM(SELECT COUNT(DISTINCT CONCAT_WS('_', oa.order_product_id, oa.group_no))*pm.meta_value AS totals FROM unwp_wc_order_product_lookup opl JOIN unwp_woocommerce_order_itemmeta oim ON oim.order_item_id = opl.order_item_id AND oim.meta_key = 'oc_order_product_id' JOIN unwp_order_assessment oa ON oa.order_product_id = oim.meta_value JOIN unwp_wc_order_stats os ON os.order_id = opl.order_id JOIN unwp_postmeta pm ON pm.post_id = opl.product_id AND pm.meta_key = '_regular_price' JOIN unwp_wc_customer_lookup cl on cl.customer_id = opl.customer_id WHERE cl.user_id = :customer_id AND opl.product_id = :product_id AND os.status = 'wc-completed' AND oa.assigned_name IS NULL GROUP BY pm.meta_value) b)")
                            ->queryScalar([':customer_id' => $row['customer_id'], ':product_id' => $row['product_id']]);

                        Yii::app()->session->add('mrp-inv', $mrp);

                        return '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $mrp);
                    }
                ],
                [
                    'header' => 'Amount paid',
                    'htmlOptions' => ['data-title' => 'Amount Paid'],
                    'filter' => false,
                    'value' => function($row) {

                        $paid = Yii::app()->db->createCommand("(SELECT SUM(b.totals) FROM(SELECT COUNT(DISTINCT CONCAT_WS('_', oa.order_product_id, oa.group_no))*opl.product_gross_revenue AS totals FROM unwp_wc_order_product_lookup opl JOIN unwp_woocommerce_order_itemmeta oim ON oim.order_item_id = opl.order_item_id AND oim.meta_key = 'oc_order_product_id' JOIN unwp_order_assessment oa ON oa.order_product_id = oim.meta_value JOIN unwp_wc_order_stats os ON os.order_id = opl.order_id JOIN unwp_wc_customer_lookup cl on cl.customer_id = opl.customer_id WHERE cl.user_id= :customer_id AND opl.product_id = :product_id AND os.status = 'wc-completed' AND oa.assigned_name IS NULL GROUP BY opl.product_gross_revenue) b)")
                            ->queryScalar([':customer_id' => $row['customer_id'], ':product_id' => $row['product_id']]);
                        Yii::app()->session->add('paid-inv', $paid);

                        return '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $paid);
                    }
                ],
                [
                    'header' => 'Profit potential',
                    'htmlOptions' => ['data-title' => 'Profit potential'],
                    'filter' => false,
                    'value' => function($row) {

                        $mrp = Yii::app()->session->get('mrp-inv', 0);
                        $paid = Yii::app()->session->get('paid-inv', 0);

                        return '₹' . Yii::app()->numberFormatter->format('#,##,##,###', $mrp - $paid);
                    }
                ],

                ['header' => 'Actions',
                    'filter' => false,
                    'htmlOptions' => ['data-title' => 'Actions'],
                    'type' => 'raw',
                    'value' => function($row) {

                        $html = CHtml::link('<i class="fa fa-shopping-cart"></i>',
                            sprintf('https://%s/checkout?add-to-cart=%d', Yii::app()->getRequest()->serverName, $row['product_id']), [
                                'title' => sprintf('Buy %s', $row['product_name']),
                                'target' => '_blank',
                                'class' => 'btn btn-sm btn-default uni-tooltip'
                            ]);

                        if($row['unused'] > 0) {
                            $html .= CHtml::link('Allocate', '#', [
                                'data-toggle' => 'modal',
                                'data-target' => '#allocate-modal',
                                'title' => 'Allocate',
                                'data-id' => OpenSslEncrypt::getInstance()->encrypt($row['product_id']),
                                'class' => 'btn btn-default btn-sm ml-2 uni-tooltip'
                            ]);
                        }

                        if($row['unused'] != $row['qty']) {
                            $html .= CHtml::link('<i class="fa fa-eye"></i>', $this->createUrl('/counsellor/inventory/students', ['InventoryModel[product_id]' =>
                                $row['product_id']]), [
                                'title' => 'View Students',
                                'class' => 'btn btn-sm btn-default uni-tooltip ml-2'
                            ]);
                        }

                        return $html;
                    }
                ]

            ],
            'pagerCssClass' => 'pagination-pager',
            'itemsCssClass' => 'table table-striped table-bordered',
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

    <div class="modal fade" id="allocate-modal" tabindex="-1" role="dialog" aria-labelledby="AllocateModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title text-center" id="AllocateModalLabel">Allocate Product to Student</h4>
                </div>
                <?php
                $form = $this->beginWidget('CActiveForm', [
                    'enableAjaxValidation' => true,
                    'enableClientValidation' => false,
                    'clientOptions' => ['validateOnSubmit' => true, 'validateOnChange' => false, 'validateOnType' => false,
                        'afterValidate' => 'js:function(form, data, hasError){if(!hasError){$("#allocate-modal").modal("hide");window.location.reload();}return false;}'
                    ],

                ]);
                ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">Student Email</label>
                        <?php
                        echo $form->hiddenField($AllowcateForm, 'id');

                        $this->widget('zii.widgets.jui.CJuiAutoComplete', [
                            'model' => $AllowcateForm,
                            'attribute' => "email",
                            'source' => $this->createUrl('findStudents'),
                            'options' => [
                                'minLength' => '2',
                                'select' => "js:function(event, ui) {
                                    $('#AllowcateForm_name').val(ui.item.name);                                    
                                }",
                            ],
                            'htmlOptions' => [
                                'placeholder' => 'Student email',
                                'class' => 'form-control',
                            ],
                        ]);
                        //echo $form->textField($AllowcateForm, 'email', ['class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'Student
                        // email']);
                        echo $form->error($AllowcateForm, 'email', ['class' => 'errorMessage text-danger'])
                        ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Student Name</label>
                        <?php
                        echo $form->textField($AllowcateForm, 'name', ['class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'Student name']);
                        echo $form->error($AllowcateForm, 'name', ['class' => 'errorMessage text-danger'])
                        ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Allocate</button>
                </div>
                <?php $this->endWidget(); ?>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {
            $('.uni-tooltip').tooltip();
        });

        $('#allocate-modal').on('show.bs.modal', function (event) {
            $('#AllowcateForm_email').val('');
            $('#AllowcateForm_name').val('');
            $('#AllowcateForm_id').val($(event.relatedTarget).data('id'));
        });
    </script>

</div>
