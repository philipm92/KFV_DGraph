<?php
header('Content-Type: application/json');

use Shuchkin\SimpleXLSX;
require_once("assets/SimpleXLSX.php");

$filename = "20220223_Flandorfer_Dashboard-Daten_Verkehrstote_Berichtsjahr.xlsx";
$xlsx = SimpleXLSX::parse($filename);

// just assume states for base json list
$state_json = array(
  "Labels" => array(),
  "Dataset" => array(
    "Burgenland" => array("borderColor" => "#004899", "backgroundColor" => "#99b6d6", "data" => array()),
    "Kärnten" => array("borderColor" => "#a45b96", "backgroundColor" => "#dbbdd5", "data" => array()),
    "Niederösterreich" => array("borderColor" => "#dbdfdc", "backgroundColor" => "#f1f2f1", "data" => array()),
    "Oberösterreich" => array("borderColor" => "#4ca686", "backgroundColor" => "#b7dbcf", "data" => array()),
    "Salzburg" => array("borderColor" => "#458cc3", "backgroundColor" => "#b5d1e7", "data" => array()),
    "Steiermark" => array("borderColor" => "#e4e826", "backgroundColor" => "#f4f6a8", "data" => array()),
    "Tirol" => array("borderColor" => "#d9b300", "backgroundColor" => "#f0e199", "data" => array()),
    "Vorarlberg" => array("borderColor" => "#d64550", "backgroundColor" => "#efb5b9", "data" => array()),
    "Wien" => array("borderColor" => "#3599b8", "backgroundColor" => "#aed6e3", "data" => array())
  )
);


if ($xlsx) {
  // Produce array keys from the array values of 1st array element
  $header_values = $accidents = [];
  foreach ( $xlsx->rows() as $k => $r ) {
      if ( $k === 0 ) {
          $header_values = $r;
          continue;
      }
      $accidents[] = array_combine( $header_values, $r);
  }
  // order by array like sql php
  $states_array = array();
  $report_year_array = array();
  // Obtain a list of columns
  foreach ($accidents as $key => $row) {
    $states_array[$key]  = $row["Bundesland"];
    $report_year_array[$key] = $row["Berichtsjahr"];
  }
  array_multisort($states_array, SORT_ASC, $report_year_array, SORT_ASC, $accidents);


  // group array and sum it by number of deaths per year and get ONLY necessary data
  $accidents_totalized = array_reduce($accidents, function($carry, $item){
    $key = "{$item['Bundesland']} {$item['Jahr']}";
    if(!isset($carry[$key])){ 
        $carry[$key] = ["Bundesland" => $item["Bundesland"], "Jahr" => $item["Jahr"], "Getötete" => $item["Getötete"]]; 
    } else { 
        $carry[$key]["Getötete"] += $item["Getötete"]; 
    } 
    return $carry; 
  });


  // finish json file
  $states = array_keys($state_json["Dataset"]);
  foreach ($states as $state_key) {
    if ($state_key == "Labels") continue;
    foreach ($accidents_totalized as $tot_key => $tot_array) {
      // get sorted years as label into to-be-made json
      $year = (int)$accidents_totalized[$tot_key]["Jahr"];
      if (!in_array($year, $state_json["Labels"])) array_push($state_json["Labels"], $year);
      // add totalized death to data
      if (str_contains($tot_key, $state_key) && array_key_exists($tot_key, $accidents_totalized)) {
        $death = $accidents_totalized[$tot_key]["Getötete"];
        array_push($state_json["Dataset"][$state_key]["data"], $death);
        unset($accidents_totalized[$tot_key]); // remove from array to avoid duplicates
      }
      
    }
    
  }
  echo json_encode($state_json);
  

} else {
  echo SimpleXLSX::parseError();
}

