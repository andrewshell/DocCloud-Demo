<?php
include 'functions.php';
setupCheck();

$docs = scandir(DOC_PATH);
foreach ($docs as $k => $filename) {
    if ('.' == $filename[0] || is_dir(DOC_PATH . '/' . $filename)) {
        unset($docs[$k]);
    }
}
if (isset($_GET['filename'])) {
    $filename = $_GET['filename'];
    if (in_array($_GET['filename'], $docs)) {
        $type = getFileType(DOC_PATH . '/' . $filename);
        $fp = fopen(DOC_PATH . '/' . $filename, 'rb');
        header("Content-Type: {$type}");
        header("Content-Length: " . filesize(DOC_PATH . '/' . $filename));
        header("X-RSS-Cloud: " . MY_CLOUD_URL);
        fpassthru($fp);
        exit;
    }
}

authUser($users);

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    if (!empty($_POST['url'])) {
        fetchRemoteFile($_POST['url']);
    } elseif (!empty($_POST['filename2'])) {
        saveFileContents($_POST['filename2'], $_POST['contents']);
    } elseif (!empty($_POST['filename1'])) {
        saveFileContents($_POST['filename1'], $_POST['contents']);
    }
    header('Location: index.php');
    exit;
}

?>
<DOCTYPE html>
<html>
<head><title>DocCloud Demo</title></head>
<body>
<ul>
<?php foreach ($docs as $filename): ?>
    <li><a href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?filename=' . urlencode($filename); ?>"><?php echo htmlentities($filename); ?></a></li>
<?php endforeach; ?>
</ul>
<form method="POST">
<dl>
    <dt><label for="url">Subscribe to URL</label></dt>
    <dd><input type="text" name="url" id="url"></dd>

    <dt><label for="filename1">or New File Filename</label></dt>
    <dd><input type="text" name="filename1" id="filename1"></dd>

    <dt><label for="filename2">or Existing File</label></dt>
    <dd><select name="filename2" id="filename2">
        <option value="">-- Create New File --</option>
        <?php foreach ($docs as $filename): ?>
            <option value="<?php echo $filename; ?>"><?php echo htmlentities($filename); ?></option>
        <?php endforeach; ?>
    </select></dd>

    <dt><label for="contents">File Contents</label></dt>
    <dd><textarea name="contents" id="contents"></textarea></dd>

    <dd><input type="submit" value="Submit"></dd>
</dl>
</form>
</body>
</html>