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
		$this->load->model('ModelDropOffPickUpCar');
		$this->load->model('MainOperation');

		$idScheduleCarDropOffPickUp	=	validatePostVar($this->postVar, 'idScheduleCarDropOffPickUp', true);
		$detailOrder			    =	$this->ModelDropOffPickUpCar->getDetailOrder($idScheduleCarDropOffPickUp);

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
	
	// public function updateStatusOrder(){
	// 	$this->load->model('MainOperation');
	// 	$this->load->model('ModelOrder');

	// 	$idReservationDetails	=	validatePostVar($this->postVar, 'idReservationDetails', true);
	// 	$statusProcess			=	validatePostVar($this->postVar, 'statusProcess', true);
	// 	$gpsl					=	validatePostVar($this->postVar, 'gpsl', true);
	// 	$gpsb					=	validatePostVar($this->postVar, 'gpsb', true);
	// 	$tableUpdate			=	$this->idPartnerType == 1 ? "t_schedulevendor" : "t_scheduledriver";
	// 	$whereUpdate			=	array("IDRESERVATIONDETAILS"=>$idReservationDetails);
	// 	$detailOrder			=	$this->ModelOrder->getDetailOrder($idReservationDetails, $this->idPartnerType, $this->idPartner);
	// 	$idReservation			=	$detailOrder['IDRESERVATION'];
	// 	$idSource				=	$detailOrder['IDSOURCE'];
	// 	$bookingCode			=	$detailOrder['BOOKINGCODE'];
	// 	$dateScheduleDB			=	$detailOrder['SCHEDULEDATESTR'];
	// 	$timeScheduleDB			=	$detailOrder['RESERVATIONTIMESTART'];
	// 	$scheduleDateText		=	$detailOrder['SCHEDULEDATETEXT'];
	// 	$customerName			=	$detailOrder['CUSTOMERNAME'];
	// 	$reservationTitle		=	$detailOrder['RESERVATIONTITLE'];
	// 	$productName			=	$detailOrder['PRODUCTNAME'];
	// 	$feeNominal				=	$detailOrder['NOMINAL'];
	// 	$feeNotes				=	$detailOrder['NOTES'];
	// 	$detailCollectPayment	=	$this->ModelOrder->detailCollectPayment($idReservation, $this->idPartnerType, $this->idPartner, $dateScheduleDB);
	// 	$maxStatusProcess		=	$this->MainOperation->getMaxStatusProcess($this->idPartnerType);
	// 	$msg					=	"Order status updated";
	// 	$statusReservation		=	3;
		
	// 	if($dateScheduleDB > date('Y-m-d')) setResponseForbidden(array("token" => $this->newToken, "msg" => "Order status updates are not allowed before the activity day"));
		
	// 	if($statusProcess == $maxStatusProcess){
	// 		$msg					=	"Order finished. Please tell your customer to review your service";
	// 		$statusReservation		=	4;
	// 		$partnerDetail			=	$this->idPartnerType == 1 ? $this->MainOperation->getDetailVendor($this->idPartner) : $this->MainOperation->getDetailDriver($this->idPartner);
	// 		$newFinanceScheme		=	$partnerDetail['NEWFINANCESCHEME'] * 1;
	// 		$idVendor				=	$this->idPartnerType == 1 ? $this->idPartner : 0;
	// 		$idDriver				=	$this->idPartnerType == 2 ? $this->idPartner : 0;
	// 		$dateTimeScheduleDB		=	$dateScheduleDB." ".$timeScheduleDB;
	// 		$dateScheduleDBCompare	=	str_replace('-', '', $dateScheduleDB) * 1;
	// 		$timeStampAllowFee		=	strtotime($dateTimeScheduleDB) + 60 * 60 * MIN_DURATION_ORDER_TO_CREATE_FEE;
	// 		$timeAllowFeeStr		=	date('H:i', $timeStampAllowFee);
			
	// 		if($detailCollectPayment && $newFinanceScheme == 1){
	// 			$statusCollectPayment	=	$detailCollectPayment['STATUS'] * 1;
	// 			$idCollectPayment		=	$detailCollectPayment['IDCOLLECTPAYMENT'] * 1;

	// 			if($idCollectPayment != 0 && $statusCollectPayment != 1) setResponseForbidden(array("token" => $this->newToken, "msg" => "You cannot complete this order. Please confirm collect payment in this order first"));
	// 		}
			
	// 		if(date('H') < MIN_TIME_HOUR_ORDER_TO_CREATE_FEE && $dateScheduleDBCompare >= date('Ymd')) setResponseForbidden(array("token" => $this->newToken, "msg" => "You cannot complete this order. Please update the order completion after ".MIN_TIME_HOUR_ORDER_TO_CREATE_FEE.":00"));
	// 		if(strtotime("now") < $timeStampAllowFee) setResponseForbidden(array("token" => $this->newToken, "msg" => "You cannot complete this order. Please update the order completion ".MIN_DURATION_ORDER_TO_CREATE_FEE." hours after reservation start (after ".$timeAllowFeeStr.")"));

	// 		if($this->financeSchemeType == 1){
	// 			$isFeeExist			=	$this->ModelOrder->isFeeExist($idReservation, $idReservationDetails, $idVendor, $idDriver);
	// 			if($newFinanceScheme == 1 && $dateScheduleDB > '2023-01-31' && !$isFeeExist){
	// 				$arrInsertFee	=	array(
	// 					"IDRESERVATION"			=>	$idReservation,
	// 					"IDRESERVATIONDETAILS"	=>	$idReservationDetails,
	// 					"IDVENDOR"				=>	$idVendor,
	// 					"IDDRIVER"				=>	$idDriver,
	// 					"DATESCHEDULE"			=>	$dateScheduleDB,
	// 					"RESERVATIONTITLE"		=>	$reservationTitle,
	// 					"JOBTITLE"				=>	$productName,
	// 					"FEENOMINAL"			=>	$feeNominal,
	// 					"FEENOTES"				=>	$feeNotes,
	// 					"DATETIMEINPUT"			=>	date('Y-m-d H:i:s')
	// 				);
	// 				$this->MainOperation->addData("t_fee", $arrInsertFee);
	// 			}
	// 		} else {
				
	// 			if($detailCollectPayment){
	// 				$idCollectPayment			=	$detailCollectPayment['IDCOLLECTPAYMENT'] * 1;
	// 				$idReservationPayment		=	$detailCollectPayment['IDRESERVATIONPAYMENT'] * 1;
	// 				$collectPaymentAmount		=	$detailCollectPayment['TOTALAMOUNTIDRCOLLECTPAYMENT'] * 1;
					
	// 				if($collectPaymentAmount > 0){
	// 					$arrInsertDepositRecord	=	array(
	// 						"IDVENDOR"				=>	$idVendor,
	// 						"IDRESERVATIONDETAILS"	=>	0,
	// 						"IDCOLLECTPAYMENT"		=>	$idCollectPayment,
	// 						"DESCRIPTION"			=>	"Conversion of deposit from collect payment from customer ".$customerName." on the activity on ".$scheduleDateText.", package : ".$productName,
	// 						"AMOUNT"				=>	$collectPaymentAmount,
	// 						"USERINPUT"				=>	"Auto System",
	// 						"DATETIMEINPUT"			=>	date('Y-m-d H:i:s')
	// 					);
	// 					$this->MainOperation->addData("t_depositvendorrecord", $arrInsertDepositRecord);
						
	// 					$arrUpdateCollectPayment	=	array(
	// 						"DATETIMESTATUS"			=>	date('Y-m-d H:i:s'),
	// 						"STATUSSETTLEMENTREQUEST"	=>	2,
	// 						"LASTUSERINPUT"				=>	"Auto System"
	// 					);
	// 					$this->MainOperation->updateData("t_collectpayment", $arrUpdateCollectPayment, "IDCOLLECTPAYMENT", $idCollectPayment);
						
	// 					$arrInsertCollectHistory	=	array(
	// 						"IDCOLLECTPAYMENT"	=>	$idCollectPayment,
	// 						"DESCRIPTION"		=>	"Settlement has been approved. Fees are converted to deposit",
	// 						"USERINPUT"			=>	"Auto System",
	// 						"DATETIMEINPUT"		=>	date('Y-m-d H:i:s'),
	// 						"STATUS"			=>	2
	// 					);
	// 					$this->MainOperation->addData("t_collectpaymenthistory", $arrInsertCollectHistory);
						
	// 					$arrUpdatePayment	=	array(
	// 						"STATUS"		=>	1,
	// 						"DATETIMEUPDATE"=>	date('Y-m-d H:i:s'),
	// 						"USERUPDATE"	=>	"Auto System",
	// 						"EDITABLE"		=>	0,
	// 						"DELETABLE"		=>	0
	// 					);
	// 					$this->MainOperation->updateData("t_reservationpayment", $arrUpdatePayment, "IDRESERVATIONPAYMENT", $idReservationPayment);
	// 				}
	// 			}
				
	// 			$arrInsertDepositRecord	=	array(
	// 				"IDVENDOR"				=>	$idVendor,
	// 				"IDRESERVATIONDETAILS"	=>	$idReservationDetails,
	// 				"IDCOLLECTPAYMENT"		=>	0,
	// 				"DESCRIPTION"			=>	"Deposit deduction after order completion for customer ".$customerName." on the activity on ".$scheduleDateText.", package : ".$productName,
	// 				"AMOUNT"				=>	$feeNominal * -1,
	// 				"USERINPUT"				=>	"Auto System",
	// 				"DATETIMEINPUT"			=>	date('Y-m-d H:i:s')
	// 			);
	// 			$this->MainOperation->addData("t_depositvendorrecord", $arrInsertDepositRecord);				
	// 		}
	// 	}

	// 	if($this->idPartnerType == 2) $whereUpdate["IDDRIVER"]	=	$this->idPartner;

	// 	$arrUpdate	=	array("STATUSPROCESS" => $statusProcess, "STATUS" => 2);
	// 	$isFinish	=	$forceRequestReview	=	false;
		
	// 	if($statusProcess == $maxStatusProcess) {
	// 		$isFinish			=	true;
	// 		$arrUpdate["STATUS"]=	3;
	// 		$forceRequestReview	=	REQUEST_REVIEW_FORCE;
			
	// 		if($idSource == 4) $this->executeEBookingCoinEarned($idReservation, $bookingCode);
	// 	}
		
	// 	$procUpdate	=	$this->MainOperation->updateData($tableUpdate, $arrUpdate, $whereUpdate);
		
	// 	if($procUpdate['status']){
	// 		$detailStatusProcess		=	$this->MainOperation->getDetailStatusProcessDescription($this->idPartnerType, $statusProcess);
	// 		$descriptionStatusProcess	=	$detailStatusProcess['STATUSPROCESSNAME'];
	// 		$isTrackingLocation			=	$detailStatusProcess['ISTRACKINGLOCATION'];
	// 		$arrInsert					=	array(
	// 			"IDRESERVATIONDETAILS"	=>	$idReservationDetails,
	// 			"DESCRIPTION"			=>	$descriptionStatusProcess,
	// 			"DATETIME"				=>	date('Y-m-d H:i:s'),
	// 			"GPSL"					=>	$gpsl,
	// 			"GPSB"					=>	$gpsb
	// 		);
	// 		$this->MainOperation->addData('t_reservationdetailstimeline', $arrInsert);
	// 		$this->MainOperation->updateData("t_reservation", array("STATUS"=>$statusReservation), "IDRESERVATION", $idReservation);
			
	// 		setResponseOk(
	// 			array(
	// 				"token"				=>	$this->newToken,
	// 				"msg"				=>	$msg,
	// 				"isFinish"			=>	$isFinish,
	// 				"isTrackingLocation"=>	(bool) $isTrackingLocation,
	// 				"forceRequestReview"=>	$forceRequestReview
	// 			)
	// 		);
	// 	}

	// 	setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Internal server error. Please try again later"));
	// }
}