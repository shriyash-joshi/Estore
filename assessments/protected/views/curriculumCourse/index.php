<?php

/* @var CurriculumFeedback $model */
/* @var bool $pdf_generated */
/* @var bool $survey_complete */
/* @var CActiveForm $form */

Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/bootstrap-rating-input.js');
?>

<div class="container">
    <h1 class="text-center">Welcome to the Curriculum Evaluator!</h1>
    <h5 class="text-center text-muted">We are excited to help you identify the right curriculum for your child.</h5>
</div>

<div class="container pt-4 pb-4">

    <?php if(!$pdf_generated): ?>
        <h3>Next Steps</h3>

        <div class="panel panel-success">
            <div class="panel-heading">
                Step 1: Fill out the Questionnaire
            </div>
            <div class="panel-body">
                Our experts have carefully designed a set of questions that will help us understand your aspirations
                and expectations for your Child's future. Our mapping logic, which has been created after hundreds
                of hours of research maps your inputs with the most suitable Curriculum for your child.
            </div>
        </div>

        <div class="panel panel-success">
            <div class="panel-heading">
                Step 2: Course content
            </div>
            <div class="panel-body">
                Once you have completed the Survey you can access the video content and expert
                insights complemented by reading material.This helps you understand the various parameters
                required to choose the right curriculum for your child.
            </div>
        </div>

        <div class="panel panel-success">
            <div class="panel-heading">
                Step 3: Review responses and get your recommendation
            </div>
            <div class="panel-body">
                Based on the insights from the videos and reading material, you can review and modify your
                responses to the survey. Then submit the same to get your personalized report with the top 2
                curriculum recommendations suitable for your aspirations.
            </div>
        </div>
    <?php endif; ?>

    <?php if(!$survey_complete): ?>
        <div class="text-center">
            <a href="<?php echo $this->createUrl('basicInfo', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []) ?>"
               class="btn btn-success btn-sm">
                Click here to start the questionnaire
            </a>
        </div>
        <hr/>
    <?php endif; ?>


    <?php if($survey_complete): ?>
        <br/>
        <h2 class="text-center">Course Content</h2>

        <div class="panel panel-info">
            <div class="panel-heading panel-heading-collapse" style="cursor: pointer;">
                MODULE 1 - K-12 EDUCATION SYSTEMS IN INDIA
                <span class="pull-right"><i class="fa fa-caret-up fa-lg"></i></span>
            </div>
            <div class="panel-body" style="display: <?php echo $pdf_generated ? 'none' : 'block'; ?>">
                <p>
                    The first module of Curriculum Selector comprises emerging trends in the 21st century
                    Indian education system - focus on skill building of teachers, physical fitness of students,
                    technology-based learning, and steady rise in STEAM. This is followed by evolution of the international
                    curricula in the recent years and changing trends across National media.
                </p>

                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="342528983">
                            K-12 Education Systems in India
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="342528801">
                            Emerging trends in 21st Century education in India
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="342528880">
                            Evolution of International curricula in India in the last 5 years
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="342528699">
                            Changing trends in National curricula (CBSE, ICSE, State Boards)
                        </a>
                    </li>
                </ul>

                <ul class="fa-ul">
                    <li>
                        <span class="fa-li"><i class="fa fa-file-pdf-o"></i></span>
                        <a href="https://univariety.sgp1.digitaloceanspaces.com/gcc/curriculum_evaluator/k_12_education_systems_in_india.pdf"
                           target="_blank">
                            Reading Material
                        </a>

                    </li>
                </ul>

                <h6 style="margin-top: 20px;">Expert Insights from Ashok Pandey (Principal, Ahlcon International School, Delhi)</h6>
                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="345838994">
                            What is the difference between curriculum and syllabus?
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="345839609">
                            What are the salient features of a curriculum?
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="345839841">
                            What is the purpose of curriculum and how to achieve that?
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="345840203">
                            How to choose the right school and curriculum?
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="345841277">
                            How can the curriculum influence a child’s overall growth?
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="panel panel-info">
            <div class="panel-heading panel-heading-collapse" style="cursor: pointer;">
                MODULE 2 - DEEP DIVE INTO DIFFERENT CURRICULA
                <span class="pull-right"><i class="fa fa-caret-up fa-lg"></i></span>
            </div>
            <div class="panel-body" style="display:none;">
                <p>
                    This module covers 5 main curricula followed across Indian schools.
                    They are the International Baccalaureate Program (IB), Cambridge Assessment International Education,
                    Central Board of Secondary Education (CBSE), Council for the Indian School
                    Certificate Examinations (CISCE/ICSE) and State Boards.
                </p>

                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="343602233">
                            Deep dive into different curricula
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="343602261">
                            International Baccalaureate (IB)
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="343601681">
                            Cambridge Assessment International Education (Cambridge International)
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="343601904">
                            Central Board of Secondary Education (CBSE)
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="343602119">
                            Council for the Indian School Certificate Examinations (CISCE)
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="343602478">
                            State Boards
                        </a>
                    </li>
                </ul>

                <ul class="fa-ul">
                    <li>
                        <span class="fa-li"><i class="fa fa-file-pdf-o"></i></span>
                        <a href="https://univariety.sgp1.digitaloceanspaces.com/gcc/curriculum_evaluator/deep_dive_into_different_curricula.pdf"
                           target="_blank">
                            Reading Material
                        </a>
                    </li>
                </ul>

                <h6 style="margin-top: 20px;">Expert Insights from: Ruchira Ghosh (Principal, Sancta Maria International School)</h6>
                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="345442004">
                            What are the common misconceptions parents have about Cambridge International curriculum?
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="345437426">
                            Do you think by adapting to the India Academic Calendar, Cambridge International curriculum has increased its local appeal but
                            jeopardised its international image?
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="345440734">
                            The number of Cambridge International Schools has increased to 400+ schools in under a decade, what makes Cambridge International
                            Schools so popular among parents?
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="345439865">
                            How can students discover new abilities and develop new skills with Cambridge International Curriculum?
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="panel panel-info">
            <div class="panel-heading panel-heading-collapse" style="cursor: pointer;">
                MODULE 3 - CURRICULA - ONE-ONE COMPARISON(S)
                <span class="pull-right"><i class="fa fa-caret-up fa-lg"></i></span>
            </div>
            <div class="panel-body" style="display:none;">
                <p>
                    This module compares the major boards across the country and helps you in making the right curriculum choice.
                    After completion of this module, you will have a profound idea of how these curricula stack up against
                    each other and which one could be a better fit for your child.
                </p>
                <p>
                    The curricula compared here are:
                </p>

                <ul class="fa-ul">
                    <li>
                        <span class="fa-li"><i class="fa fa-circle"></i></span>
                        IB vs. Cambridge International
                    </li>
                    <li>
                        <span class="fa-li"><i class="fa fa-circle"></i></span>
                        IB vs. CBSE
                    </li>
                    <li>
                        <span class="fa-li"><i class="fa fa-circle"></i></span>
                        Cambridge International vs. CBSE
                    </li>
                    <li>
                        <span class="fa-li"><i class="fa fa-circle"></i></span>
                        CBSE vs. CISCE
                    </li>
                </ul>

                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="342526913">
                            IB vs. Cambridge International
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="342526130">
                            IB vs. CBSE
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="342524344">
                            Cambridge International vs. CBSE
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="342525217">
                            CBSE vs. CISCE
                        </a>
                    </li>
                </ul>

                <ul class="fa-ul">
                    <li>
                        <span class="fa-li"><i class="fa fa-file-pdf-o"></i></span>
                        <a href="https://univariety.sgp1.digitaloceanspaces.com/gcc/curriculum_evaluator/curricula_one_one_comparison_s.pdf"
                           target="_blank">
                            Reading Material
                        </a>
                    </li>
                </ul>

                <h6 style="margin-top: 20px;">Expert Insights from: Archana Goenka (Trustee, CP Goenka Group of Schools)</h6>
                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="345685965">
                            What are the recent developments in Cambridge International Curriculum and how have you implemented them in school?
                        </a>
                    </li>
                </ul>

                <h6 style="margin-top: 20px;">Expert Insights from Chetna Sabharwal (Head, College Placements & Guidance, Bodhi International School, Jodhpur,
                    Rajasthan)</h6>
                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="345686556">
                            What are the recent developments in Cambridge International Curriculum and how have you implemented them in school?
                        </a>
                    </li>
                </ul>

                <h6 style="margin-top: 20px;">Expert Insights from: Stefanie Leong (Head of Development and Recognition, Asia Pacific, International
                    Baccalaureate)</h6>
                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="372309184">
                            Does curriculum have an influence on a student's overall performance at your university and their career prospects after that?
                        </a>
                    </li>

                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="372309631">
                            How do students from different curricula get adjusted to the course and college?
                        </a>
                    </li>

                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="372309858">
                            Do all the top universities around the world recognize international curricula like IB for their undergraduate admissions?
                        </a>
                    </li>

                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="372310201">
                            There is a common perception that IB students can’t get into Engineering and Medicine in Indian colleges. Is that a fact?
                        </a>
                    </li>

                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="372310544">
                            Are IB students more inclined towards Non-STEM courses? Would you relate it to the subject combinations in the curriculum?
                        </a>
                    </li>

                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="372310758">
                            Does the curriculum that includes extracurricular activities impact a student’s performance in a positive way?
                        </a>
                    </li>

                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="372311216">
                            Does the curriculum that fosters experiential learning puts students in a better position in the universities?
                        </a>
                    </li>

                </ul>
            </div>
        </div>

        <div class="panel panel-info">
            <div class="panel-heading panel-heading-collapse" style="cursor: pointer;">
                MODULE 4 - OTHER CURRICULA IN INDIA
                <span class="pull-right"><i class="fa fa-caret-up fa-lg"></i></span>
            </div>
            <div class="panel-body" style="display:none;">
                <p>
                    This module discusses different range of options available for parents other than CBSE, ICSE, Cambridge,
                    IB and State Boards. This is helpful for those who are on the lookout for a slightly different
                    educational experience for their child. The curricula covered here are Fieldwork Education,
                    American Curriculum, Edexcel, Waldorf, and National Institute of Open Schooling.
                </p>

                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="342427703">
                            Fieldwork Education & American Curriculum
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="342427510">
                            Edexcel, London, UK , Waldorf and National Institute of Open Schooling
                        </a>
                    </li>
                </ul>

                <ul class="fa-ul">
                    <li>
                        <span class="fa-li"><i class="fa fa-file-pdf-o"></i></span>
                        <a href="https://univariety.sgp1.digitaloceanspaces.com/gcc/curriculum_evaluator/other_curricula_in_india.pdf"
                           target="_blank">
                            Reading Material
                        </a>
                    </li>
                </ul>

                <h6 style="margin-top: 20px;">Expert Insights: Priyamvada Taneja (Ex-Regional Manager for India, Middle East & Africa, Fieldwork Education)</h6>
                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="345442089">
                            How can students develop knowledge, skills and understanding with fieldwork education curriculum?
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="panel panel-info">
            <div class="panel-heading panel-heading-collapse" style="cursor: pointer;">
                MODULE 5 - CRITICAL SKILLS OF THE FUTURE
                <span class="pull-right"><i class="fa fa-caret-up fa-lg"></i></span>
            </div>
            <div class="panel-body" style="display:none;">
                <p>
                    With the age of automation and artificial intelligence catching up, the future leaves a lot of scope for advancements,
                    and at the same time, is highly unpredictable. In this module, we will guide you in identifying
                    critical skills that your child can develop and be future ready.
                </p>

                <p>
                    The different sections in this module are:
                </p>

                <ul class="fa-ul">
                    <li>
                        <span class="fa-li"><i class="fa fa-circle"></i></span>
                        The Future of Jobs
                    </li>
                    <li>
                        <span class="fa-li"><i class="fa fa-circle"></i></span>
                        Critical Skills to Inculcate in Every Child
                    </li>
                    <li>
                        <span class="fa-li"><i class="fa fa-circle"></i></span>
                        Curricula and Critical Skills
                    </li>
                </ul>

                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>
                        <a href="javascript:void(0);" class="vimeo-video" data-id="342424801">
                            The future of jobs
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>

                        <a href="javascript:void(0);" class="vimeo-video" data-id="342423785">
                            Critical Skills to inculcate in every child
                        </a>
                    </li>
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>

                        <a href="javascript:void(0);" class="vimeo-video" data-id="342424463">
                            Curricula and Critical Skills
                        </a>
                    </li>
                </ul>

                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-file-pdf-o"></i>
                        </span>
                        <a href="https://univariety.sgp1.digitaloceanspaces.com/gcc/curriculum_evaluator/critical_skills_of_the_future.pdf"
                           target="_blank">
                            Reading Material
                        </a>
                    </li>
                </ul>

                <h6 style="margin-top: 20px;">Expert Insights from: Rod Fraser (Chair of the Board of Trustees, Round Square International Network of Schools)
                </h6>
                <ul class="fa-ul">
                    <li>
                        <span class="fa-li">
                            <i class="fa fa-video-camera"></i>
                        </span>

                        <a href="javascript:void(0);" class="vimeo-video" data-id="345441953">
                            Round Square Schools focus on character education and experience learning as the key tenets for a student’s development, can you
                            share your insights on how these tenets contribute to the students’ overall development?
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <?php if(!$pdf_generated): ?>
            <div class="text-center">
                <a href="<?php echo $this->createUrl('Reevalute', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []) ?>"
                   class="btn btn-success btn-sm">
                    Re-evalute responses and get the report
                </a>
            </div>

            <div>
                <h5 class="text-warning">
                    Please Note:
                </h5>
                <p class="text-warning">
                    1. Once you click the above button, you will find an option to 'Generate Report' at the end of your responses. <br/>
                    2. You will be able to generate the report only once, so we encourage you to review your responses before you generate the report.
                </p>
            </div>

            <hr/>
        <?php endif; ?>

        <?php if($pdf_generated): ?>
            <div class="text-center">
                <a href="<?php echo $this->createUrl('downloadReport', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []) ?>"
                   class="btn btn-success btn-sm">
                    Download Report
                </a>
            </div>

            <div id="feed-back">
                <?php
                $form = $this->beginWidget('CActiveForm', [
                    'id' => 'ChooseTest',
                    'enableAjaxValidation' => true,
                    'enableClientValidation' => false,
                    'htmlOptions' => ['class' => 'form-horizontal'],
                    'clientOptions' => ['validateOnSubmit' => true, 'validateOnChange' => false, 'validateOnType' => false],
                ]);

                ?>

                <div class="row mt-5">
                    <div class="col-sm-6 col-sm-offset-3">

                        <div class="form-group ">

                            <label class="text-center">
                                On a scale of 4 how accurate do you think are the curriculum recommendations?
                            </label>

                            <div class="text-center mt-3">
                                <input class="rating" data-max="4" data-min="1"
                                       id="CurriculumFeedback_rating" name="CurriculumFeedback[rating]"
                                       data-icon-lib="fa" value="<?php echo $model->rating; ?>"
                                       data-active-icon="fa-star text-warning fa-4x mr-3"
                                       data-inactive-icon="fa-star-o fa-4x mr-3"
                                       type="number"/>

                                <?php
                                echo $form->error($model, "rating", ["class" => "errorMessage text-danger"]);
                                ?>
                            </div>
                        </div>

                        <div id="additional-feedback" style="display: none;">
                            <div class="form-group">
                                <label>Phone number to contact you</label>
                                <?php
                                echo $form->textField($model, "contact_number", ['Placeholder' => "Mobile number", 'class' => 'form-control']);
                                echo $form->error($model, "contact_number", ["class" => "errorMessage text-danger"]);
                                ?>
                            </div>

                            <div class="form-group">
                                <label>Preferred time slot</label>
                                <?php
                                echo $form->textField($model, "time_slot", ['Placeholder' => "Time slot", 'class' => 'form-control']);
                                echo $form->error($model, "time_slot", ["class" => "errorMessage text-danger"]);
                                ?>
                            </div>
                        </div>

                        <button style="display:none;" type="submit" id="fb-submit" class="btn btn-success btn-block btn-sm">Submit</button>
                    </div>
                </div>

                <?php $this->endWidget(); ?>
            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

<script type="text/javascript">
    $(function () {
        $('a.vimeo-video').colorbox({
            iframe: true,
            href: function () {
                return 'https://player.vimeo.com/video/' + $(this).data('id');
            },
            width: 640,
            height: 380
        });
    });

    $('body').on('click', '.panel-heading-collapse', function () {
        $(this).find('i').toggleClass('fa-caret-up fa-caret-down');
        $(this).parent().find('div.panel-body').toggle('slow');
    });

    $('body').on('click', 'div.rating-input i', function () {
        $("#fb-submit").show('slow');
        if ($(this).data('value') !== 4) {
            $('#additional-feedback').show('slow');
            $('#fb-submit').html('Talk to a counsellor');
        } else {
            $('#additional-feedback').hide('slow');
            $('#fb-submit').html('Submit');
        }
    });

</script> 