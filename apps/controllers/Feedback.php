<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Feedback extends CI_controller {
	
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

		if($method != 'GET' && $funcName != "imageFeedback"){
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
	
	public function listFeedback(){
		$this->load->model('ModelFeedback');

		$listFeedback	=	$this->ModelFeedback->getListFeedback($this->idPartner);
		setResponseOk(
			array(
				"token"			=>	$this->newToken,
				"listFeedback"	=>	$listFeedback
			)
		);
	}
	
	public function submitFeedback(){
		$this->load->model('MainOperation');

		if($this->idPartnerType == 2){
			$title				=	validatePostVar($this->postVar, 'title', true);
			$description		=	validatePostVar($this->postVar, 'description', true);
			$image				=	validatePostVar($this->postVar, 'image', false);
			$image				=	$image == "" ? "noimage.jpg" : $image;
			$arrInsertFeedBack	=	[
				"IDDRIVER"		=>	$this->idPartner,
				"TITLE"			=>	$title,
				"DESCRIPTION"	=>	$description,
				"IMAGE"			=>	$image,
				"DATETIME"		=>	date('Y-m-d H:i:s')
			];
			
			$procInsertFeedBack	=	$this->MainOperation->addData('t_driverfeedback', $arrInsertFeedBack);
			if(!$procInsertFeedBack['status']) switchMySQLErrorCode($procInsertFeedBack['errCode'], $this->newToken);			
			setResponseOk(array("msg"=>"Feedback has been added, thank you!"));
		} else {
			setResponseForbidden(array("msg"=>"You are not allowed to do this action"));
		}		
	}

	public function uploadImageFeedback(){
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
		$dir		=	PATH_STORAGE_FEEDBACK_IMAGE;
		
		if(!file_exists($dir.$filename)){
			$move		=	move_uploaded_file($_FILES["uploaded_file"]["tmp_name"], $dir.$filename);
			if($move){
				setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_FEEDBACK_IMAGE.$filename));
			} else {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. Please try again"));
			}
			
		} else {
			setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_FEEDBACK_IMAGE.$filename));
		}
		
	}
	
	public function imageFeedback($filename){
		$loc			=	PATH_STORAGE_FEEDBACK_IMAGE.$filename;
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