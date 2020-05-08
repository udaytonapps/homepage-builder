<?php
require_once "../config.php";

use Tsugi\Core\LTIX;

$p = $CFG->dbprefix;

$LAUNCH = LTIX::requireData();

if($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    if($_SESSION['syllabus_blob'] != null){
        $delSyllabus = $PDOX->prepare("DELETE FROM {$p}blob_file WHERE file_id = :syllabusId");
        $delSyllabus->execute(array(":syllabusId" => $_SESSION['syllabus_blob']));
        $_SESSION['syllabus_blob'] = null;
    }
}

exit;
