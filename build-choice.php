<?php
require_once "../config.php";

use Tsugi\Core\LTIX;

$p = $CFG->dbprefix;

$LAUNCH = LTIX::requireData();

// Fetch list of courses that aren't this one
$allHomesStmt = $PDOX->prepare("SELECT * FROM {$p}course_home WHERE user_id = :userId AND link_id != :linkId ORDER BY course_title");
$allHomesStmt->execute(array(":userId" => $USER->id, ":linkId" => $LINK->id));
$allHomes = $allHomesStmt->fetchAll(PDO::FETCH_ASSOC);

if ($USER->instructor) {
    if (isset($allHomes) && count($allHomes) > 0) {
        $OUTPUT->splashPage(
            "Homepage Builder",
            __("How would you like to start?")
        );
?>
        <div style="display: flex; flex-direction: column; gap: 16px">
            <div style="display: flex; justify-content: center;">
                <a href="edit.php" class="btn btn-default"><i style="margin-right: 8px;" class="fa fa-plus"></i>Build a New Homepage</a>
            </div>
            <div style="display: flex; justify-content: center;">
                <a href="edit.php?action=import" class="btn btn-default"><i style="margin-right: 8px;" class="fa fa-file-import"></i>Import from an Existing Site</a>
            </div>
        </div>
<?php

    } else {
        header('Location: ' . 'edit.php');
    }
} else {
    $OUTPUT->splashPage(
        "",
        __("Welcome to " . $CONTEXT->title . ".")
    );
}
