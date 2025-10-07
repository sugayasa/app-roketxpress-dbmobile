<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_controller {
	
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
	
	public function dataDashboard(){
		$this->load->model('ModelDashboard');
		$this->load->model('ModelAgreementDriver');
		
		$detailFee					=	$this->ModelDashboard->getTotalActiveFee($this->idPartnerType, $this->idPartner);
		$totalJobs					=	$detailFee['TOTALJOBS'];
		$totalFee					=	$detailFee['TOTALFEE'];		
		$dateStr					=	date('F Y');
		$totalActiveOrder			=	$this->ModelDashboard->getTotalActiveOrder($this->idPartnerType, $this->idPartner);
		$totalOrderDropOffPickUpCar	=	$this->ModelDashboard->getTotalActiveOrderDropOffPickupCar($this->idPartnerType, $this->idPartner);
		$dateRangeOrder				=	$this->ModelDashboard->getDateRangeOrder($this->idPartnerType, $this->idPartner);
		$dataSecretPIN				=	$this->ModelDashboard->getSecretPINStatus($this->idPartnerType, $this->idPartner);
		$dataDepositBalance			=	$this->ModelDashboard->getDataDepositBalanceVendor($this->idPartner);
		$dataCollectPayment			=	$this->ModelDashboard->getDataCollectPayment($this->idPartnerType, $this->idPartner);
		$dataLoan					=	$dataPrepaidCapital	=	false;
		$arrAdditionalResp			=	array();
		$reviewBonusPunishmentStatus=	$isGroupDriver	=	$isCarRentalDriver	=	false;
		
		if($this->idPartnerType == 2){
			$dataAllowedLoan			=	$this->MainOperation->getDataAllowedLoan($this->idPartner);
			$arrIdLoanType				=	explode(",", $dataAllowedLoan);
			$dataLoan					=	$this->ModelDashboard->getDataDriverLoanPrepaidCapital($this->idPartner, 1);
			$dataPrepaidCapital			=	$this->ModelDashboard->getDataDriverLoanPrepaidCapital($this->idPartner, 2);
			$reviewBonusPunishmentStatus=	$this->ModelDashboard->isDriverAllowReviewBonusPunishment($this->idPartner);
			$isGroupDriver				=	$this->detailPartner['ISGROUPDRIVER'] == 1 ? true : $isGroupDriver;
			$isCarRentalDriver			=	$this->detailPartner['ISCARRENTALDRIVER'] == 1 ? true : $isCarRentalDriver;
			
			if(in_array(1, $arrIdLoanType) || in_array(2, $arrIdLoanType))	$arrAdditionalResp['dataLoan']	=	$dataLoan;
			if(in_array(3, $arrIdLoanType))	$arrAdditionalResp['dataPrepaidCapital']	=	$dataPrepaidCapital;
		}
		
		$dataNewAgreement	=	new stdClass();;
		$dataNewAgreement	=	$this->idPartnerType == 2 ? $this->ModelAgreementDriver->getDataNewAgreement($this->idPartner) : $dataNewAgreement;
		
		setResponseOk(
			array_merge(
				array(
					"token"							=>	$this->newToken,
					"totalJobs"						=>	$totalJobs,
					"totalFee"						=>	$totalFee,
					"dateStr"						=>	$dateStr,
					"totalActiveOrder"				=>	$totalActiveOrder,
					"totalOrderDropOffPickUpCar"	=>	$totalOrderDropOffPickUpCar,
					"dateOrderStart"				=>	$dateRangeOrder['DATEORDERSTART'],
					"dateOrderEnd"					=>	$dateRangeOrder['DATEORDEREND'],
					"dataCollectPayment"			=>	$dataCollectPayment,
					"dataDepositBalance"			=>	$dataDepositBalance,
					"secretPINStatus"				=>	$dataSecretPIN['SECRETPINSTATUS'],
					"secretPINLastUpdate"			=>	$dataSecretPIN['SECRETPINLASTUPDATE'],
					"idPartnerType"					=>	$this->idPartnerType,
					"partnershipType"				=>	$this->partnershipType,
					"financeSchemeType"				=>	$this->financeSchemeType,
					"transportService"				=>	$this->transportService,
					"isGroupDriver"					=>	$isGroupDriver,
					"isCarRentalDriver"				=>	$isCarRentalDriver,
					"reviewBonusPunishmentStatus"	=>	$reviewBonusPunishmentStatus,
					"dataNewAgreement"				=>	$dataNewAgreement
				),
				$arrAdditionalResp
			)
		);
	}
	
	public function dataNotification(){
		$this->load->model('ModelDashboard');
		$page				=	validatePostVar($this->postVar, 'page', true);
		$dataNotification	=	$this->ModelDashboard->getDataNotification($page, $this->idPartnerType, $this->idPartner);
		
		setResponseOk(array("token"=>$this->newToken, "dataNotification"=>$dataNotification));
	}
	
	public function carVendorList(){
		$this->load->model('ModelDashboard');
		$carVendorList	=	$this->ModelDashboard->getCarVendorList($this->idPartner);
		
		setResponseOk(array("token"=>$this->newToken, "carVendorList"=>$carVendorList));
	}
}