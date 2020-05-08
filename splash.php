<?php
require_once('../config.php');

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

// Start of the output
$OUTPUT->header();

$OUTPUT->bodyStart();

$OUTPUT->topNav();

if ($USER->instructor) {
    $OUTPUT->splashPage(
        "Homepage Builder",
        __("Fill in some quick information about your section(s) to help students easily find important details like class meeting times, office hours, your preferred method of contact, your syllabus, and more."),
        "edit.php"
    );
} else {
    $OUTPUT->splashPage(
        "",
        __("Welcome to ".$CONTEXT->title.".")
    );
}

$OUTPUT->footerStart();

$OUTPUT->footerEnd();
