<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Access extends CI_controller {
	
	var $postVar;
	var $imei;
	var $token;
	
	public function __construct(){
        parent::__construct();
		$this->postVar	=	decodeJsonPost();
		$this->imei		=	validatePostVar($this->postVar, 'imei', true);
		$this->fcmtoken	=	validatePostVar($this->postVar, 'fcmtoken', true);
		$this->token	=	validatePostVar($this->postVar, 'token', false);
		$this->email	=	validatePostVar($this->postVar, 'email', false);
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function accessCheck(){
		accessCheck($this->fcmtoken, $this->email, $this->imei, $this->token, false);
	}
	
	public function logout(){
		$this->load->model('MainOperation');
		$this->load->model('ModelAccess');
		
		$isUserExist	=	$this->ModelAccess->isUserExist($this->imei, $this->token);
		
		if(!$isUserExist){
			setResponseForbidden(array("token"=>$this->token, "msg"=>"Forbidden. You are not allowed to perform this action"));
		}
		
		$idUserMobile	=	$isUserExist['IDUSERMOBILE'];
		$arrUpdate		=	array(
			"TOKENACCESS"	=>	"",
			"TOKEN1"		=>	"",
			"TOKEN2"		=>	"",
			"TOKENFCM"		=>	""
		);
		$procReset		=	$this->MainOperation->updateData("m_usermobile", $arrUpdate, "IDUSERMOBILE", $idUserMobile);
		
		if(!$procReset) switchMySQLErrorCode($procReset['errCode'], $this->token);
		setResponseOk(array("token"=>"", "stepCode"=>0, "msg"=>"You are logged out"));		
	}
	
	public function getOptionHelper(){
		$this->load->model('ModelAccess');
		$this->load->model('MainOperation');

		$detailPartner				=	$this->MainOperation->getDetailPartner($this->token);
		$idPartnerType				=	$detailPartner['IDPARTNERTYPE'];
		$idPartner					=	$detailPartner['IDPARTNER'];
		$dataAdditionalCostType		=	$this->ModelAccess->getDataOptionHelperAdditionalCostType();
		$dataBank					=	$this->ModelAccess->getDataOptionHelperBank();
		$dataLoanPrepaidCapitalType	=	$idPartnerType == 1 ? [] : $this->ModelAccess->getDataLoanPrepaidCapitalType($idPartner);
		$data						=	array(
			"dataAdditionalCostType"	=> $dataAdditionalCostType,
			"dataBank"					=> $dataBank,
			"dataLoanPrepaidCapitalType"=> $dataLoanPrepaidCapitalType
		);
		setResponseOk(array("data"=>$data));
	}

	public function updateLastPosition(){
		$this->load->model('ModelAccess');
		$this->load->model('MainOperation');

		$detailPartner	=	$this->MainOperation->getDetailPartner($this->token);
		$idPartnerType	=	$detailPartner['IDPARTNERTYPE'];
		$idPartner		=	$detailPartner['IDPARTNER'];
		
		if($idPartnerType == 2){
			$gpsL		=	validatePostVar($this->postVar, 'gpsL', true);
			$gpsB		=	validatePostVar($this->postVar, 'gpsB', true);
			$accuration	=	validatePostVar($this->postVar, 'accuration', true);
			$isFakeGPS	=	validatePostVar($this->postVar, 'isFakeGPS', false);
			
			$this->ModelAccess->updateLastPosition($idPartner, $gpsL, $gpsB, $accuration);
			$this->ModelAccess->insertDriverPositionLog($idPartner, $gpsL, $gpsB, $accuration, $isFakeGPS);
		}
		
		setResponseOk(array("msg"=>"Coordinates has been updated"));
	}
}