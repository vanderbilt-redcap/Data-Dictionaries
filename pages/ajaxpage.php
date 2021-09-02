

<?php
$mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
$message = "";
    if (in_array($_FILES['file']['type'], $mimes)) {
        $info = pathinfo($_FILES['file']['name']);

        $ext = $info['extension'];
        $newName = "NEWITEMS." . $ext;

        $target_path = $module->framework->getModulePath() . "csv/" . $newName;


        move_uploaded_file($_FILES['file']['tmp_name'], $target_path);
    } else {
        //$message .= "Sorry file type not allowed";
        die("Sorry file type not allowed");
    }

$module->compareDataDictionaries();
