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
    <style>
        .profile-img {
            padding: 1rem 2rem;
            text-align: center;
        }

        .profile-rating {
            margin-top: 2em;
            overflow-wrap: break-word;
        }

        .profile-rating div {
            font-weight: 600;
            padding-left: 22px;
        }

        .profile-rating span:first-of-type {
            padding-left: 0;
        }

        .profile-work p {
            font-weight: 600;
            margin-top: 1em;
        }

        .profile-work ul {
            list-style: none;
        }

        .videoWrapper {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 */
            height: 0;
            margin-left: 4rem;
            margin-right: 4rem;
            border: 2px solid var(--background-focus);
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

        .page-banner {
            width: 100%;
            height: 80px;
            padding: 0;
            margin: 0;
            background: var(--primary);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .inst-profile-image {
            max-width: 100%;
        }
        .page-edit-button {
            top: 5px;
            right: 5px;
            position: absolute;
        }
        .edit-btn {
            background-color: white;
            color: black;
        }
        .download-btn, .download-btn:hover {
            font-weight: 400;
            font-size: 1rem;
        }
    </style>
<?php
$OUTPUT->bodyStart();

$OUTPUT->flashMessages();
if ($USER->instructor) {
    ?>
    <div class="page-edit-button">
        <a href="edit.php" class="btn edit-btn"><span class="far fa-edit" aria-hidden="true"></span> Edit</a>
    </div>
    <?php
}
?>
    <div class="page-banner">
        <h4 class="text-white inline-block p-2 pt-4 text-center">
            <?= $home['course_title'] ?>
        </h4>
    </div>
    <div style="padding:1em 2em;">
        <?php
        $sections = $home["sections"] ? explode(',', $home["sections"]) : false;
        $meetings = $home["meetings"] ? explode(',', $home["meetings"]) : false;
        if (($home["syllabus_blob_id"] && $home["syllabus_blob_id"] != "") || ($home["schedule_blob_id"] && $home["schedule_blob_id"] != "") || ($sections && count($sections) > 0) || (isset($home['start_date']) && $home["start_date"] != '') || (isset($home['end_date']) && $home["end_date"] != '') || ($meetings && count($meetings) > 0) || (isset($home["class_location"]) && $home["class_location"] != ''))
        {
        ?>
        <div class="row"> <!-- Course details row -->
            <div class="col-12"><h5 class="mb-3">Course Details</h5></div>
            <?php
            if (($home["syllabus_blob_id"] && $home["syllabus_blob_id"] != "") || ($home["schedule_blob_id"] && $home["schedule_blob_id"] != "")) {
            ?>
            <div class="col-md-4 col-sm-5 col-12">
                    <div class="profile-rating text-uppercase" style="margin-top:0;">
                        <span class="far fa-fw fa-file-alt" aria-hidden="true"></span> Course Documents</div>
                    <div class="profile-work pb-2">
                        <?php
                        $syllabus_url = BlobUtil::getAccessUrlForBlob($home["syllabus_blob_id"], Output::getUtilUrl('/public_blob_serve.php'));
                        if ($home["syllabus_blob_id"] && $home["syllabus_blob_id"] != "" && $syllabus_url) {
                            ?>
                            <p class="mb-1">
                                <a class="download-btn btn btn-outline-background btn-block"
                                   href="<?= addsession($syllabus_url) ?>"><span class="fa fa-download"
                                                                                 aria-hidden="true"></span> Download
                                    Syllabus</a>
                            </p>
                            <?php
                        }
                        $schedule_url = BlobUtil::getAccessUrlForBlob($home["schedule_blob_id"], Output::getUtilUrl('/public_blob_serve.php'));
                        if ($home["schedule_blob_id"] && $home["schedule_blob_id"] != "" && $schedule_url) {
                            ?>
                            <p class="mb-1">
                                <a class="download-btn btn btn-outline-background btn-block"
                                   href="<?= addSession($schedule_url) ?>"><span class="fa fa-download"
                                                                                 aria-hidden="true"></span> Download
                                    Schedule</a>
                            </p>
                            <?php
                        }
                        ?>
                    </div>
            </div>
                <?php
            }
            ?>
            <div class="col-md-8 col-sm-7 col-12">
                <div class="row">
                    <?php
                    if (($sections && count($sections) > 0) || (isset($home['start_date']) && $home["start_date"] != '') || (isset($home['end_date']) && $home["end_date"] != '')) {
                    ?>
                    <div class="col-sm-6 col-12">
                        <?php
                        if ($sections && count($sections) > 0) {
                            ?>
                            <div class="profile-rating pb-3" style="margin-top:0;">
                                <span class="fas fa-fw fa-cube" ></span>
                                SECTIONS
                                <?php
                                foreach ($sections as $section) {
                                    echo '<div>' . $section . '</div>';
                                }
                                ?>
                            </div>
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
                            <div class="profile-rating pb-3" style="margin-top:0;">
                                <span class="far fa-fw fa-calendar" ></span>
                                DATES
                                <div>
                            <?= $formattedStartDate ?>
                            <?= $formattedStartDate != '' && $formattedEndDate != '' ? ' - ' : '' ?>
                            <?= $formattedEndDate ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                    }
                    if (($meetings && count($meetings) > 0) || (isset($home["class_location"]) && $home["class_location"] != '')) {
                    ?>
                    <div class="col-sm-6 col-12">
                        <?php
                        if ($meetings && count($meetings) > 0) {
                            ?>
                            <div class="profile-rating pb-3" style="margin-top:0;">
                                <span class="far fa-fw fa-clock" ></span>
                                CLASS TIMES
                                <?php
                                foreach ($meetings as $meeting) {
                                    echo '<div>' . $meeting . '</div>';
                                }
                                ?>
                            </div>
                            <?php
                        }
                        if (isset($home["class_location"]) && $home["class_location"] != '') {
                            ?>
                            <div class="profile-rating pb-3" style="margin-top:0;">
                                <span class="far fa-fw fa-building" ></span>
                                CLASS LOCATION
                                <div><?= $home["class_location"] ?></div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        }
        $profile_url = BlobUtil::getAccessUrlForBlob($home["picture_blob_id"], Output::getUtilUrl('/public_blob_serve.php'));
        $office_hours = $home["office_hours"] ? explode(',', $home["office_hours"]) : false;
        $addtl_contacts = $home["addtl_contacts"] ? explode(',', $home["addtl_contacts"]) : false;
        if (($home["picture_blob_id"] && $home["picture_blob_id"] != "" && $profile_url) ||
        ($home['instructor_name'] && $home['instructor_name'] != "") ||
        (isset($home['phone']) && $home['phone'] !== '') || (isset($home['email']) && $home['email'] !== '') ||
        (isset($home['office_location']) && $home['office_location'] !== '') || ($office_hours && count($office_hours) > 0))
        {
        ?>
        <div> <!-- Instructor info row -->
            <div class="col-12">
                <hr>
                <h5 class="mb-2">Instructor Information</h5>
                <div class="row">
                    <?php
                    if ($home["picture_blob_id"] && $home["picture_blob_id"] != "" && $profile_url) {
                        ?>
                        <div class="col-sm-4 col-xs-5">
                            <div class="profile-img">
                                <img class="inst-profile-image img-rounded img-fluid" alt="<?= $home['instructor_name'] ?>"
                                     src="<?= addsession($profile_url) ?>"/>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="col-sm-8 col-xs-7">
                        <div>
                            <?php
                            if ($home['instructor_name'] && $home['instructor_name'] != ""){
                            ?>
                            <div class="col-12">
                                <h4 class="font-weight-light">
                                    <small class="font-weight-normal"><?= $home['prefix'] ?> </small> <?= $home['instructor_name'] ?>
                                </h4>
                            </div>
                            <?php
                            }
                            if ((isset($home['phone']) && $home['phone'] !== '') || (isset($home['email']) && $home['email'] !== '')) {
                            ?>
                            <div class="col-sm-6 col-12">
                                <?php
                                    if (isset($home['phone']) && $home['phone'] !== '') {
                                        ?>
                                        <div class="profile-rating pb-3 mt-0">
                                            <span class="fas fa-fw fa-phone" ></span>
                                            PHONE<div><?= $home['phone'] ?></div> <?= $home['preferred_contact'] == 'phone' ? '<div>(preferred)</div>' : '' ?>
                                        </div>
                                        <?php
                                    }
                                    if (isset($home['email']) && $home['email'] !== '') {
                                        ?>
                                        <div class="profile-rating pb-3 mt-0">
                                            <span class="fas fa-fw fa-envelope" ></span>
                                            EMAIL<div><?= $home['email'] ?></div> <?= $home['preferred_contact'] == 'email' ? '<div>(preferred)</div>' : '' ?>
                                        </div>
                                        <?php
                                    }
                                ?>
                            </div>
                            <?php
                            }
                            if ((isset($home['office_location']) && $home['office_location'] !== '') || ($office_hours && count($office_hours) > 0))
                            {
                            ?>
                            <div class="col-sm-6 col-12">
                                <?php
                                if (isset($home['office_location']) && $home['office_location'] !== '') {
                                    ?>
                                    <div class="profile-rating pb-3 mt-0">
                                        <span class="fas fa-fw fa-building" ></span> OFFICE
                                        LOCATION<div><?= $home['office_location'] ?></div>
                                    </div>
                                    <?php
                                }
                                if ($office_hours && count($office_hours) > 0) {
                                    ?>
                                    <div class="profile-rating pb-3 mt-0">
                                        <span class="fas fa-fw fa-clock" ></span> OFFICE HOURS
                                        <?php
                                        foreach ($office_hours as $hrs) {
                                            echo '<div>' . $hrs . '</div>';
                                        }
                                        ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                            }
                            if ($addtl_contacts && count($addtl_contacts) > 0) {
                            ?>
                                <div class="col-sm-6 col-12">
                                    <div class="profile-rating pb-3 mt-0">
                                        <span class="fas fa-fw fa-users" ></span> Additional Contacts
                                        <?php
                                        foreach ($addtl_contacts as $contact) {
                                            echo '<div>' . $contact . '</div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            }
            ?>
            <!-- Right column -->
            <div class="col-12">
                <?php
                if (isset($home["course_desc"]) && $home["course_desc"] != '') {
                    ?>
                    <hr>
                    <h5 class="mb-3">Course Description</h5>
                    <div>
                        <?= $home['course_desc'] ?>
                    </div>
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
                    <h5 class="mb-3">Getting Started</h5>
                    <div>
                        <?= $home['getting_started'] ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
<?php
$OUTPUT->footer();