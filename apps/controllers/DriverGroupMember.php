<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DriverGroupMember extends CI_controller {
	
	var $postVar;
	var $imei;
	var $token;
	var $newToken;
	var $detailPartner;
	var $idUserMobile;
	var $idPartnerType;
	var $partnershipType;
	var $transportService;
	var $financeSchemeType;
	var $idPartner;
	var $newFinanceScheme;
	
	public function __construct(){
        parent::__construct();
		$this->load->model('MainOperation');
		
		$this->postVar	=	decodeJsonPost();
		$this->imei		=	validatePostVar($this->postVar, 'imei', true);
		$this->fcmtoken	=	validatePostVar($this->postVar, 'fcmtoken', true);
		$this->token	=	validatePostVar($this->postVar, 'token', false);
		$this->email	=	numberValidator(validatePostVar($this->postVar, 'email', false));
		$this->newToken	=	accessCheck($this->fcmtoken, $this->email, $this->imei, $this->token, true);
			
		$detailPartner			=	$this->MainOperation->getDetailPartner($this->newToken);
		$this->detailPartner	=	$detailPartner;
		$this->idUserMobile		=	$detailPartner['IDUSERMOBILE'];
		$this->idPartnerType	=	$detailPartner['IDPARTNERTYPE'];
		$this->partnershipType	=	$detailPartner['PARTNERSHIPTYPE'];
		$this->transportService	=	$detailPartner['TRANSPORTSERVICE'];
		$this->financeSchemeType=	$detailPartner['FINANCESCHEMETYPE'];
		$this->idPartner		=	$detailPartner['IDPARTNER'];
		$this->newFinanceScheme	=	$detailPartner['NEWFINANCESCHEME'];
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function driverMemberList(){
		$this->load->model('ModelDriverGroupMember');
		
		$dateSchedule		=	validatePostVar($this->postVar, 'dateSchedule', true);
		$dateScheduleDT		=	DateTime::createFromFormat('d-m-Y', $dateSchedule);
		$dateSchedule		=	$dateScheduleDT->format('Y-m-d');
		$dateScheduleStr	=	$dateScheduleDT->format('d M Y');
		$driverMemberList	=	$this->partnershipType == 1 ? [] : $this->ModelDriverGroupMember->getDriverMemberList($this->idPartner);
		
		if(count($driverMemberList) > 0){
			foreach($driverMemberList as $keyMemberList){
				$keyMemberList->ISHAVESCHEDULE	=	$this->ModelDriverGroupMember->isDriverHaveSchedule($keyMemberList->IDDRIVERGROUPMEMBER, $dateSchedule);
			}
		}
		
		setResponseOk(
			array_merge(
				array(
					"token"				=>	$this->newToken,
					"dateSelected"		=>	$dateScheduleStr,
					"driverMemberList"	=>	$driverMemberList
				)
			)
		);
	}
	
	public function insertDriverMember(){
		$this->checkInputData();
		$this->load->model('MainOperation');
		$this->load->model('ModelDriverGroupMember');
		
		if($this->idPartnerType == 1) setResponseForbidden(array("token"=>$this->newToken, "msg"=>"You are not allowed to perform this action"));
		
		$driverName			=	validatePostVar($this->postVar, 'driverName', true);
		$driverPhoneNumber	=	validatePostVar($this->postVar, 'driverPhoneNumber', true);
		$carBrand			=	validatePostVar($this->postVar, 'carBrand', true);
		$carModel			=	validatePostVar($this->postVar, 'carModel', true);
		$carNumberPlate		=	validatePostVar($this->postVar, 'carNumberPlate', true);

		if($driverPhoneNumber == "+62" || substr($driverPhoneNumber, 0, 3) != "+62") setResponseBadRequest(array("token"=>$this->newToken, "msg"=>"Please enter a valid phone number"));
		$checkDataExists=	$this->ModelDriverGroupMember->checkDataExists($this->idPartner, $driverName, $driverPhoneNumber);

		$driverPhoneNumber	=	preg_replace('/[^0-9]+/', '', $driverPhoneNumber);
		$arrInsertUpdate=	array(
			"IDDRIVER"			=>	$this->idPartner,
			"DRIVERNAME"		=>	ucwords($driverName),
			"DRIVERPHONENUMBER"	=>	$driverPhoneNumber,
			"CARNUMBERPLATE"	=>	strtoupper($carNumberPlate),
			"CARBRAND"			=>	strtoupper($carBrand),
			"CARMODEL"			=>	strtoupper($carModel)
		);

		if($checkDataExists){
			$msg		=	"Driver group member data : ".$driverName." (".$driverPhoneNumber.") already exists. Please enter different data";
			setResponseForbidden(array("token"=>$this->newToken, "msg"=>$msg));
		}

		$insertResult	=	$this->MainOperation->addData("m_drivergroupmember", $arrInsertUpdate);
		if(!$insertResult['status']) switchMySQLErrorCode($insertResult['errCode'], $this->newToken);
		
		setResponseOk(array("token"=>$this->newToken, "msg"=>"New driver group member data saved"));
	}
	
	public function updateDriverMember(){
		$this->checkInputData();
		$this->load->model('MainOperation');
		$this->load->model('ModelDriverGroupMember');
		
		if($this->idPartnerType == 1) setResponseForbidden(array("token"=>$this->newToken, "msg"=>"You are not allowed to perform this action"));
		
		$idDriverGroupMember=	validatePostVar($this->postVar, 'idDriverGroupMember', true);
		$driverName			=	validatePostVar($this->postVar, 'driverName', true);
		$driverPhoneNumber	=	validatePostVar($this->postVar, 'driverPhoneNumber', true);
		$carBrand			=	validatePostVar($this->postVar, 'carBrand', true);
		$carModel			=	validatePostVar($this->postVar, 'carModel', true);
		$carNumberPlate		=	validatePostVar($this->postVar, 'carNumberPlate', true);

		if($driverPhoneNumber == "+62" || substr($driverPhoneNumber, 0, 3) != "+62") setResponseBadRequest(array("token"=>$this->newToken, "msg"=>"Please enter a valid phone number"));
		$checkDataExists=	$this->ModelDriverGroupMember->checkDataExists($this->idPartner, $driverName, $driverPhoneNumber, $idDriverGroupMember);

		$driverPhoneNumber	=	preg_replace('/[^0-9]+/', '', $driverPhoneNumber);
		$arrInsertUpdate	=	array(
			"DRIVERNAME"		=>	ucwords($driverName),
			"DRIVERPHONENUMBER"	=>	$driverPhoneNumber,
			"CARNUMBERPLATE"	=>	strtoupper($carNumberPlate),
			"CARBRAND"			=>	strtoupper($carBrand),
			"CARMODEL"			=>	strtoupper($carModel)
		);

		if($checkDataExists){
			$msg		=	"Driver group member data : ".$driverName." (".$driverPhoneNumber.") already exists. Please enter different data";
			setResponseForbidden(array("token"=>$this->newToken, "msg"=>$msg));
		}

		$updateResult	=	$this->MainOperation->updateData("m_drivergroupmember", $arrInsertUpdate, 'IDDRIVERGROUPMEMBER', $idDriverGroupMember);
		if(!$updateResult['status']) switchMySQLErrorCode($updateResult['errCode'], $this->newToken);
		
		setResponseOk(array("token"=>$this->newToken, "msg"=>"Driver group member data has been updated"));
	}
	
	private function checkInputData(){
		$arrVarValidate	=	array(
			array("driverName","text","Driver Name"),
			array("driverPhoneNumber","text","Phone Number"),
			array("carBrand","text","Car Brand"),
			array("carModel","text","Car Model"),
			array("carNumberPlate","text","Car Number Plate")
		);
		$errorValidate	=	validateVar($this->postVar, $arrVarValidate);
		
		if($errorValidate) setResponseBadRequest(array("token"=>$this->newToken, "msg"=>$errorValidate));
		return true;
	}
	
	public function deleteDriverMember(){
		$this->load->model('MainOperation');
		
		if($this->idPartnerType == 1) setResponseForbidden(array("token"=>$this->newToken, "msg"=>"You are not allowed to perform this action"));
		
		$idDriverGroupMember=	validatePostVar($this->postVar, 'idDriverGroupMember', true);
		$deleteResult		=	$this->MainOperation->deleteData("m_drivergroupmember", array("IDDRIVERGROUPMEMBER" => $idDriverGroupMember));
		
		if(!$deleteResult['status']) switchMySQLErrorCode($deleteResult['errCode'], $this->newToken);
		setResponseOk(array("token"=>$this->newToken, "msg"=>"Driver group member data has been deleted"));
	}
}