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
	
	public function dayOffCalendar(){

		$this->load->model('MainOperation');
		$this->load->model('DayOff/ModelCar');

		$yearMonth		=	validatePostVar($this->postVar, 'yearMonth', true);
		$detailPartner	=	$this->MainOperation->getDetailPartner($this->newToken);
		$idVendor		=	$detailPartner['IDPARTNER'];
		$dataCalendar	=	$this->ModelCar->getDataCalendarDayOff($idVendor, $yearMonth);

		setResponseOk(array("token"=>$this->newToken, "dataCalendar"=>$dataCalendar));
		
	}
	
	public function dayOffDetail(){

		$this->load->model('MainOperation');
		$this->load->model('DayOff/ModelCar');

		$date			=	validatePostVar($this->postVar, 'date', true);
		$detailPartner	=	$this->MainOperation->getDetailPartner($this->newToken);
		$idVendor		=	$detailPartner['IDPARTNER'];
		$detailCalendar	=	$this->ModelCar->getDetailCalendarDayOff($idVendor, $date);

		setResponseOk(array("token"=>$this->newToken, "detailCalendar"=>$detailCalendar));
		
	}
	
	public function submitDayOff(){

		$this->load->model('MainOperation');
		$this->load->model('DayOff/ModelCar');

		$idCarVendor	=	validatePostVar($this->postVar, 'idCarVendor', true);
		$date			=	validatePostVar($this->postVar, 'date', true);
		$reason			=	validatePostVar($this->postVar, 'reason', true);
		$totalSchedule	=	$this->ModelCar->getTotalSchedule($idCarVendor, $date);
		
		if($totalSchedule > 0){
			setResponseForbidden(array("token"=>$this->newToken, "msg"=>"Rejected! Your car has an order scheduled on the selected date. \nPlease ask the admin to cancel the order first"));
		}
		
		$arrInsert		=	array(
								"IDCARVENDOR"	=>	$idCarVendor,
								"DATE"			=>	$date,
								"REASON"		=>	$reason,
								"DATETIME"		=>	date('Y-m-d H:i:s')
							);
		$procInsert		=	$this->MainOperation->addData("t_dayoff", $arrInsert);
		
		if(!$procInsert['status']){
			if($procInsert['errCode'] == "1062"){
				setResponseForbidden(array("token"=>$this->newToken, "msg"=>"Failed. Day off data is exist for the car and date you choose"));
			} else {
				switchMySQLErrorCode($procInsert['errCode'], $this->newToken);
			}
		}

		setResponseOk(array("token"=>$this->newToken));
		
	}
	
}