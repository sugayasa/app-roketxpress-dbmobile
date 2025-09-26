<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function generateArrFieldInsertUpdate($postVar, $arrPostVar, $arrFieldName){
	
	$arrInsertField	=	array();
	$i				=	0;
	
	foreach($arrPostVar as $postVarKey){
		
		if(isset($postVar[$postVarKey])){
			$arrInsertField	=	array_merge(array($arrFieldName[$i]=>$postVar[$postVarKey]), $arrInsertField);
		}
		$i++;
		
	}
	
	return $arrInsertField;
	
}

function switchMySQLErrorCode($errCode, $token){
	
	switch($errCode){
		case 0		:	setResponseNotModified(array("token"=>$token, "msg"=>"No data changes"));
						break;
		case 1062	:	setResponseConflict(array("token"=>$token, "msg"=>"There is a duplication of input data"));
						break;
		case 1054	:	setResponseNoContent(array("token"=>$token, "msg"=>"Database internal script error"));
						break;
		default		:	setResponseUnknown(array("token"=>$token, "msg"=>"Unkown database internal error"));
						break;
	}
	
	return true;
	
}