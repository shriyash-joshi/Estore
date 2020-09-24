<style>
    .round-circle {
        background: #f8f8f8;
        width: 300px !important;
        height: 300px !important;
        border-radius: 50%;
        margin: 0 auto;
        text-align: center;
        line-height: 300px !important;
    }
</style>
<?php
$benifits = ['p3_title' => 'Benefits of the VIP',
    'p3_points' => ['<b>Practical Experience - </b>Do the tasks that actual professionals do in their field.', '<b>Detailed VIP (Virtual Internship Program) Report - </b>Our team of experts analyze your performance and provide you with an in depth report about your standing with respect to that field.', '<b>Easy Online Access - </b>Anytime. Anywhere. Access the Program at the comfort of your home at your convenience.', '<b>Professional Guidance - </b>Have Industry Experts in their respective fields guide you to make the right choice about your career.']];

$courseData = [];
$courseData['law'] = [
    'title' => 'Law',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/law.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Law',
    'p1_desc' => 'Confused about which career to choose? Have you wondered about stepping into the shoes of world-famous lawyers like Barack Obama and Ram Jethmalani? Curious to find out how good you will be as a lawyer? Practically experience activities like understanding a case, drafting legal papers, etc. Test yourself as a Lawyer through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Instructor: Prem Rajani',
    'p2_subtitle' => 'Corporate Law | Criminal Law | Counsel',
    'p2_desc' => 'Mr Prem Rajani, one of the pronounced and globally recognized personalities in the field of law, has brought about a change in the lives of many by his exceptional work. Being the founding partner of Rajani Associates and with over 25 years of experience, he has created strong foundations in private equity, mergers and acquisitions, banking, finance and capital markets. His recognition spreads across India, US, Europe, Middle East, South East Asia and Australia. His firm, Rajani Associates, has been consistently ranked among the top law firms in India. Watch him as he talks about the intricacies and nuances of the legal world.'
];

$courseData['hospitality'] = [
    'title' => 'Hospitality',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/hospitality.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Hospitality',
    'p1_desc' => 'Confused about which career to choose? Does the thought of running your own restaurant empire like Riyaaz Amlani of Social, or deGustibus Hospitality of Indigo Deli excite you? Have you wondered how you would do in the world of Hospitality? Practically experience activities like menu drafting, briefings, customer handling, among others. Test yourself as a Restaurateur through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Instructor: Roshan Shetty',
    'p2_subtitle' => 'Hotel Management | Restaurateur | Chef',
    'p2_desc' => 'Roshan Shetty, the famous Hotelier and Restaurateur, and the proud owner of several find-dine restaurants across various cities in India under the brand names Melting Pot and Ten One, has mastered the industry of service, with loyal crowd and celebrities being constantly satisfied and elated. His brilliant skills and hard work have made sure that his restaurants in India are consistently ranked as one of the best. Learn from Roshan, as he unravels the mystery, about how to start and run a top-notch restaurant.'
];

$courseData['film-making'] = [
    'title' => 'Film making',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/film-making.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Film making',
    'p1_desc' => 'Confused about which career to choose? Awed by the films made by Karan Johar, Rajkumar Hirani and many others? Curious to find out how good you will be as a Filmmaker? Practically experience activities like casting, shooting, editing, directing, etc. Test yourself as a Filmmaker through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Instructor: Howard Rosemeyer',
    'p2_subtitle' => '',
    'p2_desc' => 'Meet Howard Rosemeyer, an extravagant director and a predominant bollywood actor, with the talents and versatility of being a dancer, choreographer and a theatre artist, who is known for his exemplary work In Ms. Dhoni: The Untold Story, Pyaar ka Punchnaama 2, Dil Toh Baccha Hai Ji, Parineeta, Oliver Twist, Peter Pan, among others. Join the industry extraordinaire as he unravels the nitty-gritties of this world and his mantra to success.'
];

$courseData['dentist'] = [
    'title' => 'Dentistry',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/dentistry.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Dentistry',
    'p1_desc' => 'Confused about which career to choose? Does the concept of perfecting a person’s smile fascinate you? Would you like to find out how you would do as a dentist for a day? Practically experience activities like consultation, diagnosis and treatment of a patient, amongst the other aspects of being a dentist. Test yourself as a Dentist through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Dr. Rajiv Verma',
    'p2_subtitle' => '',
    'p2_desc' => 'Dr. Rajiv Verma, who is internationally recognized in the field of Dentistry, is considered as one of the finest Dentists in India. A clinician for 30 years, a Dental Ceramist of repute for 21 years, an IDA Speaker for 19 years, a course conductor for 13 years, an International Speaker for Past 6 years and an author of 2 books, he is limitless. Watch him as he unfolds his experiences and teachings just for you!',
];

$courseData['architecture'] = [
    'title' => 'Architecture',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/architecture.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Architecture',
    'p1_desc' => 'Confused about which career to choose? Have you ever dreamt of creating an architectural marvel like the Burj Khalifa or the Taj Mahal? Do you think you have what it takes to be an architect for a day? Practically experience & understand concepts like Architectural planning, floor plan designing, 3D drawing, etc. Test yourself as an Architect through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Arjun Rathi',
    'p2_subtitle' => '',
    'p2_desc' => 'Arjun Rathi, an established Architect, is the principal of the self-named multidisciplinary design practice, Arjun Rathi Designs, operating from Mumbai, India. Established in 2011, his work has gained national recognition for its exploratory approach towards design processes. Arjun consistently contributes to the field of architecture by publishing papers on his innovative work. If you want to know more about what entails in architecture. Get ready to look at this profession from different, creative angles, through the eyes of Arjun Rathi!',
];

$courseData['fund-management'] = [
    'title' => 'Fund Management',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/fund-manager.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Fund Management',
    'p1_desc' => 'Confused about which career to choose? Would you like to step into the world of a fund manager like Rakesh Jhunjhunwala or Warren Buffet? Curious to see how you would fare advising someone on how to manage their investments? Practically experience activities that make up the day of a fund manager, like screening companies, valuation, investments, etc. Test yourself as a Fund Manager through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Dhruvesh Sanghvi',
    'p2_subtitle' => '',
    'p2_desc' => 'The esteemed financial wizard, Mr. Dhruvesh Sanghvi, is an Engineer, an MBA, and a SEBI registered Research Analyst. He has been working in the financial markets since many years, and because of his undying passion for equity, he established his own renowned Company, Prospero Tree Financial Services. He has been proficiently practicing the art and science of investing in companies that create value in the long run, and has mastered the in and out of Investment. Who to understand Fund Management better from? Watch Mr. Dhruvesh breakdown the complexities of finance, decipher trade & investment secrets and conquer the financial realm.',
];

$courseData['marketing'] = [
    'title' => 'Marketing',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/marketing.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Marketing',
    'p1_desc' => 'Confused about which career to choose? How would you like to spend a day doing what Neil Patel does? Have you wondered how you would do in the world of marketing? Practically experience & understand activities like promotions, marketing campaigns and strategies, setting budgets, etc. Test yourself as a Marketing guru through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Ronak Sheth',
    'p2_subtitle' => '',
    'p2_desc' => 'Ronak Sheth, the marketer of various luxury and lavish brands like Cartier, Polo and Ralph Lauren and of India\'s 3rd largest eye brand, Opium, has made sure that through his majestic strategies, his brands are known and used by masses. Being the owner of Opium, he has mesmerized celebrities all over the world with the styles, comfort and variety of his eye wares. He has been reviewed as \'A Visionary in The Lifestyle Business Era\' as the Director of Eternity Lifestyles Private Limited. He has worked closely with Gucci, Chanel, Puma and Mont Blanc among others. Don’t miss the opportunity to understand Marketing as a career while Ronak shares his secret tips and tricks to success and recognition.',
];

$courseData['fashion-designing'] = [
    'title' => 'Fashion Designing',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/fashion.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Fashion Designing',
    'p1_desc' => 'Confused about which career to choose? Have you always wanted to explore the world of fashion? Are you curious to see how you would do in the shoes of designers like Sabyasachi & Versace? Practically experience & understand activities like sketching, creating mood boards, fabric selection, etc. Test yourself as a Fashion Designer through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Harsh Bhargava',
    'p2_subtitle' => '',
    'p2_desc' => 'The glorious Harsh Bhargava, a former Model and now Fashion Designer, through his fashion label House of HB, has established his creativity through his work, where he leaves no stone unturned to make people look their best. He believes that fashion is an extension of one\'s personality. His elegant and lavish work can be seen in women\'s and men\'s traditional as well as western outfits. Catch Harsh while he explains his step by step procedure in creating unique shapes and silhouettes for his elated customers.',
];

$courseData['graphic-designing'] = [
    'title' => 'Graphic Designing',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/graphics.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Graphic Designing',
    'p1_desc' => 'Confused about which career to choose? Has the vastly creative world of graphic designing always called out to you? Do you think you have what it takes to design & execute logos, posters, etc. from scratch? Practically experience & understand design activities like creating mood boards, picking colors and typography, etc. Test yourself as a Graphic Designer through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Priyanka Shah',
    'p2_subtitle' => '',
    'p2_desc' => 'The iconic, new generation Graphic Designer, Ms. Priyanka Shah, is the face and the creativity behind the most memorable logos of brands like Crosswords and EuroKids. Her work embodies finesse, appeal and depth beyond compare. This visual communicator has the reputation for bringing freshness and newness to the table. In a competitive world, she has made and continues to make her mark. Look at how she holds strong and consistently energizes her creativity in this Graphic Designing VIP.',
];

$courseData['chartered-accountant'] = [
    'title' => 'Chartered accountancy',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/ca.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Chartered Accountancy',
    'p1_desc' => 'Confused about which career to choose? Do you think you have what it takes to be a master of the world of business & finance? Have you wondered how your love of numbers can translate into the right career for you? Practically experience & learn the aspects of accounting, depreciation, auditing and tax collection, among others. Test yourself as a Chartered Accountant through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Manoj Shah',
    'p2_subtitle' => '',
    'p2_desc' => 'Mr. Manoj Shah, one of the most reputable and honorable Chartered accountants, is the founding partner of the renowned Shah & Modi Chartered Accountancy. His over 30 years of valuable and professional experience adds principally to his commendable credit. His expertise fall in the fields of Direct Tax, Audit and FEMA. He has several rewards and recognitions to his name. Watch him as he talks about the intricacies and nuances of the Chartered Accountancy world.',
];

$courseData['computer-engineering'] = [
    'title' => 'Computer Engineering',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/ce.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Computer Engineering',
    'p1_desc' => 'Confused about what career to choose? Always wondered what it would be like to explore the world of Sundar Pichai and Bill Gates? Are you curious to try your hand at coding or building your own app? Practically experience activities like designing a wireframe, coding, testing, etc. of your own app. Test yourself as a Computer Engineer through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Karan Shah',
    'p2_subtitle' => '',
    'p2_desc' => 'Karan Shah, an enthusiastic and innovative Computer Engineer, has also pursued his MBA in Information Technology. His experimental ways of dealing with projects, finding solutions, and providing with outstanding end-products, have made him prominent as a Computer engineer. With an experience of developing applications, websites and softwares of various well-known and creative companies, he has developed strong back-bones for all his clients. Watch Karan Shah take you through the different realms of computer engineering in his own unique way!',
];

$courseData['psychology'] = [
    'title' => 'Psychology',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/psychology.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Psychology',
    'p1_desc' => 'Confused about which career to choose? Has the idea of getting an insight into a person’s behavior and thinking always been fascinating to you? Are you curious to find out how you would be as a psychologist/counselor? Practically experience & understand concepts like rapport building, identification, testing, therapy, etc. Test yourself as a Psychologist/Counselor through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Janvi Sutaria',
    'p2_subtitle' => '',
    'p2_desc' => 'Curious to know how counselling sessions work and what a psychologist\'s role actually is? Janvi, a practicing Therapist, Counselor and a Health Psychologist, is all set to take you on a counseling ride. She has pursued her studies from Newcastle University (UK). Her therapeutic treatments are an integration of the refined international concepts and her cultural learnings. She has specialized in treating different issues like personality disorders, relationship dysfunctions, stress, depression, anxiety, etc. Hold on tight while Janvi takes you through a captivating experience of a counsellor\'s life!',
];

$courseData['mechanical-engineering'] = [
    'title' => 'Mechanical Engineering',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/me.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Mechanical Engineering',
    'p1_desc' => 'Confused about which career to choose? Are you fascinated by machines and the work that goes into creating one from scratch? How would you like to step into the world of mechanical engineering for a day? Practically experience & understand concepts like design of a machine, functioning of a gear, quality control, etc. Test yourself as a Mechanical Engineer through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Deep Goel',
    'p2_subtitle' => '',
    'p2_desc' => 'Mr Deep Goel, the remarkable mechanical engineer, has accomplished legendary works in the industries of cars, steel, paper, textiles, and holograms among others. His rigorous experience of more than 35 years has built pillars of beliefs of being reliable, pre- planned, cost effective, and service oriented. He is the honored founder of Transinov Engitec and his persona is a perfect blend of Experience, Management and Zeal. Customer satisfaction has always been his mantra. His extraordinary technical skills, Problem-solving skills, Creativity, Commercial awareness and Team working skills have awed all the young engineers. Make him your role model and grasp the chance of being guided by him to become the best mechanical engineer.',];

$courseData['medicine'] = [
    'title' => 'Medicine',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/medicine.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Medicine',
    'p1_desc' => 'Confused about which career to choose? Always been inspired to cure people? Curious to find out how good you will be as a Doctor? Practically experience activities like creating a diagnosing patients, treating patients, referring to specialists, etc. Test yourself as a Doctor through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Dr. Sonali Tank',
    'p2_subtitle' => '',
    'p2_desc' => 'Dr. Sonali Tank is one of finest pediatrician in Mumbai. She has over 15 years of experience in this profession. She has worked across Government Hospitals and Private Clinics. With immense knowledge in treating patients, she takes you on an immersive journey to experience the life of a doctor.',
];

$courseData['teaching'] = [
    'title' => 'Teaching',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/teaching.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Teaching',
    'p1_desc' => 'Confused about which career to choose? Always been inspired to spread knowledge? Curious to find out how good you will be as a Teacher? Practically experience activities like creating a lesson plan, setting question papers, correcting answer sheets, etc. Test yourself as a Teacher through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Dr. Ranjini Krishnaswamy',
    'p2_subtitle' => '',
    'p2_desc' => 'Dr. Ranjini Krishnswamy, one of the most reputed Principals in Mumbai, has over 40 years of experience in the field of education. She has won the National Award for her contributions in the field of teaching. Currently, she works as a Director, Education in the Dosti Group. With immense knowledge in setting up and transforming schools, she takes you on an immersive journey to experience the life of a teacher.',
];

$courseData['civil-services'] = [
    'title' => 'Civil Services',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/civilservices.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Civil Services',
    'p1_desc' => 'Confused about which career to choose? Wanted to work for the government and serve the nation? Curious to find out how good you will be as a Civil Servant? Practically experience activities that IAS, IPS and IRS officers undertake on a daily basis. Test yourself as a Civil Servant through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Dr. Mangesh Gondavale',
    'p2_subtitle' => '',
    'p2_desc' => 'Dr. Mangesh Gondavale is the Deputy Commissioner of GST, Maharashtra Government. He has over 15 years of experience in this profession. With immense knowledge in working with the government as an IRS officer, he takes you on an immersive journey to experience the life of a civil servant.',
];

$courseData['ethical-hacking'] = [
    'title' => 'Ethical Hacking',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/ethicalhacking.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Ethical Hacking',
    'p1_desc' => 'Confused about which career to choose? Wanted to understand more about how computers are hacked and how they can be prevented from hacking? Curious to find out how good you will be as an Ethical Hacker? Practically experience activities like creating a gathering information, scanning systems, gaining access, etc. Test yourself as an Ethical Hacker through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Vedant Gulati',
    'p2_subtitle' => '',
    'p2_desc' => 'Vedant Gulati is a professional ethical hacker. He has over 6 years of experience in this profession. With immense knowledge in preventing systems from being hacked, he takes you on an immersive journey to experience the life of an ethical hacker.',
];

$courseData['family-business'] = [
    'title' => 'Family Business',
    'image' => 'https://univariety.sgp1.digitaloceanspaces.com/img/immrse/family-business.png',
    'p1_title' => 'Virtual Internship Program (VIP) in Family Business',
    'p1_desc' => 'Confused about which career to choose? Have you always wanted to follow in your family’s footsteps or have your own business? Do you feel like you would like to be the director of a big family business someday, like the Godrej family? Practically experience & understand concepts like family dynamics, production, manufacturing, sales, etc. Test yourself as an Entrepreneur through our online Virtual Internship Program (VIP), simply from the comfort of your home, anytime, anywhere!',
    'p2_title' => 'Meet your Instructor: Akash Doshi',
    'p2_subtitle' => '',
    'p2_desc' => 'Meet Mr. Akash Doshi, the Director of S. Doshi Papers Industries Pvt. Ltd, an extravagant Company that has successfully earned profits year after year in an extremely competitive industry. Akash has nurtured this Company for over 18 years to make it the manufacturing giant it is today. In this VIP, Akash will share how he has fused modern technology to his years-old family business, and how he has successfully continued and progressed the business using value-based leadership and entrepreneurship.',
];

$displayCourseData = $courseData[$course];

$allowChange = 1;
if($this->OrderAssessment->completed_on) {
    $button = CHtml::link('View Report', $this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []), ['class' => 'btn-sm btn-green take-the-test', 'target' => '_blank']);
} else {

    if(!$this->OrderAssessment->started_on) {
        //$button = CHtml::link('Start Program', $this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []), ['class' => 'btn-sm btn-green take-the-test', 'target' => '_blank']);
        $button = '<a href="javascript:void(0)" class="btn-sm btn-green" data-toggle="modal" data-target="#instModal">Start Program</a>';
        if ($this->OrderProductSKU == 'immrse') {
            $allowChange = 2;
        }
    } else {
        $button = CHtml::link('Continue The Program', $this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []), ['class'
        => 'btn-sm btn-green take-the-test', 'target' => '_blank']);
    }
}
?>
<div class="blue_bg">
    <div class="container">
        <div class="row pt-4 pb-4">
            <div class="col-md-7">
                <h1 class="white-text mb-0"> <?php echo ucfirst($displayCourseData['title']); ?></h1>
            </div>
            <div class="col-md-3 pt-4 white-text col-xs-12">&nbsp;</div>

            <div class="col-md-2 col-xs-12 pl-md-0">
                <ul class="list-none white-text list-inline  pt-3 pr-3">
                    <li>
                        <?php echo $button; ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container pt-4 pb-4">
    <div class="row pt-4 pb-4">
        <div class="col-md-4 text-center">
            <div class="round-circle">
                <img src="<?php echo $displayCourseData['image']; ?>" class="img-circle" width="300px"/>
            </div>
        </div>
        <div class="col-md-8">
            <h3><?php echo $displayCourseData['p1_title']; ?></h3>
            <p><?php echo $displayCourseData['p1_desc']; ?></p>

            <br>
            <p style="line-height:22px;"><span
                        style="font-size:26px;font-weight:400;font-family: 'DM Serif Display',serif"><?php echo $displayCourseData['p2_title']; ?></span><?php if($displayCourseData['p2_subtitle']) { ?>
                <br><i><?php echo $displayCourseData['p2_subtitle']; ?><?php } ?></i></p>
            <p><?php echo $displayCourseData['p2_desc']; ?></p>

            <br>
            <h3><?php echo $benifits['p3_title']; ?></h3>
            <ul class="pl-3">
                <?php
                if(count($benifits['p3_points']) > 0) {
                    foreach($benifits['p3_points'] as $val) {
                        echo "<li>$val</li>";
                    }
                }
                ?>
            </ul>
            <br>
            <h3>Instructions:</h3>
            <ul class="pl-3">
                <li>The Internship comprises of both Video content and Career Activities.</li>
                <li>After you have completed the VIP you will receive an in-depth VIP report covering every minute and vital detail related to the professional
                    life in the career.
                </li>
                <li>Once you complete the internship and generate the report, you will not be able to access the course content.</li>
            </ul>

            <div class="row">
                <?php if($allowChange == 2) { ?>
                    <div class="col-md-2 col-sm-4 mb-3 mt-3 pr-0"> <?php echo $button; ?></div>
                    <div class="col-md-6 col-sm-12 mb-3 mt-3 ml-3 pr-0">
                        <a href="<?php echo $this->createUrl('program', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []); ?>"
                           class="float:left;">Change Program</a>
                        <br/><small>*Program cannot be changed once started</small>
                    </div>
                <?php } else { ?>
                    <div class="col-md-3 col-sm-4 mb-3 mt-3 pr-0"> <?php echo $button; ?></div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div id="instModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title text-center">Instructions</h4>
      </div>
      <div class="modal-body">
            <ul>
<!--                <li>Please use laptop/PC/desktop for best experience.</li>-->
                <li>The entire program is divided into multiple chapters. Some might be video chapters and others might be activity chapters.</li>
                <li>The chapter numbers will be seen on the top of the page. You have to go from one chapter to the next chapter in order to complete your program. You can not skip any chapters in between. <b class="text-danger">Once you go to the next chapter, you will not be able to go back to any of the previous chapters.</b></li>
                <li>All the activity chapters will start with an instruction video. These videos are just for you to watch and understand how the activity is to be performed.</li>
                <li>All the activities are time bound. If the time runs out, the activity will automatically stop and the next chapter will start.</li>
            </ul>
          <br>
          <div class="row text-center">
            <?php echo $button = CHtml::link('Continue to Program', $this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []), ['class' => 'btn-sm btn-green take-the-test', 'target' => '_blank']); ?>
          </div>
          <br>
      </div>
      
    </div>

  </div>
</div>
<script type="text/javascript">
    //$('body').on('click', '.take-the-test', function () {
    //    window.location = '<?php //echo $this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []); ?>//';
    //    //$.colorbox({
    //    //    href: "<?php ////echo $this->createUrl('takeTest', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []) ?>////",
    //    //    width: '60%',
    //    //    height: '60%',
    //    //    iframe: true,
    //    //    onClosed: function () {
    //    //        window.location.reload();
    //    //    }
    //    //});
    //});
</script>