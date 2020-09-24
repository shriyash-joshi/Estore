<?php
/**
 * @var $this TutorialsController
 * @var $form CActiveForm
 * @var $model RequestDemo
 */

$this->pageTitle = 'Become A SuperCounsellor';
?>

<div class="container mb-5">
    <h3 class="text-center mb-5"><?php echo $this->pageTitle; ?></h3>

    <div class="text-center mb-5">
        <iframe width="560" height="315"
                src="https://www.youtube.com/embed/6EB1k2GTeDA" frameborder="0"
                allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
        </iframe>
    </div>

    <?php
    if((int)Yii::app()->session->get('customer_group_id') !== 2 && !Yii::app()->session->get('demo_requested') ){
        include_once('request_demo.php');
    }
    ?>

</div>
