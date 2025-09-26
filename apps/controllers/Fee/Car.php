<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Car extends CI_controller {
	
	var $postVar;
	var $imei;
	var $token;
	var $newToken;
	
	public function __construct(){
        parent::__construct();
		$this->postVar	=	decodeJsonPost();
		$this->imei		=	validatePostVar($this->postVar, 'imei', true);
		$this->fcmtoken	=	validatePostVar($this->postVar, 'fcmtoken', true);
		$this->token	=	validatePostVar($this->postVar, 'token', false);
		$this->email	=	numberValidator(validatePostVar($this->postVar, 'email', false));
		$this->newToken	=	accessCheck($this->fcmtoken, $this->email, $this->imei, $this->token, true);
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function recapFee(){

		$this->load->model('MainOperation');
		$this->load->model('Fee/ModelCar');

		$yearMonth		=	validatePostVar($this->postVar, 'yearMonth', true);
		$detailPartner	=	$this->MainOperation->getDetailPartner($this->newToken);
		$idVendor		=	$detailPartner['IDPARTNER'];
		$dataRecap		=	$this->ModelCar->getDataFeeRecap($idVendor, $yearMonth);
		$dataPerDate	=	$this->ModelCar->getDataFeePerDate($idVendor, $yearMonth);
		$dataPerCar		=	$this->ModelCar->getDataFeePerCar($idVendor, $yearMonth);
		
		setResponseOk(array("token"=>$this->newToken, "dataRecap"=>$dataRecap, "dataPerDate"=>$dataPerDate, "dataPerCar"=>$dataPerCar));
		
	}
	
	public function listFeeByDate(){

		$this->load->model('MainOperation');
		$this->load->model('Fee/ModelCar');

		$date			=	validatePostVar($this->postVar, 'date', true);
		$detailPartner	=	$this->MainOperation->getDetailPartner($this->newToken);
		$idVendor		=	$detailPartner['IDPARTNER'];
		$dataFeeByDate	=	$this->ModelCar->getDataFeeByDate($idVendor, $date);
		
		setResponseOk(array("token"=>$this->newToken, "dataFeeByDate"=>$dataFeeByDate));
		
	}
	
	public function listFeeByCar(){

		$this->load->model('Fee/ModelCar');

		$yearMonth		=	validatePostVar($this->postVar, 'yearMonth', true);
		$idCarVendor	=	validatePostVar($this->postVar, 'idCarVendor', true);
		$dataFeeByCar	=	$this->ModelCar->getDataFeeByCar($idCarVendor, $yearMonth);
		
		setResponseOk(array("token"=>$this->newToken, "dataFeeByCar"=>$dataFeeByCar));
		
	}
	
}