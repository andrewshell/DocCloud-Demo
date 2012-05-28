<?php
include 'functions.php';
setupCheck();
if (file_exists(DATA_FILE)) {
    require DATA_FILE;
}

if (isset($_GET['challenge'])) {
    if (isset($data[$_GET['url']])) {
        logMessage("Challenge accepted for " . $_GET['url']);
        die($_GET['challenge']);
    } else {
        logMessage("Challenge ignored for " . $_GET['url']);
        die("Not subscribed to file");
    }
}

if (isset($_POST['url'])) {
    if (isset($data[$_POST['url']])) {
        logMessage("Ping accepted for " . $_POST['url']);
        fetchRemoteFile($_POST['url'], false);
    } else {
        logMessage("Ping ignored for " . $_POST['url']);
    }
}