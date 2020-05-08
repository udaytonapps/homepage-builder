<?php
require_once "../config.php";

use Tsugi\Core\LTIX;

$p = $CFG->dbprefix;

$LAUNCH = LTIX::requireData();

if($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    if($_SESSION['schedule_blob'] != null){
        $delSchedule = $PDOX->prepare("DELETE FROM {$p}blob_file WHERE file_id = :scheduleId");
        $delSchedule->execute(array(":scheduleId" => $_SESSION['schedule_blob']));
        $_SESSION['schedule_blob'] = null;
    }
}

exit;