<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function setArrayOneDimention($resultDbase){
	
	$returnData	=	array();
	foreach($resultDbase as $arrKey){
		$arrToPush	=	array();
		foreach($arrKey as $value){
			$arrToPush[]	=	$value;
		}
		$returnData[]	=	$arrToPush;
	}
	
	return $returnData;
	
}

function array_flatten($array) { 
  if (!is_array($array)) { 
    return FALSE; 
  } 
  $result = array(); 
  foreach ($array as $key => $value) { 
    if (is_array($value)) { 
      $result = array_merge($result, array_flatten($value)); 
    } else { 
      $result[$key] = $value; 
    } 
  } 
  return $result; 
}

function array_onedimention($array) {
  $result = array(); 
  foreach ($array as $key => $value) { 
    array_push($result, $value);
  } 
  return $result; 
}

function array_mergeResultSQL($sqlResult, $key1, $key2){
	
	$sqlResult[$key1]	=	array("value"=>$sqlResult[$key1], "label"=>$sqlResult[$key2]);
	unset($sqlResult[$key2]);
	return $sqlResult;
	
}