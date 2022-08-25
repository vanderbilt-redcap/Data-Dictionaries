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
            $new_choices = explode(",",$val);
            $array_to_fill[trim($new_choices[0])] = trim($new_choices[1]);
        }

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
                $col .= '<table border="0" cellpadding="2" cellspacing="0" class="ReportTableWithBorder">'.
                        '<tr><td style="background-color:#ffc107;">' . $new[$sixth_field] . '</td></tr>'.
                        '<tr><td style="text-decoration: line-through;">' . $old[$sixth_field] . '</td></tr>'.
                    '</table>';
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
                        $col .= '<td>'.$label . $old[$first_field] . '</td>';
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


    function tableColumns($status, $first, $second, $third, $option="")
    {
        $icon = "fa-pencil-alt";
        $iconPDF = "#";
        if($status == "changed"){
            $icon = "fa-pencil-alt";
            $iconPDF = "#";
        }else if($status == "added"){
            $icon = "fa-plus";
            $iconPDF = "+";
        }else if($status == "removed"){
            $icon = "fa-minus";
            $iconPDF = "-";
        }

        $legend = '<a href="#" data-toggle="tooltip" title="'.$status.'" data-placement="top" class="custom-tooltip" style="vertical-align: -2px;"><span class="label '.$status.'" title="'.$status.'"><i class="fas '.$icon.'" aria-hidden="true"></i></span></a>';
        if($option == "pdf"){
            $legend = '<span class="label '.$status.' labeltext">'.$iconPDF.'</span>';
        }
        $table = "";
        $table .= '<td style="background-color: #fff;">'.$legend.'</td>';
        $table .= "<td>" . $first . "</td>";
        $table .= "<td>" . $second . "</td>";
        $table .= "<td>" . $third . "</td>";

        return $table;
    }

    function ColorTable($new, $old, $option=""){
        $table = "";
        $sumOfAll = 0;
        foreach($new as $formNames => $field_names){
            $table .= "<tr><td colspan='4' style='background-color:#333; color:#fff;'>Instrument: " . ucwords(str_replace('_', ' ', $formNames)) . "</td></tr>";
            foreach($field_names as $type => $metaRows){
                foreach($metaRows as $key => $value){
                    $first_col = "";
                    $second_col = "";
                    $third_col = "";
                    $old_value = $old[$key];
                    $choices = $this->parseArray($value['select_choices_or_calculations']);
                    $oldChoices = $this->parseArray($old_value['select_choices_or_calculations']);

                    if ($type === 'changed') {
                        $table .= "<tr>";
                        $first_col .= $this->main($value, $old_value, 'field_name');
                        $first_col .= $this->secondary($value, $old_value, 'branching_logic', 'Show the field ONLY if:');
                        $second_col .= $this->secondary($value, $old_value, 'section_header', 'Section Header :');
                        $second_col .= $this->main($value, $old_value, 'field_label');
                        $second_col .= $this->secondary($value, $old_value, 'field_note');
                        $third_col .= $this->thirdCol($value, $old_value, 'field_type', 'text_validation_type_or_show_slider_number', 'required_field', 'identifier', 'field_annotation', 'select_choices_or_calculations', 'text_validation_min', 'text_validation_max');
                        $table .= $this->tableColumns("changed", $first_col, $second_col, $third_col,$option);
                    }

                    if($type === 'added'){
                         $table .= "<tr style='background-color:#5d9451; color:#fff;'>";
                         $first_col .= $this->first_col($value);
                         $second_col .= $this->second_col($value);
                         $third_col .= $this->third_col($value);
                         $table .= $this->tableColumns("added", $first_col, $second_col, $third_col,$option);
                    }
                    if($type === 'removed'){
                        $table .= "<tr style='background-color:#cb410b; color:#fff;'>";
                        $first_col .= $this->first_col($value);
                        $second_col .= $this->second_col($value);
                        $third_col .= $this->third_col($value);
                        $table .= $this->tableColumns("removed", $first_col, $second_col, $third_col,$option);

                    }

                    $table .= "</tr>";
                }

            }
        }
        if($option == "pdf"){
            return $table;
        }else{
            print $table;
        }

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
            $text = '<div class="alert alert-info">No data dictionary changes detected.</div>';
            $anyItems = false;
        }else {
            $text .= "<ul class='numberOfRows'>";
            $text .= "<li><span class='mr-1'>Rows added: $added </span></li>";
            $text .= "<li><span class='mr-1'>Rows changed: $changed </span></li>";
            $text .= "<li><span>Rows removed: $removed </span></li>";
        }

        $text .= "</ul>";
        

        print $text;

        return $anyItems;
    }
 



    function compareDataDictionaries($path)
    {
        $old = \REDCap::getDataDictionary(PROJECT_ID, 'array', false);
        $new = $this->dataDictionaryCSVToMetadataArray($path);
        $this->setProjectSetting('filedata',json_encode($new));

        $removed = array_diff_key($old, $new);
        $added = array_diff_key($new, $old);

        $possiblyChanged = array_intersect_key($new, $old);

        foreach ($possiblyChanged as $key => $value) {
            if ($old[$key] != $value) {
                $hasValueChanged = false;
                foreach ($value as $fieldType => $dataValue) {
                    if (trim($dataValue) != trim($old[$key][$fieldType])) {
                        $hasValueChanged = true;
                    }
                }
                if($hasValueChanged){
                    $changed[$key] = $value;
                }

            }
        }
        $allItems = $this->custom_array_merge($changed, $added, $removed);
?>

        <script>
            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            })
        </script>
        <div class="container-fluid mt-5">
            <div class="row">
                <div class="col-md-12">
                    <?php
                    $anyItems = $this->countedItems($added, $changed, $removed);
                    if($anyItems){
                    ?>
                        <div>
                            <ul class="legend" style="float: left">
                                <li><a href="#" data-toggle="tooltip" title="changed" data-placement="top" class="custom-tooltip" style="vertical-align: -2px;"><span class="label changed" title="changed"><i class="fas fa-pencil-alt" aria-hidden="true"></i></span></a> Changed</li>
                                <li><a href="#" data-toggle="tooltip" title="added" data-placement="top" class="custom-tooltip" style="vertical-align: -2px;"><span class="label added" title="added"><i class="fas fa-plus" aria-hidden="true"></i></span></a> Added</li>
                                <li><a href="#" data-toggle="tooltip" title="removed" data-placement="top" class="custom-tooltip" style="vertical-align: -2px;"><span class="label removed" title="removed"><i class="fas fa-minus" aria-hidden="true"></i></span></a> Removed</li>
                                <li><a href="#" data-toggle="tooltip" title="old" data-placement="top" class="custom-tooltip" style="vertical-align: -2px;"><span class="label old" title="old"><i class="fas fa-times" aria-hidden="true"></i></span></a> Old Value</li>
                            </ul>
                            <form method="POST" action="<?=$this->getUrl('generate_pdf.php')?>" style="display: inline-block; float:right; margin-top:-20px">
                                <button type="submit" class="btn btn-primary" name="btnPDF" id="btnPDF"><em class="fa fa-file-pdf"></em> PDF</button>
                                <input type="hidden" name="filenamePDF" id="filenamePDF" value="">
                            </form>
                        </div>
                        <div class="table-wrapper-scroll-y custom-scrollbar table-responsive">
                            <table class='table table-bordered table-striped'>
                                <thead>
                                    <th>Status</th>
                                    <th>Variable / Field Name</th>
                                    <th>Field Label <br /> <small><i><b>Field Note</b></i></small></th>
                                    <th>Field Attributes (Field Type, Validation, Choices, Calculations, etc.)</th>
                                </thead>

                                <?php
                                $this->ColorTable($allItems, $old);
                                ?>
                            </table>
                        </div>
                    <?php } ?>
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
