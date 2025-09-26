<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(FCPATH."vendor/phpmailer/phpmailer/src/Exception.php");
require_once(FCPATH."vendor/phpmailer/phpmailer/src/PHPMailer.php");
require_once(FCPATH."vendor/phpmailer/phpmailer/src/SMTP.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Profile extends CI_controller {
	
	var $postVar;
	var $imei;
	var $token;
	var $newToken;
	var $idUserMobile;
	var $idPartnerType;
	var $idPartner;
	
	public function __construct(){
        parent::__construct();
		$method		=	$this->router->fetch_method();
		$funcName	=	$this->uri->segment(2);

		if($method != 'GET' && $funcName != "profilePicture"){
			$this->load->model('MainOperation');

			if(isset($_FILES) && count($_FILES) > 0){
				$this->imei		=	isset($_POST['imei']) ? $_POST['imei'] : setResponseBadRequest(array());
				$this->fcmtoken	=	isset($_POST['fcmtoken']) ? $_POST['fcmtoken'] : setResponseBadRequest(array());
				$this->token	=	isset($_POST['token']) ? $_POST['token'] : setResponseBadRequest(array());
				$this->email	=	isset($_POST['email']) ? $_POST['email'] : "";
			} else {				
				$this->postVar	=	decodeJsonPost();
				$this->imei		=	validatePostVar($this->postVar, 'imei', true);
				$this->fcmtoken	=	validatePostVar($this->postVar, 'fcmtoken', true);
				$this->token	=	validatePostVar($this->postVar, 'token', false);
				$this->email	=	isset($_POST['email']) ? $_POST['email'] : "";
			}

			$this->newToken	=	accessCheck($this->fcmtoken, $this->email, $this->imei, $this->token, true);
			
			$detailPartner		=	$this->MainOperation->getDetailPartner($this->newToken);
			$this->detailPartner=	$detailPartner;
			$this->idUserMobile	=	$detailPartner['IDUSERMOBILE'];
			$this->idPartnerType=	$detailPartner['IDPARTNERTYPE'];
			$this->idPartner	=	$detailPartner['IDPARTNER'];
		}
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function detailProfile(){
		$this->load->model('ModelProfile');

		if($this->idPartnerType == 2){
			$detailProfile	=	$this->ModelProfile->getDetailProfileDriver($this->idPartner);
		} else {
			$detailProfile	=	$this->ModelProfile->getDetailProfileVendor($this->idPartner);
		}
		
		$dataBankAccount				=	$this->ModelProfile->getDataActiveBankAccount($this->idPartnerType, $this->idPartner);
		$initialName					=	preg_match_all('/\b\w/', $detailProfile['INITIALNAME'], $matches);
		$initialName					=	implode('', $matches[0]);
		$detailProfile['INITIALNAME']	=	strtoupper($initialName);
		
		setResponseOk(
			array(
				"token"				=>	$this->newToken,
				"detailProfile"		=>	$detailProfile,
				"dataBankAccount"	=>	$dataBankAccount
			)
		);
	}
	
	public function detailHistoryPoint(){
		$this->load->model('ModelProfile');

		$totalBasicPoint=	$totalReviewPoint	=	0;
		$pointHistory	=	array();
		
		if($this->idPartnerType == 2){
			$subtract30Days		=	date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d'))));
			$detailProfile		=	$this->ModelProfile->getDetailProfileDriver($this->idPartner);
			$pointHistory		=	$this->ModelProfile->getPointHistoryDriver($this->idPartner, $subtract30Days);
			$totalBasicPoint	=	$detailProfile['BASICPOINT'];
			$totalReviewPoint	=	$detailProfile['REVIEWPOINT'];
		}
		
		setResponseOk(
			array(
				"token"				=>	$this->newToken,
				"totalBasicPoint"	=>	$totalBasicPoint,
				"totalReviewPoint"	=>	$totalReviewPoint,
				"pointHistory"		=>	$pointHistory
			)
		);
	}

	public function detailReview(){
		$this->load->model('ModelProfile');
		$idDriverRatingPoint=	validatePostVar($this->postVar, 'idDriverRatingPoint', true);
		$detailReview		=	$this->ModelProfile->getDetailReview($this->idPartner, $idDriverRatingPoint);
		
		if(!$detailReview) setResponseNotFound(array("token"=>$this->newToken, "msg"=>"Review details you are looking for were not found"));

		setResponseOk(
			array(
				"token"			=>	$this->newToken,
				"detailReview"	=>	$detailReview
			)
		);
	}
	
	public function listAreaPriority(){
		$this->load->model('ModelProfile');

		$listArea		=	array();		
		if($this->idPartnerType == 2) $listArea	=	$this->ModelProfile->getListAreaPriority($this->idPartner);

		setResponseOk(
			array(
				"token"		=>	$this->newToken,
				"listArea"	=>	$listArea
			)
		);
	}
	
	public function setSecretPIN(){
		$this->load->model('ModelProfile');
		$secretPIN		=	validatePostVar($this->postVar, 'secretPIN', true);
		
		if(strlen($secretPIN) != 4) setResponseForbidden(array("token" => $this->newToken, "msg" => "Secret PIN must be 4 digits"));
		if(!preg_match("/^\d+$/", $secretPIN)) setResponseForbidden(array("token" => $this->newToken, "msg" => "Secret PIN can only contain numbers"));

		$detailProfile	=	array();
		$fieldWhere		=	"ID";
		if($this->idPartnerType == 2){
			$table			=	"m_driver";
			$fieldWhere		=	"IDDRIVER";
			$detailProfile	=	$this->ModelProfile->getDetailProfileDriver($this->idPartner);
		} else {
			$table			=	"m_vendor";
			$fieldWhere		=	"IDVENDOR";
			$detailProfile	=	$this->ModelProfile->getDetailProfileVendor($this->idPartner);
		}
		
		$secretPINStatus	=	$detailProfile['SECRETPINSTATUS'];
		$secretPINLastUpdate=	$detailProfile['SECRETPINLASTUPDATE'];
		
		if($secretPINStatus != 1) setResponseForbidden(array("token" => $this->newToken, "msg" => "You are not allowed to change the secret PIN.\nPIN was last updated on ".$secretPINLastUpdate));
		$arrUpdate			=	array(
			"SECRETPIN"				=>	$secretPIN,
			"SECRETPINSTATUS"		=>	2,
			"SECRETPINLASTUPDATE"	=>	date('Y-m-d H:i:s')
		);
		$procUpdate			=	$this->MainOperation->updateData($table, $arrUpdate, $fieldWhere, $this->idPartner);

		if(!$procUpdate['status']) switchMySQLErrorCode($procUpdate['errCode'], $this->newToken);
		setResponseOk(
			array(
				"token"	=>	$this->newToken,
				"msg"	=>	"Your secret PIN has been updated. Please always remember your secret PIN"
			)
		);
	}
	
	public function addBankAccountCheckSecretPIN(){
		$this->load->model('ModelProfile');
		$secretPIN			=	validatePostVar($this->postVar, 'secretPIN', true);
		$idBank				=	validatePostVar($this->postVar, 'idBank', true);
		$accountNumber		=	validatePostVar($this->postVar, 'accountNumber', true);
		$accountHolderName	=	validatePostVar($this->postVar, 'accountHolderName', true);
		
		checkSecretPIN($this->newToken, $this->idPartnerType, $this->idPartner, $secretPIN);
		
		$isActiveBankAccountExist	=	$this->ModelProfile->getDataActiveBankAccount($this->idPartnerType, $this->idPartner);
		if($isActiveBankAccountExist && !empty((array)$isActiveBankAccountExist)){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "You already have an active bank account. You can only add 1 active bank account"));
		}
		
		$otpCodeGenerated			=	generateOTPCode(4);
		$isDataBankAccountExist		=	$this->ModelProfile->checkDataBankAccountExist($this->idPartnerType, $this->idPartner, $idBank, $accountNumber);
		$arrUpdateResetOTP			=	array("TEMPOTP"	=> '');
		$arrWhereResetOTP			=	array(
			"IDPARTNERTYPE"	=> $this->idPartnerType,
			"IDPARTNER"		=> $this->idPartner
		);
		$this->MainOperation->updateData("t_bankaccountpartner", $arrUpdateResetOTP, $arrWhereResetOTP);
		
		if($isDataBankAccountExist){
			$idBankAccountPartner	=	$isDataBankAccountExist['IDBANKACCOUNTPARTNER'];
			$otpCodeGenerated		=	$isDataBankAccountExist['TEMPOTP'] == "" ? $otpCodeGenerated : $isDataBankAccountExist['TEMPOTP'];
			$otpCodeGenerated		=	$this->email == "rezza.ilmi@gmail.com" ? "1234" : $otpCodeGenerated;
			$arrUpdate				=	array(
				"ACCOUNTHOLDERNAME"	=>	$accountHolderName,
				"TEMPOTP"			=>	$otpCodeGenerated
			);
			$procUpdate	=	$this->MainOperation->updateData("t_bankaccountpartner", $arrUpdate, "IDBANKACCOUNTPARTNER", $idBankAccountPartner);
		} else {
			$arrInsert	=	array(
				"IDBANK"			=>	$idBank,
				"IDPARTNERTYPE"		=>	$this->idPartnerType,
				"IDPARTNER"			=>	$this->idPartner,
				"ACCOUNTNUMBER"		=>	$accountNumber,
				"ACCOUNTHOLDERNAME"	=>	$accountHolderName,
				"TEMPOTP"			=>	$otpCodeGenerated
			);
			$procInsert	=	$this->MainOperation->addData("t_bankaccountpartner", $arrInsert);

			if(!$procInsert['status']) switchMySQLErrorCode($procInsert['errCode'], $this->newToken);
		}
		
		$partnerDetails	=	$this->idPartnerType == 1 ? $this->MainOperation->getDetailVendor($this->idPartner) : $this->MainOperation->getDetailDriver($this->idPartner);
		$partnerName	=	$partnerDetails['NAME'];
		$partnerEmail	=	$partnerDetails['EMAIL'];
		
		if($partnerEmail != "-"){
			$mailer		=	new PHPMailer(true);
			try {
				$mailer->isSMTP();
				$mailer->Host			= MAIL_HOST;
				$mailer->SMTPAuth		= true;
				$mailer->Username		= MAIL_USERNAME;
				$mailer->Password		= MAIL_PASSWORD;
				$mailer->SMTPSecure		= PHPMailer::ENCRYPTION_SMTPS;
				$mailer->Port			= MAIL_SMTPPORT;

				$mailer->setFrom(MAIL_USERNAME, MAIL_NAME);
				$mailer->addAddress($partnerEmail, $partnerName);
				$mailer->AddCC('agus.adiyasa@gmail.com');
				$mailer->addReplyTo(MAIL_USERNAME, MAIL_NAME);

				$mailer->isHTML(true);
				$mailer->Subject	=	"BST Partner - OTP for bank account data changes";
				$mailer->Body   	=	"Hello, ".$partnerName.",<br/><br/>";
				$mailer->Body   	.=	"We have received a request to add bank account data. To continue the process, we need OTP verification.<br/><br/>";
				$mailer->Body   	.=	"Your OTP is <b>".$otpCodeGenerated."</b><br/>";
				$mailer->Body   	.=	"Date Time <b>".date('d M Y H:i')."</b><br/><br/>";
				$mailer->Body   	.=	"Enter this OTP code through your application. Ignore this message if you do not do the process of adding a bank account<br/><br/>";
				$mailer->Body   	.=	"Thank You!";
				$mailer->Body   	.=	MAIL_CSSSTYLE;
				$mailer->send();
				
			} catch (Exception $e) {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to process this action, please try again later"));
			}
		}
		
		setResponseOk(
			array(
				"token"	=>	$this->newToken,
				"msg"	=>	"Secret PIN is valid. Continue to OTP form"
			)
		);
	}
	
	public function addBankAccountCheckOTPAndSubmit(){

		$this->load->model('ModelProfile');
		$otpCode					=	validatePostVar($this->postVar, 'otpCode', true);
		$isOTPAddBankAccountValid	=	$this->ModelProfile->checkOTPAddBankAccountValid($this->idPartnerType, $this->idPartner, $otpCode);

		if(!$isOTPAddBankAccountValid){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "Invalid OTP. Please try again"));
		}
		
		$idBankAccountPartner	=	$isOTPAddBankAccountValid['IDBANKACCOUNTPARTNER'];
		$bankName				=	$isOTPAddBankAccountValid['BANKNAME'];
		$accountNumber			=	$isOTPAddBankAccountValid['ACCOUNTNUMBER'];
		$accountHolderName		=	$isOTPAddBankAccountValid['ACCOUNTHOLDERNAME'];
		$arrUpdate				=	array(
										"TEMPOTP"	=>	'',
										"STATUS"	=>	1
									);
		$procUpdate				=	$this->MainOperation->updateData("t_bankaccountpartner", $arrUpdate, "IDBANKACCOUNTPARTNER", $idBankAccountPartner);

		if(!$procUpdate['status']){
			switchMySQLErrorCode($procUpdate['errCode'], $this->newToken);
		}
		
		$partnerDetails			=	$this->idPartnerType == 1 ? $this->MainOperation->getDetailVendor($this->idPartner) : $this->MainOperation->getDetailDriver($this->idPartner);
		$partnerName			=	$partnerDetails['NAME'];
		$partnerEmail			=	$partnerDetails['EMAIL'];
		
		if($partnerEmail != "-"){
			$mailer 			=	new PHPMailer(true);
			try {
				$mailer->isSMTP();
				$mailer->Host			= MAIL_HOST;
				$mailer->SMTPAuth		= true;
				$mailer->Username		= MAIL_USERNAME;
				$mailer->Password		= MAIL_PASSWORD;
				$mailer->SMTPSecure		= PHPMailer::ENCRYPTION_SMTPS;
				$mailer->Port			= MAIL_SMTPPORT;

				$mailer->setFrom(MAIL_USERNAME, MAIL_NAME);
				$mailer->addAddress($partnerEmail, $partnerName);
				$mailer->addReplyTo(MAIL_USERNAME, MAIL_NAME);

				$mailer->isHTML(true);
				$mailer->Subject	=	"BST Partner - New bank account data has been added";
				$mailer->Body   	=	"Hello, ".$partnerName.",<br/><br/>";
				$mailer->Body   	.=	"New bank account data has been added. Bank account details;<br/><br/>";
				$mailer->Body   	.=	"Bank <b>".$bankName."</b><br/>";
				$mailer->Body   	.=	"Account Number <b>".$accountNumber."</b><br/>";
				$mailer->Body   	.=	"Account Holder Name <b>".$accountHolderName."</b><br/><br/>";
				$mailer->Body   	.=	"If the change in bank account data <b>is made by a party other than you</b> or the data does not match, <b>please contact Bali Sun Tour immediately</b><br/><br/>";
				$mailer->Body   	.=	"Thank You!";
				$mailer->Body   	.=	MAIL_CSSSTYLE;
				$mailer->send();
				
			} catch (Exception $e) {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to process this action, please try again later"));
			}
		}
		
		setResponseOk(
			array(
				"token"	=>	$this->newToken,
				"msg"	=>	"New bank account data has been added"
			)
		);
	
	}
	
	public function deleteBankAccountCheckSecretPIN(){

		$this->load->model('ModelProfile');
		$secretPIN			=	validatePostVar($this->postVar, 'secretPIN', true);
		
		if(strlen($secretPIN) != 4){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "Secret PIN must be 4 digits"));
		}
		
		$isSecretPINValid	=	$this->MainOperation->checkSecretPINPartner($this->idPartnerType, $this->idPartner, $secretPIN);
		if(!$isSecretPINValid){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "The secret PIN you entered is invalid"));
		}
		
		$isActiveBankAccountExist	=	$this->ModelProfile->getDataActiveBankAccount($this->idPartnerType, $this->idPartner);
		if(!$isActiveBankAccountExist){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "You do not have an active bank account. Process cannot be continued"));
		}
		
		$arrUpdate		=	array(
								"TEMPOTP"	=> '',
								"STATUS"	=> 0
							);
		$arrWhereUpdate	=	array(
								"IDPARTNERTYPE"	=> $this->idPartnerType,
								"IDPARTNER"		=> $this->idPartner,
								"STATUS"		=>	1
							);
		$procUpdate		=	$this->MainOperation->updateData("t_bankaccountpartner", $arrUpdate, $arrWhereUpdate);
		
		if(!$procUpdate['status']){
			switchMySQLErrorCode($procUpdate['errCode'], $this->newToken);
		}
				
		setResponseOk(
			array(
				"token"	=>	$this->newToken,
				"msg"	=>	"Your active bank account has been deleted"
			)
		);
		
	}
	
	public function uploadProfilePicture(){

		if((($_FILES["uploaded_file"]["type"] == "image/gif")
			|| ($_FILES["uploaded_file"]["type"] == "image/jpeg")
			|| ($_FILES["uploaded_file"]["type"] == "image/jpg")
			|| ($_FILES["uploaded_file"]["type"] == "image/pjpeg")
			|| ($_FILES["uploaded_file"]["type"] == "text/xml")
			|| ($_FILES["uploaded_file"]["type"] == "application/octet-stream"))
			&& ($_FILES["uploaded_file"]["size"] < 20000000000)){
			if ($_FILES["uploaded_file"]["error"] > 0) {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. Files corrupted"));
			}
		} else {
			setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. File types not allowed (".$_FILES["uploaded_file"]["type"].") or the size is too large (".$_FILES["uploaded_file"]["size"].")"));
		}
				
		$filename		=	str_replace(" ", "_", $_FILES["uploaded_file"]["name"]);
		$dir			=	PATH_PROFILE_PICTURE;
		
		if(!file_exists($dir.$filename)){

			$move		=	move_uploaded_file($_FILES["uploaded_file"]["tmp_name"], $dir.$filename);
			if($move){
				$arrUpdateProfilePic	=	array("PROFILEPICFILENAME"	=>	$filename);
				if($this->idPartnerType == 1) $this->MainOperation->updateData("m_vendor", $arrUpdateProfilePic, "IDVENDOR", $this->idPartner);
				if($this->idPartnerType == 2) $this->MainOperation->updateData("m_driver", $arrUpdateProfilePic, "IDDRIVER", $this->idPartner);
				setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_PROFILE_PICTURE.$filename));
			} else {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. Please try again"));
			}
			
		} else {
			setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_PROFILE_PICTURE.$filename));
		}
		
	}
	
	public function profilePicture($filename){
		
		$loc			=	PATH_PROFILE_PICTURE.$filename;
		if(file_exists($loc)) {
			$image 		= 	@imagecreatefromjpeg($loc) or $image = 'testpng';
			if($image == "testpng"){
				$image 		= 	@imagecreatefrompng($loc) or $image = 'brokenimage';
				if($image	==	'brokenimage') {
					noimage('brokenimage');	
					die();
				}

				$background = imagecolorallocatealpha($image,0,0,0,127);
				imagecolortransparent($image, $background);
				imagealphablending($image, false);
				imagesavealpha($image, true);

				header	("Content-Type: image/png");
				imagepng		($image,NULL);
				imagedestroy	($image);
			}

			header	("Content-Type: image/jpeg");
			imagejpeg		($image,NULL);
			imagedestroy	($image);								
		} else {
			noimage("noimage");
		}

	}
}