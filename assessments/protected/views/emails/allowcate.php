<?php
/**
 * @var OrderAssessments $OrderAssessments
 * @var OrderAssessment $OrderAssessment
 * @var AllowcateForm $model
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body style="margin:0; padding:0;">

<table border="0" cellpadding="10" cellspacing="0" width="100%" style="background-color: #ebebeb;">
    <tr>
        <td>
            <table style="width:800px; margin:0 auto; font-family:verdana,arial; font-size:14px; color:#333; line-height:24px; background-color: #ffffff; border: 10px solid #ffffff;"
                   border="0" cellpadding="0" cellspacing="0" align="center">
                <tr>
                    <td colspan="2">
                        <table>
                            <tr>
                                <td style="width: 25px;"></td>
                                <td>
                                    <table style="width:100%; margin:0 auto;" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td>
                                                <p style="margin:0 0 20px 0;font-size: 16px;">
                                                    Dear <?php echo ucwords($model->name); ?>,
                                                </p>

                                                <p style="margin-bottom: 20px; font-size: 16px;">
                                                    The following product<?php echo (count($OrderAssessments) > 1) ? 's are' : ' is';  ?> allocated for you
                                                </p>

                                                <table width="90%" border="1" style="border-collapse: collapse;">
                                                    <tbody>
                                                    <tr>
                                                        <th>Description</th>
                                                        <th>Link</th>
                                                    </tr>
                                                    </tbody>
                                                    <tbody>
                                                    <?php
                                                    foreach($OrderAssessments as $OrderAssessment){
                                                        echo '<tr>';
                                                        echo sprintf('<td>%s</td>', $OrderAssessment->assessment_name);
                                                        echo sprintf('<td><a href="%s">%s</a></td>', $OrderAssessment->test_link, $OrderAssessment->test_link);
                                                        echo '</tr>';
                                                    }
                                                    ?>
                                                    </tbody>
                                                </table>
                                                <p>
                                                </p>

                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <p style="margin:20px 0;">
                                                    Thanks, <br/>
                                                    <?php echo Yii::app()->session->get('firstname') . ' ' . Yii::app()->session->get('lastname') ?>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 25px;"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>