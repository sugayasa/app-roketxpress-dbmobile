<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function setResponseOk($arrData){
	$endResponse	=	array_merge(array("status"=>200, "msg"=>"OK"), $arrData);
	header('Content-Type: application/json');
	echo json_encode($endResponse);
	die();
}

function setResponseNoContent($arrData){
	$endResponse	=	array_merge(array("status"=>204, "msg"=>"No Content"), $arrData);
	header('Content-Type: application/json');
	echo json_encode($endResponse);
	die();
}

function setResponseNotModified($arrData){
	$endResponse	=	array_merge(array("status"=>304, "msg"=>"Not Modified"), $arrData);
	header('Content-Type: application/json');
	echo json_encode($endResponse);
	die();
}

function setResponseBadRequest($arrData){
	$endResponse	=	array_merge(array("status"=>400, "msg"=>"Bad Request"), $arrData);
	header('Content-Type: application/json');
	echo json_encode($endResponse);
	die();
}

function setResponseForbidden($arrData){
	$endResponse	=	array_merge(array("status"=>403, "msg"=>"Bad Forbidden"), $arrData);
	header('Content-Type: application/json');
	echo json_encode($endResponse);
	die();
}

function setResponseNotFound($arrData){
	$endResponse	=	array_merge(array("status"=>404, "msg"=>"Not Found"), $arrData);
	header('Content-Type: application/json');
	echo json_encode($endResponse);
	die();
}

function setResponseConflict($arrData){
	$endResponse	=	array_merge(array("status"=>409, "msg"=>"Conflict"), $arrData);
	header('Content-Type: application/json');
	echo json_encode($endResponse);
	die();
}

function setResponseExpiresSession($arrData){
	$endResponse	=	array_merge(array("status"=>410, "msg"=>"Gone"), $arrData);
	header('Content-Type: application/json');
	echo json_encode($endResponse);
	die();
}

function setResponseInternalServerError($arrData){
	$endResponse	=	array_merge(array("status"=>409, "msg"=>"Internal server error"), $arrData);
	header('Content-Type: application/json');
	echo json_encode($endResponse);
	die();
}