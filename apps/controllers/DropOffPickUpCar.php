<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DropOffPickUpCar extends CI_controller {
	
	var $postVar;
	var $imei;
	var $token;
	var $newToken;
	var $detailPartner;
	var $idUserMobile;
	var $idPartnerType;
	var $idPartner;
	var $partnerName;
	var $financeSchemeType;
	
	public function __construct(){
        parent::__construct();
		$this->load->model('MainOperation');

		if($_SERVER['REQUEST_METHOD'] === 'POST'){
			$this->postVar			=	decodeJsonPost();
			$this->imei				=	validatePostVar($this->postVar, 'imei', true);
			$this->fcmtoken			=	validatePostVar($this->postVar, 'fcmtoken', true);
			$this->token			=	validatePostVar($this->postVar, 'token', false);
			$this->email			=	numberValidator(validatePostVar($this->postVar, 'email', false));
			$this->newToken			=	accessCheck($this->fcmtoken, $this->email, $this->imei, $this->token, true);
			
			$detailPartner			=	$this->MainOperation->getDetailPartner($this->newToken);
			$this->detailPartner	=	$detailPartner;
			$this->idUserMobile		=	$detailPartner['IDUSERMOBILE'];
			$this->idPartnerType	=	$detailPartner['IDPARTNERTYPE'];
			$this->idPartner		=	$detailPartner['IDPARTNER'];
			$this->partnerName		=	$detailPartner['PARTNERNAME'];
			$this->financeSchemeType=	$detailPartner['FINANCESCHEMETYPE'];
		} else {
			//For development test only
			$this->idUserMobile		=	0;
			$this->idPartnerType	=	1;
			$this->idPartner		=	9;
			$this->partnerName		=	'-';
			$this->financeSchemeType=	1;
		}
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function listOrderByDate(){
		$this->load->model('ModelDropOffPickUpCar');
		$this->load->model('MainOperation');

		$showActiveOnly =	validatePostVar($this->postVar, 'showActiveOnly', false);
		$dateStart      =	validatePostVar($this->postVar, 'dateStart', false);
		$dateStart      =	!isset($dateStart) || $dateStart == "" ? date('Y-m-d') : $dateStart;
		$dateEnd        =	validatePostVar($this->postVar, 'dateEnd', false);
		$dateEnd        =	!isset($dateEnd) || $dateEnd == "" ? $dateStart : $dateEnd;
		$listOrder      =	$this->ModelDropOffPickUpCar->listOrderByDate($this->idPartner, $dateStart, $dateEnd, $showActiveOnly);

		if(!$listOrder) setResponseNotFound(array("token"=>$this->newToken, "msg"=>"You have no drop off/pick up car order in selected date/period"));
        setResponseOk(array("token"=>$this->newToken, "listOrder"=>$listOrder));
	}
	
	public function detailOrder(){
		$this->load->model('MainOperation');
		$this->load->model('ModelDropOffPickUpCar');

		$idScheduleCarDropOffPickUp	=	validatePostVar($this->postVar, 'idScheduleCarDropOffPickUp', true);
		$detailOrder			    =	$this->ModelDropOffPickUpCar->getDetailDropOffPickUpOrder($idScheduleCarDropOffPickUp);

		if(!$detailOrder){			
			setResponseNotFound(array("token"=>$this->newToken, "msg"=>"Detail order not found or this order has been canceled."));
		} else {						
			$detailOrder['CUSTOMERCONTACT']	=	preg_replace("/[^0-9+]/", "", $detailOrder['CUSTOMERCONTACT']);
			setResponseOk(
				array(
					"token"         =>	$this->newToken,
					"detailOrder"   =>	$detailOrder
				)
			);
		}		
	}
	
	public function updateStatusOrder(){
		$this->load->model('MainOperation');
		$this->load->model('ModelDropOffPickUpCar');

		$idScheduleCarDropOffPickUp	=	validatePostVar($this->postVar, 'idScheduleCarDropOffPickUp', true);
		$statusProcess				=	validatePostVar($this->postVar, 'statusProcess', true);
		$detailDropOffPickUpOrder	=	$this->ModelDropOffPickUpCar->getDetailDropOffPickUpOrder($idScheduleCarDropOffPickUp);

		if(!$detailDropOffPickUpOrder) setResponseNotFound(array("token" => $this->newToken, "msg" => "Detail drop off/pick up order not found."));

		$jobType		=	$detailDropOffPickUpOrder['JOBTYPE'];
		$jobDate		=	$detailDropOffPickUpOrder['JOBDATEDB'];
		$idReservation	=	$detailDropOffPickUpOrder['IDRESERVATION'];

		if(strtotime($jobDate) > strtotime('today')) setResponseForbidden(array("token" => $this->newToken, "msg" => "You can only update the order status on or after the schedule day"));

		$procUpdate	=	$this->MainOperation->updateData('t_schedulecardropoffpickup', ['IDSTATUSPROCESSCARDROPOFFPICKUP' => $statusProcess], ['IDSCHEDULECARDROPOFFPICKUP' => $idScheduleCarDropOffPickUp]);

		if($procUpdate['status']){
			if($jobType == 2){
				$maxStatusProcess	=	$this->ModelDropOffPickUpCar->getMaxStatusProcessDropOffPickUpCar();
				
				if($statusProcess == $maxStatusProcess) $this->MainOperation->updateData("t_reservation", array("STATUS"=>4), "IDRESERVATION", $idReservation);
			}

			setResponseOk(
				array(
					"token"	=>	$this->newToken,
					"msg"	=>	"Order status has been updated"
				)
			);
		}

		setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Internal server error. Please try again later"));
	}
}