<?php
require_once "../config.php";

use Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

if ( $USER->instructor && isset($_POST["importSite"]) && is_numeric($_POST["importSite"])) {

    $homeId = (int) $_POST["importSite"];
    $homeStmt = $PDOX->prepare("SELECT * FROM {$p}course_home WHERE home_id = :home_id");
    $homeStmt->execute(array(":home_id" => $homeId));
    $home = $homeStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($home) {
        $currentHomeStmt = $PDOX->prepare("SELECT * FROM {$p}course_home WHERE link_id = :link_id");
        $currentHomeStmt->execute(array(":link_id" => $LINK->id));
        $currentHome = $currentHomeStmt->fetch(PDO::FETCH_ASSOC);
        $isImportingFiles = isset($_POST["isImportingFiles"]) ? true : false;
        
        if ($currentHome) {
            // Condition will be empty string if file import option wasn't selected
            $importCondition = $isImportingFiles ? 'syllabus_blob_id = :syllabus_blob_id, schedule_blob_id = :schedule_blob_id,' : "";
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
                                        prefix = :prefix,
                                        instructor_name = :instructor_name,
                                        office_location = :office_location,
                                        phone = :phone,
                                        email = :email,
                                        preferred_contact = :preferred_contact,
                                        office_hours = :office_hours,
                                        addtl_contacts = :addtl_contacts,
                                        getting_started = :getting_started,
                                        about_me = :about_me,
                                        {$importCondition}
                                        picture_blob_id = :picture_blob_id
                                        where home_id = :home_id");
            $arr = array(
                ":sections" => $home["sections"],
                ":meetings" => $home["meetings"],
                ":class_location" => $home["class_location"],
                ":start_date" => $home["start_date"],
                ":end_date" => $home["end_date"],
                ":course_title" => $home["course_title"],
                ":course_desc" => $home["course_desc"],
                ":course_video" => $home["course_video"],
                ":prefix" => $home["prefix"],
                ":instructor_name" => $home["instructor_name"],
                ":office_location" => $home["office_location"],
                ":phone" => $home["phone"],
                ":email" => $home["email"],
                ":preferred_contact" => $home["preferred_contact"],
                ":office_hours" => $home["office_hours"],
                ":addtl_contacts" => $home["addtl_contacts"],
                ":getting_started" => $home["getting_started"],
                ":about_me" => $home["about_me"],
                ":picture_blob_id" => $home["picture_blob_id"],
                ":home_id" => $currentHome["home_id"]);
            if ($isImportingFiles) {
                // Combine the file upload elements only if file import option was selected
                $arr = array_merge($arr, array(":syllabus_blob_id" => $home["syllabus_blob_id"], ":schedule_blob_id" => $home["schedule_blob_id"]));

                // Clear out the previous session value after import (if importing files)
                // If only importing one or the other, this prevents a previously existing doc from being re-saved
                $_SESSION['syllabus_blob'] = $home["syllabus_blob_id"];
                $_SESSION['schedule_blob'] = $home["schedule_blob_id"];
            }
            $updateHomeStmt->execute($arr);
        } else {
            // Conditions will be empty strings if file import option wasn't selected
            $insertFileCols = $isImportingFiles ? 'syllabus_blob_id, schedule_blob_id,' : "";
            $insertFileVals = $isImportingFiles ? ':syllabus_blob_id, :schedule_blob_id,' : "";
            
            // New record
            $newHomeStmt = $PDOX->prepare("INSERT INTO {$p}course_home (
                link_id, 
                context_id, 
                user_id, 
                sections, 
                meetings, 
                class_location, 
                start_date, 
                end_date, 
                course_title, 
                course_desc, 
                course_video, 
                prefix, 
                instructor_name, 
                office_location, 
                phone, 
                email, 
                preferred_contact, 
                office_hours, 
                addtl_contacts,
                getting_started, 
                about_me,
                {$insertFileCols}
                picture_blob_id
            )
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
             :prefix, 
             :instructor_name, 
             :office_location, 
             :phone, 
             :email, 
             :preferred_contact, 
             :office_hours, 
             :addtl_contacts,
             :getting_started,
             :about_me,
             {$insertFileVals}
             :picture_blob_id
            )");
            $arr = array(
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
                ":prefix" => $home["prefix"],
                ":instructor_name" => $home["instructor_name"],
                ":office_location" => $home["office_location"],
                ":phone" => $home["phone"],
                ":email" => $home["email"],
                ":preferred_contact" => $home["preferred_contact"],
                ":office_hours" => $home["office_hours"],
                ":addtl_contacts" => $home["addtl_contacts"],
                ":getting_started" => $home["getting_started"],
                ":about_me" => $home["about_me"],
                ":picture_blob_id" => $home["picture_blob_id"]
            );
            if ($isImportingFiles) {
                // Combine the file upload elements only if file import option was selected
                $arr = array_merge($arr, array(":syllabus_blob_id" => $home["syllabus_blob_id"], ":schedule_blob_id" => $home["schedule_blob_id"]));
            }
            $newHomeStmt->execute($arr);
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
