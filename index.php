<?php
require_once "../config.php";

use Tsugi\Blob\BlobUtil;
use Tsugi\Core\LTIX;
use Tsugi\UI\Output;

$p = $CFG->dbprefix;

$LAUNCH = LTIX::requireData();

$homeStmt = $PDOX->prepare("SELECT * FROM {$p}course_home WHERE link_id = :linkId");
$homeStmt->execute(array(":linkId" => $LINK->id));
$home = $homeStmt->fetch(PDO::FETCH_ASSOC);

if (!$home && $USER->instructor) {
    // If no home set up then go straight to edit
    header( 'Location: '.addSession('edit.php') ) ;
} else if (!$home) {
    echo '<h3>Instructor has not yet added course details.</h3>';
    return;
}

$OUTPUT->header();
?>
    <!-- Bootstrap CSS -->
    <link crossorigin="anonymous" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
          integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" rel="stylesheet">
<style>
    .profile-img img{
        max-width: 100%;
        max-height: 125px;
        width: auto;
        height: auto;
    }
    .profile-head h5{
        color: #333;
    }
    .profile-rating, .profile-rating2 {
        font-size: 12px;
        color: #818182;
        margin-top: 2em;
    }
    .profile-rating span{
        color: #495057;
        font-size: 13px;
        font-weight: 600;
    }
    .profile-work{
    }
    .profile-work p{
        font-size: 12px;
        color: #818182;
        font-weight: 600;
        margin-top: 2em;
    }
    .profile-work a{
        text-decoration: none;
        color: #495057;
        font-weight: 600;
        font-size: 14px;
    }
    .profile-work ul{
        list-style: none;
    }
    .videoWrapper {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 */
        height: 0;
    }
    .videoWrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    #body_container {
        padding-left: 0;
        padding-right: 0;
    }
</style>
<?php
$OUTPUT->bodyStart();

$OUTPUT->flashMessages();
if ($USER->instructor) {
    ?>
    <div class="position-absolute" style="top:0;right:0;">
        <a href="edit.php" class="btn btn-light"><span class="far fa-edit" aria-hidden="true"></span> Edit</a>
    </div>
    <?php
}
?>
    <div style="width:100%;height:80px;padding:0;margin: 0;background:linear-gradient(40deg,#4c6dab,#002F87);">
        <h4 class="text-white inline-block p-2 pt-4 text-center">
            <?= $home['course_title'] ?>
        </h4>
    </div>
    <div style="padding:1em 2em;">
        <div class="row">
            <div class="col-md-3 col-sm-4 col-5">
                <?php
                $sections = explode(',', $home["sections"]);
                if ($sections) {
                    ?>
                    <p class="profile-rating" style="margin-top:0;">
                        SECTIONS<br/>
                        <?php
                        foreach ($sections as $section) {
                            echo '<span>' . $section . '</span><br />';
                        }
                        ?>
                    </p>
                    <?php
                }
                $meetings = explode(',', $home["meetings"]);
                if ($meetings) {
                    ?>
                    <p class="profile-rating" style="margin-top:0;">
                        CLASS MEETINGS<br/>
                        <?php
                        foreach ($meetings as $meeting) {
                            echo '<span>' . $meeting . '</span><br />';
                        }
                        ?>
                    </p>
                    <?php
                }
                if ($home['start_date'] || $home['end_date']) {
                    $formattedStartDate = '';
                    if ($home['start_date'] && $home['start_date'] != '') {
                        $startdate = new DateTime($home['start_date']);
                        $formattedStartDate = $startdate->format("M. j");
                    }
                    $formattedEndDate = '';
                    if ($home['end_date'] && $home['end_date'] != '') {
                        $enddate = new DateTime($home['end_date']);
                        $formattedEndDate = $enddate->format("M. j");
                    }
                    ?>
                    <p class="profile-rating" style="margin-top:0;">
                        DATES<br/>
                        <span>
                            <?= $formattedStartDate ?>
                            <?= $formattedStartDate != '' && $formattedEndDate != '' ? ' - ' : '' ?>
                            <?= $formattedEndDate ?>
                        </span>
                    </p>
                    <?php
                }
                ?>
                <hr />
                <div class="profile-work pb-4">
                    <?php
                    $syllabus_url = BlobUtil::getAccessUrlForBlob($home["syllabus_blob_id"], Output::getUtilUrl('/public_blob_serve.php'));
                    if ($home["syllabus_blob_id"] && $home["syllabus_blob_id"] != "" && $syllabus_url) {
                        ?>
                        <p class="mb-0">SYLLABUS</p>
                        <a href="<?= addsession($syllabus_url) ?>"><span class="fa fa-download" aria-hidden="true"></span> Download</a>
                        <?php
                    }
                    $schedule_url = BlobUtil::getAccessUrlForBlob($home["schedule_blob_id"], Output::getUtilUrl('/public_blob_serve.php'));
                    if ($home["schedule_blob_id"] && $home["schedule_blob_id"] != "" && $schedule_url) {
                        ?>
                        <p class="mb-0">SCHEDULE</p>
                        <a href="<?= addSession($schedule_url) ?>"><span class="fa fa-download" aria-hidden="true"></span> Download</a>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="col-md-9 col-sm-8 col-7">
                <h6>Course Description</h6>
                <p>
                    <?= $home['course_desc'] ?>
                </p>
                <?php
                if (isset($home['course_video']) && $home['course_video'] !== '') {
                    ?>
                    <div class="videoWrapper">
                        <!-- Copy & Pasted from YouTube -->
                        <iframe height="360" width="640" src="<?= $home['course_video'] ?>" frameborder="0" scrolling="0" allow="autoplay; encrypted-media; fullscreen;  picture-in-picture;" allowfullscreen></iframe>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-md-3 col-sm-4 col-5">
                <?php
                $profile_url = BlobUtil::getAccessUrlForBlob($home["picture_blob_id"], Output::getUtilUrl('/public_blob_serve.php'));
                if ($home["picture_blob_id"] && $home["picture_blob_id"] != "" && $profile_url) {
                    ?>
                    <div class="profile-img">
                        <img alt="<?= $home['instructor_name'] ?>" src="<?= addsession($profile_url) ?>"/>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="col-md-9 col-sm-8 col-7">
                <div class="profile-head">
                    <h5>
                        <?= $home['prefix'] ?> <?= $home['instructor_name'] ?>
                    </h5>
                    <?php
                    if (isset($home['office_location']) && $home['office_location'] !== '') {
                        ?>
                        <h6>
                            <span class="fas fa-building" style="color:#818182;"></span> <?= $home['office_location'] ?>
                        </h6>

                        <?php
                    }
                    if ((isset($home['phone']) && $home['phone'] !== '') || (isset($home['email']) && $home['email'] !== '')) {
                        echo '<p class="profile-rating">';
                        if (isset($home['phone']) && $home['phone'] !== '') {
                            ?>
                            PHONE : <span><?= $home['phone'] ?></span> <?= $home['preferred_contact'] == 'phone' ? ' (preferred)' : '' ?><br/>
                            <?php
                        }
                        if (isset($home['email']) && $home['email'] !== '') {
                            ?>
                            EMAIL : <span><?= $home['email'] ?></span> <?= $home['preferred_contact'] == 'email' ? ' (preferred)' : '' ?>
                            <?php
                        }
                        echo '</p>';
                    }
                    $office_hours = explode(',', $home["office_hours"]);
                    if ($office_hours) {
                        ?>
                        <p class="profile-rating">
                            OFFICE HOURS<br/>
                            <?php
                            foreach ($office_hours as $hrs) {
                                echo '<span>' . $hrs . '</span><br />';
                            }
                            ?>
                        </p>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        if (isset($home['getting_started']) && $home['getting_started'] !== '') {
            ?>
            <hr/>
            <div class="row">
                <div class="col-12">
                    <h6>Getting Started</h6>
                    <p>
                        <?= $home['getting_started'] ?>
                    </p>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
<?php
$OUTPUT->footer();