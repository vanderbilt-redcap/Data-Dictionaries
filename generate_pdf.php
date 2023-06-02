<?php
namespace VUMC\DataDictionaryModule;
require __DIR__ .'/vendor/autoload.php';

$filename = $module->getProjectSetting('filename');

#compareDataDictionaries
$old = \REDCap::getDataDictionary(PROJECT_ID, 'array', false);
$new = json_decode($module->getProjectSetting('filedata'),true);

$removed = array_diff_key($old, $new);
$added = array_diff_key($new, $old);

$possiblyChanged = array_intersect_key($new, $old);

foreach ($possiblyChanged as $key => $value) {
    if ($old[$key] != $value) {
        $hasValueChanged = false;
        foreach ($value as $fieldType => $dataValue) {
            if (trim($dataValue) != trim($old[$key][$fieldType])) {
                //check if they have enetered the choices with a space between the '|' separator
                if($fieldType == "select_choices_or_calculations"){
                    $choicesOld = $module->parseArray($old[$key][$fieldType]);
                    $choices = $module->parseArray($value[$fieldType]);
                    $possiblyChangedChoicesValues = array_diff($choices, $choicesOld);
                    $possiblyChangedChoicesKey = array_diff_key($choices, $choicesOld);
                    if(!empty($possiblyChangedChoicesValues) && !empty($possiblyChangedChoicesKey)){
                        $hasValueChanged = true;
                    }
                }else{
                    $hasValueChanged = true;
                }
            }
        }
        if($hasValueChanged){
            $changed[$key] = $value;
        }

    }
}
$allItems = $module->custom_array_merge($changed, $added, $removed);

$numberOfRows = "";
$anyItems = true;
if(isset($added))
    $added = count($added);
else
    $added = 0;
if(isset($removed))
    $removed = count($removed);
else
    $removed = 0;
if(isset($changed))
    $changed = count($changed);
else
    $changed = 0;

if($changed == 0 && $removed == 0 && $added == 0){
    $numberOfRows = '<div class="alert alert-info">No data dictionary changes detected.</div>';
    $anyItems = false;
}else {
    $numberOfRows .= "<ul class='numberOfRows'>";
    $numberOfRows .= "<li><span class='mr-1'>Rows added: $added </span></li>";
    $numberOfRows .= "<li><span class='mr-1'>Rows changed: $changed </span></li>";
    $numberOfRows .= "<li><span>Rows removed: $removed </span></li>";
}

$numberOfRows .= "</ul>";

$page_num = '<style>.footer .page-number:after { content: counter(page); } .footer { position: fixed; bottom: 0px;color:grey }a{text-decoration: none;}</style>';
$page_styles = '<style>
.mainPDF{
   font-size:12pt;
}
table {
    border-collapse: collapse;
}
.table-bordered td, .table-bordered th {
    border: 1px solid #dee2e6;
}
thead tr:nth-child(1) th {
    background: white;
    position: sticky;
    top: 0;
    z-index: 10;
}

.table-bordered thead td, .table-bordered thead th {
    border-bottom-width: 2px;
}
.table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid #dee2e6;
}
.table-bordered td, .table-bordered th {
    border: 1px solid #dee2e6;
}
.table td, .table th {
    padding: 0.75rem;
    vertical-align: top;
    border-top: 1px solid #dee2e6;
}
th {
    text-align: inherit;
    text-align: -webkit-match-parent;
}
.legendPDF { list-style: none;padding-bottom: 10px;padding-left: 0;}
.legendPDF li { float: left;}
.legendPDF .label{ float: left; margin: 2px; }
.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,0.05);
}
.label {
    width: 5px;
    height: 10px;
    display: inline;
    padding: 0.2em 0.6em 0.3em;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25em;
}
.labeltext{
    color:#fff;
    font-weight: bold; 
}
.changed{background-color:#ffc107;}
.added{background-color:#5d9451;}
.removed{background-color:#cb410b;}
.old {background-color: #6c757d;}
.bg-warning {
    background-color: #ffc107 !important;
}
#bg-warning div{
    background-color:#ffc107 !important;
}

</style>';

$APP_PATH_MODULE = APP_PATH_WEBROOT_FULL."modules/".substr(__DIR__,strlen(dirname(__DIR__))+1);

$html_pdf = "<html>
          <head>
          <link rel='stylesheet' type='text/css' href='" . $module->framework->getUrl('css/styles.css', true) . "' />
        ".$page_styles. "
          </head>
        <body style='font-family:\"Calibri\";font-size:10pt;'>".$page_num
    ."<div class='footer'><span class='page-number'>Page </span></div>"
    ."<div class='mainPDF'><table style='width: 100%;'><tr><td>Data Dictionaries Compare File: <strong>".$filename."</strong></td></tr></table></div>"
    ."<div class='mainPDF'><table style='width: 100%;'><tr><td>Source Project: <strong>".\REDCap::getProjectTitle()." [".(int)$_GET['pid']."]</strong></td></tr></table></div>"
    ."<div class='mainPDF'><table style='width: 100%;'><tr><td>";
    $html_pdf .=  $numberOfRows.'</div>
                    <div>
                        <ul class="legendPDF">
                            <li><span class="label changed labeltext">#</span> <span style="padding-left:22px">Changed</span></li>
                            <li><span class="label added labeltext">+</span> <span style="padding-left:22px">Added</span></li>
                            <li><span class="label removed labeltext">-</span> <span style="padding-left:22px">Removed</span></li>
                            <li><span class="label old labeltext">x</span> <span style="padding-left:22px">Old Value</span></li>
                        </ul>
                     </div>
                    <table class="table table-bordered table-striped" style="width: 50%">
                        <thead>
                            <th>Status</th>
                            <th>Variable / Field Name</th>
                            <th>Field Label <br /> <small><i><b>Field Note</b></i></small></th>
                            <th>Field Attributes (Field Type, Validation, Choices, Calculations, etc.)</th>
                        </thead>
                        <tbody>'.$module->ColorTable($allItems, $old,"pdf").
                    '    <tbody>
                    </table>';

        $html_pdf .=  '
        </td></tr></table></div>
    </body></html>';

$filenamePdf = "DataDictionariesCompare_".preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename)."_".date("Y-m-d_h-i",time());

//SAVE PDF ON DB
$reportHash = $filenamePdf;
$storedName = md5($reportHash);
$filePath = EDOC_PATH.$storedName;

#OPTIONS
$options = new \Dompdf\Options();
$options->setChroot($APP_PATH_MODULE.'/icons/'); // this is a temporary file
$options->setIsRemoteEnabled(true);

$pdf = new \Dompdf\Dompdf($options);
$pdf->loadHtml($html_pdf);
$pdf->setPaper('A4', 'portrait');
$pdf->render();
$pdf->stream($filenamePdf);
file_put_contents(EDOC_PATH.$storedName, ob_get_contents());

//echo $html_pdf;
?>