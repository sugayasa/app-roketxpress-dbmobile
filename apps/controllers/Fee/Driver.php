<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Driver extends CI_controller {
	
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
		$this->load->model('Fee/ModelDriver');

		$yearMonth			=	validatePostVar($this->postVar, 'yearMonth', true);
		$showActiveOnly		=	validatePostVar($this->postVar, 'showActiveOnly', false);
		$partnerDetail		=	$this->MainOperation->getDetailDriver($this->idPartner);
		$newFinanceScheme	=	$partnerDetail['NEWFINANCESCHEME'] * 1;
		
		if($newFinanceScheme == 1){
			$dataRecap		=	$this->ModelDriver->getDataFeeRecapNewScheme($this->idPartner, $yearMonth, $showActiveOnly);
			$dataListFee	=	$this->ModelDriver->getDataListFeeNewScheme($this->idPartner, $yearMonth, $showActiveOnly);
		} else {
			$dataRecap		=	$this->ModelDriver->getDataFeeRecap($this->idPartner, $yearMonth);
			$dataListFee	=	$this->ModelDriver->getDataListFee($this->idPartner, $yearMonth);			
		}
		
		setResponseOk(array("token"=>$this->newToken, "dataRecap"=>$dataRecap, "dataListFee"=>$dataListFee));
		
	}
	
}