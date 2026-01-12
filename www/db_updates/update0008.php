<?php

function update0008($conn){

    $stmt = $conn->prepare("
        ALTER TABLE `photos` DROP `year_notexactly`;
    ");

    if (!$stmt->execute()) {
        error_log(print_r($stmt->errorInfo(),true));
        return false;
    }

    $stmt = $conn->prepare("
        ALTER TABLE `photos` ADD `year_notexactly` VARCHAR(10) NOT NULL DEFAULT 'no';
    ");

    if (!$stmt->execute()) {
        error_log(print_r($stmt->errorInfo(),true));
        return false;
    }

    return true;
}


