<?php
$module->redcap_every_page_top(PROJECT_ID);

require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
// print_array($module->getUrl('test.php'));
$tesst = $module->getUrl("images/1484.gif");
?>
<h6 class="container">
    Drag and drop a REDCap Data Dictionary CSV. It will be compared with the data dictionary of the current project. Added, deleted, or revised variables will be listed below.
</h6>
<br />
<br />
<div class="container-fluid p-y-1">
    <div class="row m-b-1">
        <div class="col-sm-6 offset-sm-3">
            <button type="button" class="btn buttonColor btn-block" onclick="document.getElementById('inputFile').click()">Add CSV File</button>
            <div class="form-group upload-area" id="drop_file_area">
                <label class="sr-only" for="inputFile">File Upload</label>
                <input type="file" class="form-control-file  font-weight-bold" id="inputFile" data-title="Drag and drop a file">
            </div>
        </div>
    </div>
    <div id="uploaded_file">

    </div>

    <div class="modal">
        <!-- Place at bottom of page -->
    </div>

    <?php




    // require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
