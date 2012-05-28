<?php
include 'functions.php';
setupCheck();
if (file_exists(DATA_FILE)) {
    require DATA_FILE;
}

$requests = array();

foreach ($data as $url => $info) {
    list($cloudUrl, $ts) = $info;
    if (!isset($requests[$cloudUrl])) {
        $requests[$cloudUrl] = array();
    }
    $requests[$cloudUrl][] = $url;
}

foreach ($requests as $cloudUrl => $urls)
{
    logMessage('Requesting notification for ' . count($urls) . ' URL(s) from ' . $cloudUrl);
    requestNotifications($cloudUrl, $urls);
}