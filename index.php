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

if (!$home) {
    // If no home set up then go straight to splash
    header('Location: ' . addSession('splash.php'));
    return;
}

$OUTPUT->header();
?>
    <!-- Bootstrap CSS -->
    <link crossorigin="anonymous" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
          integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" rel="stylesheet">
    <style>
        body {
        }
        .profile-img img {
            max-width: 100%;
            max-height: 125px;
            width: auto;
            height: auto;
        }

        .profile-head h5 {
            color: #333;
        }

        .profile-rating, .profile-rating2 {
            color: #818182;
            margin-top: 2em;
            overflow-wrap: break-word;
        }

        .profile-rating span {
            color: #495057;
            font-weight: 600;
        }

        .profile-work {
        }

        .profile-work p {
            color: #818182;
            font-weight: 600;
            margin-top: 2em;
        }

        .profile-work a {

        }

        .profile-work ul {
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
            <div class="col-12"><h5>Course Details</h5></div>
            <div class="col-md-4 col-sm-5 col-12">
                <?php
                if (($home["syllabus_blob_id"] && $home["syllabus_blob_id"] != "") || ($home["schedule_blob_id"] && $home["schedule_blob_id"] != "")) {
                    ?>
                    <p class="profile-rating text-uppercase" style="margin-top:0;">
                        Course Documents</p>
                    <div class="profile-work pb-2">
                        <?php
                        $syllabus_url = BlobUtil::getAccessUrlForBlob($home["syllabus_blob_id"], Output::getUtilUrl('/public_blob_serve.php'));
                        if ($home["syllabus_blob_id"] && $home["syllabus_blob_id"] != "" && $syllabus_url) {
                            ?>
                            <p class="mb-1 mt-2">
                                <a class="btn btn-outline-primary btn-block"
                                   href="<?= addsession($syllabus_url) ?>"><span class="fa fa-download"
                                                                                 aria-hidden="true"></span> Download
                                    Syllabus</a>
                            </p>
                            <?php
                        }
                        $schedule_url = BlobUtil::getAccessUrlForBlob($home["schedule_blob_id"], Output::getUtilUrl('/public_blob_serve.php'));
                        if ($home["schedule_blob_id"] && $home["schedule_blob_id"] != "" && $schedule_url) {
                            ?>
                            <p class="mb-1 mt-2">
                                <a class="btn btn-outline-primary btn-block"
                                   href="<?= addSession($schedule_url) ?>"><span class="fa fa-download"
                                                                                 aria-hidden="true"></span> Download
                                    Schedule</a>
                            </p>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="col-md-8 col-sm-7 col-12">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <?php
                        $sections = explode(',', $home["sections"]);
                        if (count($sections) > 0) {
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
                        if ((isset($home['start_date']) && $home["start_date"] != '') || (isset($home['end_date']) && $home["end_date"] != '')) {
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
                    </div>
                    <div class="col-sm-6 col-12">
                        <?php
                        $meetings = explode(',', $home["meetings"]);
                        if (count($meetings) > 0) {
                            ?>
                            <p class="profile-rating" style="margin-top:0;">
                                CLASS TIMES<br/>
                                <?php
                                foreach ($meetings as $meeting) {
                                    echo '<span>' . $meeting . '</span><br />';
                                }
                                ?>
                            </p>
                            <?php
                        }
                        if (isset($home["class_location"]) && $home["class_location"] != '') {
                            ?>
                            <p class="profile-rating" style="margin-top:0;">
                                CLASS LOCATION<br/>
                                <span><?= $home["class_location"] ?></span>
                            </p>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <hr>
                <h5>Instructor Information</h5>
                <div class="row">
                    <div class="col-sm-4 col-12">
                        <?php
                        $profile_url = BlobUtil::getAccessUrlForBlob($home["picture_blob_id"], Output::getUtilUrl('/public_blob_serve.php'));
                        if ($home["picture_blob_id"] && $home["picture_blob_id"] != "" && $profile_url) {
                            ?>
                            <div class="profile-img">
                                <img class="img-rounded" alt="<?= $home['instructor_name'] ?>"
                                     src="<?= addsession($profile_url) ?>"/>
                            </div>
                            <h4 class="font-weight-light">
                                <small class="font-weight-normal text-muted"><?= $home['prefix'] ?></small><br><?= $home['instructor_name'] ?>
                            </h4>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="profile-head col-sm-4 col-12">
                        <?php
                        if ((isset($home['phone']) && $home['phone'] !== '') || (isset($home['email']) && $home['email'] !== '')) {
                            if (isset($home['phone']) && $home['phone'] !== '') {
                                ?>
                                <p class="profile-rating mb-3 mt-2">
                                    <span class="fas fa-fw fa-phone" style="color:#818182;"></span>
                                    PHONE<br><span><?= $home['phone'] ?></span> <?= $home['preferred_contact'] == 'phone' ? ' (preferred)' : '' ?>
                                    <br/>
                                </p>
                                <?php
                            }
                            if (isset($home['email']) && $home['email'] !== '') {
                                ?>
                                <p class="profile-rating mb-3 mt-2">
                                    <span class="fas fa-fw fa-envelope" style="color:#818182;"></span>
                                    EMAIL<br><span><?= $home['email'] ?></span> <?= $home['preferred_contact'] == 'email' ? ' (preferred)' : '' ?>
                                </p>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="profile-head col-sm-4 col-12">
                        <?php
                        if (isset($home['office_location']) && $home['office_location'] !== '') {
                            ?>
                            <p class="profile-rating mb-3 mt-2">
                                <span class="fas fa-fw fa-building" style="color:#818182;"></span> OFFICE
                                LOCATION<br><span><?= $home['office_location'] ?></span>
                            </p>
                            <?php
                        }
                        $office_hours = explode(',', $home["office_hours"]);
                        if (count($office_hours) > 0) {
                            ?>
                            <p class="profile-rating mb-3 mt-2">
                                <span class="fas fa-fw fa-clock" style="color:#818182;"></span> OFFICE HOURS<br/>
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
            <!-- Right column -->
            <div class="col-12">
                <?php
                if (isset($home["course_desc"]) && $home["course_desc"] != '') {
                    ?>
                    <hr>
                    <h5>Course Description</h5>
                    <p>
                        <?= $home['course_desc'] ?>
                    </p>
                    <?php
                }
                if (isset($home['course_video']) && $home['course_video'] !== '') {
                    ?>
                    <div class="videoWrapper">
                        <!-- Copy & Pasted from YouTube -->
                        <iframe height="360" width="640" src="<?= $home['course_video'] ?>" frameborder="0"
                                scrolling="0" allow="autoplay; encrypted-media; fullscreen;  picture-in-picture;"
                                allowfullscreen></iframe>
                    </div>
                    <?php
                }
                if (isset($home['getting_started']) && $home['getting_started'] !== '') {
                    ?>
                    <hr/>
                    <h5>Getting Started</h5>
                    <p>
                        <?= $home['getting_started'] ?>
                    </p>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
<?php
$OUTPUT->footer();