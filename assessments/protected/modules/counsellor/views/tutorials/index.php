<?php
/**
 * @var $this TutorialsController
 */

$this->pageTitle = 'Technology to help you solve Student Problems';
?>

<div class="container mb-5">
    <h2 class="text-center mb-3"><?php echo $this->pageTitle; ?></h2>
    <h4 class="text-center mb-5">Why do products make the life of a counsellor more efficient, effective and easier.</h4>

    <p>
        We at Univariety believe that, Counselling can be made much more effective and efficient when technology is clubbed along with it. Let technology do the
        repetitive tasks such as making sure that all the important questions / factors towards making a decision are considered.
    </p>

    <p>
        Say a parent comes to you with a problem. For example he is confused regarding which curriculum would be most suitable for his daughter.
    </p>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">What you used to do before</h3>
        </div>
        <div class="panel-body">
            <p>
                You fix an appointment with the Parent. You speak at length with him trying to understand his background and problems in detail. Then you try to help
                him with your knowledge gained through many years of counselling.
            </p>
            <label>End Result</label>
            <ol>
                <li>
                    Parent does not get the feeling that a scientific approach has been followed.
                </li>
                <li>
                    The whole process has taken a huge amount of your time. Hence you will not be able to scale this practice efficiently to more and more students.
                </li>
            </ol>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">What we suggest you should do</h3>
        </div>
        <div class="panel-body">
            <p>
                You share the Curriculum evaluator (one in a kitty of many products we bring to you) with the Parent. The Deep fitment Analysis Report which is
                generated at the end of completion of the program makes sure that the Parent has been taken through every parameter which plays a role in curriculum
                selection. Finally the report uses our algorithm to make recommendations to the Parent.
            </p>

            <p>
                Once the Parent has completed the program and got the report he comes to you. You help him with any further questions ,help him understand the report
                etc. As a result of this approach the parent has solid grounds on which the recommendation is made and is confident in the recommendation provided. They
                can see the various parameters that have been considered in making this recommendation.
            </p>

            <label>End Result</label>
            <ol>
                <li>
                    Parent gets assurance that a scientific process has been followed
                </li>
                <li>
                    You are able to scale your practice as the repetitive, time consuming part is taken care by the technology
                </li>
            </ol>
        </div>
    </div>

    <h6 class="mt-5">How to share products with Students / Parents ?</h6>
    <p>
        When you buy a product from products.uniavriety.com you will see the product in your inventory. From your inventory you will be able to allocate
        products to students / parents.
    </p>

    <h6 class="mt-5">How does this make business sense ?</h6>
    <p>
        You will always receive products from us at a discounted rate compared to the price seen by a regular student buying the same product from
        products.univariety.com. Further, you will be able to club the particular product with counselling and sell it to Parents / Students as a solution combo
        and increase the price as you deem fit.
    </p>

    <h6 class="mt-5">Let's take the Curriculum Evaluator as an example :</h6>
    <div class="no-more-tables mb-5">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>Counsellor buys</th>
                <th>Revenue on selling at MRP</th>
                <th>Cost to counsellor</th>
                <th>Profit</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td data-title="Counsellor buy">3 Units</td>
                <td align="right" data-title="Revenue on selling at MRP">INR 14,550</td>
                <td align="right" data-title="Cost to counsellor">INR 7,275</td>
                <td align="right" data-title="Profit">INR 7,275</td>
            </tr>
            <tr>
                <td data-title="Counsellor buys">10 Units</td>
                <td align="right" data-title="Revenue on selling at MRP">INR 48,500</td>
                <td align="right" data-title="Cost to counsellor">INR 19,400</td>
                <td align="right" data-title="Profit">INR 29,100</td>
            </tr>
            </tbody>
        </table>
    </div>

    <?php

    if((int)Yii::app()->session->get('customer_group_id') !== 2 && !Yii::app()->session->get('demo_requested')) {
        //include_once('request_demo.php');
    }
    ?>
</div>
