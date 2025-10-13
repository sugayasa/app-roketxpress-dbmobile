<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Kreait\Firebase\Factory;

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
			$idReservationDetails			=	$detailOrder['IDRESERVATIONDETAILS'];
			$listAdditionalCost				=	$this->ModelDropOffPickUpCar->getListAdditionalCost($this->idPartner, $idReservationDetails);
			setResponseOk(
				array(
					"token"         	=>	$this->newToken,
					"detailOrder"   	=>	$detailOrder,
					"listAdditionalCost"=>	$listAdditionalCost,
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
	
	public function addAdditionalCost(){
		$this->load->model('MainOperation');
		$this->load->model('ModelAdditionalCost');
		$this->load->model('ModelDropOffPickUpCar');
		
		$idScheduleCarDropOffPickUp	=	validatePostVar($this->postVar, 'idScheduleCarDropOffPickUp', true);
		$detailDropOffPickUpOrder	=	$this->ModelDropOffPickUpCar->getDetailDropOffPickUpOrder($idScheduleCarDropOffPickUp);

		if(!$detailDropOffPickUpOrder) setResponseNotFound(array("token" => $this->newToken, "msg" => "Detail drop off/pick up order not found."));

		$idAdditionalCostType	=	validatePostVar($this->postVar, 'idAdditionalCostType', true);
		$description			=	validatePostVar($this->postVar, 'description', true);
		$nominal				=	validatePostVar($this->postVar, 'nominal', true);
		$nominal				=	preg_replace("/[^0-9,]/", "", $nominal);
		$imageReceipt			=	validatePostVar($this->postVar, 'imageReceipt', true);
		$jobType				=	$detailDropOffPickUpOrder['JOBTYPE'];
		$jobDate				=	$detailDropOffPickUpOrder['JOBDATEDB'];
		$idReservationDetails	=	$detailDropOffPickUpOrder['IDRESERVATIONDETAILS'];
		$prefixDescription		=	$jobType == 1 ? "[Car Drop Off]" : "[Car Pick Up]";

		$jobDateDT				=	new DateTime($jobDate);
		$dateDifferenceDays		=	$jobDateDT->diff(new DateTime());
		$daysDifference			=	$dateDifferenceDays->days;

		if($daysDifference > MAX_DAY_ADDITIONAL_COST_INPUT) setResponseForbidden(array("token"=>$this->newToken, "msg"=>"You can only add additional cost up to ".MAX_DAY_ADDITIONAL_COST_INPUT." days after the job date."));

		$arrInsertAddCost		=	array(
			"IDRESERVATIONDETAILS"	=>	$idReservationDetails,
			"IDDRIVER"				=>	$this->idPartner,
			"IDADDITIONALCOSTTYPE"	=>	$idAdditionalCostType,
			"DESCRIPTION"			=>	$prefixDescription." ".$description,
			"NOMINAL"				=>	$nominal,
			"IMAGERECEIPT"			=>	$imageReceipt,
			"DATETIMEINPUT"			=>	date("Y-m-d H:i:s")
		);
		$procInsert				=	$this->MainOperation->addData("t_reservationadditionalcost", $arrInsertAddCost);
		
		if(!$procInsert['status']) switchMySQLErrorCode($procInsert['errCode'], $this->newToken);

		if(PRODUCTION_URL){
			$partnerName			=	$this->detailPartner['PARTNERNAME'];
			$newAdditionalCostTotal	=	$this->ModelAdditionalCost->getTotalAdditionalCostRequest();
			$newCarCostTotal		=	$this->ModelDropOffPickUpCar->getTotalCarRentCostRequest();
			$factory				=	(new Factory)
											->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)
											->withDatabaseUri(FIREBASE_RTDB_URI);
			$database				=	$factory->createDatabase();
			$database->getReference(FIREBASE_RTDB_MAINREF_NAME."unprocessedFinanceDriver/additionalCost")
			->set([
				'newAdditionalCostTotal'	=>	$newAdditionalCostTotal
			]);

			$database->getReference(FIREBASE_RTDB_MAINREF_NAME."unprocessedFinanceVendor/carRentCost")
			->set([
				'newAdditionalCostStatus'	=>	true,
				'newAdditionalCostTotal'	=>	$newAdditionalCostTotal,
				'newAdditionalCostMessage'	=>	"New additional cost request ".$prefixDescription." from ".$partnerName." - ".number_format($nominal, 0, '.', ',')." IDR.<br/>Description : ".$description,
				'timestampUpdate'			=>	gmdate("YmdHis")
			]);
		}
		
		setResponseOk(array("token"=>$this->newToken, "msg"=>"Additional cost have been saved and waiting for approval"));
	}
}