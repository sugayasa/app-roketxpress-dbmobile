<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AgreementDriver extends CI_controller {
	
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
		
		if($method != 'GET' && $funcName != "fileMaster" && $funcName != "signedLetter"){
			if(isset($_FILES) && count($_FILES) > 0){
				$this->imei		=	isset($_POST['imei']) ? $_POST['imei'] : setResponseBadRequest(array());
				$this->fcmtoken	=	isset($_POST['fcmtoken']) ? $_POST['fcmtoken'] : setResponseBadRequest(array());
				$this->token	=	isset($_POST['token']) ? $_POST['token'] : setResponseBadRequest(array());
				$this->email	=	numberValidator(isset($_POST['email']) ? $_POST['email'] : "");
			} else {
				$this->postVar	=	decodeJsonPost();
				$this->imei		=	validatePostVar($this->postVar, 'imei', true);
				$this->fcmtoken	=	validatePostVar($this->postVar, 'fcmtoken', true);
				$this->token	=	validatePostVar($this->postVar, 'token', false);
				$this->email	=	numberValidator(validatePostVar($this->postVar, 'email', false));
			}

			$this->load->model('MainOperation');
			$this->newToken	=	accessCheck($this->fcmtoken, $this->email, $this->imei, $this->token, true);
			
			$detailPartner		=	$this->MainOperation->getDetailPartner($this->newToken);
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
	
	public function fileMaster($filename){
		$fullPathFilename	=	PATH_AGREEMENT_MASTER.$filename;
		$isFileExist		=	file_exists($fullPathFilename);
		
		if(!$isFileExist){
			$arrMessages=	[
				"heading"	=>	"Invalid file",
				"message"	=>	"The URL you are redirecting to is invalid"
			];
			$this->output->set_status_header('404');
			$this->load->view('errors/html/error_404', $arrMessages);
		} else {
			$pdfData	=	file_get_contents($fullPathFilename);
			header('Content-Type: application/pdf');
			header('Content-Disposition: inline; filename="' . basename($fullPathFilename) . '"');
			header('Content-Length: ' . strlen($pdfData));
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			
			echo $pdfData;
		}
	}
	
	public function uploadAgreementSignature(){
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
		$dir			=	PATH_AGREEMENT_SIGNATURE;
		
		if(!file_exists($dir.$filename)){

			$move		=	move_uploaded_file($_FILES["uploaded_file"]["tmp_name"], $dir.$filename);
			if($move){
				setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully"));
			} else {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. Please try again"));
			}
			
		} else {
			setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully"));
		}
	}
	
	public function uploadDriverIdentity(){
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
		$dir			=	PATH_AGREEMENT_IDENTITY;
		
		if(!file_exists($dir.$filename)){

			$move		=	move_uploaded_file($_FILES["uploaded_file"]["tmp_name"], $dir.$filename);
			if($move){
				setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully"));
			} else {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. Please try again"));
			}
			
		} else {
			setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully"));
		}
	}

	public function submitAgreement(){
		$this->load->model('MainOperation');
		
		$idDriverAgreement	=	validatePostVar($this->postVar, 'idDriverAgreement', true);
		$fileDriverIdentity	=	validatePostVar($this->postVar, 'fileDriverIdentity', false);
		$fileSignature		=	validatePostVar($this->postVar, 'fileSignature', true);
		$arrUpdateAgreement	=	array(
									"FILEIDENTITY"			=>	$fileDriverIdentity,
									"FILEAGREEMENTSIGNATURE"=>	$fileSignature,
									"DATESIGNATURE"			=>	date("Y-m-d H:i:s"),
									"APPROVALSTATUS"		=>	1
								);
		$procUpdateAgreement=	$this->MainOperation->updateData("t_driveragreement", $arrUpdateAgreement, 'IDDRIVERAGREEMENT', $idDriverAgreement);
		
		if(!$procUpdateAgreement['status']){
			switchMySQLErrorCode($procUpdateAgreement['errCode'], $this->newToken);
		}
		
		setResponseOk(array("token"=>$this->newToken, "msg"=>"Your agreement have been submitted and waiting for approval"));
	}

	public function agreementList(){
		$this->load->model('ModelAgreementDriver');
		$agreementList	=	$this->ModelAgreementDriver->getAgreementList($this->idPartner);

		setResponseOk(array("token"=>$this->newToken, "agreementList"=>$agreementList));
	}
	
	public function signedLetter($filename){
		$fullPathFilename	=	PATH_AGREEMENT_SIGNED_LETTER.$filename;
		$isFileExist		=	file_exists($fullPathFilename);
		
		if(!$isFileExist){
			$arrMessages=	[
				"heading"	=>	"Invalid file",
				"message"	=>	"The URL you are redirecting to is invalid"
			];
			$this->output->set_status_header('404');
			$this->load->view('errors/html/error_404', $arrMessages);
		} else {
			header('Content-Type: application/pdf');
			header('Content-Disposition: inline; filename="' . basename($fullPathFilename) . '"');
			header('Content-Transfer-Encoding: binary');
			header('Accept-Ranges: bytes');
			@readfile($fullPathFilename);
		}
	}
	
}