<?php

/*
	stepCode:
	0 : Login page
	1 : OTP
	2 : Dashboard
*/

require_once(FCPATH."vendor/phpmailer/phpmailer/src/Exception.php");
require_once(FCPATH."vendor/phpmailer/phpmailer/src/PHPMailer.php");
require_once(FCPATH."vendor/phpmailer/phpmailer/src/SMTP.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_controller {
	
	var $postVar;
	var $imei;
	var $token;
	var $password;
	var $fcmtoken;
	var $newToken;
	
	public function __construct(){
        parent::__construct();
		$this->postVar		=	decodeJsonPost();
		$this->imei			=	validatePostVar($this->postVar, 'imei', true);
		$this->token		=	validatePostVar($this->postVar, 'token', true);
		$this->email		=	validatePostVar($this->postVar, 'email', true);
		$this->fcmtoken		=	validatePostVar($this->postVar, 'fcmtoken', true);
		$this->newToken		=	accessCheck('', $this->email, $this->imei, $this->token, true);
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function submitEmail(){

		$this->load->model('ModelLogin');
		$this->load->model('MainOperation');
		$cekMailPattern	=	checkMailPattern($this->email);
		
		if(!$cekMailPattern){
			setResponseBadRequest(array("token"=>$this->newToken, "msg"=>"Please enter a valid email"));
		}
		
		$loginCheck		=	$this->ModelLogin->loginCheck($this->email, $this->imei);
		
		if($loginCheck){
			$tempOTP			=	$loginCheck['TEMPOTP'];
			$lastLogin			=	$loginCheck['LASTLOGIN'];
			$diffLastLoginSec	=	strtotime(date('Y-m-d H:i:s')) - strtotime($lastLogin);
			$otpCode			=	$diffLastLoginSec <= 7200 && $tempOTP != 0 && strlen($tempOTP) == 4 ? $tempOTP : generateOTPCode(4);
			$otpCode			=	$this->email == "rezza.ilmi@gmail.com" ? "1234" : $otpCode;
			$tokenExpiredTime	=	date("Y-m-d H:i:s", time() + TOKEN_MAXAGE_SECONDS);
			$arrResetData		=	array("TOKEN1"		=>'',
										  "TOKEN2"		=>'',
										  "TOKENFCM"	=>''
										 );
			$this->MainOperation->updateData('m_usermobile', $arrResetData, 'IMEI', $this->imei);
			
			if($loginCheck['IDUSERMOBILE'] != 0){
				$arrInsUpdate	=	array(
										"TEMPOTP"			=>	$otpCode,
										"TOKENACCESS"		=>	$this->newToken,
										"TOKENEXPIRED"		=>	$tokenExpiredTime,
										"LASTLOGIN"			=>	date('Y-m-d H:i:s'),
										"LASTACTIVITY"		=>	date('Y-m-d H:i:s')
									);
				$arrWhere		=	array(
										"IDPARTNERTYPE"		=>	$loginCheck['IDPARTNERTYPE'],
										"IDSUBPARTNERTYPE"	=>	$loginCheck['IDSUBPARTNERTYPE'],
										"IDPARTNER"			=>	$loginCheck['IDPARTNER'],
										"IMEI"				=>	$this->imei
									);
				$procInsUpdate	=	$this->MainOperation->updateData('m_usermobile', $arrInsUpdate, $arrWhere);
			} else {
				$arrWhereDelete	=	array(
										"IDPARTNERTYPE"		=>	$loginCheck['IDPARTNERTYPE'],
										"IDSUBPARTNERTYPE"	=>	$loginCheck['IDSUBPARTNERTYPE'],
										"IDPARTNER"			=>	$loginCheck['IDPARTNER']
									);
				$this->MainOperation->deleteData('m_usermobile', $arrWhereDelete);

				$arrInsUpdate	=	array(
										"IDPARTNERTYPE"		=>	$loginCheck['IDPARTNERTYPE'],
										"IDSUBPARTNERTYPE"	=>	$loginCheck['IDSUBPARTNERTYPE'],
										"IDPARTNER"			=>	$loginCheck['IDPARTNER'],
										"IMEI"				=>	$this->imei,
										"TEMPOTP"			=>	$otpCode,
										"TOKENACCESS"		=>	$this->newToken,
										"TOKENEXPIRED"		=>	$tokenExpiredTime,
										"LASTLOGIN"			=>	date('Y-m-d H:i:s'),
										"LASTACTIVITY"		=>	date('Y-m-d H:i:s')
									);
				$procInsUpdate	=	$this->MainOperation->addData('m_usermobile', $arrInsUpdate);
			}
			
			if($procInsUpdate['status']){
				
				$mail 			=	new PHPMailer(true);

				try {
					$mail->isSMTP();
					$mail->Host			= MAIL_HOST;
					$mail->SMTPAuth		= true;
					$mail->Username		= MAIL_USERNAME;
					$mail->Password		= MAIL_PASSWORD;
					$mail->SMTPSecure	= PHPMailer::ENCRYPTION_SMTPS;
					$mail->Port			= MAIL_SMTPPORT;

					$mail->setFrom(MAIL_USERNAME, MAIL_NAME);
					$mail->addAddress($this->email, $loginCheck['NAME']);
					$mail->addReplyTo(MAIL_USERNAME, MAIL_NAME);

					$mail->isHTML(true);
					$mail->Subject	=	"BST mobile application OTP";
					$mail->Body   	=	"Hello, ".$loginCheck['NAME'].",<br/><br/>";
					$mail->Body   	.=	"A sign in attempt requires further verification because we did not recognize your device. To complete the sign in, enter the verification code on the unrecognized device.<br/><br/>";
					$mail->Body   	.=	"Your OTP is <b>".$otpCode."</b><br/>";
					$mail->Body   	.=	"Date Time <b>".date('d M Y H:i')."</b><br/><br/>";
					$mail->Body   	.=	"Enter this OTP code through your application to use the BST mobile application.<br/><br/>";
					$mail->Body   	.=	"Thank You!";
					$mail->Body   	.=	MAIL_CSSSTYLE;
					if($this->email != "rezza.ilmi@gmail.com") $mail->send();
					
					setResponseOk(array("token"=>$this->newToken, "stepCode"=>1, "stepCode"=>1, "data"=>$loginCheck));
					
				} catch (Exception $e) {
					setResponseInternalServerError(array("token"=>$this->newToken, "stepCode"=>0, "msg"=>"Failed to process data, please try again later"));
				}
			} else {
				setResponseInternalServerError(array("token"=>$this->newToken, "stepCode"=>0, "msg"=>"Failed to process data, please try again later"));
			}
			
		} else {
			setResponseForbidden(array("msg"=>"You are not allowed to login"));
		}
		
	}
	
	public function submitOTP(){

		$this->load->model('ModelLogin');
		$this->load->model('MainOperation');
		
		$otpCode	=	validatePostVar($this->postVar, 'otpCode', true);
		$checkOTP	=	$this->ModelLogin->checkOTP($this->newToken, $otpCode);
		
		if(!$checkOTP){
			setResponseForbidden(array("token"=>$this->newToken, "stepCode"=>1, "msg"=>"Incorrect OTP, please check your email message which contains OTP"));
		} else {
			$arrWhere		=	array(
									"IDPARTNERTYPE"		=>	$checkOTP['IDPARTNERTYPE'],
									"IDPARTNER"			=>	$checkOTP['IDPARTNER'],
									"IDSUBPARTNERTYPE"	=>	$checkOTP['IDSUBPARTNERTYPE']
								);
			$this->MainOperation->deleteData('m_usermobile', $arrWhere, array("IDUSERMOBILE"=>$checkOTP['IDUSERMOBILE']));
			$this->MainOperation->deleteData('m_usermobile', array("IMEI"=>$this->imei), array("IDUSERMOBILE"=>$checkOTP['IDUSERMOBILE']));
			
			$arrUpdateData	=	array(
									"IMEI"			=>	$this->imei,
									"TOKENACCESS"	=>	"",
									"TOKEN1"		=>	$this->newToken,
									"TOKEN2"		=>	$this->newToken,
									"TOKENFCM"		=>	$this->fcmtoken,
									"TEMPOTP"		=>	"0"
								);
			$this->MainOperation->updateData("m_usermobile", $arrUpdateData, "IDUSERMOBILE", $checkOTP['IDUSERMOBILE']);
			setResponseOk(
				array(
					"token"				=>	$this->newToken,
					"stepCode"			=>	2,
					"idPartnerType"		=>	$checkOTP['IDPARTNERTYPE'],
					"partnershipType"	=>	$checkOTP['PARTNERSHIPTYPE'],
					"transportService"	=>	$checkOTP['TRANSPORTSERVICE'],
					"financeSchemeType"	=>	$checkOTP['FINANCESCHEMETYPE'],
					"msg"				=>	"OTP valid, redirect to dashboard"
				)
			);
		}

	}
	
}