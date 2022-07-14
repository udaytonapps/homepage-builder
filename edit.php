<?php
require_once "../config.php";

use Tsugi\Blob\BlobUtil;
use Tsugi\Core\LTIX;

$p = $CFG->dbprefix;

// Sometimes, if the maxUpload_SIZE is exceeded, it deletes all of $_POST
// Thus losing our session :(
if ($_SERVER['REQUEST_METHOD'] == 'POST' && count($_POST) == 0) {
    die('Error: Maximum size of ' . BlobUtil::maxUpload() . 'MB exceeded.');
}

$LAUNCH = LTIX::requireData();

$homeStmt = $PDOX->prepare("SELECT * FROM {$p}course_home WHERE link_id = :linkId");
$homeStmt->execute(array(":linkId" => $LINK->id));
$home = $homeStmt->fetch(PDO::FETCH_ASSOC);

if ($home) {
    $sections = $home["sections"];
    $meetings = $home["meetings"];
    $class_location = $home["class_location"];
    $start_date = $home["start_date"];
    $end_date = $home["end_date"];
    $course_title = $home["course_title"];
    $course_desc = $home["course_desc"];
    $course_video = $home["course_video"];
    $prefix = $home["prefix"];
    $instructor_name = $home["instructor_name"];
    $office_location = $home["office_location"];
    $phone = $home["phone"];
    $email = $home["email"];
    $preferred_contact = $home["preferred_contact"];
    $office_hours = $home["office_hours"];
    $getting_started = $home["getting_started"];
    $about_me = $home["about_me"];
    $_SESSION['syllabus'] = isset($home["syllabus_blob_id"]) && $home["syllabus_blob_id"] != null ? BlobUtil::getAccessUrlForBlob($home["syllabus_blob_id"]) : false;
    $_SESSION['schedule'] = isset($home["schedule_blob_id"]) && $home["schedule_blob_id"] != null ? BlobUtil::getAccessUrlForBlob($home["schedule_blob_id"]) : false;
    $_SESSION['picture'] = isset($home["picture_blob_id"]) && $home["picture_blob_id"] != null ? BlobUtil::getAccessUrlForBlob($home["picture_blob_id"]) : false;
} else {
    $sections = '';
    $meetings = '';
    $class_location = '';
    $start_date = '';
    $end_date = '';
    $course_title = $CONTEXT->title;
    $course_desc = '';
    $course_video = '';
    $prefix = '';
    $instructor_name = $USER->displayname;
    $office_location = '';
    $phone = '';
    $email = $USER->email;
    $preferred_contact = '';
    $office_hours = '';
    $getting_started = '';
    $about_me = '';
    $_SESSION['syllabus'] = false;
    $_SESSION['schedule'] = false;
    $_SESSION['picture'] = false;
}

// Other times, we see an error indication on bad upload that does not delete all the $_POST
// Upload syllabus file and add blob id to session
if (isset($_FILES['syllabus']) && $_FILES['syllabus']['error'][0] == 1) {
    $_SESSION['error'] = 'Error: Maximum size of ' . BlobUtil::maxUpload() . 'MB exceeded.';
    header('Location: ' . addSession('edit.php'));
    return;
}
if (isset($_FILES['syllabus']) && $_FILES['syllabus']['error'][0] == 0) {
    $fdes = $_FILES['syllabus'];

    $filename = isset($fdes['name'][0]) ? basename($fdes['name'][0]) : false;

    $fdes['name'] = $fdes['name'][0];
    $fdes['type'] = $fdes['type'][0];
    $fdes['tmp_name'] = $fdes['tmp_name'][0];
    $fdes['error'] = $fdes['error'][0];
    $fdes['size'] = $fdes['size'][0];

    // Sanity-check the file
    $safety = BlobUtil::validateUpload($fdes);
    if ($safety !== true) {
        $_SESSION['error'] = "Error: " . $safety;
        error_log("Upload Error: " . $safety);
        header('Location: ' . addSession('edit.php'));
        return;
    }

    $blob_id = BlobUtil::uploadToBlob($fdes);
    if ($blob_id === false) {
        $_SESSION['error'] = 'Problem storing file in server: ' . $filename;
        header('Location: ' . addSession('edit.php'));
        return;
    }

    $_SESSION['syllabus_blob'] = $blob_id;
}

// Upload schedule file and add blob id to session
if (isset($_FILES['schedule']) && $_FILES['schedule']['error'][0] == 1) {
    $_SESSION['error'] = 'Error: Maximum size of ' . BlobUtil::maxUpload() . 'MB exceeded.';
    header('Location: ' . addSession('edit.php'));
    return;
}
if (isset($_FILES['schedule']) && $_FILES['schedule']['error'][0] == 0) {
    $fdes = $_FILES['schedule'];

    $filename = isset($fdes['name'][0]) ? basename($fdes['name'][0]) : false;

    $fdes['name'] = $fdes['name'][0];
    $fdes['type'] = $fdes['type'][0];
    $fdes['tmp_name'] = $fdes['tmp_name'][0];
    $fdes['error'] = $fdes['error'][0];
    $fdes['size'] = $fdes['size'][0];

    // Sanity-check the file
    $safety = BlobUtil::validateUpload($fdes);
    if ($safety !== true) {
        $_SESSION['error'] = "Error: " . $safety;
        error_log("Upload Error: " . $safety);
        header('Location: ' . addSession('edit.php'));
        return;
    }

    $blob_id = BlobUtil::uploadToBlob($fdes);
    if ($blob_id === false) {
        $_SESSION['error'] = 'Problem storing file in server: ' . $filename;
        header('Location: ' . addSession('edit.php'));
        return;
    }

    $_SESSION['schedule_blob'] = $blob_id;
}

// Upload profile picture file and add blob id to session
if (isset($_FILES['picture']) && $_FILES['picture']['error'][0] == 1) {
    $_SESSION['error'] = 'Error: Maximum size of ' . BlobUtil::maxUpload() . 'MB exceeded.';
    header('Location: ' . addSession('edit.php'));
    return;
}
if (isset($_FILES['picture']) && $_FILES['picture']['error'][0] == 0) {
    $fdes = $_FILES['picture'];

    $filename = isset($fdes['name'][0]) ? basename($fdes['name'][0]) : false;

    $fdes['name'] = $fdes['name'][0];
    $fdes['type'] = $fdes['type'][0];
    $fdes['tmp_name'] = $fdes['tmp_name'][0];
    $fdes['error'] = $fdes['error'][0];
    $fdes['size'] = $fdes['size'][0];

    // Sanity-check the file
    $safety = BlobUtil::validateUpload($fdes);
    if ($safety !== true) {
        $_SESSION['error'] = "Error: " . $safety;
        error_log("Upload Error: " . $safety);
        header('Location: ' . addSession('edit.php'));
        return;
    }

    $blob_id = BlobUtil::uploadToBlob($fdes);
    if ($blob_id === false) {
        $_SESSION['error'] = 'Problem storing file in server: ' . $filename;
        header('Location: ' . addSession('edit.php'));
        return;
    }

    $_SESSION['picture_blob'] = $blob_id;
}

// File should already be uploaded if there was a new one
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {

    $sections = isset($_POST["sections"]) ? $_POST["sections"] : "";
    $meetings = isset($_POST["meetings"]) ? $_POST["meetings"] : "";
    $classLocation = isset($_POST["class_location"]) ? $_POST["class_location"] : "";
    $startDate = null;
    if (isset($_POST["start_date"]) && $_POST["start_date"] != "") {
        $timestamp = strtotime($_POST["start_date"]);
        $startDate = date("Y-m-d H:i:s", $timestamp);
    }
    $endDate = null;
    if (isset($_POST["end_date"]) && $_POST["end_date"] != "") {
        $timestamp = strtotime($_POST["end_date"]);
        $endDate = date("Y-m-d H:i:s", $timestamp);
    }
    $title = isset($_POST["course_title"]) ? $_POST["course_title"] : "";
    $description = isset($_POST["course_desc"]) ? $_POST["course_desc"] : "";
    $videoUrl = isset($_POST["video_url"]) ? $_POST["video_url"] : "";

    $syllabusFileId = isset($_SESSION["syllabus_blob"]) ? intval($_SESSION["syllabus_blob"]) : null;
    $scheduleFileId = isset($_SESSION["schedule_blob"]) ? intval($_SESSION["schedule_blob"]) : null;
    $pictureFileId = isset($_SESSION["picture_blob"]) ? intval($_SESSION["picture_blob"]) : null;

    $prefix = isset($_POST["prefix"]) ? $_POST["prefix"] : "";
    $name = isset($_POST["instructor_name"]) ? $_POST["instructor_name"] : "";
    $location = isset($_POST["office_location"]) ? $_POST["office_location"] : "";
    $phone = isset($_POST["phone"]) ? $_POST["phone"] : "";
    $email = isset($_POST["email"]) ? $_POST["email"] : "";
    $preferred = isset($_POST["preferred"]) ? $_POST["preferred"] : "";
    $hours = isset($_POST["office_hours"]) ? $_POST["office_hours"] : "";

    $getStarted = isset($_POST["getting_started"]) ? $_POST["getting_started"] : "";
    $aboutMe = isset($_POST["about_me"]) ? $_POST["about_me"] : "";

    if (!$home) {
        // Homepage was never created so insert
        $insertStmt = $PDOX->prepare("INSERT INTO {$p}course_home (link_id, context_id, user_id, sections, meetings, class_location, start_date, end_date, course_title, course_desc, course_video, syllabus_blob_id, schedule_blob_id, picture_blob_id, prefix, instructor_name, office_location, phone, email, preferred_contact, office_hours, getting_started, about_me) values (:link_id, :context_id, :user_id, :sections, :meetings, :class_location, :start_date, :end_date, :course_title, :course_desc, :course_video, :syllabus_blob_id, :schedule_blob_id, :picture_blob_id, :prefix, :instructor_name, :office_location, :phone, :email, :preferred_contact, :office_hours, :getting_started, :about_me)");
        $insertStmt->execute(array(
            ":link_id" => $LINK->id,
            ":context_id" => $CONTEXT->id,
            ":user_id" => $USER->id,
            ":sections" => $sections,
            ":meetings" => $meetings,
            ":class_location" => $classLocation,
            ":start_date" => $startDate,
            ":end_date" => $endDate,
            ":course_title" => $title,
            ":course_desc" => $description,
            ":course_video" => $videoUrl,
            ":syllabus_blob_id" => $syllabusFileId,
            ":schedule_blob_id" => $scheduleFileId,
            ":picture_blob_id" => $pictureFileId,
            ":prefix" => $prefix,
            ":instructor_name" => $name,
            ":office_location" => $location,
            ":phone" => $phone,
            ":email" => $email,
            ":preferred_contact" => $preferred,
            ":office_hours" => $hours,
            ":getting_started" => $getStarted,
            ":about_me" => $aboutMe
        ));
    } else {
        // First delete old blobs before adding new ones
        $delSyllabus = $PDOX->prepare("DELETE FROM {$p}blob_file WHERE file_id = :syllabusId");
        $delSyllabus->execute(array(":syllabusId" => $home["syllabus_blob_id"]));

        $delSchedule = $PDOX->prepare("DELETE FROM {$p}blob_file WHERE file_id = :scheduleId");
        $delSchedule->execute(array(":scheduleId" => $home["schedule_blob_id"]));

        $delPicture = $PDOX->prepare("DELETE FROM {$p}blob_file WHERE file_id = :pictureId");
        $delPicture->execute(array(":pictureId" => $home["picture_blob_id"]));

        // Homepage previously existed so update
        $updateStmt = $PDOX->prepare("UPDATE {$p}course_home SET 
                                        sections = :sections,
                                        meetings = :meetings,
                                        class_location = :class_location,
                                        start_date = :start_date,
                                        end_date = :end_date,
                                        course_title = :course_title,
                                        course_desc = :course_desc,
                                        course_video = :course_video,
                                        syllabus_blob_id = :syllabus_blob_id,
                                        schedule_blob_id = :schedule_blob_id,
                                        picture_blob_id = :picture_blob_id,
                                        prefix = :prefix,
                                        instructor_name = :instructor_name,
                                        office_location = :office_location,
                                        phone = :phone,
                                        email = :email,
                                        preferred_contact = :preferred_contact,
                                        office_hours = :office_hours,
                                        getting_started = :getting_started,
                                        about_me = :about_me
                                        WHERE link_id = :link_id");
        $updateStmt->execute(array(
            ":sections" => $sections,
            ":meetings" => $meetings,
            ":class_location" => $classLocation,
            ":start_date" => $startDate,
            ":end_date" => $endDate,
            ":course_title" => $title,
            ":course_desc" => $description,
            ":course_video" => $videoUrl,
            ":syllabus_blob_id" => $syllabusFileId,
            ":schedule_blob_id" => $scheduleFileId,
            ":picture_blob_id" => $pictureFileId,
            ":prefix" => $prefix,
            ":instructor_name" => $name,
            ":office_location" => $location,
            ":phone" => $phone,
            ":email" => $email,
            ":preferred_contact" => $preferred,
            ":office_hours" => $hours,
            ":getting_started" => $getStarted,
            ":about_me" => $aboutMe,
            ":link_id" => $LINK->id
        ));
    }

    $_SESSION["success"] = "Homepage saved successfully.";
    header('Location: ' . addSession('index.php'));
    return;
}

$OUTPUT->header();
?>
    <style>
        .course-description .ck-editor__editable_inline {
            min-height: 140px;
        }
        .tab-content {
            padding: 1rem;
            border: 1px solid #ddd;
            border-top: none;
        }
        .nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus {
            color: #333;
            opacity: 1;
        }

        .ck-editor__editable_inline {
            min-height: 200px;
        }
        .modal-header, .modal-body, .modal-footer {
            background-color: var(--background-focus);
        }
        .modal-header .close {
            color: white;
            opacity: .7;
        }
    </style>
<?php
$OUTPUT->bodyStart();

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();
?>
    <div class="pull-right" style="padding-top:1rem;">
        <a href="#importModal" class="btn btn-link" data-toggle="modal"><span class="fas fa-file-import" aria-hidden="true"></span>
        Import from Previous Site</a>
    </div>
    <h3>Edit Homepage Information</h3>
    <p>Use the form below to add information to your homepage. The information has been divided into four
        tabs for ease of entry.</p>
    <form action="<?php addSession('edit.php'); ?>" method="post" enctype="multipart/form-data"
          style="padding-bottom: 1rem;">
        <ul class="nav nav-tabs">
            <li class="active"><a id="details-tab-link" data-toggle="tab" href="#details">1. Course Details <span class="fa fa-arrow-right" aria-hidden="true"></span></a></li>
            <li><a id="instructor-tab-link" data-toggle="tab" href="#instructor">2. Instructor Info. <span class="fa fa-arrow-right" aria-hidden="true"></span></a></li>
            <li><a id="desc-tab-link" data-toggle="tab" href="#desc">3. Course Description <span class="fa fa-arrow-right" aria-hidden="true"></span></a></li>
            <li><a id="started-tab-link" data-toggle="tab" href="#started">4. Getting Started <span class="fa fa-save" aria-hidden="true"></span></a></li>
        </ul>

        <div class="tab-content">
            <div id="details" class="tab-pane fade in active">
                <h4>Course Details</h4>
                <div class="form-group">
                    <label for="course_title">Title</label>
                    <input type="text" class="form-control" id="course_title" name="course_title"
                           placeholder="e.g. Introduction to Philosophy" value="<?= $course_title ?>">
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label for="syllabus">Syllabus</label>
                            <input type="file" class="filepond" id="syllabus" name="syllabus[]">
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label for="schedule">Schedule</label>
                            <input type="file" class="filepond" id="schedule" name="schedule[]">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="sections">Section(s) <br/><small class="text-muted">Use comma to separate sections to separate
                            lines</small></label>
                    <input type="text" class="form-control" id="sections" name="sections"
                           placeholder="e.g. PHL 103 01, PHL 103 02" value="<?= $sections ?>">
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label for="start">Start Date</label>
                            <input type="text" class="form-control" id="start" name="start_date" autocomplete="off"
                                   value="<?= $start_date ?>">
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label for="end">End Date</label>
                            <input type="text" class="form-control" id="end" name="end_date" autocomplete="off"
                                   value="<?= $end_date ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="meetings">Meetings Times<br/><small class="text-muted">Use comma to
                            separate meeting days to separate lines</small></label>
                    <input type="text" class="form-control" id="meetings" name="meetings"
                           value="<?= $meetings ?>" placeholder="e.g. MWF 10:10a - 12:05p, TR 1:00p - 2:15p">
                </div>
                <div class="form-group">
                    <label for="class_location">Location</label>
                    <input type="text" class="form-control" id="class_location" name="class_location"
                           placeholder="e.g. MH 103 / Online via Zoom" value="<?= $class_location ?>">
                </div>
                <hr>
                <a id="details-next" class="btn btn-link" data-toggle="tab" href="javascript:void(0);">Next Section <span class="fa fa-arrow-right" aria-hidden="true"></span></a>
            </div>
            <div id="instructor" class="tab-pane fade">
                <h4>Instructor Information</h4>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label for="prefix">Title</label>
                            <select class="form-control" id="prefix" name="prefix">
                                <option value="">None</option>
                                <option <?= $prefix == 'Professor' ? 'selected' : '' ?>>Professor</option>
                                <option <?= $prefix == 'Instructor' ? 'selected' : '' ?>>Instructor</option>
                                <option <?= $prefix == 'Prof.' ? 'selected' : '' ?>>Prof.</option>
                                <option <?= $prefix == 'Dr.' ? 'selected' : '' ?>>Dr.</option>
                                <option <?= $prefix == 'Mr.' ? 'selected' : '' ?>>Mr.</option>
                                <option <?= $prefix == 'Mrs.' ? 'selected' : '' ?>>Mrs.</option>
                                <option <?= $prefix == 'Ms.' ? 'selected' : '' ?>>Ms.</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="instructor_name">Name</label>
                            <input type="text" class="form-control" id="instructor_name" name="instructor_name"
                                   value="<?= $instructor_name ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   placeholder="e.g. (937) 229-2074" value="<?= $phone ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" class="form-control" id="email" name="email"
                                   value="<?= $email ?>">
                        </div>
                        <h5>Preferred Method of Contact</h5>
                        <div class="radio">
                            <label><input type="radio" value="none"
                                          name="preferred" <?= $preferred_contact != 'phone' && $preferred_contact != 'email' ? 'checked' : '' ?>>No
                                Preference</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" value="phone"
                                          name="preferred" <?= $preferred_contact == 'phone' ? 'checked' : '' ?>>Phone</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" value="email"
                                          name="preferred" <?= $preferred_contact == 'email' ? 'checked' : '' ?>>Email</label>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <label for="picture">Profile Picture</label>
                            <input type="file" class="filepond" id="picture" name="picture[]">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="office_location">Office Location</label>
                    <input type="text" class="form-control" id="office_location" name="office_location"
                           placeholder="e.g. LTC 030" value="<?= $office_location ?>">
                </div>
                <div class="form-group">
                    <label for="office_hours">Office Hours<br/><small>Use a comma to
                            separate office hours to separate lines</small></label>
                    <input type="text" class="form-control" id="office_hours" name="office_hours"
                           value="<?= $office_hours ?>" placeholder="e.g. MWF 2:00p - 3:15p, Tues. 10:00a - 11:00a">
                </div>
                <hr>
                <a id="instructor-next" class="btn btn-link" data-toggle="tab" href="javascript:void(0);">Next Section <span class="fa fa-arrow-right" aria-hidden="true"></span></a>
            </div>
            <div id="desc" class="tab-pane fade">
                <h4>Course Description</h4>
                <div class="form-group course-description">
                    <label for="course_desc">At a high level, what would you want students to know about this course?</label>
                    <textarea class="form-control" rows="5" id="course_desc"
                              name="course_desc"><?= $course_desc ?></textarea>
                </div>
                <div class="form-group">
                    <label for="video_url">Intro Video URL</label>
                    <input type="text" class="form-control" id="video_url" name="video_url"
                           placeholder="e.g. https://udayton.warpwire.com/w/bTsBAA/" value="<?= $course_video ?>">
                </div>
                <hr>
                <a id="desc-next" class="btn btn-link" data-toggle="tab" href="javascript:void(0);">Next Section <span class="fa fa-arrow-right" aria-hidden="true"></span></a>
            </div>
            <div id="started" class="tab-pane fade">
                <h4>Getting Started</h4>
                <div class="form-group">
                    <label for="getting_started">How should students get started in your course or Isidore site?</label>
                    <textarea class="form-control" rows="5" id="getting_started"
                              name="getting_started"><?= $getting_started ?></textarea>
                </div>
                <hr>
                <p><em>Click "Save" below to save your changes and return to the main page.</em></p>
            </div>
        </div>
        <hr>
        <h5>All finished? Click "Save" to save your changes on all tabs and return to the main page.</h5>
        <button type="submit" name="save" class="btn btn-primary">Save</button>
        <a href="<?= addSession("index.php") ?>" class="btn btn-default">Cancel</a>
    </form>
<?php
echo '</div>'; // End container
// Get all course homes for user
$allHomesStmt = $PDOX->prepare("SELECT * FROM {$p}course_home WHERE user_id = :userId AND link_id != :linkId ORDER BY course_title");
$allHomesStmt->execute(array(":userId" => $USER->id, ":linkId" => $LINK->id));
$allHomes = $allHomesStmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <div id="importModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Import Homepage Content from Previous Site</h4>
                </div>
                <form action="import.php" method="post">
                    <div class="modal-body">
                        <p class="alert alert-warning">Please note that any syllabus, schedule, and profile picture
                            files from the previous site will not be imported and will need to be re-uploaded to this
                            site.</>
                        <?php
                        if ($allHomes) {
                            echo '<div class="form-group"><label for="importSite">Select Homepage to Import</label><select class="form-control" id="importSite" name="importSite">';
                            foreach ($allHomes as $prevHome) {
                                $sitestmt = $PDOX->prepare("SELECT title FROM {$p}lti_context WHERE context_id = :contextId;");
                                $sitestmt->execute(array(":contextId" => $prevHome["context_id"]));
                                $site = $sitestmt->fetch(PDO::FETCH_ASSOC);
                                if (!$site) {
                                    $title = $prevHome["course_title"];
                                } else {
                                    $title = $site["title"];
                                }
                                echo '<option value="' . $prevHome["home_id"] . '">' . $title . '</option>';
                            }
                            echo '</select></div>';
                        } else {
                            echo '<p><em>You do not have any previously completed homepages to import from.</em></p>';
                        }
                        ?>
                    </div>
                    <div class="modal-footer">
                        <button type="<?= $allHomes ? 'submit' : 'button' ?>"
                                class="btn btn-primary <?= $allHomes ? '' : 'disabled' ?>">Submit
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
<?php

$OUTPUT->footerStart();
?>
    <script src="https://cdn.ckeditor.com/ckeditor5/19.0.0/classic/ckeditor.js"></script>
    <script>
        $(document).ready(function () {
            $("#details-next").on("click", function() {
                $("#instructor-tab-link").click();
            });
            $("#instructor-next").on("click", function() {
                $("#desc-tab-link").click();
            });
            $("#desc-next").on("click", function() {
                $("#started-tab-link").click();
            });

            $("#start").datepicker();
            $("#end").datepicker();
            ClassicEditor
                .create(document.querySelector('#course_desc'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
                    link: {
                        addTargetToExternalLinks: true
                    }
                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#getting_started'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
                    link: {
                        addTargetToExternalLinks: true
                    }
                })
                .catch(error => {
                    console.error(error);
                });
        });

        FilePond.registerPlugin(
            FilePondPluginFileEncode,
            FilePondPluginFileValidateSize,
            FilePondPluginFileValidateType,
            FilePondPluginImageExifOrientation,
            FilePondPluginImagePreview,
            FilePondPluginImageEdit,
            FilePondPluginImageCrop,
            FilePondPluginImageResize,
            FilePondPluginImageTransform
        );

        Doka.setOptions({
            labelStatusAwaitingImage: 'Waiting for image…',
            labelStatusLoadImageError: 'Error loading image…',
            labelStatusLoadingImage: 'Loading image…',
            labelStatusProcessingImage: 'Processing image…'
        });

        const doka = Doka.create({
            cropResizeScrollRectOnly: true
        });

        const pond_picture = FilePond.create(document.querySelector('#picture'), {
            acceptedFileTypes: ['image/*'],
            labelIdle: '<span class="filepond-main-label">Drag & Drop Your Photo or Click to Browse</span>',
            imageResizeTargetHeight: 400,
            imageEditEditor: doka,
            imageEditInstantEdit: false,
            server: {
                process: 'edit.php?PHPSESSID=<?php echo session_id() ?>',
                revert: 'delete-picture.php?PHPSESSID=<?php echo session_id() ?>'
            },
            files: [
                <?php
                if ($_SESSION['picture'] && $_SESSION['picture'] != null) {
                    echo '{source: "' . addSession($_SESSION['picture']) . '"}';
                }
                ?>
            ]
        });

        const pond_syllabus = FilePond.create(document.querySelector('#syllabus'), {
            server: {
                process: 'edit.php?PHPSESSID=<?php echo session_id() ?>',
                revert: 'delete-syllabus.php?PHPSESSID=<?php echo session_id() ?>'
            },
            files: [
                <?php
                if ($_SESSION['syllabus'] && $_SESSION['syllabus'] != null) {
                    echo '{source: "' . addSession($_SESSION['syllabus']) . '"}';
                }
                ?>
            ]
        });

        const pond_schedule = FilePond.create(document.querySelector('#schedule'), {
            server: {
                process: 'edit.php?PHPSESSID=<?php echo session_id() ?>',
                revert: 'delete-schedule.php?PHPSESSID=<?php echo session_id() ?>'
            },
            files: [
                <?php
                if ($_SESSION['schedule'] && $_SESSION['schedule'] != null) {
                    echo '{source: "' . addSession($_SESSION['schedule']) . '"}';
                }
                ?>
            ]
        });
    </script>
<?php
$OUTPUT->footerEnd();