<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;

class AdditionalCost extends CI_controller {
	
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
		
		if($method != 'GET' && $funcName != "imageAdditionalCost"){
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
	
	public function listSchedule(){

		$this->load->model('ModelAdditionalCost');
		$dateStart		=	date('Y-m-d', strtotime('-'.MAX_DAY_ADDITIONAL_COST_INPUT.' days'));
		$dateEnd		=	date('Y-m-d');
		$listSchedule	=	$this->ModelAdditionalCost->getListSchedule($this->idPartner, $dateStart, $dateEnd);
		
		if(!$listSchedule){
			setResponseNotFound(array("token"=>$this->newToken, "msg"=>"You have no schedule to add new additional cost"));			
		}
		
		setResponseOk(array("token"=>$this->newToken, "listSchedule"=>$listSchedule));
		
	}
	
	public function addAdditionalCost(){
		$this->load->model('MainOperation');
		$this->load->model('ModelAdditionalCost');
		
		$idReservationDetails	=	validatePostVar($this->postVar, 'idReservationDetails', true);
		$idAdditionalCostType	=	validatePostVar($this->postVar, 'idAdditionalCostType', true);
		$description			=	validatePostVar($this->postVar, 'description', true);
		$nominal				=	validatePostVar($this->postVar, 'nominal', true);
		$nominal				=	preg_replace("/[^0-9,]/", "", $nominal);
		$imageReceipt			=	validatePostVar($this->postVar, 'imageReceipt', true);

		$arrInsertAddCost		=	array(
										"IDRESERVATIONDETAILS"	=>	$idReservationDetails,
										"IDDRIVER"				=>	$this->idPartner,
										"IDADDITIONALCOSTTYPE"	=>	$idAdditionalCostType,
										"DESCRIPTION"			=>	$description,
										"NOMINAL"				=>	$nominal,
										"IMAGERECEIPT"			=>	$imageReceipt,
										"DATETIMEINPUT"			=>	date("Y-m-d H:i:s")
									);
		$procInsert				=	$this->MainOperation->addData("t_reservationadditionalcost", $arrInsertAddCost);
		
		if(!$procInsert['status']) switchMySQLErrorCode($procInsert['errCode'], $this->newToken);

		if(PRODUCTION_URL){
			$partnerName				=	$this->detailPartner['PARTNERNAME'];
			$totalAdditionalCostRequest	=	$this->ModelAdditionalCost->getTotalAdditionalCostRequest();
			$factory					=	(new Factory)
											->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)
											->withDatabaseUri(FIREBASE_RTDB_URI);
			$database					=	$factory->createDatabase();
			$database->getReference(FIREBASE_RTDB_MAINREF_NAME."unprocessedFinanceDriver/additionalCost")
			->set([
				'newAdditionalCostStatus'	=>	true,
				'newAdditionalCostTotal'	=>	$totalAdditionalCostRequest,
				'newAdditionalCostMessage'	=>	"New additional cost request from ".$partnerName." - ".number_format($nominal, 0, '.', ',')." IDR.<br/>Description : ".$description,
				'timestampUpdate'			=>	gmdate("YmdHis")
			]);
		}
		
		setResponseOk(array("token"=>$this->newToken, "msg"=>"Additional cost have been saved and waiting for approval"));
	}
	
	public function uploadImageAdditionalCost(){
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
		$dir		=	PATH_STORAGE_ADDITIONAL_COST_IMAGE;
		
		if(!file_exists($dir.$filename)){
			$move	=	move_uploaded_file($_FILES["uploaded_file"]["tmp_name"], $dir.$filename);
			if($move){
				setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_ADDITIONAL_COST_IMAGE.$filename));
			} else {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. Please try again"));
			}
		} else {
			setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_ADDITIONAL_COST_IMAGE.$filename));
		}
	}
	
	public function imageAdditionalCost($filename){
		
		$loc			=	PATH_STORAGE_ADDITIONAL_COST_IMAGE.$filename;

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

	public function listAdditionalCost(){
		$this->load->model('MainOperation');
		$this->load->model('ModelAdditionalCost');

		$showActiveOnly		=	validatePostVar($this->postVar, 'showActiveOnly', false);
		$dateStart			=	validatePostVar($this->postVar, 'dateStart', true);
		$dateEnd			=	validatePostVar($this->postVar, 'dateEnd', true);
		$listAdditionalCost	=	$this->ModelAdditionalCost->getListAdditionalCost($this->idPartner, $dateStart, $dateEnd, $showActiveOnly);
		
		if(!$listAdditionalCost) setResponseNotFound(array("token"=>$this->newToken, "msg"=>"You have no additional cost data"));

		setResponseOk(array("token"=>$this->newToken, "listAdditionalCost"=>$listAdditionalCost));		
	}
}