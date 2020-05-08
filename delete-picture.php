<?php
require_once "../config.php";

use Tsugi\Core\LTIX;

$p = $CFG->dbprefix;

$LAUNCH = LTIX::requireData();

if($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    if($_SESSION['picture_blob'] != null){
        $delPicture = $PDOX->prepare("DELETE FROM {$p}blob_file WHERE file_id = :pictureId");
        $delPicture->execute(array(":pictureId" => $_SESSION['picture_blob']));
        $_SESSION['picture_blob'] = null;
    }
}

exit;