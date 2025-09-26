<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function decodeJsonPost(){
	return json_decode(trim(file_get_contents('php://input')), true);
}

function validateVar($postVar, $arrVarValidate){
	
	$msg	=	false;
	foreach($postVar as $varName=>$value){
		foreach($arrVarValidate as $varValidate){
			$validateName	=	$varValidate[0];
			$validateType	=	$varValidate[1];
			$validateText	=	$varValidate[2];
			
			if($varName == $validateName){
				
				switch($validateType){
					case "text"		:	if($value == ""){
											$msg	=	"Harap masukkan ".$validateText;
										}
										break;
					case "number"	:	if($value == "" || $value <= 0){
											$msg	=	"Harap masukkan nilai ".$validateText." lebih dari 0 (nol)";
										}
										break;
					case "option"	:	if($value == "" || $value == 0){
											$msg	=	"Harap pilih ".$validateText;
										}
										break;
					default			:	if($value == ""){
											$msg	=	"Harap masukkan ".$validateText;
										}
										break;
				}
				
			}
		}
	}
	
	return $msg;
	
}

function generateOptYear(){
	
	$tahunawal	=	2018;
	$tahunakhir	=	date('Y');
	$opttahun	=	"";
	for($i=$tahunawal; $i<=$tahunakhir; $i++){
		$selected	=	$i == date('Y') ? "selected" : "";
		$opttahun	.=	"<option value='".$i."' ".$selected.">".$i."</option>";
	}
	
	return $opttahun;
	
}

function generateOptJam(){
	
	$optjam	=	"";
	for($j=0; $j<=23; $j++){
		$padval	=	str_pad($j, 2, "0", STR_PAD_LEFT);
		$optjam	.=	"<option value='".$padval."'>".$padval."</option>";
	}
	
	return $optjam;
	
}

function getPageProperties($page, $dataperpage){

	$startid	=	($page * 1 - 1) * $dataperpage;
	$datastart	=	$startid + 1;
	$dataend	=	$datastart + $dataperpage - 1;
	
	return array($startid, $datastart, $dataend);
	
}

function validatePostVar($postArr, $varName, $badReqResponse = false){
	
	if($badReqResponse) isset($postArr[$varName]) && $postArr[$varName] <> "" ? $postArr[$varName] : setResponseBadRequest(array("msg"=>"Bad Request - ".$varName));
	return isset($postArr[$varName]) ? $postArr[$varName] : "";
	
}

function validatePostVarBody($varName, $badReqResponse = false){
	
	$ci	=& get_instance();
	if($badReqResponse) null !== $ci->input->post($varName) && $ci->input->post($varName) <> "" ? $ci->input->post($varName) : setResponseBadRequest(array());
	return null !== $ci->input->post($varName) ? $ci->input->post($varName) : "";
	
}

function checkMailPattern($str) {
	return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
}
	
function noimage($type){
	header	("Content-Type: image/jpeg");
	$filename	=	$type == "noimage" ? PATH_STORAGE."noimage.jpg" : PATH_STORAGE."errimage.jpg";
	$image 		= 	imagecreatefromjpeg($filename);
	imagejpeg		($image,NULL);
	imagedestroy	($image);	
}