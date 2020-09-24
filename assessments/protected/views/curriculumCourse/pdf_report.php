<?php
/* @var bool $cbse_override */
/* @var Survey $Survey */
/* @var array $aspirations */
/* @var array $boards */
/* @var int $GradeIndex */
/* @var array $board_parameters */

?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Curriculum Report </title>
    <meta name="description" content="Curriculum">
    <link href="https://fonts.googleapis.com/css?family=Anton&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/assessments/favicon.ico">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/chart.js"></script>
    <style type="text/css">
        * {
            box-sizing: border-box;
        }

        body {
            font-family: arial, sans-serif;
            background-color: #525659;
            font-size: 1rem;
            color: #000;
        }

        hr {
            margin-top: 20px;
            margin-bottom: 20px;
            border: 0;
            border-top: 3px solid #000;
        }

        h3 {
            font-size: 2rem;
            margin: 0.6rem 0;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        .mt-0 {
            margin-top: 0 !important;
        }

        .footer-cover-page {
            padding: 10% 27% 0 10%;
            background: #125aaf;
            background: -moz-linear-gradient(-45deg, #125aaf 0%, #125aae 51%, #109fda 100%);
            background: -webkit-linear-gradient(-45deg, #125aaf 0%, #125aae 51%, #109fda 100%);
            background: linear-gradient(135deg, #125aaf 0%, #125aae 51%, #109fda 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#125aaf', endColorstr='#109fda', GradientType=1);
            height: 100%;
            position: relative;
        }

        .footer-cover-page .content {
            font-size: 26px;
            line-height: 45px;
            color: #fff;
        }

        .footer-cover-page .content h4 {
            font-weight: normal;
            margin: 5px 0;
        }

        .footer-cover-page .content a {
            color: #fff;
            text-decoration: none;
        }

        .footer-cover-page .content .border {
            display: block;
            width: 100px;
            border-top: 7px solid #fff;
            margin-top: 50px;
            padding-bottom: 40px;
        }

        .footer-cover-page .content-footer {
            position: absolute;
            bottom: 16px;
            left: 0;
            right: 0;
            text-align: center;
        }

        .cover-page {
            background-image: url(<?php echo Yii::app()->request->baseUrl; ?>/images/ce/Cover-01.png);
            background-size: cover;
            height: 100%;
        }

        .cover-page .logo {
            height: 80px;
            padding-top: 48px;
            padding-right: 20px;
        }

        .cover-page .logo img {
            float: right;

        }

        .cover-page .title {

            padding: 253px 20px 19px 74px;
        }

        .personal-info {
            background-color: #fff;
            padding: 20px 3%;
            margin: 0 33% 0 11%;
            border-radius: 27px;
            font-size: 24px;
        }

        .personal-info span {
            width: 25%;
            display: inline-block;
            font-weight: bold;
        }

        .personal-info strong {
            width: 74%;
            display: inline-block;
        }

        .content-page {
            background-image: url(<?php echo Yii::app()->request->baseUrl; ?>/images/ce/1-01.png);
            background-size: cover;
            background-repeat: no-repeat;
            background-position: left top;
            height: 100%;
            position: relative;
        }

        .content-page .content-header {
            padding: 4% 8% 0;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center top;
        }

        .content-header .head-top {
            height: 70px;
            overflow: hidden;
        }

        .content-header .left {
            float: left;
        }

        .content-header .right {
            float: right;
        }

        .content-header .right img {
            width: 150px;
        }

        .content-page h3 {
            font-size: 20px;
            margin-bottom: 25px;
        }

        .mr-4 {
            margin-right: 20px;
        }

        .content-page h4 {
            font-family: Anton;
            /*background: url(*/
        <?php //echo Yii::app()->request->baseUrl; ?> /*/images/ce/border-line.png) no-repeat;*/
            background-position: left bottom;
            font-size: 35px;
            /*line-height: 70px;*/
            /*background-size: 101% 17%;*/
            display: inline-block;
            margin: 0;
            letter-spacing: 4px;
            margin-bottom: 20px;

        }

        .content-page .contents {
            font-size: 19px;
            line-height: 35px;
            padding: 0 0 0 6%;

        }

        .content-page .contents-row i {
            font-size: 14px;
            font-weight: bold;
        }

        .content-page .contents-row {
            margin-bottom: 1.5rem;
            line-height: 35px;
            clear: both;
        }

        .content-page .contents-row a {
            color: #000;
            text-decoration: none;
        }

        .content-page .contents-row p {
            margin: 0.5rem 0;
        }

        .contents-row.sub-heading {
            padding: 0 0 0 20px;
            border-left: 5px solid #F39111;
            font-size: 20px;
            font-weight: bold;
            margin-top: 10px;
        }

        .sub-heading .span-1 {
            display: inline-block;

        }

        .sub-heading .span-2 {
            display: inline-block;
            top: 2px;
            position: relative;
            left: 10px;
        }

        .sub-heading .span-2 img {
            width: 35px;
        }

        .contents-row ul {
            list-style: none;
            padding-left: 0px;
            margin: 0px;
        }

        .contents-row ul li {
            background: url(<?php echo Yii::app()->request->baseUrl; ?>/images/ce/tick.png) no-repeat left 11px;
            padding-left: 30px;

        }

        .float-right {
            float: right;
        }

        .university-select {
            position: relative;
            width: 588px;
        }

        .university-select img {

            padding: 10px;
            margin: 0 auto;
            display: block;
        }

        .university-select .selection-1 {
            background: #0062AE;
            position: absolute;
            top: 30px;
            left: 32px;
            font-size: 20px;
            color: #fff;
            padding: 30px 52px;

            border-radius: 55%;
            min-height: 80px;
            vertical-align: middle;
            min-width: 118px;
        }

        .university-select .selection-2 {
            background: #2BB1AA;
            position: absolute;
            top: 38px;
            right: 0px;
            font-size: 20px;
            color: #fff;
            padding: 30px 52px;
            border-radius: 57%;
            min-height: 80px;
            vertical-align: middle;
            text-align: center;
            min-width: 118px;

        }

        .column-50 div {
            text-align: center;
            margin-bottom: 10px;
        }

        .column-50 div:last-child {
            margin-bottom: 0px;
        }

        .column-50 h3 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        .column-50 div p {
            text-align: center;
            margin: 0px !important;
            display: inline-block;
        }

        .call-list-left {
            background: url(<?php echo Yii::app()->request->baseUrl; ?>/images/ce/orange-strip.png) no-repeat;
            background-size: 100% 100%;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            text-align: center;

        }

        .call-list-right {
            background: url(<?php echo Yii::app()->request->baseUrl; ?>/images/ce/blue-strip.png) no-repeat;
            background-size: 100% 100%;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            text-align: center;
        }

        .sub-title {
            font-size: 20px;
            font-weight: bold;
            background: url(<?php echo Yii::app()->request->baseUrl; ?>/images/ce/tick.png) no-repeat left center;
            margin: 0;
            padding: 0 0 0 33px;
        }

        .pl-3 {
            padding-left: 33px;
        }

        .content-page .content-footer {
            position: absolute;
            bottom: 16px;
            left: 0;
            right: 0;

        }

        .content-page .content-footer .left {
            float: left;
            padding: 22px;
            font-weight: bold;
            color: #333;
        }

        .content-page .content-footer .right {
            float: right;
            padding: 0 3% 0 0;
        }

        .content-page .content-footer span {
            position: absolute;
            top: -52px;
            left: 52px;
            background: #109FDA;
            font-size: 1.3rem;
            border-radius: 50%;
            color: #fff;
            width: 40px;
            height: 40px;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            line-height: 41px;
        }

        .content-page .content-footer .right a {
            color: #333;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .content-page .column-50 {
            width: 50%;
            float: left;

        }

        .content-page ul.list-inline {
            padding: 0;
        }

        .content-page ul.list-inline li {
            display: inline-block;
            padding-right: 10px;
            padding-left: 10px;
            border-right: 1px solid #fff;
        }

        .graph {
            background: url(<?php echo Yii::app()->request->baseUrl; ?>/images/ce/graph-line.png) no-repeat;
            min-height: 362px;
            background-size: 100% 100%;
            position: relative;
            left: -70px;
            padding: 0 0 0 105px;
        }

        .column-25 {
            width: 25%;
            float: left;
        }

        .column-33 {
            width: 33.3%;
            float: left;

        }

        .graph .column-25 {
            width: 25%;
            float: left;
            text-align: center;
            min-height: 360px;
            position: relative;
        }

        .content-text .column-25 {
            width: 25%;
            float: left;
            text-align: center;
        }

        .graph img {
            margin: 0 auto;
            display: table;
            position: absolute;
            vertical-align: bottom;
            bottom: 0;
            left: 10%;
        }

        .graph h3 {
            position: absolute;
            bottom: -74px;
            left: 44%;
            text-transform: uppercase;
        }

        .pictore {
            min-height: 306px;
        }

        .pictore .column-33 {
            width: 33.3%;
            float: left;
            text-align: center;
            min-height: 360px;
            position: relative;
        }

        .pictore .column-33 img {
            margin: 0 auto;
            display: table;
            position: absolute;
            vertical-align: bottom;
            bottom: 0;
            left: 27%;
        }

        .pictore-text .column-33 h3 {
            text-transform: uppercase;
        }

        .text-center {
            text-align: center;
        }

        .ib-banner {
            background: url(<?php echo Yii::app()->request->baseUrl; ?>/images/ce/IB-banner.png) no-repeat 87px bottom;
            height: 93%;
            background-size: contain;
            background-position-x: 5px;
            background-position-y: 100%;
            width: 87%;
            margin: 0 auto;
        }

        .cambridge-banner {
            background: url(<?php echo Yii::app()->request->baseUrl; ?>/images/ce/cam-banner.png) no-repeat 87px bottom;
            height: 93%;
            background-size: contain;
            background-position-x: 5px;
            background-position-y: 100%;
            width: 87%;
            margin: 0 auto;
        }

        .cbse-banner {
            background: url(<?php echo Yii::app()->request->baseUrl; ?>/images/ce/cbse_banner.png) no-repeat 87px bottom;
            height: 93%;
            background-size: contain;
            background-position-x: 5px;
            background-position-y: 100%;
            width: 87%;
            margin: 0 auto;
        }

        .banner h4 {
            font-size: 30px;
        }

        .container {
            width: 32cm !important;
            height: 40.42cm !important;
            display: block !important;
            margin: 0 auto !important;
            margin-bottom: 1cm !important;

            position: relative;
            background-color: #ffffff;
        }

        .table {
            border: 1px solid #F9F9F9;
            background: #fff;
        }

        .table thead tr th {
            border: 0 none;
        }

        .table thead tr th, .table tbody tr td {
            padding: 6px 8px;
            line-height: 20px;
        }

        .table thead {
            background: #0125aa;
            background: -moz-linear-gradient(-45deg, #0125aa 0%, #0125aa 51%, #109fda 100%);
            background: -webkit-gradient(left top, right bottom, color-stop(0%, #0125aa), color-stop(51%, #0125aa), color-stop(100%, #109fda));
            background: -webkit-linear-gradient(-45deg, #0125aa 0%, #0125aa 51%, #109fda 100%);
            background: -o-linear-gradient(-45deg, #0125aa 0%, #0125aa 51%, #109fda 100%);
            background: -ms-linear-gradient(-45deg, #0125aa 0%, #0125aa 51%, #109fda 100%);
            background: linear-gradient(135deg, #0125aa 0%, #0125aa 51%, #109fda 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#0125aa', endColorstr='#109fda', GradientType=1);
            color: #fff;
        }

        .content-table {
            padding-right: 10%;
            line-height: 20px;
        }

        .table tbody tr td:nth-child(even) {
            background: #F9F9F9;
        }

    </style>

    <script type="text/javascript">
        Chart.pluginService.register({
            beforeRender: function (chart) {
                if (chart.config.options.showAllTooltips) {
                    // create an array of tooltips
                    // we can't use the chart tooltip because there is only one tooltip per chart
                    chart.pluginTooltips = [];
                    chart.config.data.datasets.forEach(function (dataset, i) {
                        chart.getDatasetMeta(i).data.forEach(function (sector, j) {
                            chart.pluginTooltips.push(new Chart.Tooltip({
                                _chart: chart.chart,
                                _chartInstance: chart,
                                _data: chart.data,
                                _options: chart.options.tooltips,
                                _active: [sector]
                            }, chart));
                        });
                    });

                    // turn off normal tooltips
                    chart.options.tooltips.enabled = false;
                }
            },
            afterDraw: function (chart, easing) {
                if (chart.config.options.showAllTooltips) {
                    // we don't want the permanent tooltips to animate, so don't do anything till the animation runs atleast once
                    if (!chart.allTooltipsOnce) {
                        if (easing !== 1)
                            return;
                        chart.allTooltipsOnce = true;
                    }

                    // turn on tooltips
                    chart.options.tooltips.enabled = true;
                    Chart.helpers.each(chart.pluginTooltips, function (tooltip) {
                        tooltip.initialize();
                        tooltip.update();
                        // we don't actually need this since we are not animating tooltips
                        tooltip.pivot();
                        tooltip.transition(easing).draw();
                    });
                    chart.options.tooltips.enabled = false;
                }
            }
        })
    </script>

</head>
<body>

<div class="container">
    <div class="cover-page">
        <div class="logo">
            <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/coverpage-logo.png" alt="logo"/>
        </div>
        <div class="title">
            <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/header.png" alt=""/>
        </div>

        <div class="personal-info">
            <?php
            $sutdent_name = ucwords(SurveyResponse::getCurriculumStudentName($Survey->id));
            $student_dob = SurveyResponse::getCurriculumStudentDob($Survey->id);
            $student_board = SurveyResponse::getCurriculumStudentCurriculum($Survey->id);

            $date_diff = date_diff(date_create($student_dob), date_create(date("Y-m-d")));
            $student_age = $date_diff->format('%y');

            $board_alias = ['ib' => 'IB', 'cambridge' => 'Cambridge International', 'icse' => 'CISCE', 'cbse' => 'CBSE'];
            $page = 0;
            ?>
            <p>
                <span>Name </span><strong>: <?php echo $sutdent_name; ?></strong>
            </p>
            <p>
                <?php
                $grade = SurveyResponse::getCurriculumStudentGrade($Survey->id);
                $grade_label = ($grade == 'Below Grade 1') ? $grade : preg_replace('/[^0-9]/', '', $grade);
                ?>

                <span>Grade</span><strong>: <?php echo $grade_label; ?></strong>
            </p>
            <p>
                <span>School</span><strong>: <?php echo SurveyResponse::getCurriculumStudentSchoolName($Survey->id); ?></strong>
            </p>
            <p><span>Curriculum</span><strong>: <?php echo $student_board; ?></strong></p>
            <p><span>City </span><strong>: <?php echo SurveyResponse::getCurriculumStudentCity($Survey->id); ?></strong>
            </p>

        </div>
    </div>
</div>

<div class="container">
    <div class="content-page">
        <div class="content-header">

            <?php include('pdf_header.php'); ?>

            <div class="contents">
                <h4>CURRICULUM SELECTION</h4>
                <div class="contents-row">
                    <i>
                        Congratulations on choosing this program to understand various curricula available in India and also equip yourself with information to
                        make the right choices for your child.
                    </i>
                </div>
                <div class="contents-row">
                    Hours of research by our team of educators, counsellors, industry professionals and real-time feedback from parents all over India have gone
                    into the creation of this first-of-its-kind program by Univariety - The Curriculum Evaluator.
                </div>
                <div class="contents-row">
                    Out of the several decisions you make every day, carving a bright future for your child is probably the most important one. That begins with
                    choosing the perfect school for your child. Picking a curriculum is an important aspect of deciding the right school. More often than not,
                    the school and curriculum choices are made early and the child follows suit. Several preschools are now attached to certain curricula so
                    that the children can make an easy transition to school.
                </div>
                <div class="contents-row">
                    Having an all-round experience at school through various pedagogical approaches plays an important role in their future career decisions.
                    So, choosing the right curriculum at an early stage becomes all the more crucial. We say this because changing the curriculum at a later
                    stage involves adjustment to new pedagogical methods, environments, people and the transition may not always be smooth. Therefore, by
                    deciding wisely and early, parents can make this shift easily without causing any disruption in their child’s life.
                </div>
                <div class="content-row">
                    <img width="400" src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/0-01.png" alt=""
                         align="right"/>
                    <h5>The methodology used to determine the right fit curricula:</h5>
                    <p>
                        Choosing a curriculum is based on various factors such as the parent and child's aspirations, finances, lifestyle habits, outlook,
                        peer-group of your children, family set-up and location, among others. Putting together each of these factors, we have come up with four
                        main attributes which are crucial in understanding your preferences while choosing a curriculum for your child. </p>
                    <ol>
                        <li>
                            Willingness to Spend
                        </li>
                        <li>
                            Future Perspectives
                        </li>
                        <li>
                            Self-Discovery
                        </li>
                        <li>
                            Parental Involvement
                        </li>
                    </ol>
                    <p>
                        Your responses to the Deep Fitment Analysis Survey help us a great deal in understanding your expectations from a curriculum. Our
                        algorithm then recommends the best curriculum according to your inputs and the curricula framework.
                    </p>
                </div>

            </div>

            <?php include('pdf_footer.php'); ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="content-page">
        <div class="content-header">
            <?php include('pdf_header.php') ?>
            <div class="contents">
                <div class="contents-row">
                    <p><strong>DEEP FITMENT ANALYSIS</strong></p>
                    <h4 class="blue-bg-title">OUR RECOMMENDATION</h4>
                    <p>
                        With plethora of options at hand, we understand that today's parents are skeptical when it comes to choosing a curriculum for their
                        child. We at Univariety, strive hard to research, update our knowledge base and build products that are not only useful for parents like
                        you but also enable them with valuable information that helps them make informed decisions.
                    </p>
                    <p>
                        Our recommendation to you is based on highly reliable research data which is designed scientifically using qualitative inputs and
                        analytical methods.
                    </p>
                    <div class="float-right university-select">
                        <span class="selection-1"><?php echo $board_alias[$boards[0]] ?></span>
                        <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/0-03.png" alt="" width=""/>
                        <span class="selection-2"><?php echo $board_alias[$boards[1]] ?></span>
                    </div>
                    <p>
                        Based on our evaluation criteria, your responses show fitment for -
                        <strong><?php echo $board_alias[$boards[0]] ?></strong>
                        and <strong><?php echo $board_alias[$boards[1]] ?></strong> curricula.
                    </p>
                    <?php if($cbse_override): ?>
                        <p>
                            Based on your responses, we see that you aspire to pursue professional courses in India. Although your preference pattern indicates
                            a different curriculum, your aspiration warrants a national curriculum such as CBSE which provides the best preparation for
                            professional courses in India.
                        </p>
                    <?php endif; ?>

                </div>

                <div class="contents-row">
                    <div class="graph">
                        <div class="column-25">
                            <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/high.png" alt=""/>
                        </div>
                        <div class="column-25">
                            <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/high.png" alt=""/>
                        </div>
                        <div class="column-25">
                            <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/medium.png" alt=""/>
                        </div>
                        <div class="column-25">
                            <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/low.png" alt=""/>
                        </div>
                    </div>

                    <div class="content-text">
                        <div class="column-25">
                            <h3><?php echo $board_alias[$boards[0]] ?></h3>

                        </div>
                        <div class="column-25">
                            <h3><?php echo $board_alias[$boards[1]] ?></h3>

                        </div>
                        <div class="column-25">
                            <h3><?php echo $board_alias[$boards[2]] ?></h3>

                        </div>
                        <div class="column-25">
                            <h3><?php echo $board_alias[$boards[3]] ?></h3>

                        </div>
                    </div>

                    <div style="clear: both;">
                        <h2 class="text-center">CURRICULUM</h2>
                    </div>
                </div>
            </div>
            <?php include('pdf_footer.php') ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="content-page">
        <div class="content-header">
            <?php include('pdf_header.php'); ?>
            <div class="contents">
                <?php
                $ws = [$boards[0] => $board_parameters[$boards[0]]['ws'], $boards[1] => $board_parameters[$boards[1]]['ws'],];

                arsort($ws);
                $ws_labels = array_keys($ws);

                $ws_text = [
                    'cbse' => [
                        'label' => 'CENTRAL BOARD OF SECONDARY EDUCATION',
                        'text' => 'CBSE is a curriculum offered by a lot of top schools in India. CBSE has a wide network of schools across India which makes it easily accessible and provides varied options to choose from different schools in your city or otherwise.Being a national curriculum overhead expenses  are reduced to a greater extent and therefore proves to be cost-effective. Our fitment analysis shows a closer match to CBSE curriculum on this parameter.',
                        'text_alt' => 'We recommend CBSE because you aspire to pursue professional courses for your child in India as CBSE supports good preparation for entrance exams.'
                    ],
                    'icse' => [
                        'label' => 'CISCE: THE COUNCIL FOR THE INDIAN SCHOOL CERTIFICATE EXAMINATIONS',
                        'text' => 'CISCE board is selective on the kind of schools that can offer the curriculum, hence there are comparatively fewer schools across the country. It is cost-effective when compared to other International curricula and encourages experiential learning through project works for all subjects. Your score through the fitment analysis closely matches with CISCE curriculum on this parameter.',
                    ],
                    'cambridge' => [
                        'label' => 'CAMBRIDGE INTERNATIONAL',
                        'text' => 'CAIE is a well established International Organisation and offers a flexible curriculum. Teachers act as facilitators and are highly skilled to plan activities for active learning and therefore teacher training is a mandatory process across all the schools that are affiliated to CAIE. This raises the costs of attending these schools. We believe this curriculum is a perfect fit for your profile considering your willingness to spend in your child\'s education.',
                    ],
                    'ib' => [
                        'label' => 'INTERNATIONAL BACCALAUREATE',
                        'text' => 'Renowned for its quality education, IB not only focuses on the academic development of the child but also emphasizes on different skill-building activities. The board ensures quality on all parameters through its regular audits across the globe. Hence, there are various charges such as affiliation fees, exam fees, teacher training fees that contribute to the high tuition fees of these schools. Your profile shows an inclination towards a willingness to invest in your child’s education for a brighter future, therefore, IB is a close match.'
                    ],
                ];

                ?>

                <div class="contents-row">
                    <p><strong>DEEP FITMENT ANALYSIS</strong></p>
                    <h4>WILLINGNESS TO SPEND</h4>
                    <p>
                        This parameter measures, not just the ability to spend but the willingness to invest in your child's future. The motivation for a parent
                        to spend might vary from meeting basic education needs as a child’s success may not be purely dependent on financial factors to treating
                        education as the best investment for your child’s future success.
                    </p>

                </div>

                <div class="contents-row">
                    <h3 class="text-center">YOUR FITMENT</h3>
                    <div class="row">
                        <div style="width: 800px;">
                            <canvas id="chart-ws" height="100" width="300"></canvas>
                        </div>
                    </div>
                </div>
                <br/>
                <br/>

                <div class="contents-row">
                    <h3>
                        <?php echo $ws_text[$ws_labels[0]]['label']; ?>
                    </h3>
                    <p>
                        <?php
                        echo ($cbse_override && isset($ws_text[$ws_labels[0]]['text_alt'])) ? $ws_text[$ws_labels[0]]['text_alt'] : $ws_text[$ws_labels[0]]['text'];
                        ?>
                    </p>
                </div>

                <div class="contents-row">
                    <h3><?php echo $ws_text[$ws_labels[1]]['label']; ?></h3>
                    <p>
                        <?php
                        echo ($cbse_override && isset($ws_text[$ws_labels[1]]['text_alt'])) ? $ws_text[$ws_labels[1]]['text_alt'] : $ws_text[$ws_labels[1]]['text'];
                        ?>
                    </p>
                </div>

                <script>
                    new Chart(document.getElementById('chart-ws'), {
                        type: 'horizontalBar',
                        data: {
                            labels: ['<?php echo $board_alias[$ws_labels[0]] ?>', '<?php echo $board_alias[$ws_labels[1]] ?>'],
                            datasets: [{
                                data: <?php echo json_encode(array_values($ws)); ?>,
                                backgroundColor: "#0062AE", borderColor: "#0062AE", borderWidth: 1
                            }]
                        },
                        options: {
                            showAllTooltips: true,
                            legend: {display: false},
                            scales: {xAxes: [{ticks: {beginAtZero: true}}]},
                            tooltips: {
                                callbacks: {
                                    title: function(tooltipItem) { return tooltipItem.yLabel;}
                                }
                            },
                        }
                    });
                </script>


            </div>
            <?php include('pdf_footer.php') ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="content-page">
        <div class="content-header">
            <?php include('pdf_header.php'); ?>
            <div class="contents">
                <?php
                $fm = [$boards[0] => $board_parameters[$boards[0]]['fm'], $boards[1] => $board_parameters[$boards[1]]['fm'],];

                arsort($fm);
                $fm_labels = array_keys($fm);

                $fm_text = [
                    'cbse' => [
                        'label' => 'CENTRAL BOARD OF SECONDARY EDUCATION',
                        'text' => 'The CBSE curriculum is more focussed on preparing students for academic success and entrance exams. This curriculum is ideally suited for students with career aspirations in India and also for students wanting to pursue professional courses. Your aspirations for your child on this attribute match closely with the CBSE curriculum.',
                        'text_alt' => 'We recommend CBSE because you aspire to pursue professional courses for your child in India. The CBSE curriculum is more focussed on preparing students for academic success and entrance exams.  Top notch CBSE schools believe that 21st century skills are essential and prepare the students with those critical skills and enrolling in such schools is a good match for you.'
                    ],
                    'icse' => [
                        'label' => 'CISCE: THE COUNCIL FOR THE INDIAN SCHOOL CERTIFICATE EXAMINATIONS',
                        'text' => 'CISCE is designed in a way that it enables students to have a well-rounded personality. It helps your child to acquire the necessary skill set making him/her adept at handling new and challenging situations. It also encourages critical and logical thinking.Therefore, your requirement for your child to be future-ready is achieved by selecting this curriculum.',
                    ],
                    'cambridge' => [
                        'label' => 'CAMBRIDGE INTERNATIONAL',
                        'text' => 'Cambridge curriculum encourages an active learning environment that prepares children for emerging careers prospects. Future careers would require a multi-faceted understanding of concepts while also being innovative and open-minded. Analysing your perspectives on the future of your child’s education, we recommend the Cambridge curriculum as the perfect fit.',
                    ],
                    'ib' => [
                        'label' => 'INTERNATIONAL BACCALAUREATE',
                        'text' => 'IB curriculum fosters an environment that encourages critical thinking, problem solving and decision making. It makes children resourceful and resilient in the face of challenges and change. Your fitment is highest for the International curriculum (such as IB) since your orientation towards these qualities to be inculcated in your child is on the higher side.'
                    ],
                ];

                ?>

                <div class="contents-row">
                    <p><strong>DEEP FITMENT ANALYSIS</strong></p>
                    <h4>FUTURE PERSPECTIVES</h4>
                    <p>
                        While one may want their kid to follow the tested and tried formulae of education, another might want to opt for skills that might
                        emerge as future career options. While choosing a curriculum, it is important we understand your aspirations as a parent and how you
                        envision a future through education for your child.
                    </p>
                </div>

                <div class="contents-row">
                    <h3 class="text-center">YOUR FITMENT</h3>
                    <div class="row">
                        <div style="width: 800px;">
                            <canvas id="chart-fm" height="100" width="300"></canvas>
                        </div>
                    </div>
                </div>

                <br/>
                <br/>
                <div class="contents-row">
                    <h3><?php echo $fm_text[$fm_labels[0]]['label']; ?></h3>
                    <p>
                        <?php
                        echo ($cbse_override && isset($fm_text[$fm_labels[0]]['text_alt'])) ? $fm_text[$fm_labels[0]]['text_alt'] : $fm_text[$fm_labels[0]]['text'];
                        ?>
                    </p>
                </div>

                <div class="contents-row">
                    <h3><?php echo $fm_text[$fm_labels[1]]['label']; ?></h3>
                    <p>
                        <?php
                        echo ($cbse_override && isset($fm_text[$fm_labels[1]]['text_alt'])) ? $fm_text[$fm_labels[1]]['text_alt'] : $fm_text[$fm_labels[1]]['text'];
                        ?>
                    </p>
                </div>

                <script>
                    new Chart(document.getElementById('chart-fm'), {
                        type: 'horizontalBar',
                        data: {
                            labels: ['<?php echo $board_alias[$fm_labels[0]] ?>', '<?php echo $board_alias[$fm_labels[1]] ?>'],
                            datasets: [{
                                data: <?php echo json_encode(array_values($fm)); ?>,
                                backgroundColor: "#E5942B", borderColor: "#E5942B", borderWidth: 1
                            }]
                        },
                        options: {
                            showAllTooltips: true,
                            legend: {display: false},
                            scales: {xAxes: [{ticks: {beginAtZero: true}}]},
                            tooltips: {
                                callbacks: {
                                    title: function(tooltipItem) { return tooltipItem.yLabel;}
                                }
                            },
                        }
                    });
                </script>


            </div>
            <?php include('pdf_footer.php') ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="content-page">
        <div class="content-header">
            <?php include('pdf_header.php'); ?>
            <div class="contents">
                <?php
                $sd = [$boards[0] => $board_parameters[$boards[0]]['sd'], $boards[1] => $board_parameters[$boards[1]]['sd'],];

                arsort($sd);
                $sd_labels = array_keys($sd);

                $sd_text = [
                    'cbse' => [
                        'label' => 'CENTRAL BOARD OF SECONDARY EDUCATION',
                        'text' => 'As the national curriculum is evolving every year, new approaches and methods are being adopted to encourage independent learning, which is complemented through the structured syllabus that the curriculum adopts. The Secondary Curriculum is learner-centered where the students are encouraged to build self-concept, a sense of enterprise and also aesthetic sensibilities. Your aspirations for your child to develop independent thinking matches closely with the objectives of the framework of the CBSE curriculum.',
                        'text_alt' => 'We recommend CBSE because you aspire to pursue professional courses for your child in India. This curriculum is evolving every year, new approaches and methods are being adopted to encourage independent learning, which is complemented through the structured syllabus that the curriculum adopts. The overall development of a child is a priority in most of the top notch CBSE schools and therefore taking this curriculum from a good CBSE school is apt for you.'
                    ],
                    'icse' => [
                        'label' => 'CISCE: THE COUNCIL FOR THE INDIAN SCHOOL CERTIFICATE EXAMINATIONS',
                        'text' => 'CISCE curriculum fosters an environment that allows the students to think deeply about issues that impact life and society, while also learning to solve problems creatively. Through providing activity-based projects, the curriculum empowers the students with the attitudes for teamwork and also encourages them to develop individual ideas. We understand from the assessment of your profile that your required learning outcomes are inclined towards building those abilities in your child.',
                    ],
                    'cambridge' => [
                        'label' => 'CAMBRIDGE INTERNATIONAL',
                        'text' => 'Cambridge curriculum is child-centric and thus is about active learning that enables the child to become independent and responsible global citizen. This essentially is one of your core aspirations to build these abilities in your child. Your score on this attitude is on the higher side and therefore we recommend an International Curriculum such as Cambridge International.',
                    ],
                    'ib' => [
                        'label' => 'INTERNATIONAL BACCALAUREATE',
                        'text' => 'IB curriculum focuses on encouraging the child to explore new ideas and be innovative. The students become critical thinkers, risk-takers, great communicators and open-minded individuals. They cultivate habits of curiosity as they are encouraged to ask questions. The curriculum is focused on inquiry and research-based learning approach. Your belief in inculcating these qualities in your child closely matches the IB curriculum.'
                    ],
                ];

                ?>

                <div class="contents-row">
                    <p><strong>DEEP FITMENT ANALYSIS</strong></p>
                    <h4>SELF DISCOVERY</h4>
                    <p>
                        To explore and understand one’s strengths and weaknesses, experiential learning is crucial. From structured learning programs to
                        self-learning and research-based approaches, learning through experiences encourages students to become better independent individuals.
                        While choosing a curriculum, we look at the one that matches closely with your requirement of helping the child discover him/herself.
                    </p>
                </div>

                <div class="contents-row">
                    <h3 class="text-center">YOUR FITMENT</h3>
                    <div class="row">
                        <div style="width: 800px;">
                            <canvas id="chart-sd" height="100" width="300"></canvas>
                        </div>
                    </div>
                </div>
                <br/>
                <br/>

                <div class="contents-row">
                    <h3><?php echo $sd_text[$sd_labels[0]]['label']; ?></h3>
                    <p>
                        <?php
                        echo ($cbse_override && isset($sd_text[$sd_labels[0]]['text_alt'])) ? $sd_text[$sd_labels[0]]['text_alt'] : $sd_text[$sd_labels[0]]['text'];
                        ?>
                    </p>
                </div>

                <div class="contents-row">
                    <h3><?php echo $sd_text[$sd_labels[1]]['label']; ?></h3>
                    <p>
                        <?php
                        echo ($cbse_override && isset($sd_text[$sd_labels[1]]['text_alt'])) ? $sd_text[$sd_labels[1]]['text_alt'] : $sd_text[$sd_labels[1]]['text'];
                        ?>
                    </p>
                </div>

                <script>
                    new Chart(document.getElementById('chart-sd'), {
                        type: 'horizontalBar',
                        data: {
                            labels: ['<?php echo $board_alias[$sd_labels[0]] ?>', '<?php echo $board_alias[$sd_labels[1]] ?>'],
                            datasets: [{
                                data: <?php echo json_encode(array_values($sd)); ?>,
                                backgroundColor: "#77C236", borderColor: "#77C236", borderWidth: 1
                            }]
                        },
                        options: {
                            showAllTooltips: true,
                            legend: {display: false},
                            scales: {xAxes: [{ticks: {beginAtZero: true}}]},
                            tooltips: {
                                callbacks: {
                                    title: function(tooltipItem) { return tooltipItem.yLabel;}
                                }
                            },
                        }
                    });
                </script>


            </div>
            <?php include('pdf_footer.php') ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="content-page">
        <div class="content-header">
            <?php include('pdf_header.php'); ?>
            <div class="contents">

                <?php
                $ca = [$boards[0] => $board_parameters[$boards[0]]['ca'], $boards[1] => $board_parameters[$boards[1]]['ca'],];

                arsort($ca);
                $ca_labels = array_keys($ca);

                $ca_text = [
                    'cbse' => [
                        'label' => 'CENTRAL BOARD OF SECONDARY EDUCATION',
                        'text' => 'The CBSE curriculum is highly structured and hence the child\'s learning process is smooth. Although the curriculum is rigorous it lays a strong foundation for intensive workload, and so requires minimum parental involvement. It is also well-balanced with a good number of extra-curricular activities. With some level of parental involvement you can make an easy assessment of your child’s overall performance that is vital for the child’s development. Your score on this parameter closely matches with CBSE.',
                        'text_alt' => 'We recommend CBSE because you aspire to pursue professional courses for your child in India. Parental Involvement is an important aspect for a child’s overall development and most progressive schools that follow CBSE curriculum ensure that through participation in extracurricular activities. A good top notch CBSE school is a good match for you.'
                    ],
                    'icse' => [
                        'label' => 'CISCE: THE COUNCIL FOR THE INDIAN SCHOOL CERTIFICATE EXAMINATIONS',
                        'text' => 'The CISCE curriculum advocates experiential learning through cultivating a value system and focusing on the personality development of the child. The curriculum ensures the right balance by including parents as an active partner in their child’s education. Your fitment analysis shows a close match with the CISCE curriculum.',
                    ],
                    'cambridge' => [
                        'label' => 'CAMBRIDGE INTERNATIONAL',
                        'text' => 'With a focus on teaching the child to be independent, the Cambridge curriculum also highlights the necessity of parental involvement. They encourage and believe that parents are also equally important and are partners in their child\'s education. This level of parental engagement allows the child to become self-driven and self-motivated. We recommend the Cambridge curriculum based on the fact that you closely match with this idea of parental involvement.',
                    ],
                    'ib' => [
                        'label' => 'INTERNATIONAL BACCALAUREATE',
                        'text' => 'Although the curriculum develops and encourages students to become independent learners, involvement of parents at different stages motivates the child towards achieving good results/grades. As parents, active participation is one of the best ways to create a positive learning environment for your child. Your score on this parameter matches the IB curriculum.'
                    ],
                ];

                ?>

                <div class="contents-row">
                    <p><strong>DEEP FITMENT ANALYSIS</strong></p>
                    <h4>PARENTAL INVOLVEMENT </h4>

                    <p>
                        Active involvement in a child’s education promotes the holistic development of the child. The approach may vary from continued parental
                        involvement in a child’s activities to a more hands-off approach of inculcating greater self-responsibility in him/her. While choosing a
                        curriculum, it’s important to know your level of involvement in your child’s academic achievements.
                    </p>
                </div>

                <div class="contents-row">
                    <h3 class="text-center">YOUR FITMENT</h3>
                    <div class="row">
                        <div style="width: 800px;">
                            <canvas id="chart-ca" height="100" width="300"></canvas>
                        </div>
                    </div>
                </div>
                <br/>
                <br/>
                <div class="contents-row">
                    <h3><?php echo $ca_text[$ca_labels[0]]['label']; ?></h3>
                    <p>
                        <?php
                        echo ($cbse_override && isset($ca_text[$ca_labels[0]]['text_alt'])) ? $ca_text[$ca_labels[0]]['text_alt'] : $ca_text[$ca_labels[0]]['text'];
                        ?>
                    </p>
                </div>

                <div class="contents-row">
                    <h3><?php echo $ca_text[$ca_labels[1]]['label']; ?></h3>
                    <p>
                        <?php
                        echo ($cbse_override && isset($ca_text[$ca_labels[1]]['text_alt'])) ? $ca_text[$ca_labels[1]]['text_alt'] : $ca_text[$ca_labels[1]]['text'];
                        ?>
                    </p>
                </div>

                <script>
                    new Chart(document.getElementById('chart-ca'), {
                        type: 'horizontalBar',
                        data: {
                            labels: ['<?php echo $board_alias[$ca_labels[0]] ?>', '<?php echo $board_alias[$ca_labels[1]] ?>'],
                            datasets: [{
                                data: <?php echo json_encode(array_values($ca)); ?>,
                                backgroundColor: "#44B0C7", borderColor: "#44B0C7", borderWidth: 1
                            }]
                        },
                        options: {
                            showAllTooltips: true,
                            legend: {display: false},
                            scales: {xAxes: [{ticks: {beginAtZero: true}}]},
                            tooltips: {
                                callbacks: {
                                    title: function(tooltipItem) { return tooltipItem.yLabel;}
                                }
                            },
                        }
                    });
                </script>
            </div>
            <?php include('pdf_footer.php') ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="content-page">
        <div class="content-header">
            <?php include('pdf_header.php') ?>

            <div class="contents">
                <h4>SUMMARY</h4>

                <div class="contents-row">
                    Based on our <strong>Deep Fitment Analysis</strong>, considering your fitment to the four different attributes, we find that your aspirations and preferences
                    are best satisfied by <?php echo $board_alias[$boards[0]] ?> and <?php echo $board_alias[$boards[1]] ?>.
                </div>

                <?php
                $b_0 = [
                    $board_parameters[$boards[0]]['ws'],
                    $board_parameters[$boards[0]]['fm'],
                    $board_parameters[$boards[0]]['sd'],
                    $board_parameters[$boards[0]]['ca'],
                ];

                $b_1 = [
                    $board_parameters[$boards[1]]['ws'],
                    $board_parameters[$boards[1]]['fm'],
                    $board_parameters[$boards[1]]['sd'],
                    $board_parameters[$boards[1]]['ca'],
                ];

                $summary_text = [
                    'ib' => 'The IB curriculum focuses on personal growth, independence of the child, high involvement in  extracurricular activities and global preparedness which match well with your preferences and aspirations.',
                    'cambridge' => 'The Cambridge international curriculum encourages independent thinking, active learning and preparedness for future challenges which maps well with your preferences.',
                    'icse' => 'The CISCE curriculum focuses more on the hand-on approach and is experiential in nature. It equips students with requisite knowledge and skills that will help the students to adapt to the new challenging situations in the future. Your aspirations for your child closely match with this curriculum offers.',
                    'cbse' => 'This curriculum is highly structured and there are a lot of reputed schools that offer this curriculum across the country. The curriculum is both intensive and comprehensive and provides your child the necessary skill set to be future-ready.'
                ];

                ?>

                <div class="contents-row">
                    <h3 class="text-center">FITMENT - <?php echo strtoupper($board_alias[$boards[0]]) ?> CURRICULUM</h3>
                    <div style="width: 90%;">
                        <canvas id="chart-fitment-first" width="300" height="120"></canvas>
                    </div>
                </div>

                <script>
                    new Chart(document.getElementById('chart-fitment-first'), {
                        type: 'horizontalBar',
                        data: {
                            labels: ['WILLINGNESS TO SPEND', 'FUTURE PERSPECTIVES', ' SELF-DISCOVERY ', 'PARENTAL INVOLVEMENT'],
                            datasets: [{
                                data: <?php echo json_encode($b_0); ?>,
                                backgroundColor: ['#116BB1', '#E5942B', '#77C236', '#44B0C7'],
                                borderColor: ['#116BB1', '#E5942B', '#77C236', '#44B0C7'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            showAllTooltips: true,
                            legend: {display: false},
                            scales: {
                                xAxes: [{ticks: {beginAtZero: true}}]
                            },
                            tooltips: {
                                callbacks: {
                                    title: function(tooltipItem) { return tooltipItem.yLabel;}
                                }
                            },
                        }
                    });
                </script>
                <br/>
                <div class="contents-row">
                    <h3 class="text-center">FITMENT - <?php echo strtoupper($board_alias[$boards[1]]) ?> CURRICULUM</h3>
                    <div style="width: 90%;">
                        <canvas id="chart-fitment-second" width="300" height="120"></canvas>
                    </div>
                </div>
                <script>
                    new Chart(document.getElementById('chart-fitment-second'), {
                        type: 'horizontalBar',
                        data: {
                            labels: ['WILLINGNESS TO SPEND', 'FUTURE PERSPECTIVES', ' SELF-DISCOVERY ', 'PARENTAL INVOLVEMENT'],
                            datasets: [{
                                data: <?php echo json_encode($b_1); ?>,
                                backgroundColor: ['#116BB1', '#E5942B', '#77C236', '#44B0C7'],
                                borderColor: ['#116BB1', '#E5942B', '#77C236', '#44B0C7'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            showAllTooltips: true,
                            legend: {display: false},
                            scales: {
                                xAxes: [{ticks: {beginAtZero: true}}]
                            },
                            tooltips: {
                                callbacks: {
                                    title: function(tooltipItem) { return tooltipItem.yLabel;}
                                }
                            },
                        }
                    });
                </script>

                <div class="contents-row">
                    <?php echo '<strong>' . $board_alias[$boards[0]] . '</strong>: ' . $summary_text[$boards[0]]; ?>
                </div>

                <div class="contents-row">
                    <?php echo '<strong>' . $board_alias[$boards[1]] . '</strong>: ' . $summary_text[$boards[1]]; ?>
                </div>

            </div>
            <?php include('pdf_footer.php') ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="content-page">
        <div class="content-header">
            <?php include('pdf_header.php') ?>
            <div class="contents">
                <h4>COMPARISON OF BOARDS ACROSS INDIA</h4>
                <div class="contents-row content-table">
                    <table class="table" width="100%" border="1" cellpadding="0" cellspacing="0">
                        <thead>
                        <tr>
                            <th></th>
                            <th align="center" valign="middle">CBSE</th>
                            <th align="center" valign="middle">CISCE</th>
                            <th align="center" valign="middle">CAIE</th>
                            <th align="center" valign="middle">IB</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td align="left" valign="middle">Establishment Year</td>
                            <td align="center" valign="middle">1962</td>
                            <td align="center" valign="middle">1958</td>
                            <td align="center" valign="middle">1858</td>
                            <td align="center" valign="middle">1968</td>
                        </tr>
                        <tr>
                            <td align="left" valign="middle">Country of Accreditation</td>
                            <td align="center" valign="middle">India</td>
                            <td align="center" valign="middle">India</td>
                            <td align="center" valign="middle">Britain</td>
                            <td align="center" valign="middle">Switzerland</td>
                        </tr>
                        <tr>
                            <td align="left" valign="middle">Type</td>
                            <td align="center" valign="middle">Government</td>
                            <td align="center" valign="middle">Private</td>
                            <td align="center" valign="middle">Private</td>
                            <td align="center" valign="middle">Private</td>
                        </tr>
                        <tr>
                            <td align="left" valign="middle">Recognition</td>
                            <td align="center" valign="middle">National & International</td>
                            <td align="center" valign="middle">National & International</td>
                            <td align="center" valign="middle">International</td>
                            <td align="center" valign="middle">International</td>
                        </tr>
                        <tr>
                            <td align="left" valign="middle">Methods</td>
                            <td align="center" valign="middle">Theoretical</td>
                            <td align="center" valign="middle">Application Based</td>
                            <td align="center" valign="middle">Application Based</td>
                            <td align="center" valign="middle">Application Based</td>
                        </tr>
                        <tr>
                            <td align="left" valign="middle">Focus</td>
                            <td align="center" valign="middle">Maths / Science</td>
                            <td align="center" valign="middle">Maths, Languages & Arts</td>
                            <td align="center" valign="middle">Varied</td>
                            <td align="center" valign="middle">Critical Thinking</td>
                        </tr>
                        <tr>
                            <td align="left" valign="middle">Ease of Learning</td>
                            <td align="center" valign="middle">Moderate</td>
                            <td align="center" valign="middle">Challenging</td>
                            <td align="center" valign="middle">Moderate</td>
                            <td align="center" valign="middle">Challenging</td>
                        </tr>
                        <tr>
                            <td align="left" valign="middle">Teacher Training Provided in India</td>
                            <td align="center" valign="middle">Moderate</td>
                            <td align="center" valign="middle">Moderate</td>
                            <td align="center" valign="middle">High</td>
                            <td align="center" valign="middle">High</td>
                        </tr>
                        <tr>
                            <td align="left" valign="middle">Extended to Preschool</td>
                            <td align="center" valign="middle">Yes</td>
                            <td align="center" valign="middle">Yes</td>
                            <td align="center" valign="middle">Yes</td>
                            <td align="center" valign="middle">Yes</td>
                        </tr>
                        <tr>
                            <td align="left" valign="middle">Score of Topper 2019 (India)</td>
                            <td align="center" valign="middle">99.8% (12th), 98.8% (10th)</td>
                            <td align="center" valign="middle">98.54% (CISCE), 96.52% (ISC)</td>
                            <td align="center" valign="middle">93.11% for IGCSE (Seven A*s, Two As)</td>
                            <td align="center" valign="middle">45 Points (Diploma)</td>
                        </tr>
                        <tr>
                            <td align="left" valign="middle">Fees</td>
                            <td align="center" valign="middle">Medium</td>
                            <td align="center" valign="middle">Medium</td>
                            <td align="center" valign="middle">High</td>
                            <td align="center" valign="middle">High</td>
                        </tr>

                        </tbody>
                    </table>
                    <p class="text-center" style="font-size:12px">Source: Education board websites, expert inputs, First Crayon analysis</p>
                </div>

                <br/>

                <div class="contents-row">
                    <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/goodbye.png" alt="goodbye" align="right">

                    <br/>
                    <br/>
                    <h4>CONCLUSION</h4>

                    <p>
                        Ultimately, no matter which curriculum and how expensive a school you choose, YOUR involvement is crucial in your child’s education.
                        Stay updated with the latest trends in education and actively encourage the school to use technology to keep you in the loop. With this,
                        you will be able to justify the curriculum and the school you have chosen for your child. You can clarify your queries on the report
                        with our counsellors.
                    </p>
                    <h3>GOOD LUCK AND ALL THE BEST!</h3>
                </div>

            </div>
            <?php include('pdf_footer.php') ?>
        </div>
    </div>
</div>

<?php
$annexure = 0;
include_once($boards[0] . '_page.php');
include_once($boards[1] . '_page.php');
?>

<div class="container">
    <div class="content-page">
        <div class="content-header">
            <?php include('pdf_header.php') ?>

            <div class="contents">
                <h3 class="text-center mb-0 mt-0">Annexure <?php echo ++$annexure; ?></h3>
                <br/>
                <div class="contents-row">
                    <h4 style="margin-bottom: 20px;">SUMMARY OF OTHER AVAILABLE CURRICULA</h4>
                    <?php
                    include_once('other_' . $boards[2] . '.php');
                    ?>

                </div>

                <div class="contents-row">
                    <?php include_once('other_' . $boards[3] . '.php'); ?>
                </div>

            </div>
            <?php include('pdf_footer.php') ?>
        </div>
    </div>
</div>

<div class="container" style="margin-bottom: 0 !important;">
    <div class="footer-cover-page">
        <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/bird-01.png" alt="" style="width: 200px;"/>
        <p class="content">
            Univariety is India's first company to use technology to setup a complete career & college guidance cell
            inside progressive schools. Students receive guidance from counsellors, digital tools, university admission
            officers and from Alumni of the school. Univariety runs a successful Global Career Counsellor
            program for certifying teachers in association with a top University - UCLA Extension. Univariety has proved
            to be a comprehensive partner for schools wanting to go beyond the regular and focus on student success.
        </p>
        <div class="content">
            <h4><img src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/phone.png" alt=""
                     style="float: left; margin-right: 15px;margin-top: 6px;"> +91-40-3090-3900</h4>
            <h4><img src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/mail.png" alt=""
                     style="float: left; margin-right: 15px;margin-top: 10px;"><a
                        href="mailto:products@univariety.com">products@univariety.com</a></h4>
            <h4><img src="<?php echo Yii::app()->request->baseUrl; ?>/images/ce/webicon.png" alt=""
                     style=" float: left; margin-right: 15px;margin-top: 6px;"><a href="products.univariety.com">products.univariety.com</a>
            </h4>
        </div>
        <div class="content-footer">

            <div>
                International Educational Gateway Pvt. Ltd. &copy; <?php echo date('Y'); ?>. All Rights Reserved.
                Univariety
            </div>

        </div>
    </div>
</div>

</body>
</html>