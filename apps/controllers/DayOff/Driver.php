<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Driver extends CI_controller {
	
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
		$this->load->model('DayOff/ModelDriver');

		$yearMonth		=	validatePostVar($this->postVar, 'yearMonth', true);
		$detailPartner	=	$this->MainOperation->getDetailPartner($this->newToken);
		$idDriver		=	$detailPartner['IDPARTNER'];
		$dataCalendar	=	$this->ModelDriver->getDataCalendarDayOff($idDriver, $yearMonth);		

		setResponseOk(array("token"=>$this->newToken, "dataCalendar"=>$dataCalendar));
		
	}
	
	public function dayOffDetail(){

		$this->load->model('MainOperation');
		$this->load->model('DayOff/ModelDriver');

		$date			=	validatePostVar($this->postVar, 'date', true);
		$detailPartner	=	$this->MainOperation->getDetailPartner($this->newToken);
		$idDriver		=	$detailPartner['IDPARTNER'];
		$detailCalendar	=	$this->ModelDriver->getDetailCalendarDayOff($idDriver, $date);
		
		if(!$detailCalendar){
			setResponseNotFound(array("token"=>$this->newToken, "msg"=>"Detail day off not found"));
		}
		
		setResponseOk(array("token"=>$this->newToken, "detailCalendar"=>$detailCalendar));
		
	}
	
	public function submitDayOffRequest(){

		$this->load->model('MainOperation');
		$this->load->model('DayOff/ModelDriver');
		
		$date					=	validatePostVar($this->postVar, 'date', true);
		$detailPartner			=	$this->MainOperation->getDetailPartner($this->newToken);
		$idDriver				=	$detailPartner['IDPARTNER'];
		$isQuotaSufficient		=	$this->ModelDriver->isDayOffQuotaSufficient($date);
		$isDriverLimitNotExceed	=	$this->isDriverLimitNotExceed($idDriver, $date, $detailPartner);
		
		if($isQuotaSufficient && $isDriverLimitNotExceed){
			$this->submitDayOff();
		} else {		
			$date			=	validatePostVar($this->postVar, 'date', true);
			$reason			=	validatePostVar($this->postVar, 'reason', true);
			$dateStr		=	DateTime::createFromFormat('Y-m-d', $date);
			$dateStr		=	$dateStr->format('d M Y');
			$totalSchedule	=	$this->ModelDriver->getTotalSchedule($idDriver, $date);
			
			if($totalSchedule > 0){
				setResponseForbidden(array("token"=>$this->newToken, "msg"=>"Rejected! You have an order scheduled on the selected date. \n\nPlease ask the admin to cancel the order first"));
			}

			$isRequestExist	=	$this->ModelDriver->isRequestExist($idDriver, $date);
			
			if($isRequestExist > 0){
				setResponseForbidden(array("token"=>$this->newToken, "msg"=>"Rejected! You have a request for day off on a selected date and it has not been approved. Please wait for admin to do approval"));
			}
						
			/**** DISABLE DRIVER OFF QUOTA FOR DRIVER IN 1 MONTH LIMIT ****/
			// $dayOffYearMonth	=	substr($date, 0, 7);
			// $dayOffDate			=	DateTime::createFromFormat('Y-m-d', $date);
			// $monthYearDayOffStr	=	$dayOffDate->format('M Y');
			// $dayOffLimitDriver	=	$this->ModelDriver->getTotalDayOffDriverInMonth($idDriver, $dayOffYearMonth);
			// $dayOffLimitSetting	=	$this->MainOperation->getValueSystemSettingVariable(11);
			// $partnershipType	=	$detailPartner['PARTNERSHIPTYPE'];
			
			// if($dayOffLimitDriver >= $dayOffLimitSetting && ($partnershipType == 1 || $partnershipType == 4)){
			// 	setResponseForbidden(
			// 		array(
			// 			"token"	=>	$this->newToken,
			// 			"msg"	=>	"Day off input is no longer allowed.<br/>The number of days off for this driver in <b>".$monthYearDayOffStr."</b> has reached its maximum limit <b>[".$dayOffLimitSetting."]</b>"
			// 		)
			// 	);
			// }

			$dayOffQuotaExceed	=	!$isQuotaSufficient ? 1 : 0;
			$driverLimitExceed	=	!$isDriverLimitNotExceed ? 1 : 0;
			$arrInsert			=	array(
										"IDDRIVER"			=>	$idDriver,
										"DATEDAYOFF"		=>	$date,
										"REASON"			=>	$reason,
										"DAYOFFQUOTAEXCEED"	=>	$dayOffQuotaExceed,
										"DRIVERLIMITEXCEED"	=>	$driverLimitExceed,
										"DATETIMEINPUT"		=>	date('Y-m-d H:i:s')
									);
			$procInsert			=	$this->MainOperation->addData("t_dayoffrequest", $arrInsert);
			
			if(!$procInsert['status']){
				if($procInsert['errCode'] == "1062"){
					setResponseForbidden(array("token"=>$this->newToken, "msg"=>"Failed. Day off data is exist for the date you choose"));
				} else {
					switchMySQLErrorCode($procInsert['errCode'], $this->newToken);
				}
			}
			
			$idDayOffRequest=	$procInsert['lastID'];
			$dataPlayerId	=	$this->MainOperation->getDataPlayerIdOneSignal("NOTIFSCHEDULEDRIVER");
			
			if($dataPlayerId){
				$arrPlayerId	=	$dataPlayerId['arrOSUserId'];
				$arrIdUserAdmin	=	$dataPlayerId['arrIdUserAdmin'];
				$detailDriver	=	$this->MainOperation->getDetailDriver($idDriver);
				$driverName		=	$detailDriver['NAME'];
				$title			=	'New day off request from driver';
				$message		=	'Driver Name : '.$driverName.'. Date of Day Off : '.$dateStr.'. Reason : '.$reason;
				$arrData		=	array(
										"type"				=>	"driverschedule",
										"idDriver"			=>	$idDriver,
										"idDayOffRequest"	=>	$idDayOffRequest
									);
				$arrHeading		=	array(
										"en" => $title
									);
				$arrContent		=	array(
										"en" => $message
									);
				$this->MainOperation->insertAdminMessage(5, $arrIdUserAdmin, $title, $message, $arrData);
				sendOneSignalMessage($arrPlayerId, $arrData, $arrHeading, $arrContent);
			}

			setResponseOk(array("token"=>$this->newToken, "msg"=>"Day off request has been received. Please wait for admin approval"));
		}
	}

	private function isDriverLimitNotExceed($idDriver, $date, $detailPartner){
		$dayOffYearMonth	=	substr($date, 0, 7);
		$dayOffLimitDriver	=	$this->ModelDriver->getTotalDayOffDriverInMonth($idDriver, $dayOffYearMonth);
		$dayOffLimitSetting	=	$this->MainOperation->getValueSystemSettingVariable(11);
		$partnershipType	=	$detailPartner['PARTNERSHIPTYPE'];
		
		if($dayOffLimitDriver >= $dayOffLimitSetting && ($partnershipType == 1 || $partnershipType == 4)) return false;
		return true;
	}
	
	public function submitDayOff(){

		$this->load->model('MainOperation');
		$this->load->model('DayOff/ModelDriver');

		$date			=	validatePostVar($this->postVar, 'date', true);
		$reason			=	validatePostVar($this->postVar, 'reason', true);
		$detailPartner	=	$this->MainOperation->getDetailPartner($this->newToken);
		$idDriver		=	$detailPartner['IDPARTNER'];
		$totalSchedule	=	$this->ModelDriver->getTotalSchedule($idDriver, $date);
		$dateTomorrow	=	date('Y-m-d', strtotime("+1 days"));
		
		if($totalSchedule > 0){
			setResponseForbidden(array("token"=>$this->newToken, "msg"=>"Rejected! You have an order scheduled on the selected date. \n\nPlease ask the admin to cancel the order first"));
		}
		
		if(strtotime($date) <= strtotime(date('Y-m-d'))){
			setResponseForbidden(array("token"=>$this->newToken, "msg"=>"Maximum day off request is the day before (H-1)"));
		}
		
		$dataDayOffLimit	=	$this->getDataDayOffLimit($date);
		if($dataDayOffLimit['isLimited']){
			setResponseForbidden(
				array(
					"token"	=>	$this->newToken,
					"msg"	=>	"Day off input is not allowed. The number of days off has exceeded the limit allowed (".$dataDayOffLimit['maxDayOffNumber'].") for the selected date"
				)
			);
		}
		
		/**** DISABLE DRIVER OFF QUOTA FOR DRIVER IN 1 MONTH LIMIT ****/
		// $dayOffYearMonth	=	substr($date, 0, 7);
		// $dayOffDate			=	DateTime::createFromFormat('Y-m-d', $date);
		// $monthYearDayOffStr	=	$dayOffDate->format('M Y');
		// $dayOffLimitDriver	=	$this->ModelDriver->getTotalDayOffDriverInMonth($idDriver, $dayOffYearMonth);
		// $dayOffLimitSetting	=	$this->MainOperation->getValueSystemSettingVariable(11);
		// $partnershipType	=	$detailPartner['PARTNERSHIPTYPE'];
		
		// if($dayOffLimitDriver >= $dayOffLimitSetting && ($partnershipType == 1 || $partnershipType == 4)){
		// 	setResponseForbidden(
		// 		array(
		// 			"token"	=>	$this->newToken,
		// 			"msg"	=>	"Day off input is no longer allowed.<br/>The number of days off for this driver in <b>".$monthYearDayOffStr."</b> has reached its maximum limit <b>[".$dayOffLimitSetting."]</b>"
		// 		)
		// 	);
		// }
		
		// if($date == $dateTomorrow && date('H') > 23){
			// setResponseForbidden(array("token"=>$this->newToken, "msg"=>"Maximum day off request for tomorrow is at 20.00 today"));
		// }
		
		$arrInsert		=	array(
								"IDDRIVER"		=>	$idDriver,
								"DATEDAYOFF"	=>	$date,
								"REASON"		=>	$reason,
								"DATETIMEINPUT"	=>	date('Y-m-d H:i:s')
							);
		$procInsert		=	$this->MainOperation->addData("t_dayoff", $arrInsert);
		
		if(!$procInsert['status']){
			if($procInsert['errCode'] == "1062"){
				setResponseForbidden(array("token"=>$this->newToken, "msg"=>"Failed. Day off data is exist for the date you choose"));
			} else {
				switchMySQLErrorCode($procInsert['errCode'], $this->newToken);
			}
		}

		setResponseOk(array("token"=>$this->newToken, "msg"=>"Your day off request has been accepted"));
		
	}
	
	private function getDataDayOffLimit($date){
		
		$this->load->model('MainOperation');
		$this->load->model('DayOff/ModelDriver');
		
		$dataDriverMonitor	=	$this->MainOperation->getDataDriverMonitor($date);
		$maxDayOffNumber	=	$dataDriverMonitor['TOTALDAYOFFQUOTA'];
		$totalDayOffInDate	=	$dataDriverMonitor['TOTALOFFDRIVER'];
		$isLimited			=	$totalDayOffInDate >= $maxDayOffNumber;
		
		return array(
			"isLimited"			=>	$isLimited,
			"maxDayOffNumber"	=>	$maxDayOffNumber
		);
		
	}
	
	public function dayOffRequestList(){

		$this->load->model('MainOperation');
		$this->load->model('DayOff/ModelDriver');

		$idDayOffRequest=	validatePostVar($this->postVar, 'idDayOffRequest', false);
		$yearMonth		=	validatePostVar($this->postVar, 'yearMonth', true);
		$detailPartner	=	$this->MainOperation->getDetailPartner($this->newToken);
		$idDriver		=	$detailPartner['IDPARTNER'];
		$requestList	=	$this->ModelDriver->getDataDayOffRequestList($idDriver, $yearMonth, $idDayOffRequest);

		setResponseOk(array("token"=>$this->newToken, "requestList"=>$requestList));
		
	}
	
	public function submitAvailable(){

		$this->load->model('MainOperation');

		$date			=	validatePostVar($this->postVar, 'date', true);
		$detailPartner	=	$this->MainOperation->getDetailPartner($this->newToken);
		$idDriver		=	$detailPartner['IDPARTNER'];
		$partnershipType=	$detailPartner['PARTNERSHIPTYPE'];
		$dateOff		=	date_create($date);
		$dateNow		=	date_create();
		$dateDiff		=	date_diff($dateNow, $dateOff);
		$dayDiff		=	$dateDiff->format("%a");
		
		if($partnershipType != 2){
			setResponseForbidden(
				array(
					"token"	=>	$this->newToken,
					"msg"	=>	"Availability settings are only available for freelance drivers"
				)
			);
		}
		
		if($dayDiff > 7){
			setResponseForbidden(
				array(
					"token"	=>	$this->newToken,
					"msg"	=>	"Availability settings are only allowed for a period of 7 days after today"
				)
			);
		}
		
		$arrDelete		=	array(
								"IDDRIVER"		=>	$idDriver,
								"DATEDAYOFF"	=>	$date
							);
		$procInsert		=	$this->MainOperation->deleteData("t_dayoff", $arrDelete);
		
		if(!$procInsert['status']){
			switchMySQLErrorCode($procInsert['errCode'], $this->newToken);
		}

		setResponseOk(array("token"=>$this->newToken, "msg"=>"Your availability on the selected date has been saved"));
		
	}
	
}