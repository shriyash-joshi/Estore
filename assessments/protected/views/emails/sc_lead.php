<?php
/**
 * @var Counsellor $Counsellor
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
                                                <p style="margin-bottom: 20px; font-size: 16px;">
                                                    This counsellor has expressed interest to become a Supercounsellor.
                                                </p>
                                                <ul>
                                                    <li>
                                                        Name: <?php echo $Counsellor->display_name  ?>
                                                    </li>
                                                    <li>
                                                        Email: <?php echo $Counsellor->user_email; ?>
                                                    </li>
                                                    <li>
                                                        
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <p style="margin:20px 0;">
                                                    Thanks, <br/>
                                                    Team Univariety Products
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