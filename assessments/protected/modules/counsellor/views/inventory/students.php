<?php
/**
 * @var $this InventoryController
 * @var $model InventoryModel
 */

$this->pageTitle = 'Student Usage';

?>

<div class="container" style="min-height: 450px;">
    <h4 class="text-center"><?php echo $this->pageTitle; ?></h4>

    <div class="no-more-tables">
        <?php
        $this->widget('zii.widgets.grid.CGridView', [
            'id' => 'inventory-grid',
            'dataProvider' => $model->getStudentSummary(),
            'ajaxUrl' => $this->createurl('students'),
            'filter' => $model,
            'ajaxUpdate' => true,
            'afterAjaxUpdate' => 'js:function(){$(\'.uni-tooltip\').tooltip();}',
            'columns' => [
                ['name' => 'assigned_name',
                    'header' => 'Student Name <i class="fa fa-sort"></i>',
                    'htmlOptions' => ['data-title' => 'Student Name'],
                    'filter' => CHtml::activeTextField($model, 'assigned_name', ['class' => 'form-control grid-filter'])
                ],
                ['name' => 'assigned_email',
                    'header' => 'Student Email <i class="fa fa-sort"></i>',
                    'htmlOptions' => ['data-title' => 'Student Email'],
                    'filter' => CHtml::activeTextField($model, 'assigned_email', ['class' => 'form-control grid-filter'])
                ],
                ['name' => 'product_name',
                    'header' => 'Product Name <i class="fa fa-sort"></i>',
                    'htmlOptions' => ['data-title' => 'Product Name'],
                    'filter' => CHtml::activeDropDownList($model, 'product_id',
                        $model->distinctUsedProducts, [
                            'class' => 'form-control grid-filter', 'empty' => 'Select'
                    ]),
                    'value' => function($row){
                        return implode(' - ', array_unique(array_filter([$row['product_name'], $row['assessment_name']])));
                    }
                ],
                ['header' => 'Status',
                    'htmlOptions' => ['data-title' => 'Status'],
                    'filter' => false,
                    'type' => 'raw',
                    'value' => function($row) {

                        $is_used = Yii::app()->db->createCommand()
                            ->select('order_assessment_id')
                            ->from('unwp_order_assessment oa')
                            ->where('order_product_id = :order_product_id AND group_no = :group_no', [
                                ':order_product_id' => $row['order_product_id'],
                                ':group_no' => $row['group_no']
                            ])
                            ->andWhere('test_user_email IS NOT NULL OR test_user_name IS NOT NULL OR started_on IS NOT NULL')
                            ->queryScalar();

                        if($is_used){
                            // one of the links are used

                            if($row['assessment_name'] == 'Signup'){
                                return ($row['completed_on']) ? 'Completed' : 'In Progress';
                            }

                            if($row['completed_on']){
                                return CHtml::link('Report <i class="fa fa-external-link-square"></i>', $row['test_link'], ['target' => '_blank']);
                            }else{
                                return 'In Progress';
                            }
                        }else{
                            // none of the links are used

                            return CHtml::link('Not Used <i class="fa fa-undo ml-2"></i>', '#', [
                                'class' => 'btn btn-sm btn-default uni-tooltip pull-back',
                                'data-id' => OpenSslEncrypt::getInstance()->encrypt($row['order_assessment_id']),
                                'data-toggle' => 'modal',
                                'data-target' => '#pull-back-modal',
                                'data-product' => $row['product_name'],
                                'title' => 'Pull Back'
                            ]);
                        }
                    }
                ],
                [
                    'header' => 'Counselling',
                    'htmlOptions' => ['data-title' => 'Counselling'],
                    'filter' => false,
                    'type' => 'raw',
                    'value' => function($row) {

                        if($row['assessment_name'] == 'Signup'){
                            return '';
                        }

                        $done = ($row['counselling'] == 1);
                        $is_checked = $done ? 'checked' : '';
                        $label = $done ? '<i class="fa fa-check text-success"></i> Completed' : '<i class="fa fa-times text-danger"></i> Pending';
                        $id = OpenSslEncrypt::getInstance()->encrypt($row['order_assessment_id']);

                        if(!$row['completed_on']){
                            $is_checked .= ' disabled="disabled"';
                        }

                        return sprintf('<span><input class="mr-2 counselling-check" type="checkbox" %s autocomplete="off" value="%s">%s</span>', $is_checked, $id,
                            $label);
                    }
                ],

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

    <div class="modal fade" id="pull-back-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title text-center"></h4>
                </div>
                <div class="modal-body">
                    <p>
                        Are sure you want to pull back?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning pull-back-btn" value="">Pull Back</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    $(function () {
        $('.uni-tooltip').tooltip();

    });

    $('#pull-back-modal').on('show.bs.modal', function (event) {
        $('.pull-back-btn').val($(event.relatedTarget).data('id'));
        $(this).find('h4.modal-title').text($(event.relatedTarget).data('product'));
        $(event.relatedTarget).one('focus', function (e) {
            $(this).blur();
        });
    });

    $('body')
        .on('change', '.counselling-check', function () {
            $(this).parent().find('i').removeClass('fa-check fa-times text-danger text-success').addClass('fa-spin fa-spinner');
            $.post('<?php echo $this->createUrl('counsellingStatus') ?>', {'id': $(this).val()})
                .done(function (data) {
                    $('#inventory-grid').yiiGridView('update');
                });
        })
        .on('click', '.pull-back-btn', function () {
            $.post('<?php echo $this->createUrl('pullBack') ?>', {'id': $(this).val()})
                .done(function (data) {
                    $('#inventory-grid').yiiGridView('update');
                    $('#pull-back-modal').modal('hide');
                });
        });


</script>