<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;

class AdditionalIncome extends CI_controller {
	
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
		
		if($method != 'GET' && $funcName != "imageAdditionalIncome"){
			
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
			$this->partnerName	=	$detailPartner['PARTNERNAME'];
		}
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function reportData(){
		$this->load->model('ModelAdditionalIncome');

		$date			=	date('Y-m-d');
		$dateStart		=	validatePostVar($this->postVar, 'dateStart', false);
		$dateStart		=	!isset($dateStart) || $dateStart == "" ? $date : $dateStart;
		$dateEnd		=	validatePostVar($this->postVar, 'dateEnd', false);
		$dateEnd		=	!isset($dateEnd) || $dateEnd == "" ? $date : $dateEnd;
		$dataReport		=	$this->ModelAdditionalIncome->getDataAdditionalIncome($this->idPartner, $dateStart, $dateEnd);
		$dataPointRate	=	$this->ModelAdditionalIncome->getDataAdditionalIncomePointRate();
		
		setResponseOk(
			array(
				"token"			=>	$this->newToken,
				"dataReport"	=>	$dataReport,
				"dataPointRate"	=>	$dataPointRate
			)
		);
	}
	
	public function uploadImageAdditionalIncome(){
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
				
		$filename	=	str_replace(" ", "_", $_FILES["uploaded_file"]["name"]);
		$dir		=	PATH_STORAGE_ADDITIONAL_INCOME_IMAGE;
		
		if(!file_exists($dir.$filename)){
			$move	=	move_uploaded_file($_FILES["uploaded_file"]["tmp_name"], $dir.$filename);
			if($move){
				setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_ADDITIONAL_INCOME_IMAGE.$filename));
			} else {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. Please try again"));
			}
		} else {
			setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_ADDITIONAL_INCOME_IMAGE.$filename));
		}
	}
	
	public function imageAdditionalIncome($filename){
		$loc	=	PATH_STORAGE_ADDITIONAL_INCOME_IMAGE.$filename;
		if(file_exists($loc)) {
			$image	= 	@imagecreatefromjpeg($loc) or $image = 'testpng';
			if($image == "testpng"){
				$image 		= 	@imagecreatefrompng($loc) or $image = 'brokenimage';
				if($image	==	'brokenimage') {
					$this->noimage('brokenimage');	
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
			$this->noimage("noimage");
		}
	}
	
	private function noimage($type){
		header	("Content-Type: image/jpeg");
		$filename	=	$type == "noimage" ? PATH_STORAGE."noimage.jpg" : PATH_STORAGE."errimage.jpg";
		$image 		= 	imagecreatefromjpeg($filename);
		imagejpeg		($image,NULL);
		imagedestroy	($image);
	}
	
	public function submitAdditionalIncome(){
		$this->load->model('MainOperation');
		$this->load->model('ModelAdditionalIncome');

		$dateReceipt				=	validatePostVar($this->postVar, 'dateReceipt', true);
		$additionalIncomeNominal	=	validatePostVar($this->postVar, 'additionalIncomeNominal', true);
		$fileReceiptName			=	validatePostVar($this->postVar, 'fileReceiptName', true);
		$notesDescription			=	validatePostVar($this->postVar, 'notesDescription', true);
		$arrInsertAdditionalIncome	=	[
			"IDDRIVER"		=>	$this->idPartner,
			"DESCRIPTION"	=>	$notesDescription,
			"IMAGERECEIPT"	=>	$fileReceiptName,
			"INCOMENOMINAL"	=>	$additionalIncomeNominal,
			"INCOMEDATE"	=>	$dateReceipt,
			"INPUTTYPE"		=>	2,
			"INPUTUSER"		=>	$this->partnerName,
			"INPUTDATETIME"	=>	date('Y-m-d H:i:s'),
			"APPROVALSTATUS"=>	0
		];
		
		$procInsertAdditionalIncome	=	$this->MainOperation->addData("t_additionalincome", $arrInsertAdditionalIncome);
		if(!$procInsertAdditionalIncome['status']) switchMySQLErrorCode($procInsertAdditionalIncome['errCode'], $this->newToken);
		
		if(PRODUCTION_URL){
			$partnerName	=	$this->partnerName;
			$totalApproval	=	$this->ModelAdditionalIncome->getTotalAdditionalIncomeApproval();
			$factory		=	(new Factory)
								->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)
								->withDatabaseUri(FIREBASE_RTDB_URI);
			$database		=	$factory->createDatabase();
			$reference		=	$database->getReference(FIREBASE_RTDB_MAINREF_NAME."unprocessedFinanceDriver/additionalIncome")
								->set([
									'newAdditionalIncomeStatus'	=>	true,
									'newAdditionalIncomeTotal'	=>	$totalApproval,
									'newAdditionalIncomeMessage'=>	"New additional income request from ".$partnerName." - ".number_format($additionalIncomeNominal, 0, '.', ',')." IDR.<br/>Description/Notes : ".$notesDescription,
									'timestampUpdate'			=>	gmdate("YmdHis")
								]);
		}
		
		setResponseOk(
			array(
				"token"	=>	$this->newToken,
				"msg"	=>	"Additional income data has been added. Please wait for approval"
			)
		);
	}
	
}