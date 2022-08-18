<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
    // Nothing yet.
);

// The SQL to create the tables if they don't exist
$DATABASE_INSTALL = array(
    array( "{$CFG->dbprefix}course_home",
        "create table {$CFG->dbprefix}course_home (
    home_id             INTEGER NOT NULL AUTO_INCREMENT,
    link_id             INTEGER NOT NULL,
    context_id          INTEGER NOT NULL,
    user_id             INTEGER NOT NULL,
    sections            VARCHAR(255) NULL,
    meetings            varchar(255) NULL,
    class_location      VARCHAR(255) NULL,
    start_date          DATE NULL,
    end_date            DATE NULL,
    course_title        VARCHAR(255) NULL,
    course_desc         TEXT null,
    course_video        VARCHAR(1000) NULL,
    syllabus_blob_id    INTEGER NULL,
    schedule_blob_id    INTEGER NULL,
    picture_blob_id     INTEGER NULL,
    prefix              VARCHAR(255) NULL,
    instructor_name     VARCHAR(255) NULL,
    office_location     VARCHAR(255) NULL,
    phone               VARCHAR(50) NULL,
    email               VARCHAR(255) NULL,
    preferred_contact   VARCHAR(10) NULL,
    office_hours        VARCHAR(255) NULL,
    addtl_contacts      TEXT NULL,
    getting_started     TEXT NULL,
    about_me            TEXT NULL,
    
    UNIQUE(user_id, link_id),
    PRIMARY KEY(home_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8")
);

$DATABASE_UPGRADE = function ($oldversion) {
    global $CFG, $PDOX;
    // Add addtl_contacts column
    if (!$PDOX->columnExists('addtl_contacts', "{$CFG->dbprefix}course_home")) {
        $sql = "ALTER TABLE {$CFG->dbprefix}course_home ADD addtl_contacts TEXT";
        echo ("Upgrading: " . $sql . "<br/>\n");
        error_log("Upgrading: " . $sql);
        $q = $PDOX->queryDie($sql);
    }
    return '202208171424';
};