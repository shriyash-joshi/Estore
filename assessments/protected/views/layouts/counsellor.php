<?php
/**
 * @var string $content
 * @var $this CController
 */

//Yii::app()->clientScript->scriptMap['jquery-ui.min.js'] = false;

/* @var $flash_messages array */
$flash_messages = Yii::app()->user->getFlashes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/x-icon" href="/assessments/favicon.ico">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700,700i,900,900i&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=DM+Serif+Display:400,400i&display=swap&subset=latin-ext" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.min.3.3.7.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/spaces.min.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery.datetimepicker.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/font-awesome.min.4.7.0.css"/>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap.min.3.3.7.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.datetimepicker.js"></script>
    <style type="text/css">
        body {font-family: Lato, sans-serif;font-size: 16px;line-height: 24px;font-weight: 400;color: #414141;overflow-x: hidden}
        .h1, .h2, .h3, .h4, h1, h2, h3, h4, h5, h6 {margin: 10px 0;font-family: 'DM Serif Display', serif}
        .h1, h1 {font-size: 40px}
        .h2, h2 {font-size: 30px}
        .h3, h3 {font-size: 26px;line-height: 1.3}
        .h4, h4 {font-size: 24px}
        .h5,h5{font-size:22px}
        .h6,h6{font-size:18px}
        a:focus,a:hover{text-decoration:none;outline:0;color:#2680eb;opacity:.9}
        .border-all{border:1px solid #e1e1e1}
        .border-radius{border-radius:3px}
        .border-bottom{border-bottom:1px solid #e1e1e1}
        .border-top{border-top:1px solid #e1e1e1}
        .border-right{border-right:1px solid #e1e1e1}
        .navbar{background:#2680eb}
        .navbar-brand{height:auto}
        .navbar-nav>li>a{font-size:14px;font-weight:700;color:#fff;text-transform:uppercase}
        .nav>li>a:focus,.nav>li>a:hover{background:0 0}
        .nav-tabs>li.active>a,.nav-tabs>li.active>a:focus,.nav-tabs>li.active>a:hover{border:0 none}
        .nav-tabs>li>a:hover{border:0 none}
        .nav .open>a, .nav .open>a:focus, .nav .open>a:hover{background: none;}
        ul.pagination{margin:0!important}
        .table-center td, .table-center th{text-align: center;}

        @media only screen and (max-width: 760px) {
            .no-more-tables table, .no-more-tables thead, .no-more-tables tbody, .no-more-tables th, .no-more-tables td, .no-more-tables tr {display: block;}
            /* .no-more-tables thead tr {position: absolute; top: -9999px; left: -9999px;} */
            .no-more-tables thead tr {display:none;}
            .no-more-tables table{border: none;}
            .no-more-tables tr {border: 1px solid #ccc; margin-bottom: 20px;}
            .no-more-tables td {border: none; border-bottom: 1px solid #eee; position: relative; padding-left: 40% !important; white-space: normal; text-align:right;}
            .no-more-tables td:before {position: absolute; top: 4px; left: 4px; padding-right: 10px; white-space: nowrap; text-align:left; font-weight: bold;}
            .no-more-tables td:before { content: attr(data-title); }
        }
    </style>
</head>
<body ng-app="uniApp">

<header>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#counsellor-nav-bar" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fa fa-align-justify icon-bar fa-2x" style="color: #ffffff;"></i>
                </button>
                <a class="navbar-brand" href="<?php echo $this->createUrl('/counsellor/inventory') ?>">
                    <img src="/assessments/images/logo.png" alt=""/>
                </a>
            </div>

            <div class="collapse navbar-collapse" id="counsellor-nav-bar" style="margin-top: 8px;">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="<?php echo $this->createUrl('/counsellor/inventory') ?>">Inventory</a>
                    </li>
                    <li>
                        <a href="<?php echo $this->createUrl('/counsellor/inventory/students') ?>">Student Usage</a>
                    </li>
                    <li>
                        <a href="<?php echo $this->createUrl('/counsellor/inventory/history') ?>">Order History</a>
                    </li>
                    <li>
                        <a href="<?php echo $this->createUrl('/counsellor/tutorials') ?>">Tutorials</a>
                    </li>
                    <?php if((int)Yii::app()->session->get('customer_group_id') == 2): ?>
                        <?php if(Yii::app()->session->get('dashboard_login')): ?>
                         
                        <?php endif; ?>
                    <?php else: ?>
                        <li>
                            <a style="padding: 5px; margin-top: 8px;"
                               class="btn btn-sm btn-success"
                               target="_blank"
                               href="<?php echo $this->createUrl('/counsellor/tutorials/superCounsellor') ?>">
                                Become A Supercounsellor
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-unlock"></i> <?php echo StringUtils::truncate(Yii::app()->session->get('firstname', 'Howdy!'), 12); ?>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="<?php echo $this->createUrl('/counsellor/inventory/logout') ?>">
                                    <i class="fa fa-power-off mr-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

</header>

<div>
    <?php if($flash_messages): ?>
    <div class="container">
        <?php foreach($flash_messages as $alert_class =>$flash_message): ?>
        <div class="alert <?php echo $alert_class; ?>  alert-dismissable" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <p>
                <?php echo $flash_message; ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php echo $content; ?>
</div>

<footer>
    <div class="copyright">
        <div class="container text-center">
            <p>International Educational Gateway Pvt. Ltd. © <?php echo date('Y'); ?>. All Rights Reserved. Univariety</p>
        </div>
    </div>
</footer>

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