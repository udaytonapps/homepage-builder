<?php
require_once "../../config.php";

use Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

function getNewFileId($CONTEXT, $LINK, $PDOX, $p, $oldId) {
    //TODO: We are skipping this for now until there's a better way to do it.
    if (FALSE && $oldId !== NULL) {
        $fileStmt = $PDOX->prepare("SELECT * FROM {$p}blob_file WHERE file_id = :file_id");
        $fileStmt->execute(array(":file_id" => $oldId));
        $file = $fileStmt->fetch(PDO::FETCH_ASSOC);

        $blobStmt = $PDOX->prepare("INSERT INTO {$p}blob_blob (blob_sha256, deleted, content, created_at, accessed_at) 
            SELECT blob_sha256, deleted, content, created_at, accessed_at FROM {$p}blob_blob WHERE blob_id = :blob_id");
        $blobStmt->execute(array(":blob_id" => $file["blob_id"]));
        $newBlobId = $PDOX->lastInsertId();

        $newFileStmt = $PDOX->prepare("INSERT INTO {$p}blob_file f1 (file_sha256, context_id, link_id, file_name, bytelen, deleted, contenttype, path, blob_id, json, created_at, accessed_at) 
            values (:file_sha256, :context_id, :link_id, :file_name, :bytelen, :deleted, :contenttype, :path, :blob_id, :json, :created_at, :accessed_at)");
        $newFileStmt->execute(array(
            ":file_sha256" => $file["file_sha256"],
            ":context_id" => $CONTEXT->id,
            ":link_id" => $LINK->id,
            ":file_name" => $file["file_name"],
            ":bytelen" => $file["bytelen"],
            ":deleted" => $file["deleted"],
            ":contenttype" => $file["contenttype"],
            ":path" => $file["path"],
            ":blob_id" => $newBlobId,
            ":json" => $file["json"],
            ":created_at" => $file["created_at"],
            ":accessed_at" => $file["accessed_at"]
        ));
        return $PDOX->lastInsertId();
    } else {
        return NULL;
    }
}

if ( $USER->instructor && isset($_POST["importSite"])) {

    $homeStmt = $PDOX->prepare("SELECT * FROM {$p}course_home WHERE home_id = :home_id");
    $homeStmt->execute(array(":home_id" => $_POST["importSite"]));
    $home = $homeStmt->fetch(PDO::FETCH_ASSOC);

    // Copy blob content
    // TODO: This will probably not work once we move files out of the database
    $syllabus = getNewFileId($CONTEXT, $LINK, $PDOX, $p, $home["syllabus_blob_id"]);
    $schedule = getNewFileId($CONTEXT, $LINK, $PDOX, $p, $home["schedule_blob_id"]);
    $picture = getNewFileId($CONTEXT, $LINK, $PDOX, $p, $home["picture_blob_id"]);

    if ($home) {
        $currentHomeStmt = $PDOX->prepare("SELECT * FROM {$p}course_home WHERE link_id = :link_id");
        $currentHomeStmt->execute(array(":link_id" => $LINK->id));
        $currentHome = $currentHomeStmt->fetch(PDO::FETCH_ASSOC);

        if ($currentHome) {
            // Update it
            $updateHomeStmt = $PDOX->prepare("UPDATE {$p}course_home set 
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
                                        where home_id = :home_id");
            $updateHomeStmt->execute(array(
                ":sections" => $home["sections"],
                ":meetings" => $home["meetings"],
                ":class_location" => $home["class_location"],
                ":start_date" => $home["start_date"],
                ":end_date" => $home["end_date"],
                ":course_title" => $home["course_title"],
                ":course_desc" => $home["course_desc"],
                ":course_video" => $home["course_video"],
                ":syllabus_blob_id" => $syllabus,
                ":schedule_blob_id" => $schedule,
                ":picture_blob_id" => $picture,
                ":prefix" => $home["prefix"],
                ":instructor_name" => $home["instructor_name"],
                ":office_location" => $home["office_location"],
                ":phone" => $home["phone"],
                ":email" => $home["email"],
                ":preferred_contact" => $home["preferred_contact"],
                ":office_hours" => $home["office_hours"],
                ":getting_started" => $home["getting_started"],
                ":about_me" => $home["about_me"],
                ":home_id" => $currentHome["home_id"]
            ));
        } else {
            // New record
            $newHomeStmt = $PDOX->prepare("INSERT INTO {$p}course_home (link_id, context_id, user_id, sections, meetings, class_location, start_date, end_date, course_title, course_desc, course_video, syllabus_blob_id, schedule_blob_id, picture_blob_id, prefix, instructor_name, office_location, phone, email, preferred_contact, office_hours, getting_started, about_me)
            values (
             :link_id, 
             :context_id, 
             :user_id, 
             :sections, 
             :meetings, 
             :class_location, 
             :start_date, 
             :end_date, 
             :course_title, 
             :course_desc, 
             :course_video, 
             :syllabus_blob_id, 
             :schedule_blob_id, 
             :picture_blob_id, 
             :prefix, 
             :instructor_name, 
             :office_location, 
             :phone, 
             :email, 
             :preferred_contact, 
             :office_hours, 
             :getting_started, 
             :about_me
            )");
            $newHomeStmt->execute(array(
                ":link_id" => $LINK->id,
                ":context_id" => $CONTEXT->id,
                ":user_id" => $USER->id,
                ":sections" => $home["sections"],
                ":meetings" => $home["meetings"],
                ":class_location" => $home["class_location"],
                ":start_date" => $home["start_date"],
                ":end_date" => $home["end_date"],
                ":course_title" => $home["course_title"],
                ":course_desc" => $home["course_desc"],
                ":course_video" => $home["course_video"],
                ":syllabus_blob_id" => $syllabus,
                ":schedule_blob_id" => $schedule,
                ":picture_blob_id" => $picture,
                ":prefix" => $home["prefix"],
                ":instructor_name" => $home["instructor_name"],
                ":office_location" => $home["office_location"],
                ":phone" => $home["phone"],
                ":email" => $home["email"],
                ":preferred_contact" => $home["preferred_contact"],
                ":office_hours" => $home["office_hours"],
                ":getting_started" => $home["getting_started"],
                ":about_me" => $home["about_me"]
            ));
        }

        $_SESSION["success"] = "Import successful. Please click on Edit to update your homepage for the current term as well as re-upload your syllabus, schedule, and profile picture files.";
    } else {
        $_SESSION["error"] = "Unable to import content from previous site.";
    }
} else {
    $_SESSION["error"] = "Unable to import content from previous site.";
}
header( 'Location: '.addSession('index.php') ) ;
return;
