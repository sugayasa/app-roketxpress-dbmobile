<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;

class Reimbursement extends CI_controller {
	
	var $postVar;
	var $imei;
	var $token;
	var $newToken;
	var $detailPartner;
	var $idUserMobile;
	var $idPartnerType;
	var $idPartner;
	
	public function __construct(){
        parent::__construct();
		$method		=	$this->router->fetch_method();
		$funcName	=	$this->uri->segment(2);
		
		if($method != 'GET' && $funcName != "imageReimbursement"){
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
	
	public function listReimbursement(){
		$this->load->model('ModelReimbursement');
		$dateStart	=	validatePostVar($this->postVar, 'dateStart', false);
		$dateStart	=	!isset($dateStart) || $dateStart == "" ? date('Y-m-01') : $dateStart;
		$dateEnd	=	validatePostVar($this->postVar, 'dateEnd', false);
		$dateEnd	=	!isset($dateEnd) || $dateEnd == "" ? date('Y-m-t') : $dateEnd;
		$listData	=	$this->ModelReimbursement->getListReimbursement($this->idPartnerType, $this->idPartner, $dateStart, $dateEnd);
		
		if(!$listData) setResponseNotFound(array("token"=>$this->newToken, "msg"=>"No reimbursement data found"));
		setResponseOk(array("token"=>$this->newToken, "listData"=>$listData));
	}
	
	public function addReimbursement(){
		$this->load->model('MainOperation');
		$this->load->model('ModelReimbursement');
		
		$idVendor			=	$this->idPartnerType == 1 ? $this->idPartner : 0;
		$idDriver			=	$this->idPartnerType == 2 ? $this->idPartner : 0;
		$requestByType		=	$this->idPartnerType;
		$description		=	validatePostVar($this->postVar, 'description', true);
		$nominal			=	validatePostVar($this->postVar, 'nominal', true);
		$nominal			=	preg_replace("/[^0-9,]/", "", $nominal);
		$receiptDate		=	validatePostVar($this->postVar, 'receiptDate', true);
		$receiptImageName	=	validatePostVar($this->postVar, 'receiptImageName', true);
		$partnerName		=	$this->detailPartner['PARTNERNAME'];
		
		try {
			$receiptDateDT	=	DateTime::createFromFormat('Y-m-d', $receiptDate);
		} catch(Exception $e) {
			setResponseForbidden(array("token"=>$this->newToken, "msg"=>"Invalid receipt date format"));
		}

		$arrInsertReimbursement	=	[
			"IDVENDOR"			=>	$idVendor,
			"IDDRIVER"			=>	$idDriver,
			"REQUESTBY"			=>	$requestByType,
			"REQUESTBYNAME"		=>	$partnerName,
			"DESCRIPTION"		=>	$description,
			"NOMINAL"			=>	$nominal,
			"RECEIPTDATE"		=>	$receiptDate,
			"RECEIPTIMAGE"		=>	$receiptImageName,
			"INPUTMETHOD"		=>	1,
			"INPUTBYNAME"		=>	$partnerName,
			"INPUTDATETIME"		=>	date('Y-m-d H:i:s'),
			"STATUS"			=>	0
		];
		$procInsert				=	$this->MainOperation->addData("t_reimbursement", $arrInsertReimbursement);
		
		if(!$procInsert['status']) switchMySQLErrorCode($procInsert['errCode'], $this->newToken);
		if(PRODUCTION_URL){
			$totalReimbursementRequest	=	$this->ModelReimbursement->getTotalReimbursementRequest();
			$factory					=	(new Factory)
											->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)
											->withDatabaseUri(FIREBASE_RTDB_URI);
			$database					=	$factory->createDatabase();
			$reference					=	$database->getReference(FIREBASE_RTDB_MAINREF_NAME."unprocessedFinance/reimbursement")
											->set([
												'newReimbursementStatus'	=>	true,
												'newReimbursementTotal'		=>	$totalReimbursementRequest,
												'newReimbursementMessage'	=>	"New reimbursement request from ".$partnerName." - ".number_format($nominal, 0, '.', ',')." IDR.<br/>Description : ".$description,
												'timestampUpdate'			=>	gmdate("YmdHis")
											]);
		}
		
		setResponseOk(array("token"=>$this->newToken, "msg"=>"Reimbursement have been saved and waiting for approval"));
	}
	
	public function uploadImageReimbursement(){
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
		$dir			=	PATH_REIMBURSEMENT_RECEIPT;
		
		if(!file_exists($dir.$filename)){
			$move		=	move_uploaded_file($_FILES["uploaded_file"]["tmp_name"], $dir.$filename);
			if($move){
				setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_REIMBURSEMENT_IMAGE.$filename));
			} else {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. Please try again"));
			}
		} else {
			setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_REIMBURSEMENT_IMAGE.$filename));
		}
	}
	
	public function imageReimbursement($filename){
		$loc			=	PATH_REIMBURSEMENT_RECEIPT.$filename;
		if(file_exists($loc)) {
			$image 		= 	@imagecreatefromjpeg($loc) or $image = 'testpng';
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
	
}