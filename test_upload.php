<?php
echo "PHP Upload Settings:\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "file_uploads: " . (ini_get('file_uploads') ? 'On' : 'Off') . "\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "\nSubmitted files:\n";
    print_r($_FILES);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Test</title>
</head>
<body>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="test" value="test">
        <input type="file" name="test_file">
        <button type="submit">Submit</button>
    </form>
</body>
</html>
