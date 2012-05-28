<?php
$users = array(
    'admin' => 'doccloud',
);

define('MY_DOMAIN', 'doccloud.andrewshell.org');
define('MY_PORT',   '80');
define('MY_PATH',   '/');

define('DOC_PATH',  __DIR__ . '/cache/docs/');
define('DATA_FILE', __DIR__ . '/cache/data.php');
define('LOG_FILE',  __DIR__ . '/cache/log.txt');

// Default to Dave's RSS Cloud server
define('MY_CLOUD_URL', 'http://rpc.rsscloud.org:5337/rsscloud/pleaseNotify');
define('MY_PING_URL',  'http://rpc.rsscloud.org:5337/rsscloud/ping');

function authUser($users)
{
    if (
        !isset($_SERVER['PHP_AUTH_USER']) ||
        !isset($_SERVER['PHP_AUTH_PW']) ||
        !isset($users[$_SERVER['PHP_AUTH_USER']]) ||
        0 != strcmp($users[$_SERVER['PHP_AUTH_USER']], $_SERVER['PHP_AUTH_PW'])
    ) {
        header('WWW-Authenticate: Basic realm="CloudDoc Demo"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'You must login to use this demo';
        exit;
    }
}

function setupCheck()
{
    if (!file_exists(DOC_PATH)) {
        if (!mkdir(DOC_PATH, 0777, true)) {
            die("Cannot create DOC_PATH: " . DOC_PATH);
        }
    }
    if (!file_exists(DATA_FILE)) {
        updateDataFile(array());
        chmod(DATA_FILE, 0777);
    }
    if (!file_exists(LOG_FILE)) {
        logMessage('setupCheck');
        chmod(LOG_FILE, 0777);
    }
}

function updateDataFile($mergeData)
{
    if (file_exists(DATA_FILE)) {
        require DATA_FILE;
    } else {
        $data = array();
    }

    $data = array_merge($data, $mergeData);

    if (!file_put_contents(DATA_FILE, '<' . "?php\n\$data = " . var_export($data, true) . ";\n")) {
        die("Cannot create DATA_FILE: " . DATA_FILE);
    }
}

function doGetRequest($url, $optional_headers = null)
{
    $params = array(
        'http' => array(
            'method' => 'GET',
        )
    );
    if ($optional_headers !== null) {
        $params['http']['header'] = $optional_headers;
    }
    $ctx = stream_context_create($params);
    $fp = fopen($url, 'rb', false, $ctx);
    if (!$fp) {
        throw new Exception("Problem with $url, $php_errormsg");
    }
    $metadata = stream_get_meta_data($fp);
    $response = stream_get_contents($fp);
    if ($response === false) {
        throw new Exception("Problem reading data from $url, $php_errormsg");
    }
    return array($metadata, $response);
}

function doPostRequest($url, $data, $optional_headers = null)
{
    $params = array(
        'http' => array(
            'method' => 'POST',
            'content' => http_build_query($data),
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
        )
    );
    if ($optional_headers !== null) {
        $params['http']['header'] = $optional_headers;
    }
    $ctx = stream_context_create($params);
    $fp = fopen($url, 'rb', false, $ctx);
    if (!$fp) {
        throw new Exception("Problem with $url, $php_errormsg");
    }
    $response = stream_get_contents($fp);
    if ($response === false) {
        throw new Exception("Problem reading data from $url, $php_errormsg");
    }
    return $response;
}

function pingCloud($changedUrl)
{
    $data = array(
        'url' => $changedUrl,
    );
    return doPostRequest(MY_PING_URL, $data);
}

function requestNotifications($cloudUrl, $urls)
{
    if (is_array($urls)) {
        $urls = array_values($urls);
    } else {
        $urls = array($urls);
    }
    $mergeData = array();
    $data = array(
        'notifyProcedure' => '',
        'domain'   => MY_DOMAIN,
        'port'     => MY_PORT,
        'path'     => MY_PATH . 'updated.php',
        'protocol' => 'http-post',
    );
    foreach ($urls as $k => $v) {
        $data['url' . ($k+1)] = $v;
        $mergeData[$v] = array($cloudUrl, strtotime('+24 hours'));
    }
    updateDataFile($mergeData);
    return doPostRequest($cloudUrl,$data);
}

function getFileType($filename)
{
    if (function_exists('finfo_file')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $filename);
        finfo_close($finfo);
    } elseif (function_exists('mime_content_type')) {
        $type = mime_content_type($filename);
    } else {
        $type = 'text/plain';
    }
    return $type;
}

function saveFileContents($filename, $contents)
{
    $filename = preg_replace('![^a-z0-9_\.]+!is', '-', $filename);
    if (empty($filename) || '.' == $filename[0] || is_dir(DOC_PATH . '/' . $filename)) {
        // Something fishy is going on
        return false;
    }
    file_put_contents(DOC_PATH . '/' . $filename, $contents);
    pingCloud('http://' . $_SERVER['HTTP_HOST'] . MY_PATH . 'index.php?filename=' . urlencode($filename));
    return true;
}

function fetchRemoteFile($url, $reqNote = true)
{
    list($metadata, $contents) = doGetRequest($url);
    foreach ($metadata['wrapper_data'] as $hraw) {
        if (false !== strpos($hraw, ':')) {
            list($hkey, $hval) = explode(':', $hraw, 2);
            if (0 == strcmp($hkey, 'X-RSS-Cloud')) {
                $cloudUrl = trim($hval);
                break;
            }
        }
    }
    if (!isset($cloudUrl)) {
        return false;
    }
    saveFileContents($url, $contents);
    if ($reqNote) {
        requestNotifications($cloudUrl, $url);
    }
}

function logMessage($msg)
{
    file_put_contents(LOG_FILE, $msg . "\n", FILE_APPEND);
}