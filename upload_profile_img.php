<html>
<head>
    <style>
        body { margin:0px; padding:0px; overflow:hidden; font-family:arial, helvetica, clean, sans-serif; font-size:13px; line-height:23px; padding-left:5px; padding-right:5px; }
    </style>
</head>
<body>

<?php 
$plugin_path = str_replace('upload_profile_img.php', '', $_SERVER['SCRIPT_FILENAME']);
$valid_mime = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png');
$max_size = 1500000;
$contact_id = @$_REQUEST['contact_id'];

$html = <<<HTML
    <form enctype="multipart/form-data" action="upload_profile_img.php?contact_id={$contact_id}" method="post">
        <input type="button" value="Cancel" onclick="parent.close_upload_iframe();" style="float:right;" />
        <input type="submit" value="Upload" style="float:right;" />

        <input type="file" name="file" />
    </form>
HTML;

if(in_array($_FILES['file']['type'], $valid_mime)) {
    $target_path = "{$plugin_path}upload/$contact_id";

    if($_FILES['file']['error'] > 0) {
        echo '<input type="button" value="Try Again" style="float:right;" onclick="location.href = location.href" /> Error uploading file.';
    } else {
        if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
            echo '<input type="button" value="Close" onclick="parent.close_upload_iframe();" style="float:right;"/> Profile image uploaded. <script>parent.index_page.browse_contacts_table.engine_obj.fnDraw(); parent.close_upload_iframe();</script>';
        }
    }
} else if($_FILES) {
    echo '<input type="button" value="Try Again" onclick="location.href = location.href;" style="float:right;" /> This is an invalid file. Please upload GIF, JPEG, or PNG.';
} else { 
    if(!$contact_id) {
        echo '<input type="button" value="Close" onclick="parent.close_upload_iframe();" style="float:right;" /> Do not know which contact to upload for.';
    } else {
        echo $html;
    }
}
?>

</body>
</html>
