<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;

class Finance extends CI_controller {
	
	var $postVar;
	var $imei;
	var $token;
	var $newToken;
	var $detailPartner;
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
		$this->detailPartner=	$detailPartner;
		$this->idUserMobile	=	$detailPartner['IDUSERMOBILE'];
		$this->idPartnerType=	$detailPartner['IDPARTNERTYPE'];
		$this->idPartner	=	$detailPartner['IDPARTNER'];
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function summaryFinance(){
		$this->load->model('MainOperation');
		$this->load->model('ModelFinance');

		$lastDatePeriod					=	validatePostVar($this->postVar, 'lastDatePeriod', true);
		$partnerDetail					=	$this->idPartnerType == 1 ? $this->MainOperation->getDetailVendor($this->idPartner) : $this->MainOperation->getDetailDriver($this->idPartner);
		$newFinanceSchemeDate			=	substr($partnerDetail['NEWFINANCESCHEMESTART'], 0, 10);
		$totalFee						=	$this->ModelFinance->getTotalFee($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$totalAdditionalCost			=	$this->ModelFinance->getTotalAdditionalCost($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$totalReimbursement				=	$this->ModelFinance->getTotalReimbursement($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$totalReviewBonusPunishment		=	$this->ModelFinance->getTotalReviewBonusPunishment($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$totalCollectPayment			=	$this->ModelFinance->getTotalCollectPayment($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$totalPrepaidCapital			=	$this->ModelFinance->getDataLoanPrepaidCapital($this->idPartnerType, $this->idPartner, 2);
		$totalLoanCar					=	$this->ModelFinance->getDataLoanPrepaidCapital($this->idPartnerType, $this->idPartner, 1, 1);
		$totalLoanPersonal				=	$this->ModelFinance->getDataLoanPrepaidCapital($this->idPartnerType, $this->idPartner, 1, 2);
		$totalUnfinishedSchedule		=	$this->ModelFinance->getTotalUnfinishedSchedule($this->idPartnerType, $this->idPartner, $newFinanceSchemeDate, $lastDatePeriod);
		$totalPendingAdditionalCost		=	$this->ModelFinance->getTotalPendingAdditionalCost($this->idPartner, $lastDatePeriod);
		$totalUnconfirmedCollectPayment	=	$this->ModelFinance->getTotalUnconfirmedCollectPayment($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$installmentLoanCar				=	$totalLoanCar > 0 ? $this->ModelFinance->getInstallmentLoan($this->idPartnerType, $this->idPartner, 1, $lastDatePeriod) : 0;
		$installmentLoanPersonal		=	$totalLoanPersonal > 0 ? $this->ModelFinance->getInstallmentLoan($this->idPartnerType, $this->idPartner, 2, $lastDatePeriod) : 0;
		$dataActiveWithdrawal			=	$this->ModelFinance->getDataActiveWithdrawal($this->idPartnerType, $this->idPartner);
		$dataBankAccount				=	$this->MainOperation->getDataActiveBankAccount($this->idPartnerType, $this->idPartner);
		$secretPINStatus				=	$this->MainOperation->getStatusSecretPINPartner($this->idPartnerType, $this->idPartner) * 1;
		$totalWithdrawal				=	$totalFee + $totalAdditionalCost + $totalReimbursement + $totalReviewBonusPunishment - $totalCollectPayment - $totalPrepaidCapital;
		$isAdditionalIncomePaid			=	$this->ModelFinance->isAdditionalIncomePaid($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$statusWithdrawal				=	true;
		$msgStatusWithdrawal			=	"";

		if($dataBankAccount == false){
			$statusWithdrawal	=	false;
			$msgStatusWithdrawal=	"Withdrawal is not allowed. Please set up your bank account first in the profile section";
		}
		
		if($secretPINStatus != 2){
			$statusWithdrawal	=	false;
			$msgStatusWithdrawal=	"Withdrawal is not allowed. Please set your secret PIN first in the profile section";
		}
		
		if($totalWithdrawal < 0){
			$statusWithdrawal	=	false;
			$msgStatusWithdrawal=	"Withdrawal is not allowed. Your balance is insufficient to withdraw";
		}
		
		if($totalUnconfirmedCollectPayment > 0){
			$statusWithdrawal	=	false;
			$msgStatusWithdrawal=	"Withdrawal is not allowed. Please confirm all collect payments that you have";
		}

		if($totalPendingAdditionalCost > 0){
			$statusWithdrawal	=	false;
			$msgStatusWithdrawal=	"Withdrawal is not allowed. You still have pending additional costs. Please wait until the additional cost is approved first";
		}

		if($totalUnfinishedSchedule > 0){
			$statusWithdrawal	=	false;
			$lastDatePeriodDT	=	new DateTime($lastDatePeriod);
			$lastDatePeriodStr	=	$lastDatePeriodDT->format("d F Y");
			$msgStatusWithdrawal=	"Withdrawal is not allowed. Please update all your work (until ".$lastDatePeriodStr.") schedules to finish";
		}
		
		if($dataActiveWithdrawal != false){
			$statusWithdrawal	=	false;
			$msgStatusWithdrawal=	"Withdrawal is not allowed. Please wait for the previous withdrawal process to complete";
		}			
			
		setResponseOk(
			array(
				"token"						=>	$this->newToken,
				"totalFee"					=>	$totalFee,
				"totalAdditionalCost"		=>	$totalAdditionalCost,
				"totalReimbursement"		=>	$totalReimbursement,
				"totalReviewBonusPunishment"=>	$totalReviewBonusPunishment,
				"totalPrepaidCapital"		=>	$totalPrepaidCapital,
				"totalCollectPayment"		=>	$totalCollectPayment,
				"totalLoanCar"				=>	$totalLoanCar,
				"totalLoanPersonal"			=>	$totalLoanPersonal,
				"installmentLoanCar"		=>	$installmentLoanCar,
				"installmentLoanPersonal"	=>	$installmentLoanPersonal,
				"totalWithdrawal"			=>	$totalWithdrawal,
				"dataBankAccount"			=>	$dataBankAccount,
				"statusWithdrawal"			=>	$statusWithdrawal,
				"msgStatusWithdrawal"		=>	$msgStatusWithdrawal,
				"isAdditionalIncomePaid"	=>	$isAdditionalIncomePaid,
				"additionalIncomeMinimum"	=>	MIN_ADDITIONAL_INCOME_NOMINAL,
				"dataActiveWithdrawal"		=>	$dataActiveWithdrawal
			)
		);
	}
	
	public function listDetailWithdrawal(){
		$this->load->model('ModelFinance');

		$idVendor				=	$this->idPartnerType == 1 ? $this->idPartner : 0;
		$idDriver				=	$this->idPartnerType == 2 ? $this->idPartner : 0;
		$lastDatePeriod			=	validatePostVar($this->postVar, 'lastDatePeriod', false);
		$lastDatePeriod			=	$lastDatePeriod == "" ? false : $lastDatePeriod;
		$dataActiveWithdrawal	=	$this->ModelFinance->getDataActiveWithdrawal($this->idPartnerType, $this->idPartner);
		$idWithdrawalRecap		=	$dataActiveWithdrawal != false ? $dataActiveWithdrawal['IDWITHDRAWALRECAP'] : 0;
		$listDetailWithdrawal	=	$this->ModelFinance->getListDetailWithdrawal($idVendor, $idDriver, $idWithdrawalRecap, $lastDatePeriod);
		
		if(!$listDetailWithdrawal) setResponseNotFound(array("token"=>$this->newToken, "msg"=>"Detail list of withdrawal not found"));

		setResponseOk(
			array(
				"token"					=>	$this->newToken,
				"listDetailWithdrawal"	=>	$listDetailWithdrawal
			)
		);
	}
	
	public function submitWithdrawalRequest(){
		$this->load->model('MainOperation');
		$this->load->model('ModelFinance');

		$charityNominal			=	validatePostVar($this->postVar, 'charityNominal', false) * 1;
		$loanCarInstallment		=	validatePostVar($this->postVar, 'loanCarInstallment', false) * 1;
		$loanPersonalInstallment=	validatePostVar($this->postVar, 'loanPersonalInstallment', false) * 1;
		$totalAdditionalIncome	=	validatePostVar($this->postVar, 'totalAdditionalIncome', false) * 1;
		$exceptionReasonAI		=	validatePostVar($this->postVar, 'exceptionReasonAI', false);
		$message				=	validatePostVar($this->postVar, 'message', false);
		$secretPIN				=	validatePostVar($this->postVar, 'secretPIN', true);
		$lastDatePeriod			=	validatePostVar($this->postVar, 'lastDatePeriod', true);
		
		checkSecretPIN($this->newToken, $this->idPartnerType, $this->idPartner, $secretPIN);		
		
		$idVendor							=	$this->idPartnerType == 1 ? $this->idPartner : 0;
		$idDriver							=	$this->idPartnerType == 2 ? $this->idPartner : 0;
		$isAdditionalIncomePaid				=	$this->ModelFinance->isAdditionalIncomePaid($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$totalFee							=	$this->ModelFinance->getTotalFee($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$totalAdditionalCost				=	$this->ModelFinance->getTotalAdditionalCost($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$totalReimbursement					=	$this->ModelFinance->getTotalReimbursement($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$totalReviewBonusPunishment			=	$this->ModelFinance->getTotalReviewBonusPunishment($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$totalCollectPayment				=	$this->ModelFinance->getTotalCollectPayment($this->idPartnerType, $this->idPartner, $lastDatePeriod);
		$totalPrepaidCapital				=	$this->ModelFinance->getDataLoanPrepaidCapital($this->idPartnerType, $this->idPartner, 2);
		$totalLoanCar						=	$this->ModelFinance->getDataLoanPrepaidCapital($this->idPartnerType, $this->idPartner, 1, 1);
		$totalLoanPersonal					=	$this->ModelFinance->getDataLoanPrepaidCapital($this->idPartnerType, $this->idPartner, 1, 2);
		$thisMonthInstallmentLoanCar		=	$totalLoanCar > 0 ? $this->ModelFinance->getInstallmentLoan($this->idPartnerType, $this->idPartner, 1, $lastDatePeriod) : 0;
		$thisMonthInstallmentLoanPersonal	=	$totalLoanPersonal > 0 ? $this->ModelFinance->getInstallmentLoan($this->idPartnerType, $this->idPartner, 2, $lastDatePeriod) : 0;
		$totalWithdrawal					=	$totalFee + $totalAdditionalCost + $totalReimbursement + $totalReviewBonusPunishment - $totalAdditionalIncome  - $totalCollectPayment - $totalPrepaidCapital - $loanCarInstallment - $loanPersonalInstallment - $charityNominal;
		
		if($totalWithdrawal < 0){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "Your withdrawal balance is less than or equal to zero"));
		}
		
		if($charityNominal < MIN_CHARITY_NOMINAL){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "Please enter a minimum charity amount of IDR ".number_format(MIN_CHARITY_NOMINAL, 0, '.', ',')));
		}
		
		if($loanCarInstallment > $totalLoanCar){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "Car loan installment payments should not be more than IDR ".number_format($totalLoanCar, 0, '.', ',')));
		}
		
		if($loanPersonalInstallment > $totalLoanPersonal){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "Personal loan installment payments should not be more than IDR ".number_format($totalLoanPersonal, 0, '.', ',')));
		}
		
		$dataPartnerBankAccount	=	$this->MainOperation->getDataActiveBankAccount($this->idPartnerType, $this->idPartner);

		if(!$dataPartnerBankAccount){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "Your withdrawal request cannot be processed. Please set your bank account data first"));
		}
		
		if($loanCarInstallment < $thisMonthInstallmentLoanCar){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "Car loan installment payments are mandatory, with a minimum of IDR ".number_format($thisMonthInstallmentLoanCar, 0, '.', ',')));
		}
		
		if($loanPersonalInstallment < $thisMonthInstallmentLoanPersonal){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "Personal loan installment payments are mandatory, with a minimum of IDR ".number_format($thisMonthInstallmentLoanPersonal, 0, '.', ',')));
		}
		
		if(!$isAdditionalIncomePaid && $totalAdditionalIncome <= 0){
			$exceptionReasonAICheck		=	str_replace(' ', '', $exceptionReasonAI);
			$exceptionReasonAICheckLen	=	strlen($exceptionReasonAICheck);
			
			if($exceptionReasonAICheckLen < 8){
				setResponseForbidden(array("token" => $this->newToken, "msg" => "Please insert a valid reason for Additional Income"));
			}
			
			$message	.=	". Additional income exception reason : ".$exceptionReasonAI;
		}
		
		$idBankPartner				=	$dataPartnerBankAccount['IDBANK'];
		$accountNumberPartner		=	$dataPartnerBankAccount['ACCOUNTNUMBER'];
		$accountHolderNamePartner	=	$dataPartnerBankAccount['ACCOUNTHOLDERNAME'];
		$arrInsertWithdrawal		=	array(
			"IDVENDOR"						=>	$idVendor,
			"IDDRIVER"						=>	$idDriver,
			"IDBANK"						=>	$idBankPartner,
			"TOTALFEE"						=>	$totalFee,
			"TOTALADDITIONALCOST"			=>	$totalAdditionalCost,
			"TOTALADDITIONALINCOME"			=>	$totalAdditionalIncome,
			"TOTALREIMBURSEMENT"			=>	$totalReimbursement,
			"TOTALREVIEWBONUSPUNISHMENT"	=>	$totalReviewBonusPunishment,
			"TOTALCOLLECTPAYMENT"			=>	$totalCollectPayment,
			"TOTALPREPAIDCAPITAL"			=>	$totalPrepaidCapital,
			"TOTALLOANCARINSTALLMENT"		=>	$loanCarInstallment,
			"TOTALLOANPERSONALINSTALLMENT"	=>	$loanPersonalInstallment,
			"TOTALCHARITY"					=>	$charityNominal,
			"TOTALWITHDRAWAL"				=>	$totalWithdrawal,
			"MESSAGE"						=>	$message,
			"ACCOUNTNUMBER"					=>	$accountNumberPartner,
			"ACCOUNTHOLDERNAME"				=>	$accountHolderNamePartner,
			"DATELASTPERIOD"				=>	$lastDatePeriod,
			"DATETIMEREQUEST"				=>	date('Y-m-d H:i:s')
		);
		$procInsertWithdrawal		=	$this->MainOperation->addData('t_withdrawalrecap', $arrInsertWithdrawal);
		
		if(!$procInsertWithdrawal['status']){
			switchMySQLErrorCode($procInsertWithdrawal['errCode'], $this->newToken);
		}
		
		$idWithdrawalRecap					=	$procInsertWithdrawal['lastID'];
		$dataFeeWithdrawal					=	$this->ModelFinance->getDataFeeWithdrawal($idVendor, $idDriver, $lastDatePeriod);
		$dataAdditionalCostWithdrawal		=	$this->ModelFinance->getDataAdditionalCostWithdrawal($idDriver, $lastDatePeriod);
		$dataReimbursementWithdrawal		=	$this->ModelFinance->getDataReimbursementWithdrawal($idDriver, $idVendor, $lastDatePeriod);
		$dataReviewBonusPunishmentWithdrawal=	$this->ModelFinance->getDataReviewBonusPunishmentWithdrawal($idDriver, $lastDatePeriod);
		$dataCollectPaymentWithdrawal		=	$this->ModelFinance->getDataCollectPaymentWithdrawal($idVendor, $idDriver, $lastDatePeriod);
		
		//fee
		if($dataFeeWithdrawal){
			foreach($dataFeeWithdrawal as $keyFeeWithdrawal){
				$idFee	=	$keyFeeWithdrawal->IDFEE;
				$this->MainOperation->updateData("t_fee", array("IDWITHDRAWALRECAP" => $idWithdrawalRecap), "IDFEE", $idFee);
			}
		}
		
		//additional cost
		if($dataAdditionalCostWithdrawal){
			foreach($dataAdditionalCostWithdrawal as $keyAdditionalCostWithdrawal){
				$idAdditionalCost	=	$keyAdditionalCostWithdrawal->IDRESERVATIONADDITIONALCOST;
				$this->MainOperation->updateData("t_reservationadditionalcost", array("IDWITHDRAWALRECAP" => $idWithdrawalRecap), "IDRESERVATIONADDITIONALCOST", $idAdditionalCost);
			}
		}
		
		//reimbursement
		if($dataReimbursementWithdrawal){
			foreach($dataReimbursementWithdrawal as $keyReimbursementWithdrawal){
				$idReimbursement	=	$keyReimbursementWithdrawal->IDREIMBURSEMENT;
				$this->MainOperation->updateData("t_reimbursement", array("IDWITHDRAWALRECAP" => $idWithdrawalRecap), "IDREIMBURSEMENT", $idReimbursement);
			}
		}
		
		//review bonus punishment
		if($dataReviewBonusPunishmentWithdrawal){
			foreach($dataReviewBonusPunishmentWithdrawal as $keyReviewBonusPunishmentWithdrawal){
				$idReviewBonusPunishment	=	$keyReviewBonusPunishmentWithdrawal->IDDRIVERREVIEWBONUS;
				$this->MainOperation->updateData("t_driverreviewbonus", array("IDWITHDRAWALRECAP" => $idWithdrawalRecap), "IDDRIVERREVIEWBONUS", $idReviewBonusPunishment);
			}
		}
		
		//collect payment
		if($dataCollectPaymentWithdrawal){
			foreach($dataCollectPaymentWithdrawal as $keyCollectPaymentWithdrawal){
				$idCollectPayment	=	$keyCollectPaymentWithdrawal->IDCOLLECTPAYMENT;
				$this->MainOperation->updateData("t_collectpayment", array("IDWITHDRAWALRECAP" => $idWithdrawalRecap), "IDCOLLECTPAYMENT", $idCollectPayment);
			}
		}
		
		//prepaid capital
		if($idDriver != 0){
			$dataPrepaidCapital	=	$this->ModelFinance->getDataPrepaidCapitalDriver($idDriver);
			if($dataPrepaidCapital){
				foreach($dataPrepaidCapital as $keyPrepaidCapital){
					$this->MainOperation->updateData("t_loandriverrecord", array("IDWITHDRAWALRECAP" => $idWithdrawalRecap), "IDLOANDRIVERRECORD", $keyPrepaidCapital->IDLOANDRIVERRECORD);
				}
			}			
		}
		
		if(PRODUCTION_URL){
			$partnerName			=	$this->detailPartner['PARTNERNAME'];
            $rtdbTableName          =   $this->idPartnerType == 1 ? 'Vendor' : 'Driver';
			$totalWithdrawalRequest	=	$this->ModelFinance->getTotalWithdrawalRequest($this->idPartnerType);
			$factory				=	(new Factory)
										->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)
										->withDatabaseUri(FIREBASE_RTDB_URI);
			$database				=	$factory->createDatabase();
			$reference				=	$database->getReference(FIREBASE_RTDB_MAINREF_NAME."unprocessedFinance".$rtdbTableName."/withdrawalRequest")
										->set([
											'newWithdrawalRequestStatus'	=>	true,
											'newWithdrawalRequestTotal'		=>	$totalWithdrawalRequest,
											'newWithdrawalRequestMessage'	=>	"New withdrawal request from ".$partnerName." - ".number_format($totalWithdrawal, 0, '.', ',')." IDR.<br/>Message : ".$message,
											'timestampUpdate'				=>	gmdate("YmdHis")
										]);
		}
			
		setResponseOk(
			array(
				"token"		=>	$this->newToken,
				"idDriver"	=>	$idDriver,
				"msg"		=>	"Your withdrawal request has been processed. Please wait for the approval process"
			)
		);
	}
	
	public function updateAdditionalIncomeExceptionReason($idDriver, $lastDatePeriod, $exceptionReasonAI){
		$this->load->model('MainOperation');
		$this->load->model('ModelFinance');
		$yearMonth	=	substr($lastDatePeriod, 7);
		
		$arrUpdateAIRecap	=	[
			"EXCEPTIONREASON"	=>	$lastDatePeriod
		];
	}
	
	public function withdrawalHistory(){
		$this->load->model('ModelFinance');

		$year					=	validatePostVar($this->postVar, 'year', true);
		$month					=	str_pad(validatePostVar($this->postVar, 'month', true), 2, "0", STR_PAD_LEFT);
		$yearMonth				=	$year."-".$month;
		$idVendor				=	$this->idPartnerType == 1 ? $this->idPartner : 0;
		$idDriver				=	$this->idPartnerType == 2 ? $this->idPartner : 0;
		$listWithdrawalHistory	=	$this->ModelFinance->getListWithdrawalHistory($idVendor, $idDriver, $yearMonth);
		
		if(!$listWithdrawalHistory) setResponseNotFound(array("token"=>$this->newToken, "msg"=>"No withdrawal data found"));
		setResponseOk(
			array(
				"token"					=>	$this->newToken,
				"listWithdrawalHistory"	=>	$listWithdrawalHistory
			)
		);
	}
	
	public function detailWithdrawalHistory(){
		$this->load->model('ModelFinance');

		$idVendor				=	$this->idPartnerType == 1 ? $this->idPartner : 0;
		$idDriver				=	$this->idPartnerType == 2 ? $this->idPartner : 0;
		$idWithdrawalRecap		=	validatePostVar($this->postVar, 'idWithdrawalRecap', true);
		$detailWithdrawalRecap	=	$this->ModelFinance->getDetailWithdrawalRecap($idWithdrawalRecap);
		$listDetailWithdrawal	=	$this->ModelFinance->getListDetailWithdrawal($idVendor, $idDriver, $idWithdrawalRecap, false);
		
		if(!$detailWithdrawalRecap) setResponseNotFound(array("token"=>$this->newToken, "msg"=>"Detail withdrawal not found"));

		setResponseOk(
			array(
				"token"					=>	$this->newToken,
				"detailWithdrawalRecap"	=>	$detailWithdrawalRecap,
				"listDetailWithdrawal"	=>	$listDetailWithdrawal
			)
		);
	}
	
	public function summaryFinanceDeposit(){
		$this->load->model('ModelFinance');
		$dateStart						=	validatePostVar($this->postVar, 'dateStart', false);
		$dateEnd						=	validatePostVar($this->postVar, 'dateEnd', false);
		
		$depositBalanceData				=	$this->ModelFinance->getDataDepositBalanceVendor($this->idPartner);
		$depositBalanceNominal			=	$depositBalanceData['DEPOSITBALANCE'];
		$depositBalanceLastTransaction	=	$depositBalanceData['LASTDEPOSITTRANSACTION'];
		$dataFeeThisMonth				=	$this->ModelFinance->getDataFeeThisMonthDepositSchemeVendor($this->idPartner);
		$totalFeeThisMonthNominal		=	$dataFeeThisMonth['TOTALFEETHISMONTHNOMINAL'];
		$totalFeeThisMonthJob			=	$dataFeeThisMonth['TOTALFEETHISMONTHJOB'];
		$dataDepositRecordHistory		=	$this->ModelFinance->getDataDepositRecordHistory($this->idPartner, $dateStart, $dateEnd);
	
		setResponseOk(
			array(
				"token"							=>	$this->newToken,
				"depositBalanceNominal"			=>	$depositBalanceNominal,
				"depositBalanceLastTransaction"	=>	$depositBalanceLastTransaction,
				"totalFeeThisMonthNominal"		=>	$totalFeeThisMonthNominal,
				"totalFeeThisMonthJob"			=>	$totalFeeThisMonthJob,
				"dataDepositRecordHistory"		=>	$dataDepositRecordHistory
			)
		);
	}
	
	public function depositRecordHistory(){
		$this->load->model('ModelFinance');
		$dateStart					=	validatePostVar($this->postVar, 'dateStart', false);
		$dateEnd					=	validatePostVar($this->postVar, 'dateEnd', false);
		$dataDepositRecordHistory	=	$this->ModelFinance->getDataDepositRecordHistory($this->idPartner, $dateStart, $dateEnd);
		
		setResponseOk(
			array(
				"token"						=>	$this->newToken,
				"dataDepositRecordHistory"	=>	$dataDepositRecordHistory
			)
		);
	}
	
}