<?php
function debug_to_console($data) {
    $output = $data;
    if (is_array($output)) $output = implode(',', $output);
	echo "<script>console.log('$output');</script>";
}

function formatted_dump($obj, $print_to_console=FALSE) {
	if ($print_to_console) debug_to_console($obj);
	else echo "<pre>". var_dump($obj) ."</pre>";
}

function CleanInput($string) { // sanitize user input to prevent sql injection
    $new_string = trim($string);
    $new_string = strip_tags($new_string);
    $new_string = htmlspecialchars($new_string);
    return $new_string;
}

function CreateSelectDropDown($sql, $default = NULL) {
    global $db;
    $select_string = "";
    $result = $db->query($sql)->fetchArray();
    #echo gettype($result["Type"]);
    if (count($result) > 0 ) $option_array = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $result["Type"]));
    if (isset($option_array)) {
        foreach($option_array as $option) {
            if ($option == $default) $select_string .= "<option selected value='{$default}'>".ucfirst($default)."</option>";
            else $select_string .= "<option value='$option'>".ucfirst($option)."</option>";
        }
    } else $select_string = "<li>Status not available</li>";
    return $select_string;
}


function InRange($x, $lo, $hi) { return ($x >= $lo) && ($x <= $hi); }



?>