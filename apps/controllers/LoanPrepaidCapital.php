<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;

class LoanPrepaidCapital extends CI_controller {
	
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
		
		if(isset($_FILES) && count($_FILES) > 0){
			$this->imei		=	isset($_POST['imei']) ? $_POST['imei'] : setResponseBadRequest(array());
			$this->fcmtoken	=	isset($_POST['fcmtoken']) ? $_POST['fcmtoken'] : setResponseBadRequest(array());
			$this->token	=	isset($_POST['token']) ? $_POST['token'] : setResponseBadRequest(array());
			$this->email	=	isset($_POST['email']) ? $_POST['email'] : "";
		} else {				
			$this->postVar	=	decodeJsonPost();
			$this->imei		=	validatePostVar($this->postVar, 'imei', true);
			$this->fcmtoken	=	validatePostVar($this->postVar, 'fcmtoken', true);
			$this->token	=	validatePostVar($this->postVar, 'token', false);
			$this->email	=	isset($_POST['email']) ? $_POST['email'] : "";
		}
		$this->newToken	=	accessCheck($this->fcmtoken, $this->email, $this->imei, $this->token, true);
		
		$detailPartner		=	$this->MainOperation->getDetailPartner($this->newToken);
		$this->detailPartner=	$detailPartner;
		$this->idUserMobile	=	$detailPartner['IDUSERMOBILE'];
		$this->idPartnerType=	$detailPartner['IDPARTNERTYPE'];
		$this->idPartner	=	$detailPartner['IDPARTNER'];

		if($this->idPartnerType != 2){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "You are not allowed to access this feature"));
		}		

    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function summaryListHistory(){
		$this->load->model('ModelLoanPrepaidCapital');
		
		$dataSummary				=	$this->ModelLoanPrepaidCapital->getDataSummaryLoanPrepaidCapital($this->idPartner);
		$dataHistoryLoan			=	$this->ModelLoanPrepaidCapital->getHistoryLoanPrepaidCapital($this->idPartner, 1);
		$dataHistoryPrepaidCapital	=	$this->ModelLoanPrepaidCapital->getHistoryLoanPrepaidCapital($this->idPartner, 2);
		$dataSaldoLoanPerType		=	$this->ModelLoanPrepaidCapital->getNominalSaldoLoanPerType($this->idPartner);
		
		if($dataHistoryLoan){
			$dataHistoryLoan	=	array_reverse($dataHistoryLoan);
			$currentBalance		=	0;
			foreach($dataHistoryLoan as $keyHistoryLoan){
				$transactionType		=	$keyHistoryLoan->TYPE;
				$amount					=	$keyHistoryLoan->AMOUNT;
				$currentBalance			=	$transactionType == "K" ? $currentBalance - $amount : $currentBalance + $amount;
				$keyHistoryLoan->SALDO	=	$currentBalance;
			}
		}
		
		if($dataHistoryPrepaidCapital){
			$dataHistoryPrepaidCapital	=	array_reverse($dataHistoryPrepaidCapital);
			$currentBalance				=	0;
			foreach($dataHistoryPrepaidCapital as $keyHistoryPrepaidCapital){
				$transactionType				=	$keyHistoryPrepaidCapital->TYPE;
				$amount							=	$keyHistoryPrepaidCapital->AMOUNT;
				$currentBalance					=	$transactionType == "K" ? $currentBalance - $amount : $currentBalance + $amount;
				$keyHistoryPrepaidCapital->SALDO=	$currentBalance;
			}
		}
		
		setResponseOk(
			array(
				"token"						=>	$this->newToken,
				"dataSummary"				=>	$dataSummary,
				"dataHistoryLoan"			=>	$dataHistoryLoan,
				"dataHistoryPrepaidCapital"	=>	$dataHistoryPrepaidCapital,
				"dataSaldoLoanPerType"		=>	$dataSaldoLoanPerType
			)
		);
	}
	
	public function requestList(){

		$this->load->model('MainOperation');
		$this->load->model('ModelLoanPrepaidCapital');

		$activeRequest			=	$this->ModelLoanPrepaidCapital->getDataLoanPrepaidCapitalRequest($this->idPartner, "0,1");
		$requestHistory			=	$this->ModelLoanPrepaidCapital->getDataLoanPrepaidCapitalRequest($this->idPartner, "2,-1,-2");
		$dataBankAccount		=	$this->MainOperation->getDataActiveBankAccount($this->idPartnerType, $this->idPartner);
		$allowNewRequest		=	true;
		$listStatusLoanCapital	=	array(1,2);
		
		if($activeRequest){
			foreach($activeRequest as $keyRequest){
				$statusLoanCapital	=	$keyRequest->STATUSLOANCAPITAL;
				if(($key 	= array_search($statusLoanCapital, $listStatusLoanCapital)) !== false) {
					unset($listStatusLoanCapital[$key]);
				}
			}
			
			if(count($listStatusLoanCapital) <= 0) $allowNewRequest=	false;
		}
		
		setResponseOk(
			array(
				"token"				=>	$this->newToken,
				"activeRequest"		=>	$activeRequest,
				"requestHistory"	=>	$requestHistory,
				"allowNewRequest"	=>	$allowNewRequest,
				"dataBankAccount"	=>	$dataBankAccount
			)
		);
		
	}
	
	public function createRequest(){
		$this->load->model('ModelLoanPrepaidCapital');
		$secretPIN				=	validatePostVar($this->postVar, 'secretPIN', true);
		$idLoanType				=	validatePostVar($this->postVar, 'idLoanType', true);
		$idBankAccountPartner	=	validatePostVar($this->postVar, 'idBankAccountPartner', true);
		$notes					=	validatePostVar($this->postVar, 'notes', true);
		$amount					=	preg_replace("/[^0-9]/", "", validatePostVar($this->postVar, 'amount', true));
		
		checkSecretPIN($this->newToken, $this->idPartnerType, $this->idPartner, $secretPIN);		
		
		$detailLoanType			=	$this->MainOperation->getDetailLoanType($idLoanType);
		$loanPrepaidCapitalType	=	$detailLoanType['STATUSLOANCAPITAL'];
		$loanPrepaidCapitalStr	=	$detailLoanType['LOANTYPE'];
		$isActiveRequestExist	=	$this->ModelLoanPrepaidCapital->checkActiveRequestExist($this->idPartner, $loanPrepaidCapitalType);
		
		if($isActiveRequestExist) setResponseForbidden(array("token" => $this->newToken, "msg" => "You are not allowed to apply for a new loan.\n\nThere are still active request for this type of loan : ".$loanPrepaidCapitalStr));

		$isBankAccountValid		=	$this->ModelLoanPrepaidCapital->checkBankAccountValid($this->idPartnerType, $this->idPartner, $idBankAccountPartner);
		if(!$isBankAccountValid) setResponseForbidden(array("token" => $this->newToken, "msg" => "The bank account you are using is invalid"));

		$arrInsert				=	array(
			"IDDRIVER"				=>	$this->idPartner,
			"IDBANKACCOUNTPARTNER"	=>	$idBankAccountPartner,
			"IDLOANTYPE"			=>	$idLoanType,
			"NOTES"					=>	$notes,
			"AMOUNT"				=>	$amount,
			"STATUS"				=>	0,
			"DATETIMEINPUT"			=>	date('Y-m-d H:i:s')
		);
		$procInsert				=	$this->MainOperation->addData("t_loandriverrequest", $arrInsert);

		if(!$procInsert['status']) switchMySQLErrorCode($procInsert['errCode'], $this->newToken);
		
		if(PRODUCTION_URL){
			$detailLoanType					=	$this->MainOperation->getDetailLoanType($idLoanType);
			$strLoanType					=	$detailLoanType['LOANTYPE'];
			$partnerName					=	$this->detailPartner['PARTNERNAME'];
			$totalLoanPrepaidCapitalRequest	=	$this->ModelLoanPrepaidCapital->getTotalLoanPrepaidCapitalRequest();
			$totalLoanInstallmentRequest	=	$this->ModelLoanPrepaidCapital->getTotalLoanInstallmentRequest();
			$totalAllRequest				=	$totalLoanPrepaidCapitalRequest + $totalLoanInstallmentRequest;
			$factory						=	(new Factory)
												->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)
												->withDatabaseUri(FIREBASE_RTDB_URI);
			$database						=	$factory->createDatabase();
			$reference						=	$database->getReference(FIREBASE_RTDB_MAINREF_NAME."unprocessedFinanceDriver/loanPrepaidCapital")
												->set([
													'newLoanPrepaidCapitalStatus'	=>	true,
													'newLoanPrepaidCapitalTotal'	=>	$totalAllRequest,
													'newLoanPrepaidCapitalMessage'	=>	"New loan request (".$strLoanType.") from ".$partnerName." - ".number_format($amount, 0, '.', ',')." IDR.<br/>Notes : ".$notes,
													'timestampUpdate'				=>	gmdate("YmdHis")
												]);
		}
		
		setResponseOk(
			array(
				"token"	=>	$this->newToken,
				"msg"	=>	"Your loan request is being processed"
			)
		);
	}
	
	public function detailRequestApproval(){
		$this->load->model('ModelLoanPrepaidCapital');

		$idLoanDriverRequest	=	validatePostVar($this->postVar, 'idLoanDriverRequest', true);
		$detailRequestApproval	=	$this->ModelLoanPrepaidCapital->getDetailRequestApproval($this->idPartner, $idLoanDriverRequest);
		
		if(!$detailRequestApproval) setResponseNotFound(array("token" => $this->newToken, "msg" => "Details not found. Please try again later"));
		setResponseOk(
			array(
				"token"					=>	$this->newToken,
				"detailRequestApproval"	=>	$detailRequestApproval
			)
		);
	}
	
	public function confirmReceiptFunds(){

		$this->load->model('ModelLoanPrepaidCapital');

		setResponseForbidden(array("token" => $this->newToken, "msg" => "This action is not available at this time"));
		
		$idLoanDriverRequest	=	validatePostVar($this->postVar, 'idLoanDriverRequest', true);
		$detailRequestApproval	=	$this->ModelLoanPrepaidCapital->getDetailRequestApproval($this->idPartner, $idLoanDriverRequest);
		
		if(!$detailRequestApproval) setResponseNotFound(array("token" => $this->newToken, "msg" => "Details not found. Please try again later"));

		$statusLoanRequest		=	$detailRequestApproval['STATUS'];
		$userConfirmLoanRequest	=	$detailRequestApproval['USERCONFIRM'];
		$dateConfirmLoanRequest	=	$detailRequestApproval['DATETIMECONFIRM'];
		
		if($statusLoanRequest != 1) setResponseForbidden(array("token" => $this->newToken, "msg" => "You can't confirm before the loan is approved and the funds have been transferred"));
		if($userConfirmLoanRequest != "" && $userConfirmLoanRequest != "-") setResponseForbidden(array("token" => $this->newToken, "msg" => "Loan confirmation has been made on ".$dateConfirmLoanRequest));
		
		$arrUpdateRequest	=	array(
			"STATUS"			=>	2,
			"DATETIMECONFIRM"	=>	date('Y-m-d H:i:s'),
			"USERCONFIRM"		=>	'Driver'
		);
		$procUpdateRquest	=	$this->MainOperation->updateData("t_loandriverrequest", $arrUpdateRequest, "IDLOANDRIVERREQUEST", $idLoanDriverRequest);
		
		if($procUpdateRquest['status']){
			$strLoanType		=	$detailRequestApproval['LOANTYPE'];
			$arrInsertRecord	=	array(
				"IDDRIVER"		=>	$this->idPartner,
				"IDLOANTYPE"	=>	$detailRequestApproval['IDLOANTYPE'],
				"TYPE"			=>	'D',
				"DESCRIPTION"	=>	"Fund for ".$strLoanType." (".$detailRequestApproval['NOTES']."). Transferred to ".$detailRequestApproval['BANKNAME']." - ".$detailRequestApproval['ACCOUNTNUMBER']." - ".$detailRequestApproval['ACCOUNTHOLDERNAME'].". Input by : ".$detailRequestApproval['USERUPDATE'],
				"AMOUNT"		=>	$detailRequestApproval['AMOUNT'],
				"DATETIMEINPUT"	=>	date('Y-m-d H:i:s'),
				"USERINPUT"		=>	$detailRequestApproval['USERUPDATE']
			);
			$this->MainOperation->addData("t_loandriverrecord", $arrInsertRecord);
		}
		
		setResponseOk(
			array(
				"token"	=>	$this->newToken,
				"msg"	=>	"Confirmation of receipt of loan funds has been received"
			)
		);
	}
	
	public function uploadImageInstallmentLoan(){

		if((($_FILES["uploaded_file"]["type"] == "image/gif")
			|| ($_FILES["uploaded_file"]["type"] == "image/jpeg")
			|| ($_FILES["uploaded_file"]["type"] == "image/jpg")
			|| ($_FILES["uploaded_file"]["type"] == "image/pjpeg")
			|| ($_FILES["uploaded_file"]["type"] == "text/xml")
			|| ($_FILES["uploaded_file"]["type"] == "application/octet-stream"))
			&& ($_FILES["uploaded_file"]["size"] < 20000000000)){
			if ($_FILES["uploaded_file"]["error"] > 0) {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. Files corrupted"));
			}
		} else {
			setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. File types not allowed (".$_FILES["uploaded_file"]["type"].") or the size is too large (".$_FILES["uploaded_file"]["size"].")"));
		}
				
		$filename		=	str_replace(" ", "_", $_FILES["uploaded_file"]["name"]);
		$dir			=	PATH_TRANSFER_RECEIPT;
		
		if(!file_exists($dir.$filename)){
			$move		=	move_uploaded_file($_FILES["uploaded_file"]["tmp_name"], $dir.$filename);
			if($move){
				setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_TRANSFER_RECEIPT.$filename));
			} else {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. Please try again"));
			}
		} else {
			setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_TRANSFER_RECEIPT.$filename));
		}
	}
	
	public function addInstallmentLoanRequest(){
		$this->load->model('MainOperation');
		$this->load->model('ModelLoanPrepaidCapital');

		$idLoanType				=	validatePostVar($this->postVar, 'idLoanType', true);
		$amount					=	preg_replace("/[^0-9]/", "", validatePostVar($this->postVar, 'amount', true));
		$notes					=	validatePostVar($this->postVar, 'notes', false);
		$receiptFileName		=	validatePostVar($this->postVar, 'receiptFileName', true);
		$isActiveRequestExist	=	$this->ModelLoanPrepaidCapital->checkActiveInstallmentRequestExist($this->idPartner, $idLoanType);
		$nominalSaldoLoan		=	$this->ModelLoanPrepaidCapital->getNominalSaldoLoan($this->idPartner, $idLoanType);
		$detailLoanType			=	$this->MainOperation->getDetailLoanType($idLoanType);
		$loanPrepaidCapitalStr	=	$detailLoanType['LOANTYPE'];
		$strLoanType			=	$detailLoanType['LOANTYPE'];
		
		if($isActiveRequestExist) setResponseForbidden(array("token" => $this->newToken, "msg" => "You are not allowed to apply for a new installment.\n\nThere are still active request for this type of loan : ".$loanPrepaidCapitalStr));
		if($nominalSaldoLoan < $amount) setResponseForbidden(array("token" => $this->newToken, "msg" => "The amount of installment you enter exceeds the amount of the loan balance : ".number_format($nominalSaldoLoan, 0)));
		
		$arrInsert				=	array(
			"IDDRIVER"				=>	$this->idPartner,
			"IDLOANTYPE"			=>	$idLoanType,
			"AMOUNT"				=>	$amount,
			"NOTES"					=>	$notes,
			"FILETRANSFERRECEIPT"	=>	$receiptFileName,
			"STATUS"				=>	0,
			"DATETIMEINPUT"			=>	date('Y-m-d H:i:s')
		);
		$procInsert				=	$this->MainOperation->addData("t_loandriverinstallmentrequest", $arrInsert);

		if(!$procInsert['status']) switchMySQLErrorCode($procInsert['errCode'], $this->newToken);

		if(PRODUCTION_URL){
			$partnerName					=	$this->detailPartner['PARTNERNAME'];
			$totalLoanPrepaidCapitalRequest	=	$this->ModelLoanPrepaidCapital->getTotalLoanPrepaidCapitalRequest();
			$totalLoanInstallmentRequest	=	$this->ModelLoanPrepaidCapital->getTotalLoanInstallmentRequest();
			$totalAllRequest				=	$totalLoanPrepaidCapitalRequest + $totalLoanInstallmentRequest;
			$factory						=	(new Factory)
												->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)
												->withDatabaseUri(FIREBASE_RTDB_URI);
			$database						=	$factory->createDatabase();
			$reference						=	$database->getReference(FIREBASE_RTDB_MAINREF_NAME."unprocessedFinanceDriver/loanPrepaidCapital")
												->set([
													'newLoanPrepaidCapitalStatus'	=>	true,
													'newLoanPrepaidCapitalTotal'	=>	$totalAllRequest,
													'newLoanPrepaidCapitalMessage'	=>	"New loan installment request (".$strLoanType.") from ".$partnerName." - ".number_format($amount, 0, '.', ',')." IDR.<br/>Notes : ".$notes,
													'timestampUpdate'				=>	gmdate("YmdHis")
												]);
		}
		
		setResponseOk(
			array(
				"token"	=>	$this->newToken,
				"msg"	=>	"Your installment request is being processed"
			)
		);
	}
}