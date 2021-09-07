

<?php
$mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
$message = "";
    if (in_array($_FILES['file']['type'], $mimes)) {
        $module->compareDataDictionaries($_FILES['file']['tmp_name']);
    } else {
        die("Sorry file type not allowed");
    }

