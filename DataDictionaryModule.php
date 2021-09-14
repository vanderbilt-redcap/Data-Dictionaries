<?php
//METADATA VS METADATA TEMP


namespace VUMC\DataDictionaryModule;



use Design;
use Exception;
use PDO;
use REDCap;

include_once(__DIR__ . "/classes/REDCapManagement.php");

include_once(__DIR__ . "/classes/ProjectData.php");


class DataDictionaryModule extends \ExternalModules\AbstractExternalModule
{
    function redcap_every_page_top($project_id)
    {
        $this->includeStyle('css/styles.css');
        $this->includeJs('node_modules/jquery/dist/jquery.min.js');
        $this->includeJs('js/script.js');
        $settings = [
            'ajaxpage' => $this->framework->getUrl('pages/ajaxpage.php')
        ];

        $this->setJsSettings($settings);
    }


    protected function includeStyle($path)
    {
        echo '<link rel="stylesheet" type="text/css" href="' . $this->framework->getUrl($path, true) . '" />';
    }

    protected function includeJs($path)
    {
        echo '<script src="' . $this->framework->getUrl($path, true) . '"></script>';
    }

    protected function setJsSettings($settings)
    {
        echo '<script>ajax= ' . json_encode($settings) . ';</script>';
    }

    //Create a file name that writes to one file 
    //constant = FILE NAME 



    function parseArray($choices)
    {
        $array_to_fill = array();

        $select_choices = $choices;
        $select_array = explode("|", $select_choices);
        foreach ($select_array as $key => $val) {
            $position = strpos($val, ",");

            $StaticNumber = trim(substr($val, 0, $position));

            $text = trim(substr($val, $position + 1));
            $array_to_fill[] = $text;
        }
        //Change keys to start at 1
        $array_to_fill = array_combine(range(1, count($array_to_fill)), $array_to_fill);

        return $array_to_fill;
    }
    

    function main($value, $old_value, $field_name)
    {
        $color = "";
        $col = "";


        if ($value[$field_name] !== $old_value[$field_name]) {
            $color = "class='mb-2 bg-warning';";

            $col .= "<div $color>" . $value[$field_name] . "</div><div class='text-muted' style=' text-decoration: line-through;'>" . $old_value[$field_name] . "</div>";
        } else {
            $col .= "<div class='mb-2'>" . $value[$field_name] . "</div>";
        }


        return $col;
    }


    function secondary($value, $old_value, $field_name, $string = "")
    {
        $color = "";
        $col = "";

        if ($value[$field_name] !== $old_value[$field_name]) {
            if ($old_value[$field_name] == "") {
                $color = "class='mb-2 text-light p-1' style='background-color:#5d9451; font-size:12px;';";
                $col .= "<div $color>$string " . $value[$field_name] . "</div>";
                //$col .= "<small class='mb-2 d-flex' style='font-size:12px; text-decoration:line-through;'>$string" . $old_value[$field_name] . "</small>";
            } else if ($value[$field_name] == "") {
                $color = "class='mb-2 p-1 bg-warning' style='font-size:12px;';";
                //$col .= "<div $color>$string " . $value[$field_name] . "</div>";
                $col .= "<small class='mb-2 d-flex text-light p-1' style='background-color:#cb410b; font-size:12px; text-decoration:line-through;'>$string" . $old_value[$field_name] . "</small>";
            } else {
                $color = "class='mb-2 bg-warning p-1' style='font-size:12px;';";
                $col .= "<div $color>$string " . $value[$field_name] . "</div>";
                $col .= "<small class='mb-2 p-1 d-flex' style='font-size:12px; text-decoration:line-through;'>$string" . $old_value[$field_name] . "</small>";
            }
        } else if ($old_value[$field_name] != "") {
            $col .= "<small class='d-flex mb-2'><div><i class='text-muted'>$string </i><i class='text-info'> " . $old_value[$field_name] . "</i></div></small>";
        }


        return $col;
    }

    function thirdCol($new, $old, $first_field, $second_field, $third_field, $fourth_field, $fifth_field, $sixth_field, $min, $max)
    {
        $col = "";
        $color = "";
        global $lang;

        $choices = $this->parseArray($new[$sixth_field]);
        $oldChoices = $this->parseArray($old[$sixth_field]);

        if ($new[$first_field] == 'select') $new[$first_field] = 'dropdown';
        elseif ($new[$first_field] == 'textarea') $new[$first_field] = 'notes';

        if($new[$first_field] !== $old[$first_field]){
            if($old[$first_field] == ""){
                $color = "class='mb-2 text-light p-1 d-inline-block' style='background-color:#5d9451; font-size:12px;';";
                $col .= "<div $color> " . $new[$first_field] . "</div>";
            }else if($new[$first_field] == ""){
                $color = "class='mb-2 d-inline-block text-light p-1' style='background-color:#cb410b; font-size:12px; text-decoration:line-through;';";
                $col .= "<small $color>" . $old[$first_field] . "</small>";
            }else{
                $color = "class='mb-2 bg-warning p-1 d-inline-block' style='font-size:12px;';";
                $col .= "<div $color>" . $new[$first_field] . "</div>";
                $col .= "<small class='mb-2 p-1 d-inline-block' style='font-size:12px; text-decoration:line-through;'>" . $old[$first_field] . "</small>";
            }
        } else if ($old[$first_field] != "") {
            $col .= "<div class='d-inline-block mr-1 mb-2'>" . $old[$first_field] . "</div>";
        }

        if($new[$second_field] !== $old[$second_field]){
            if($old[$second_field] == ""){
                //New item
                $color = "class='mb-2 text-light p-1 d-inline-block' style='background-color:#5d9451; font-size:12px;';";
                $col .= "<div $color> (" . $new[$second_field];
                if ($new[$min] != "") {
                    $col .= ", Min:" . $new[$max];
                }
                if ($new[$min] != "") {
                    $col .= ", Max: " . $new[$max];
                }
                $col .= ") </div>";
            }elseif($new[$second_field] == ""){
                //removed
                $color = "class='mb-2 d-inline-block text-light p-1' style='background-color:#cb410b; font-size:12px; text-decoration:line-through;';";
                $col .= "<div $color> (" . $old[$second_field];
                if ($old[$min] != "") {
                    $col .= ", Min:" . $old[$max];
                }
                if ($old[$min] != "") {
                    $col .= ", Max: " . $old[$max];
                }
                $col .= ") </div>";
            }else{
                $color = "class='mb-2 bg-warning p-1 d-inline-block' style='font-size:12px;';";
                $col .= "<div $color> (" . $new[$second_field];
                if ($new[$min] != "") {
                    $col .= ", Min:" . $new[$max];
                }
                if ($new[$min] != "") {
                    $col .= ", Max: " . $new[$max];
                }
                $col .= ") </div>";

                $col .= "<div class='ml-1 d-inline-block' style='font-size:12px; text-decoration:line-through;'> (" . $old[$second_field];
                if ($old[$min] != "") {
                    $col .= ", Min:" . $old[$max];
                }
                if ($old[$min] != "") {
                    $col .= ", Max: " . $old[$max];
                }
                $col .= ") </div>";
            }
           
        } else if ($old[$second_field] != "") {
            $color = 'class="d-inline-block mr-1 mb-2" style="font-size:12px;"';
            $col .= "<div $color> (" . $old[$second_field];
            if ($old[$min] != "") {
                $col .= ", Min:" . $old[$max];
            }
            if ($old[$min] != "") {
                $col .= ", Max: " . $old[$max];
            }
            $col .= ") </div>";
        }

        if($new[$third_field] !== $old[$third_field]){
            if($old[$third_field] == ""){
                $color = "class='mb-2 text-light p-1 ml-2 d-inline-block' style='background-color:#5d9451; font-size:12px;';";
                $col .= "<small $color> Required </small>";
            }elseif($new[$third_field] == ""){
                $color = "class='mb-2 ml-2 d-inline-block text-light p-1' style='background-color:#cb410b; font-size:12px; text-decoration:line-through;';";
                $col .= "<small $color> Required </small>";
            }
        }else if($old[$third_field] != ""){
            $color = 'class="d-inline-block mr-1 mb-2" style="font-size:12px;"';
            $col .= "<small $color> Required</small>";
        }

        if ($new[$fourth_field] !== $old[$fourth_field]) {
            if ($old[$fourth_field] == "") {
                $color = "class='mb-2 text-light p-1 ml-2 d-inline-block' style='background-color:#5d9451; font-size:12px;';";
                $col .= "<small $color> Identifier </small>";
            } elseif ($new[$fourth_field] == "") {
                $color = "class='mb-2 ml-2 d-inline-block text-light p-1' style='background-color:#cb410b; font-size:12px; text-decoration:line-through;';";
                $col .= "<small $color> Identifier </small>";
            }
        } else if ($old[$fourth_field] != "") {
            $color = 'class="d-inline-block mr-1 mb-2" style="font-size:12px;"';
            $col .= "<small $color> Identifier</small>";
        }
        
        if($new[$fifth_field] !== $old[$fifth_field]){
            if($old[$fifth_field] == ""){
                $color = "class='mb-2 text-light p-1 d-block' style='background-color:#5d9451; font-size:12px;';";
                $col .= "<small $color>Field Annotation: " . $new[$fifth_field] . "</small>";
            }elseif($new[$fifth_field] == ""){
                $color = "class='mb-2 d-block text-light p-1' style='background-color:#cb410b; font-size:12px; text-decoration:line-through;';";
                $col .= "<small $color>Field Annotation: " . $old[$fifth_field] . "</small>";
            }else{
                $color = "class='mb-2 bg-warning p-1 d-inline-block' style='font-size:12px;';";
                $col .= "<div $color>" . $new[$fifth_field] . "</div>";
                $col .= "<small class='mb-2 p-1 d-inline-block' style='font-size:12px; text-decoration:line-through;'>" . $old[$fifth_field] . "</small>";
            }
        }else if($old[$fifth_field] != ""){
            $col .= "<div class='d-inline-block mr-1 mb-2'>" . $old[$fifth_field] . "</div>";
        }

        if($new[$sixth_field] !== $old[$sixth_field]){
            if($new[$first_field] == 'calc'){
                $col .= '<table>';
                $col .= '<tr>';
                $col .= '<th> Calculation </th>';
                $col .= '</tr>';
                $col .= '<tr>';
                $col .= "<td class='bg-warning'>" . $new[$sixth_field] . "</td>";
                $col .= '</tr>';
                $col .= '<tr>';
                $col .= "<td style='background-color:#cb410b; text-decoration:line-through;'>" . $old[$sixth_field] . "</td>";
                $col .= '</tr>';
                $col .= '</table>';
            } elseif ($new[$first_field] == 'sql') {
                $col .= '<table border="0" cellpadding="2" cellspacing="0" class="ReportTableWithBorder"><tr><td>' . $new[$sixth_field] . '</td></tr></table>';
            } else {
                $col .= '<table border="0" cellpadding="2" cellspacing="0" class="ReportTableWithBorder">';
                foreach ($choices as $val => $label) {
                    $col .= '<tr valign="top">';
                    $oldValue = $oldChoices[$val];
                    if($first_field == 'checkbox'){
                        $col .= '<td>' . $val . '</td>';
                        $col .= '<td>' . $new[$first_field] . '</td>';
                    }elseif($label !== $oldValue){
                        if($oldValue == ""){
                            $col .= "<td class='text-light' style='background-color:#5d9451;'>" . $val ."</td>";
                            $col .= "<td class='text-light' style='background-color:#5d9451;'>" . $label . "</td>";
                        }elseif($label == ""){
                            $col .= "<td class='text-light' style='background-color:#cb410b; text-decoration:line-through;'>" . $val . "</td>";
                            $col .= "<td class='text-light' style='background-color:#cb410b; text-decoration:line-through;'>" . $oldValue . "</td>";
                        }elseif($label !== $oldValue){
                            $col .= "<td class='bg-warning'>" . $val . "</td>";
                            $col .= "<td class='bg-warning'>" . $label . "</td>";
                            $col .= "<td class='text-light' style='background-color:#cb410b; text-decoration: line-through;'>" . $oldValue . "</td>";
                        }
                    } else {
                        $col .= "<td>" . $val . "</td>";
                        $col .= "<td>" . $label . "</td>";
                    }

                }
                $col .= '</table>';
            }
        }elseif($old[$sixth_field] != ""){
            if($old[$first_field] == 'calc'){
                    $col .= '<table>';
                    $col .= '<tr>';
                    $col .= '<th> Calculation </th>';
                    $col .= '</tr>';
                    $col .= '<tr>';
                    $col .= "<td>" . $old[$sixth_field] . "</td>";
                    $col .= '</tr>';
                    $col .= '</table>';
            }elseif($old[$first_field] == 'sql'){
                    $col .= '<table border="0" cellpadding="2" cellspacing="0" class="ReportTableWithBorder"><tr><td>' . $old[$sixth_field] . '</td></tr></table>';
            }else{
                $col .= '<table border="0" cellpadding="2" cellspacing="0" class="ReportTableWithBorder">';
                foreach ($oldChoices as $val => $label) {
                    $col .= '<tr valign="top">';
                    if ($old[$first_field] == 'checkbox' && $old[$sixth_field] != $new[$sixth_field]) {
                        $col .= '<td>' . $val . '</td>';
                        $col .= '<td>patata'.$label . $old[$first_field] . '</td>';
                    } else {
                        $col .= "<td>" . $val . "</td>";
                        $col .= "<td>" . $label . "</td>";
                    }
                }
                $col .= '</table>';
            }
        }
        return $col;
    }



   function first_col($value){
       $col = "";

       $col = $value['field_name'];
        if ($value['branching_logic'] != "") {
            $col .=  "<small class='d-flex' style='font-size:12px;'>Show the field ONLY if: " . $value['branching_logic'] . "</small>";
        }

       return $col;
   }

   function second_col($value){
        $col = "";

        if ($value['section_header'] != "") {
            $col .= "<div class='mb-2' style='font-size:12px;'>Section Header: " . $value['section_header'] . "</div>";
        }

        $col .= $value['field_label'];

        if ($value['field_note'] != "") {
            $col .= "<small class='d-flex'>Field Note: " . $value['field_note'] . "</small>";
        }

        return $col;
   }

   function third_col($value){
        $col = "";
        global $lang;
        $choices = $this->parseArray($value['select_choices_or_calculations']);
        $col .= $value['field_type'];

        if ($value['text_validation_type_or_show_slider_number'] != "") {
            if ($value['text_validation_type_or_show_slider_number'] == 'int') $value['text_validation_type_or_show_slider_number'] = 'integer';
            elseif ($value['text_validation_type_or_show_slider_number'] == 'float') $value['text_validation_type_or_show_slider_number'] = 'number';
            elseif (in_array($value['text_validation_type_or_show_slider_number'], array('date', 'datetime', 'datetime_seconds'))) $value['text_validation_type_or_show_slider_number'] .= '_ymd';
            $col .= " (" . $value['text_validation_type_or_show_slider_number'];
            if ($value['text_validation_min'] != "") {
                $col .= ", Min:" . $value['text_validation_min'];
            }
            if ($value['text_validation_max'] != "") {
                $col .= ", Max: " . $value['text_validation_max'];
            }

            $col .= ")";
        }

        if ($value['required_field'] == 'y') {
            $col .= ", Required";
        }

        if ($value['identifier'] == 'y') {
            $col .= ", Identifier";
        }

        if ($value['field_annotation'] != "") {
            $col .= "<br /> Field Annotation: " . $value['field_annotation'];
        }

        if ($value['select_choices_or_calculations'] != "" && $value['field_type'] != "descriptive") {
            if ($value['field_type'] == 'slider') {
                $col .= "<br />{$lang['design_488']} " . implode(", ", \Form::parseSliderLabels($value['select_choices_or_calculations']));
            } elseif ($value['field_type'] == 'calc') {
                $col .= '<table>';
                $col .= '<tr>';
                $col .= '<th> Calculation </th>';
                $col .= '</tr>';
                $col .= '<tr>';
                $col .= '<td>' . $value['select_choices_or_calculations'] . '</td>';
                $col .= '</tr>';
                $col .= '</table>';
            } elseif ($value['field_type'] == 'sql') {
                $col .= '<table border="0" cellpadding="2" cellspacing="0" class="ReportTableWithBorder"><tr><td>' . $value['select_choices_or_calculations'] . '</td></tr></table>';
            } else {
                $col .= '<table border="0" cellpadding="2" cellspacing="0" class="ReportTableWithBorder" style="color:#fff;">';
                foreach ($choices as $val => $label) {
                    $col .= '<tr valign="top">';
                    if ($value['field_type'] == 'checkbox') {
                        $col .= '<td>' . $val . '</td>';
                    } else {
                        $col .= '<td>' . $val . '</td>';
                    }

                    $col .= '<td>' . $label . '</td>';
                    $col .= '</tr>';
                }
                $col .= '</table>';
            }
        }



        return $col;
   }


    function tableColumns($first, $second, $third)
    {
        $table = "";

        $table .= "<td>" . $first . "</td>";
        $table .= "<td>" . $second . "</td>";
        $table .= "<td>" . $third . "</td>";

        return $table;
    }

    function ColorTable($new, $old){
        $table = "";

        $sumOfAll = 0;
        foreach($new as $formNames => $field_names){
            
            
            $table .= "<tr><td colspan='5' style='background-color:#333; color:#fff;'>Instrument: " . ucwords(str_replace('_', ' ', $formNames)) . "</td></tr>";
            foreach($field_names as $type => $metaRows){
                foreach($metaRows as $key => $value){
                    $first_col = "";
                    $second_col = "";
                    $third_col = "";
                    $old_value = $old[$key];
                    $choices = $this->parseArray($value['select_choices_or_calculations']);
                    $oldChoices = $this->parseArray($old_value['select_choices_or_calculations']);
                    $value = array_map('strip_tags', $value);

                    
                    if ($type === 'changed') {
                        $first_col .= $this->main($value, $old_value, 'field_name');
                        $first_col .= $this->secondary($value, $old_value, 'branching_logic', 'Show the field ONLY if:');
                        $second_col .= $this->secondary($value, $old_value, 'section_header', 'Section Header :');
                        $second_col .= $this->main($value, $old_value, 'field_label');
                        $second_col .= $this->secondary($value, $old_value, 'field_note');
                        $third_col .= $this->thirdCol($value, $old_value, 'field_type', 'text_validation_type_or_show_slider_number', 'required_field', 'identifier', 'field_annotation', 'select_choices_or_calculations', 'text_validation_min', 'text_validation_max');
                        $table .= $this->tableColumns($first_col, $second_col, $third_col);
                    }

                    if($type === 'added'){
                         $table .= "<tr style='background-color:#5d9451; color:#fff;'>";
                         $first_col .= $this->first_col($value);
                         $second_col .= $this->second_col($value);
                         $third_col .= $this->third_col($value);
                         $table .= $this->tableColumns($first_col, $second_col, $third_col);
                    }
                    if($type === 'removed'){
                        $table .= "<tr style='background-color:#cb410b; color:#fff;'>";
                        $first_col .= $this->first_col($value);
                        $second_col .= $this->second_col($value);
                        $third_col .= $this->third_col($value);
                        $table .= $this->tableColumns($first_col, $second_col, $third_col);

                    }

                    $table .= "</tr>";
                }

            }
        }
        print $table;

    }

    function custom_array_merge($first, $second, $third)
    {
        $result = array();
        foreach ($first as $key => $value) {
            $result[$value['form_name']]['changed'][$key] = $value;
        }

        foreach ($second as $key => $value) {
            $result[$value['form_name']]['added'][$key] = $value;
            
        }

        foreach ($third as $key => $value) {
            $result[$value['form_name']]['removed'][$key] = $value;
        }

        array_merge($result);

        return $result;
    }

    function countedItems($added, $changed, $removed){
        $text = "";
        $added = count($added);
        $removed = count($removed);
        $changed = count($changed);

        $text .= "<ul class='numberOfRows'>";
        $text .= "<li><span class='mr-1'>Rows added: $added </span></li>";
        $text .= "<li><span class='mr-1'>Rows changed: $changed </span></li>";
        $text .= "<li><span>Rows removed: $removed </span></li>";

        $text .= "</ul>";
        

        print $text;

    }
 



    function compareDataDictionaries($path)
    {

        global $lang;

        $old = \REDCap::getDataDictionary(PROJECT_ID, 'array', false);
        $new = $this->dataDictionaryCSVToMetadataArray($path);
        $table = "";

        $removed = array_diff_key($old, $new);
        $added = array_diff_key($new, $old);
        

        $possiblyChanged = array_intersect_key($new, $old);

        foreach ($possiblyChanged as $key => $value) {
            if ($old[$key] != $value) {
                $changed[$key] = $value;
            }
        }

        $allItems = $this->custom_array_merge($changed, $added, $removed);
        

?>


        <div class="container-fluid mt-5">
            <div class="row">
                <div class="col-md-12">
                    <?php $this->countedItems($added, $changed, $removed); ?>
                    <ul class="legend">
                        <li><span class="changed"></span> Changed</li>
                        <li><span class="new"></span> New</li>
                        <li><span class="removed"></span> Removed</li>
                        <li><span class="old"></span> Old Value</li>
                    </ul>
                    <div class="table-wrapper-scroll-y custom-scrollbar table-responsive">
                        <table class='table table-bordered table-striped'>
                            <thead>
                                <th>Variable / Field Name</th>
                                <th>Field Label <br /> <small><i><b>Field Note</b></i></small></th>
                                <th>Field Attributes (Field Type, Validation, Choices, Calculations, etc.)</th>
                            </thead>

                            <?php
                            $this->ColorTable($allItems, $old);
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

<?php


    }



    function dataDictionaryCSVToMetadataArray($csvFilePath, $returnType = null)
    {
        $dd_column_var = array(
            "0" => "field_name", "1" => "form_name", "2" => "section_header", "3" => "field_type",
            "4" => "field_label", "5" => "select_choices_or_calculations", "6" => "field_note", "7" => "text_validation_type_or_show_slider_number",
            "8" => "text_validation_min", "9" => "text_validation_max", "10" => "identifier", "11" => "branching_logic",
            "12" => "required_field", "13" => "custom_alignment", "14" => "question_number", "15" => "matrix_group_name",
            "16" => "matrix_ranking", "17" => "field_annotation"
        );

        // Set up array to switch out Excel column letters
        $cols = \MetaData::getCsvColNames();

        // Extract data from CSV file and rearrange it in a temp array
        $newdata_temp = array();
        $i = 1;

        // Set commas as default delimiter (if can't find comma, it will revert to tab delimited)
        $delimiter       = ",";
        $removeQuotes = false;

        if (($handle = fopen($csvFilePath, "rb")) !== false) {
            // Loop through each row
            
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                
                // Skip row 1
                if ($i == 1) {
                    ## CHECK DELIMITER
                    // Determine if comma- or tab-delimited (if can't find comma, it will revert to tab delimited)
                    $firstLine = implode(",", $row);
                    // If we find X number of tab characters, then we can safely assume the file is tab delimited
                    $numTabs = 6;
                    if (substr_count($firstLine, "\t") > $numTabs) {
                        // Set new delimiter
                        $delimiter = "\t";
                        // Fix the $row array with new delimiter
                        $row = explode($delimiter, $firstLine);
                        // Check if quotes need to be replaced (added via CSV convention) by checking for quotes in the first line
                        // If quotes exist in the first line, then remove surrounding quotes and convert double double quotes with just a double quote
                        $removeQuotes = (substr_count($firstLine, '"') > 0);
                    }
                    // Increment counter
                    $i++;
                    // Check if legacy column Field Units exists. If so, tell user to remove it (by returning false).
                    // It is no longer supported but old values defined prior to 4.0 will be preserved.
                    if (strpos(strtolower($row[2]), "units") !== false) {
                        return false;
                    }
                    continue;
                }
                if ($returnType == null) {
                    // Loop through each row and create array
                    $json_aux = array();
                    foreach ($row as $key => $value) {
                        $json_aux[$dd_column_var[$key]] = $value;
                    }
                    $newdata_temp[$json_aux['field_name']] = $json_aux;
                } else if ($returnType == 'array') {
                    // Loop through each column in this row
                    for ($j = 0; $j < count($row); $j++) {
                        // If tab delimited, compensate sightly
                        if ($delimiter == "\t") {
                            // Replace characters
                            $row[$j] = str_replace("\0", "", $row[$j]);
                            // If first column, remove new line character from beginning
                            if ($j == 0) {
                                $row[$j] = str_replace("\n", "", ($row[$j]));
                            }
                            // If the string is UTF-8, force convert it to UTF-8 anyway, which will fix some of the characters
                            if (function_exists('mb_detect_encoding') && mb_detect_encoding($row[$j]) == "UTF-8") {
                                $row[$j] = utf8_encode($row[$j]);
                            }
                            // Check if any double quotes need to be removed due to CSV convention
                            if ($removeQuotes) {
                                // Remove surrounding quotes, if exist
                                if (substr($row[$j], 0, 1) == '"' && substr($row[$j], -1) == '"') {
                                    $row[$j] = substr($row[$j], 1, -1);
                                }
                                // Remove any double double quotes
                                $row[$j] = str_replace("\"\"", "\"", $row[$j]);
                            }
                        }
                        // Add to array
                        $newdata_temp[$cols[$j + 1]][$i] = $row[$j];
                    }
                }
                $i++;
            }

            fclose($handle);
        } else {
            // ERROR: File is missing
            throw new Exception("ERROR. File is missing!");
        }

        // If file was tab delimited, then check if it left an empty row on the end (typically happens)
        if ($delimiter == "\t" && $newdata_temp['A'][$i - 1] == "") {
            // Remove the last row from each column
            foreach (array_keys($newdata_temp) as $this_col) {
                unset($newdata_temp[$this_col][$i - 1]);
            }
        }

        // Return array with data dictionary values

        return $newdata_temp;
    }
}
