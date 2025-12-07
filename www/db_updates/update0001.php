<?php

function update0001($conn){

    $stmt = $conn->prepare("
        CREATE TABLE `events` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tag` varchar(255) NOT NULL,
            `type` varchar(10) NOT NULL,
            `message` varchar(1024) NOT NULL,
            `created` datetime NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
    ");

    if (!$stmt->execute()) {
        error_log(print_r($stmt->errorInfo(),true));
        return false;
    }
    
    $stmt = $conn->prepare("
        CREATE TABLE `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(255) NOT NULL,
            `userpass` varchar(100) NOT NULL,
            `role` varchar(10) NOT NULL,
            `comment` varchar(1024) NOT NULL,
            `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE(`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8, AUTO_INCREMENT=1;
    ");
    
    if (!$stmt->execute()) {
        error_log(print_r($stmt->errorInfo(),true));
        return false;
    }

    $stmt = $conn->prepare("INSERT INTO users(username,userpass,role,comment) VALUES(?,?,?,?)");
    
    if (!$stmt->execute(array('admin','d033e22ae348aeb5660fc2140aec35850c4da997','admin', ''))) {
        error_log(print_r($stmt->errorInfo(),true));
        return false;
    }

    $stmt = $conn->prepare("
        CREATE TABLE `users_tokens` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `token` varchar(255) NOT NULL,
            `role` varchar(10) NOT NULL,
            `userid` int(11) NOT NULL,
            `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE(`token`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8, AUTO_INCREMENT=1;
    ");

    if (!$stmt->execute()) {
        error_log(print_r($stmt->errorInfo(),true));
        return false;
    }

    $stmt = $conn->prepare("
        CREATE TABLE `persons` (
            `id` int(11) NOT NULL,
            `fullname` varchar(255) NOT NULL,
            `firstname` varchar(255) NOT NULL,
            `secondname` varchar(255) NOT NULL,
            `lastname` varchar(255) NOT NULL,
            `bornlastname` varchar(255) NOT NULL,
            `sex` varchar(10) NOT NULL,
            `bornyear` int(11) NOT NULL,
            `bornmonth` int(11) NOT NULL,
            `bornday` int(11) NOT NULL,
            `yearofdeath` int(11) NOT NULL,
            `monthofdeath` int(11) NOT NULL,
            `dayofdeath` int(11) NOT NULL,
            `mother` int(11) NOT NULL,
            `father` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8  AUTO_INCREMENT=1;
    ");

    if (!$stmt->execute()) {
        error_log(print_r($stmt->errorInfo(),true));
        return false;
    }

    return true;
}