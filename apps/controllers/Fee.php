<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fee extends CI_controller {
	
	var $postVar;
	var $imei;
	var $token;
	var $newToken;
	var $idUserMobile;
	var $idPartnerType;
	var $idPartner;
	
	public function __construct(){
        parent::__construct();
		$this->load->model('MainOperation');
		
		$this->postVar	=	decodeJsonPost();
		$this->imei		=	validatePostVar($this->postVar, 'imei', true);
		$this->fcmtoken	=	validatePostVar($this->postVar, 'fcmtoken', true);
		$this->token	=	validatePostVar($this->postVar, 'token', false);
		$this->email	=	numberValidator(validatePostVar($this->postVar, 'email', false));
		$this->newToken	=	accessCheck($this->fcmtoken, $this->email, $this->imei, $this->token, true);
		
		$detailPartner		=	$this->MainOperation->getDetailPartner($this->newToken);
		$this->idUserMobile	=	$detailPartner['IDUSERMOBILE'];
		$this->idPartnerType=	$detailPartner['IDPARTNERTYPE'];
		$this->idPartner	=	$detailPartner['IDPARTNER'];
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function recapListFee(){

		$this->load->model('MainOperation');
		$this->load->model('ModelFee');

		$yearMonth			=	validatePostVar($this->postVar, 'yearMonth', true);
		$showActiveOnly		=	validatePostVar($this->postVar, 'showActiveOnly', false);
		$partnerDetail		=	$this->idPartnerType == 2 ? $this->MainOperation->getDetailDriver($this->idPartner) : $this->MainOperation->getDetailVendor($this->idPartner);
		$newFinanceScheme	=	$partnerDetail['NEWFINANCESCHEME'] * 1;
		$dataRecap			=	$this->ModelFee->getDataFeeRecapNewScheme($this->idPartnerType, $this->idPartner, $yearMonth, $showActiveOnly);
		$dataListFee		=	$this->ModelFee->getDataListFeeNewScheme($this->idPartnerType, $this->idPartner, $yearMonth, $showActiveOnly);
		
		setResponseOk(array("token"=>$this->newToken, "dataRecap"=>$dataRecap, "dataListFee"=>$dataListFee));
		
	}
	
}