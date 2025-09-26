<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends CI_controller {
	
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
		$this->load->model('ModelOrder');
		$this->load->model('MainOperation');

		$showActiveOnly		=	validatePostVar($this->postVar, 'showActiveOnly', false);
		$unconfirmStatus	=	validatePostVar($this->postVar, 'unconfirmStatus', false);
		$date				=	validatePostVar($this->postVar, 'date', false);
		$dateStart			=	validatePostVar($this->postVar, 'dateStart', false);
		$dateStart			=	!isset($dateStart) || $dateStart == "" ? $date : $dateStart;
		$dateEnd			=	validatePostVar($this->postVar, 'dateEnd', false);
		$dateEnd			=	!isset($dateEnd) || $dateEnd == "" ? $date : $dateEnd;
		$listOrder			=	$this->ModelOrder->listOrderByDate($this->idPartnerType, $this->idPartner, $dateStart, $dateEnd, $showActiveOnly, $unconfirmStatus);

		if(!$listOrder){
			setResponseNotFound(array("token"=>$this->newToken, "msg"=>"You have no order in selected date/period"));
		} else {
			
			foreach($listOrder as $keyOrder){
				$idReservation							=	$keyOrder->IDRESERVATION;
				$dateScheduleDB							=	$keyOrder->SCHEDULEDATEDB;
				$detailCollectPayment					=	$this->ModelOrder->detailCollectPayment($idReservation, $this->idPartnerType, $this->idPartner, $dateScheduleDB);
				$keyOrder->TOTALAMOUNTIDRCOLLECTPAYMENT	=	$detailCollectPayment['TOTALAMOUNTIDRCOLLECTPAYMENT'];
			}
			setResponseOk(array("token"=>$this->newToken, "listOrder"=>$listOrder));
		}
	}
	
	public function detailOrder(){
		$this->load->model('ModelOrder');
		$this->load->model('MainOperation');

		$idReservationDetails	=	validatePostVar($this->postVar, 'idReservationDetails', true);
		$detailOrder			=	$this->ModelOrder->getDetailOrder($idReservationDetails, $this->idPartnerType, $this->idPartner);
		$detailDriverData		=		[
			"NAME"				=>	"-",
			"PHONE"				=>	"-",
			"CARBRAND"			=>	"-",
			"CARTYPE"			=>	"-",
			"CARNUMBERPLATE"	=>	"-",
		];
		
		if(!$detailOrder){			
			setResponseNotFound(array("token"=>$this->newToken, "msg"=>"Detail order not found or this order has been canceled."));
		} else {						
			$reservationStatus		=	$detailOrder['RESERVATIONSTATUS'];
			$scheduleStatus			=	$detailOrder['SCHEDULESTATUS'];
			$processStatus			=	$detailOrder['STATUSPROCESS'];
			$idPartnerPrimary		=	$detailOrder['IDPARTNERPRIMARY'];
			$scheduleDate			=	strtotime($detailOrder['SCHEDULEDATESTR']);
			$dateYesterday			=	strtotime(date('Y-m-d', strtotime('-'.MAX_DAY_ADDITIONAL_COST_INPUT.' day')));
			$idReservation			=	$detailOrder['IDRESERVATION'];
			$dateScheduleDB			=	$detailOrder['SCHEDULEDATESTR'];
			$detailCollectPayment	=	$this->ModelOrder->detailCollectPayment($idReservation, $this->idPartnerType, $this->idPartner, $dateScheduleDB);
			
			if($detailCollectPayment['IDCOLLECTPAYMENT'] != 0){
				$amountCurrency			=	$detailCollectPayment['AMOUNTCURRENCY'];
				$explodeAmountCurrency	=	explode(",", $amountCurrency);
				
				if(count($explodeAmountCurrency) > 1){
					$detailCollectPayment['AMOUNTCURRENCY']		=	'IDR';
					$detailCollectPayment['EXCHANGECURRENCY']	=	"1";
					$detailCollectPayment['AMOUNT']				=	number_format($detailCollectPayment['TOTALAMOUNTIDRCOLLECTPAYMENT'] * 1, 2, ".", "");
				}
			}
		
			$orderTimeline			=	$this->ModelOrder->getTimelineOrder($idReservationDetails);
			$listAdditionalCost		=	$this->ModelOrder->getListAdditionalCost($idPartnerPrimary, $idReservationDetails);
			$detailOrder['ALLOWADDITIONALCOST']	=	0;
			
			if($reservationStatus == 1 && in_array($scheduleStatus, array(2,3)) && $scheduleDate >= $dateYesterday && $this->idPartnerType == 2){
				$detailOrder['ALLOWADDITIONALCOST']	=	1;
			}
			
			$detailOrder['CUSTOMERCONTACT']	=	preg_replace("/[^0-9+]/", "", $detailOrder['CUSTOMERCONTACT']);
			$allowSendReviewRequest			=	$scheduleStatus == 3 ? true : false;
			$allowUpdateDriverHandle		=	$this->idPartnerType == 2 && $processStatus < 2 ? true : false;
			
			if($this->idPartnerType == 2){
				$detailDriverData	=	[
					"PARTNERNAME"		=>	$this->detailPartner['PARTNERNAME'],
					"PARTNERPHONENUMBER"=>	$this->detailPartner['PARTNERPHONENUMBER'],
					"CARBRAND"			=>	$this->detailPartner['CARBRAND'],
					"CARMODEL"			=>	$this->detailPartner['CARMODEL'],
					"CARNUMBERPLATE"	=>	$this->detailPartner['CARNUMBERPLATE'],
				];
			}
			
			setResponseOk(
				array(
					"token"						=>	$this->newToken,
					"detailOrder"				=>	$detailOrder,
					"detailCollectPayment"		=>	$detailCollectPayment,
					"listAdditionalCost"		=>	$listAdditionalCost,
					"orderTimeline"				=>	setArrayOneDimention($orderTimeline),
					"allowSendReviewRequest"	=>	$allowSendReviewRequest,
					"allowUpdateDriverHandle"	=>	$allowUpdateDriverHandle,
					"detailDriverData"			=>	$detailDriverData
				)
			);
		}		
	}
	
	public function detailOrderCancel(){
		$this->load->model('ModelOrder');
		$this->load->model('MainOperation');

		$idReservation	=	validatePostVar($this->postVar, 'idReservation', true);
		$detailOrder	=	$this->ModelOrder->getDetailOrderCancel($idReservation);

		if(!$detailOrder){			
			setResponseNotFound(array("token"=>$this->newToken, "msg"=>"Detail order not found or this order has been deleted."));
		} else {
			setResponseOk(
				array(
					"token"			=>	$this->newToken,
					"detailOrder"	=>	$detailOrder
				)
			);
		}		
	}
	
	public function confirmOrder(){
		$this->load->model('ModelOrder');
		$this->load->model('MainOperation');

		$idReservationDetails	=	validatePostVar($this->postVar, 'idReservationDetails', true);
		$tableUpdate			=	$this->idPartnerType == 1 ? "t_schedulevendor" : "t_scheduledriver";
		$whereUpdate			=	array("IDRESERVATIONDETAILS"=>$idReservationDetails);

		if($this->idPartnerType == 2) $whereUpdate["IDDRIVER"]	=	$this->idPartner;
		$arrUpdate	=	array(
			"STATUSCONFIRM"		=> 1,
			"DATETIMECONFIRM"	=> date('Y-m-d H:i:s')
		);
		
		if($this->idPartnerType == 1){
			$detailStatusProcess		=	$this->MainOperation->getDetailStatusProcessDescription($this->idPartnerType, 1);
			$descriptionStatusProcess	=	$detailStatusProcess['STATUSPROCESSNAME'];
			$arrInsert					=	array(
												"IDRESERVATIONDETAILS"	=> $idReservationDetails,
												"DESCRIPTION"			=> "Booking confirmed and ".$descriptionStatusProcess,
												"DATETIME"				=> date('Y-m-d H:i:s')
											);
			$this->MainOperation->addData('t_reservationdetailstimeline', $arrInsert);
			
			$arrUpdate['STATUSPROCESS']		=	1;
		} else {
			$idDriverGroupMember=	$this->idPartnerType != 2 ? 0 : validatePostVar($this->postVar, 'idDriverGroupMember', true);
			$driverName			=	validatePostVar($this->postVar, 'driverName', true);
			$driverPhoneNumber	=	validatePostVar($this->postVar, 'driverPhoneNumber', true);
			$carBrand			=	validatePostVar($this->postVar, 'carBrand', true);
			$carModel			=	validatePostVar($this->postVar, 'carModel', true);
			$carNumberPlate		=	validatePostVar($this->postVar, 'carNumberPlate', true);
			
			if($this->detailPartner['PARTNERSHIPTYPE'] != 3){
				$arrUpdateDriver	=	[
					"PHONE"				=>	$driverPhoneNumber,
					"CARNUMBERPLATE"	=>	strtoupper($carNumberPlate),
					"CARBRAND"			=>	strtoupper($carBrand),
					"CARMODEL"			=>	strtoupper($carModel)
				];
				$this->MainOperation->updateData('m_driver', $arrUpdateDriver, ['IDDRIVER' => $this->idPartner]);
			}
			
			$arrUpdate['IDDRIVERGROUPMEMBER']	=	$idDriverGroupMember;
			$arrUpdate['DRIVERNAME']			=	$driverName;
			$arrUpdate['DRIVERPHONENUMBER']		=	$driverPhoneNumber;
			$arrUpdate['CARBRANDMODEL']			=	$carBrand." ".$carModel;
			$arrUpdate['CARNUMBERPLATE']		=	$carNumberPlate;
		}
		
		$procUpdate		=	$this->MainOperation->updateData($tableUpdate, $arrUpdate, $whereUpdate);
		if(!$procUpdate['status']) setResponseNotFound(array("token"=>$this->newToken, "msg"=>"Internal server error. Please try again later"));

		$confirmBy		=	$this->idPartnerType == 1 ? "vendor" : "driver";
		$notifType		=	$this->idPartnerType == 1 ? "vendorschedule" : "driverschedule";
		$notifTypeDB	=	$this->idPartnerType == 1 ? "NOTIFSCHEDULEVENDOR" : "NOTIFSCHEDULEDRIVER";
		$dataPlayerId	=	$this->MainOperation->getDataPlayerIdOneSignal($notifTypeDB);
		
		if($dataPlayerId){
			$arrPlayerId		=	$dataPlayerId['arrOSUserId'];
			$arrIdUserAdmin		=	$dataPlayerId['arrIdUserAdmin'];
			$detailOrder		=	$this->ModelOrder->getDetailOrder($idReservationDetails, $this->idPartnerType, $this->idPartner);
			$customerName		=	$detailOrder['CUSTOMERNAME'];
			$reservationTitle	=	$detailOrder['RESERVATIONTITLE'];
			$title				=	'Order Confirmed by '.$confirmBy;
			$message			=	'Customer Name : '.$customerName.'. Reservation Title : '.$reservationTitle;
			$arrData			=	array(
										"type"					=>	$notifType,
										"idReservationDetails"	=>	$idReservationDetails,
										"date"					=>	$detailOrder['SCHEDULEDATEPARAMNOTIF']
									);
			$arrHeading			=	array("en" => $title);
			$arrContent			=	array("en" => $message);
			$this->MainOperation->insertAdminMessage(6, $arrIdUserAdmin, $title, $message, $arrData);
			sendOneSignalMessage($arrPlayerId, $arrData, $arrHeading, $arrContent);
		}
		
		setResponseOk(array("token"=>$this->newToken, "msg"=>"Schedule confirmed"));
	}
	
	public function updateDetailDriverHandle(){
		$this->load->model('ModelOrder');
		$this->load->model('MainOperation');

		$idReservationDetails	=	validatePostVar($this->postVar, 'idReservationDetails', true);
		$idDriverGroupMember	=	$this->idPartnerType != 2 ? 0 : validatePostVar($this->postVar, 'idDriverGroupMember', true);
		$driverName				=	validatePostVar($this->postVar, 'driverName', true);
		$driverPhoneNumber		=	validatePostVar($this->postVar, 'driverPhoneNumber', true);
		$carBrand				=	validatePostVar($this->postVar, 'carBrand', true);
		$carModel				=	validatePostVar($this->postVar, 'carModel', true);
		$carNumberPlate			=	validatePostVar($this->postVar, 'carNumberPlate', true);
		
		if($this->detailPartner['PARTNERSHIPTYPE'] != 3){
			$arrUpdateDriver	=	[
				"PHONE"				=>	$driverPhoneNumber,
				"CARNUMBERPLATE"	=>	strtoupper($carNumberPlate),
				"CARBRAND"			=>	strtoupper($carBrand),
				"CARMODEL"			=>	strtoupper($carModel)
			];
			$this->MainOperation->updateData('m_driver', $arrUpdateDriver, ['IDDRIVER' => $this->idPartner]);
		}
		
		$arrUpdateSchedule	=	[
			'IDDRIVERGROUPMEMBER'	=>	$idDriverGroupMember,
			'DRIVERNAME'			=>	$driverName,
			'DRIVERPHONENUMBER'		=>	$driverPhoneNumber,
			'CARBRANDMODEL'			=>	$carBrand." ".$carModel,
			'CARNUMBERPLATE'		=>	$carNumberPlate,
		];

		$procUpdateSchedule	=	$this->MainOperation->updateData('t_scheduledriver', $arrUpdateSchedule, ["IDRESERVATIONDETAILS"=>$idReservationDetails]);
		if(!$procUpdateSchedule['status']) setResponseNotFound(array("token"=>$this->newToken, "msg"=>"Internal server error. Please try again later"));

		setResponseOk(array("token"=>$this->newToken, "msg"=>"Detail driver handle has been updated"));
	}
	
	public function confirmCollectPayment(){
		$this->load->model('ModelOrder');
		$this->load->model('MainOperation');

		$idCollectPayment		=	validatePostVar($this->postVar, 'idCollectPayment', true);
		$remarkCollectPayment	=	validatePostVar($this->postVar, 'remarkCollectPayment', false);
		$isValidCollectPayment	=	$this->ModelOrder->isValidCollectPayment($this->idPartnerType, $this->idPartner, $idCollectPayment);

		if(!$isValidCollectPayment) setResponseNotFound(array("token" => $this->newToken, "msg" => "You are not allowed to perform this action"));
		
		$idReservation			=	$isValidCollectPayment['IDRESERVATION'];	
		$dateCollect			=	$isValidCollectPayment['DATECOLLECT'];	
		$strArrIdCollectPayment	=	$this->ModelOrder->getStrArrIdCollectPaymentByDateReservation($idReservation, $this->idPartnerType, $this->idPartner, $dateCollect);
		
		if(!$strArrIdCollectPayment) setResponseNotFound(array("token" => $this->newToken, "msg" => "You are not allowed to perform this action"));
		if($dateCollect > date('Y-m-d')) setResponseForbidden(array("token" => $this->newToken, "msg" => "Collect payment confirmation is not allowed before the appointed day"));

		$arrIdCollectPayment	=	explode(",", $strArrIdCollectPayment);
		foreach($arrIdCollectPayment as $idCollectPayment){
			$arrUpdateCollectPayment	=	array(
				"STATUS"		=>	1,
				"DATETIMESTATUS"=>	date('Y-m-d H:i:s')
			);
			$procUpdateCollectPayment	=	$this->MainOperation->updateData("t_collectpayment", $arrUpdateCollectPayment, "IDCOLLECTPAYMENT", $idCollectPayment);

			if($procUpdateCollectPayment['status']){
				$partnerDetail			=	$this->idPartnerType == 1 ? $this->MainOperation->getDetailVendor($this->idPartner) : $this->MainOperation->getDetailDriver($this->idPartner);
				$partnerTypeStr			=	$this->idPartnerType == 1 ? "Vendor" : "Driver";
				$partnerName			=	$partnerDetail['NAME'];
				$arrInsertCollectHistory=	array(
					"IDCOLLECTPAYMENT"	=>	$idCollectPayment,
					"DESCRIPTION"		=>	"Partner confirms collect payment has been completed",
					"SETTLEMENTRECEIPT"	=>	"",
					"USERINPUT"			=>	$partnerName." (".$partnerTypeStr.")",
					"DATETIMEINPUT"		=>	date('Y-m-d H:i:s'),
					"STATUS"			=>	1
				);
				$this->MainOperation->addData("t_collectpaymenthistory", $arrInsertCollectHistory);
				
				if(isset($remarkCollectPayment) && $remarkCollectPayment != ""){
					$detailCollectPayment	=	$this->ModelOrder->isValidCollectPayment($this->idPartnerType, $this->idPartner, $idCollectPayment);
					$idReservationPayment	=	$detailCollectPayment['IDRESERVATIONPAYMENT'];	
					$descriptionPayment		=	$detailCollectPayment['DESCRIPTION'].". Partner Remark : ".$remarkCollectPayment;	

					$this->MainOperation->updateData("t_reservationpayment", array("DESCRIPTION"=>$descriptionPayment), "IDRESERVATIONPAYMENT", $idReservationPayment);
				}
			}
		}
		
		setResponseOk(array("token"=>$this->newToken, "msg"=>"Your collect payment has been confirmed"));
	}
	
	public function updateStatusOrder(){
		$this->load->model('MainOperation');
		$this->load->model('ModelOrder');

		$idReservationDetails	=	validatePostVar($this->postVar, 'idReservationDetails', true);
		$statusProcess			=	validatePostVar($this->postVar, 'statusProcess', true);
		$gpsl					=	validatePostVar($this->postVar, 'gpsl', true);
		$gpsb					=	validatePostVar($this->postVar, 'gpsb', true);
		$tableUpdate			=	$this->idPartnerType == 1 ? "t_schedulevendor" : "t_scheduledriver";
		$whereUpdate			=	array("IDRESERVATIONDETAILS"=>$idReservationDetails);
		$detailOrder			=	$this->ModelOrder->getDetailOrder($idReservationDetails, $this->idPartnerType, $this->idPartner);
		$idReservation			=	$detailOrder['IDRESERVATION'];
		$idSource				=	$detailOrder['IDSOURCE'];
		$bookingCode			=	$detailOrder['BOOKINGCODE'];
		$dateScheduleDB			=	$detailOrder['SCHEDULEDATESTR'];
		$timeScheduleDB			=	$detailOrder['RESERVATIONTIMESTART'];
		$scheduleDateText		=	$detailOrder['SCHEDULEDATETEXT'];
		$customerName			=	$detailOrder['CUSTOMERNAME'];
		$reservationTitle		=	$detailOrder['RESERVATIONTITLE'];
		$productName			=	$detailOrder['PRODUCTNAME'];
		$feeNominal				=	$detailOrder['NOMINAL'];
		$feeNotes				=	$detailOrder['NOTES'];
		$detailCollectPayment	=	$this->ModelOrder->detailCollectPayment($idReservation, $this->idPartnerType, $this->idPartner, $dateScheduleDB);
		$maxStatusProcess		=	$this->MainOperation->getMaxStatusProcess($this->idPartnerType);
		$msg					=	"Order status updated";
		$statusReservation		=	3;
		
		if($dateScheduleDB > date('Y-m-d')) setResponseForbidden(array("token" => $this->newToken, "msg" => "Order status updates are not allowed before the activity day"));
		
		if($statusProcess == $maxStatusProcess){
			$msg					=	"Order finished. Please tell your customer to review your service";
			$statusReservation		=	4;
			$partnerDetail			=	$this->idPartnerType == 1 ? $this->MainOperation->getDetailVendor($this->idPartner) : $this->MainOperation->getDetailDriver($this->idPartner);
			$newFinanceScheme		=	$partnerDetail['NEWFINANCESCHEME'] * 1;
			$idVendor				=	$this->idPartnerType == 1 ? $this->idPartner : 0;
			$idDriver				=	$this->idPartnerType == 2 ? $this->idPartner : 0;
			$dateTimeScheduleDB		=	$dateScheduleDB." ".$timeScheduleDB;
			$dateScheduleDBCompare	=	str_replace('-', '', $dateScheduleDB) * 1;
			$timeStampAllowFee		=	strtotime($dateTimeScheduleDB) + 60 * 60 * MIN_DURATION_ORDER_TO_CREATE_FEE;
			$timeAllowFeeStr		=	date('H:i', $timeStampAllowFee);
			
			if($detailCollectPayment && $newFinanceScheme == 1){
				$statusCollectPayment	=	$detailCollectPayment['STATUS'] * 1;
				$idCollectPayment		=	$detailCollectPayment['IDCOLLECTPAYMENT'] * 1;

				if($idCollectPayment != 0 && $statusCollectPayment != 1) setResponseForbidden(array("token" => $this->newToken, "msg" => "You cannot complete this order. Please confirm collect payment in this order first"));
			}
			
			if(date('H') < MIN_TIME_HOUR_ORDER_TO_CREATE_FEE && $dateScheduleDBCompare >= date('Ymd')) setResponseForbidden(array("token" => $this->newToken, "msg" => "You cannot complete this order. Please update the order completion after ".MIN_TIME_HOUR_ORDER_TO_CREATE_FEE.":00"));
			if(strtotime("now") < $timeStampAllowFee) setResponseForbidden(array("token" => $this->newToken, "msg" => "You cannot complete this order. Please update the order completion ".MIN_DURATION_ORDER_TO_CREATE_FEE." hours after reservation start (after ".$timeAllowFeeStr.")"));

			if($this->financeSchemeType == 1){
				$isFeeExist			=	$this->ModelOrder->isFeeExist($idReservation, $idReservationDetails, $idVendor, $idDriver);
				if($newFinanceScheme == 1 && $dateScheduleDB > '2023-01-31' && !$isFeeExist){
					$arrInsertFee	=	array(
						"IDRESERVATION"			=>	$idReservation,
						"IDRESERVATIONDETAILS"	=>	$idReservationDetails,
						"IDVENDOR"				=>	$idVendor,
						"IDDRIVER"				=>	$idDriver,
						"DATESCHEDULE"			=>	$dateScheduleDB,
						"RESERVATIONTITLE"		=>	$reservationTitle,
						"JOBTITLE"				=>	$productName,
						"FEENOMINAL"			=>	$feeNominal,
						"FEENOTES"				=>	$feeNotes,
						"DATETIMEINPUT"			=>	date('Y-m-d H:i:s')
					);
					$this->MainOperation->addData("t_fee", $arrInsertFee);
				}
			} else {
				
				if($detailCollectPayment){
					$idCollectPayment			=	$detailCollectPayment['IDCOLLECTPAYMENT'] * 1;
					$idReservationPayment		=	$detailCollectPayment['IDRESERVATIONPAYMENT'] * 1;
					$collectPaymentAmount		=	$detailCollectPayment['TOTALAMOUNTIDRCOLLECTPAYMENT'] * 1;
					
					if($collectPaymentAmount > 0){
						$arrInsertDepositRecord	=	array(
							"IDVENDOR"				=>	$idVendor,
							"IDRESERVATIONDETAILS"	=>	0,
							"IDCOLLECTPAYMENT"		=>	$idCollectPayment,
							"DESCRIPTION"			=>	"Conversion of deposit from collect payment from customer ".$customerName." on the activity on ".$scheduleDateText.", package : ".$productName,
							"AMOUNT"				=>	$collectPaymentAmount,
							"USERINPUT"				=>	"Auto System",
							"DATETIMEINPUT"			=>	date('Y-m-d H:i:s')
						);
						$this->MainOperation->addData("t_depositvendorrecord", $arrInsertDepositRecord);
						
						$arrUpdateCollectPayment	=	array(
							"DATETIMESTATUS"			=>	date('Y-m-d H:i:s'),
							"STATUSSETTLEMENTREQUEST"	=>	2,
							"LASTUSERINPUT"				=>	"Auto System"
						);
						$this->MainOperation->updateData("t_collectpayment", $arrUpdateCollectPayment, "IDCOLLECTPAYMENT", $idCollectPayment);
						
						$arrInsertCollectHistory	=	array(
							"IDCOLLECTPAYMENT"	=>	$idCollectPayment,
							"DESCRIPTION"		=>	"Settlement has been approved. Fees are converted to deposit",
							"USERINPUT"			=>	"Auto System",
							"DATETIMEINPUT"		=>	date('Y-m-d H:i:s'),
							"STATUS"			=>	2
						);
						$this->MainOperation->addData("t_collectpaymenthistory", $arrInsertCollectHistory);
						
						$arrUpdatePayment	=	array(
							"STATUS"		=>	1,
							"DATETIMEUPDATE"=>	date('Y-m-d H:i:s'),
							"USERUPDATE"	=>	"Auto System",
							"EDITABLE"		=>	0,
							"DELETABLE"		=>	0
						);
						$this->MainOperation->updateData("t_reservationpayment", $arrUpdatePayment, "IDRESERVATIONPAYMENT", $idReservationPayment);
					}
				}
				
				$arrInsertDepositRecord	=	array(
					"IDVENDOR"				=>	$idVendor,
					"IDRESERVATIONDETAILS"	=>	$idReservationDetails,
					"IDCOLLECTPAYMENT"		=>	0,
					"DESCRIPTION"			=>	"Deposit deduction after order completion for customer ".$customerName." on the activity on ".$scheduleDateText.", package : ".$productName,
					"AMOUNT"				=>	$feeNominal * -1,
					"USERINPUT"				=>	"Auto System",
					"DATETIMEINPUT"			=>	date('Y-m-d H:i:s')
				);
				$this->MainOperation->addData("t_depositvendorrecord", $arrInsertDepositRecord);				
			}
		}

		if($this->idPartnerType == 2) $whereUpdate["IDDRIVER"]	=	$this->idPartner;

		$arrUpdate	=	array("STATUSPROCESS" => $statusProcess, "STATUS" => 2);
		$isFinish	=	$forceRequestReview	=	false;
		
		if($statusProcess == $maxStatusProcess) {
			$isFinish			=	true;
			$arrUpdate["STATUS"]=	3;
			$forceRequestReview	=	REQUEST_REVIEW_FORCE;
			
			if($idSource == 4) $this->executeEBookingCoinEarned($idReservation, $bookingCode);
		}
		
		$procUpdate	=	$this->MainOperation->updateData($tableUpdate, $arrUpdate, $whereUpdate);
		
		if($procUpdate['status']){
			$detailStatusProcess		=	$this->MainOperation->getDetailStatusProcessDescription($this->idPartnerType, $statusProcess);
			$descriptionStatusProcess	=	$detailStatusProcess['STATUSPROCESSNAME'];
			$isTrackingLocation			=	$detailStatusProcess['ISTRACKINGLOCATION'];
			$arrInsert					=	array(
				"IDRESERVATIONDETAILS"	=>	$idReservationDetails,
				"DESCRIPTION"			=>	$descriptionStatusProcess,
				"DATETIME"				=>	date('Y-m-d H:i:s'),
				"GPSL"					=>	$gpsl,
				"GPSB"					=>	$gpsb
			);
			$this->MainOperation->addData('t_reservationdetailstimeline', $arrInsert);
			$this->MainOperation->updateData("t_reservation", array("STATUS"=>$statusReservation), "IDRESERVATION", $idReservation);
			
			setResponseOk(
				array(
					"token"				=>	$this->newToken,
					"msg"				=>	$msg,
					"isFinish"			=>	$isFinish,
					"isTrackingLocation"=>	(bool) $isTrackingLocation,
					"forceRequestReview"=>	$forceRequestReview
				)
			);
		}

		setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Internal server error. Please try again later"));
	}
	
	public function updateCoinBookingEcommerceTest(){
		$this->load->model('MainOperation');
		$this->load->model('ModelOrder');

		$idReservation	=	124552;
		$bookingCode	=	'BSAT5393613';
		$procUpdateCoin	=	$this->executeEBookingCoinEarned($idReservation, $bookingCode, true);
		
		echo json_encode($procUpdateCoin);
	}
	
	private function executeEBookingCoinEarned($idReservation, $bookingCode, $test = false){
		$dataEBookingCoin	=	$this->ModelOrder->isDataEBookingCoinExist($idReservation);
		$idEBookingCoin		=	0;
		$status				=	0;
		
		if(!$dataEBookingCoin){
			$arrInsertData	=	[
				'IDRESERVATION'	=>	$idReservation,
				'EXECUTETYPE'	=>	1,
				'EXECUTEBY'		=>	$this->partnerName,
				'DATETIMEINSERT'=>	date('Y-m-d H:i:s'),
				'STATUS'		=>	0
			];
			
			$procInsertData	=	$this->MainOperation->addData('t_ebookingcoin', $arrInsertData);
			
			if($procInsertData['status']) $idEBookingCoin	=	$procInsertData['lastID'];
		} else {
			$idEBookingCoin	=	$dataEBookingCoin['IDEBOOKINGCOIN'];
			$status			=	$dataEBookingCoin['STATUS'];
		}
		
		if(in_array($status, [-1,0])){
			$timeStamp		=	time();
			$dataJSON       =   json_encode(['booking_code'=>$bookingCode, 'timestamp'=>$timeStamp]);
            $privateKey     =   ROKET_ECOMMERCE_PRIVATE_KEY;
            $hmacSignature  =   hash_hmac('sha256', $dataJSON, $privateKey);
			$procUpdateCoin	=	$this->updateCoinBookingEcommerce($bookingCode, $hmacSignature, $timeStamp);
			$httpCode		=	$procUpdateCoin['httpCode'];
			$response		=	$procUpdateCoin['response'];
			$arrUpdateCoin	=	[
				'EXECUTETYPE'		=>	1,
				'EXECUTEBY'			=>	$this->partnerName,
				'DATETIMEEXECUTE'	=>	date('Y-m-d H:i:s'),
				'APIRESPONSE'		=>	$response,
				'STATUS'			=>	0
			];
			
			switch(intval($httpCode)){
				case 200	:	$arrUpdateCoin['STATUS']	=	1; break;
				case 401	:	$arrUpdateCoin['STATUS']	=	-1; break;
				case 409	:	$arrUpdateCoin['STATUS']	=	1; break;
			}
			
			$this->MainOperation->updateData('t_ebookingcoin', $arrUpdateCoin, 'IDEBOOKINGCOIN', $idEBookingCoin);
			if($test) return $procUpdateCoin;
		}
		
		if(!$test) return true;
	}
	
	private function updateCoinBookingEcommerce($bookingCode, $hmacSignature, $timeStamp){
		$response	=	"";
		$httpCode	=	500;

		try {
			$curl	=	curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL				=>	ROKET_ECOMMERCE_API_BASE_URL.'/api/customer/coin/earn-from-booking?booking_code='.$bookingCode,
			  CURLOPT_RETURNTRANSFER	=>	true,
			  CURLOPT_ENCODING			=>	'',
			  CURLOPT_MAXREDIRS			=>	10,
			  CURLOPT_TIMEOUT			=>	0,
			  CURLOPT_FOLLOWLOCATION	=>	true,
			  CURLOPT_HTTP_VERSION		=>	CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST		=>	'POST',
			  CURLOPT_HTTPHEADER		=>	array(
				'BST-Public-Key: '.ROKET_ECOMMERCE_PUBLIC_KEY,
				'BST-Signature: '.$hmacSignature,
				'BST-Timestamp: '.$timeStamp
			  ),
			));

			$response	=	curl_exec($curl);
			$httpCode	=	curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);
		} catch (Exception $e) {
		}
		
		return [
			'httpCode'	=>	$httpCode,
			'response'	=>	json_encode($response)
		];
	}

	public function templateReviewOrder(){
		$this->load->model('ModelOrder');

		$idReservationDetails	=	validatePostVar($this->postVar, 'idReservationDetails', true);
		$detailOrder			=	$this->ModelOrder->getDetailOrder($idReservationDetails, $this->idPartnerType, $this->idPartner);
		
		if(!$detailOrder) setResponseNotFound(array("token" => $this->newToken, "msg" => "[E001] Order review content is not available at this time"));
		
		$idSource			=	$detailOrder['IDSOURCE'];
		$dataTemplateReview	=	$this->ModelOrder->getDataTemplateReview($idSource);
		if(!$dataTemplateReview) setResponseNotFound(array("token" => $this->newToken, "msg" => "[E002] Order review content is not available at this time"));
		
		$customerName		=	$detailOrder['CUSTOMERNAME'];
		$bookingCode		=	$detailOrder['BOOKINGCODE'];
		$reservationTitle	=	$detailOrder['RESERVATIONTITLE'];
		$customerContact	=	preg_replace("/[^0-9]/", "", $detailOrder['CUSTOMERCONTACT']);
		$driverName			=	$this->partnerName;
		$urlReview			=	'';
		$dataArrUrlReview	=	$this->ModelOrder->getURLReviewOrder($idReservationDetails);
		
		if(!$dataArrUrlReview || !$dataArrUrlReview) setResponseNotFound(array("token" => $this->newToken, "msg" => "[E003] Order review content is not available at this time"));
		
		$strArrUrlReview	=	$dataArrUrlReview['ARRPRODUCTURL'];
		$arrUrlReview		=	explode(',', $strArrUrlReview);
		
		foreach($arrUrlReview as $strUrlReview){
			if($strUrlReview != "" && $strUrlReview != "-")	$urlReview	=	$strUrlReview;
		}
		
		if($idSource == 1) $urlReview	=	URL_KLOOK_REVIEW;
		if(!$urlReview || $urlReview == '') setResponseNotFound(array("token" => $this->newToken, "msg" => "[E004] Order review content is not available at this time"));
		
		$arrFindString		=	['{{customer_name}}', '{{reservation_title}}', '{{url_review}}', '{{driver_name}}'];
		$arrReplaceString	=	[$customerName, $reservationTitle, $urlReview, $driverName];

		foreach($dataTemplateReview as $keyTemplateReview){
			$reviewContent	=	$keyTemplateReview->TEMPLATECONTENT;
			$reviewContent	=	str_replace($arrFindString, $arrReplaceString, $reviewContent);
			
			$keyTemplateReview->TEMPLATECONTENT	=	$reviewContent;
			$keyTemplateReview->WAMEURLENCODE	=	"https://wa.me/".$customerContact."?text=".urlencode($reviewContent);
		}
		
		setResponseOk(array("token"=>$this->newToken, "dataTemplateReview"=>$dataTemplateReview));
	}
}