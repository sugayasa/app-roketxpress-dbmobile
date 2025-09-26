<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
	stepCode:
	0 : Login page
	1 : OTP
	2 : Dashboard
*/

function accessCheck($fcmtoken, $email, $imei, $token, $callback = true){
	
	$ci	=& get_instance();
	$ci->load->model('ModelAccess');
	
	$jsonData		=	trim(file_get_contents('php://input'));
	$ci->ModelAccess->insertLogDataSend($jsonData);
	if($imei == "") setResponseBadRequest(array("msg"=>"Invalid request parameter [IMEI]"));
	if($fcmtoken == "" && $callback == false) setResponseBadRequest(array("msg"=>"Invalid request parameter [FCM Token]"));
	
	$jsonData		=	json_decode($jsonData);
	$versionNumber	=	isset($jsonData->versionNumber) && $jsonData->versionNumber != "" ? $jsonData->versionNumber : 0;
	$updatefcm		=	$callback == true ? false : true;
	
	if($token == ""){
		$isImeiRegistered	=	$ci->ModelAccess->isImeiRegistered($imei);
		if($isImeiRegistered){
			$ci->ModelAccess->resetTokenByImei($imei);
		}
		
		$newToken			=	generateNewToken(TOKEN_LENGTH);
		setResponseExpiresSession(
			array(
				"token"				=>	$newToken,
				"whatsappNumber"	=>	WA_CENTER_NUMBER,
				"msg"				=>	"Forbidden access. Please login by submit your email",
				"stepCode"			=>	0,
				"updateMsg"			=>	UPDATE_MSG,
				"updateForce"		=>	UPDATE_FORCE
			)
		);
	} else {
		$isTokenExist		=	$ci->ModelAccess->isTokenExist($token);
		if($isTokenExist){
			
			$newToken		=	generateNewToken(TOKEN_LENGTH);
			$isImeiMatch	=	$imei == $isTokenExist['IMEI'];
			
			if($isImeiMatch){

				$tempOTP		=	$isTokenExist['TEMPOTP'];
				$stepCode		=	$tempOTP != "" && $tempOTP != 0 ? 1 : 2;
				$isTokenExpired	=	isTokenExpiredByTime($isTokenExist['TOKENEXPIRED']);

				if($isTokenExpired){
					$ci->ModelAccess->updateNewAccessToken($versionNumber, $imei, $newToken, $fcmtoken, true, $updatefcm);
					if($callback) return $newToken;
					setResponseOk(
						array(
							"token"			=>	$newToken,
							"whatsappNumber"=>	WA_CENTER_NUMBER,
							"stepCode"		=>	$stepCode,
							"updateMsg"		=>	UPDATE_MSG,
							"updateForce"	=>	UPDATE_FORCE,
							"data"			=>	""
						)
					);
				} else {
					$ci->ModelAccess->updateLastActivity($versionNumber, $imei, $fcmtoken);
					$newestToken	=	$ci->ModelAccess->getNewestToken($imei, $token);
					if($callback) return $newestToken;
					setResponseOk(
						array(
							"token"			=>	$newestToken,
							"stepCode"		=>	$stepCode,
							"updateMsg"		=>	UPDATE_MSG,
							"updateForce"	=>	UPDATE_FORCE,
							"data"			=>	""
						)
					);
				}

			} else {
				setResponseExpiresSession(
					array(
						"token"			=>	$newToken,
						"whatsappNumber"=>	WA_CENTER_NUMBER,
						"msg"			=>	"Forbidden access. Please login by submit your email",
						"stepCode"		=>	0,
						"updateMsg"		=>	UPDATE_MSG,
						"updateForce"	=>	UPDATE_FORCE
					)
				);
			}

		} else {
			$accessFunctionName	=	$ci->uri->segment(1);
			
			if($callback){
				if($accessFunctionName == "submitEmail" || $accessFunctionName == "submitOTP"){
					return $token;
				} else {
					setResponseExpiresSession(
						array(
							"token"			=>	$token,
							"whatsappNumber"=>	WA_CENTER_NUMBER,
							"msg"			=>	"Forbidden access. Please login by submit your email",
							"stepCode"		=>	0,
							"updateMsg"		=>	UPDATE_MSG,
							"updateForce"	=>	UPDATE_FORCE
						)
					);
				}
			} else {				
				setResponseExpiresSession(
					array(
						"token"			=>	$token,
						"whatsappNumber"=>	WA_CENTER_NUMBER,
						"msg"			=>	"Forbidden access. Please login by submit your email",
						"stepCode"		=>	0,
						"updateMsg"		=>	UPDATE_MSG,
						"updateForce"	=>	UPDATE_FORCE
					)
				);
			}
		}
	}
}

function isTokenExpiredByTime($timeTokenExpired){
	$tokenExpiredSec	=	strtotime($timeTokenExpired);
	$timeNowSec			=	strtotime(date('Y-m-d H:i:s'));
	$timeDiff			=	$tokenExpiredSec - $timeNowSec;

	if(($timeDiff * 1) <= 1) return true;
	return false;	
}

function generateNewToken($length){
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
	
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generateOTPCode($length){
	$characters			= '0123456789';
    $charactersLength	= strlen($characters);
    $randomString		= '';
	
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return str_pad($randomString * 1, $length, "0", STR_PAD_RIGHT);
}

function checkSecretPIN($token, $idPartnerType, $idPartner, $secretPIN){
	$ci	=& get_instance();
	$ci->load->model('MainOperation');

	if(strlen($secretPIN) != 4) setResponseForbidden(array("token" => $token, "msg" => "Secret PIN must be 4 digits"));

	$isSecretPINValid	=	$ci->MainOperation->checkSecretPINPartner($idPartnerType, $idPartner, $secretPIN);
	if(!$isSecretPINValid) setResponseForbidden(array("token" => $token, "msg" => "The secret PIN you entered is invalid"));
	return true;
}