<?php
/* @var string $content */
Yii::app()->clientScript->scriptMap['jquery.js'] = false;
Yii::app()->clientScript->scriptMap['jquery.min.js'] = false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.min.2.2.4.js"></script>
    <meta charset="utf-8">
    <link rel="icon" type="image/x-icon" href="/assessments/favicon.ico">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700,700i,900,900i&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=DM+Serif+Display:400,400i&display=swap&subset=latin-ext" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.min.3.3.7.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/spaces.min.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.min.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/colorbox.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery.datetimepicker.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/font-awesome.min.4.7.0.css"/>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.colorbox-min.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap.min.3.3.7.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.datetimepicker.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.yiiactiveform.modfied.js"></script>
    <style type="text/css">.round-circle {
            background: #f8f8f8;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            margin: 0 auto;
            text-align: center;
            line-height: 240px;
        }</style>
</head>
<body ng-app="uniApp">
<?php if($this->header): ?>
    <header id="header" class="transparent-nav">
        <div class="container">
            <div class="navbar-header">
                <div class="navbar-brand">
                    <a class="logo" href="/">
                        <img src="/assessments/images/logo.png">
                    </a>
                </div>
            </div>
        </div>
    </header>
<?php endif; ?>

<div>
    <?php echo $content; ?>
</div>

<?php if($this->header): ?>
    <footer>
        <div class="copyright">
            <div class="container text-center">
                <p>International Educational Gateway Pvt. Ltd. © <?php echo date('Y'); ?>. All Rights Reserved. Univariety</p>
            </div>
        </div>
    </footer>
<?php endif; ?>
<script type="text/javascript">
    $(function () {
        $("body").on('focus', 'input, select, radio', function () {
            $(this).attr('autocomplete', 'off');
            $('#' + $(this).attr('id') + '_em_').hide('slow');
        });
    });
</script>
</body>
</html>