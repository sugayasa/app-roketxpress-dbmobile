<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;

class CollectPayment extends CI_controller {
	
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
		$method		=	$this->router->fetch_method();
		$funcName	=	$this->uri->segment(2);

		if($method != 'GET' && $funcName != "imageSettlementCollectPayment"){
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

		}
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function dashboardCollectPayment(){

		$this->load->model('ModelCollectPayment');
		
		$yearMonth					=	date('Y-m');
		$dataSummary				=	$this->ModelCollectPayment->getDataSummaryCollectPayment($this->idPartnerType, $this->idPartner);
		$dataActiveCollectPayment	=	$this->ModelCollectPayment->getDataActiveCollectPayment($this->idPartnerType, $this->idPartner);
		$dataHistoryCollectPayment	=	$this->ModelCollectPayment->getDataHistoryCollectPayment($this->idPartnerType, $this->idPartner, $yearMonth);
		
		setResponseOk(
			array(
				"token"						=>	$this->newToken,
				"dataSummary"				=>	$dataSummary,
				"dataActiveCollectPayment"	=>	$dataActiveCollectPayment,
				"dataHistoryCollectPayment"	=>	$dataHistoryCollectPayment
			)
		);
		
	}
	
	public function historyCollectPayment(){

		$this->load->model('ModelCollectPayment');
		
		$year						=	validatePostVar($this->postVar, 'year', true);
		$month						=	validatePostVar($this->postVar, 'month', true);
		$yearMonth					=	$year."-".$month;
		$dataHistoryCollectPayment	=	$this->ModelCollectPayment->getDataHistoryCollectPayment($this->idPartnerType, $this->idPartner, $yearMonth);
		
		setResponseOk(
			array(
				"token"						=>	$this->newToken,
				"dataHistoryCollectPayment"	=>	$dataHistoryCollectPayment
			)
		);
		
	}
	
	public function detailCollectPayment(){
		$this->load->model('ModelCollectPayment');
		
		$idCollectPayment		=	validatePostVar($this->postVar, 'idCollectPayment', true);
		$detailCollectPayment	=	$this->ModelCollectPayment->getDetailCollectPayment($this->idPartnerType, $this->idPartner, $idCollectPayment);
		
		if(!$detailCollectPayment){
			setResponseNotFound(array("token" => $this->newToken, "msg" => "Details not found. Please try again later"));
		}
		
		$idReservation			=	$detailCollectPayment['IDRESERVATION'];
		$dateCollect			=	$detailCollectPayment['DATECOLLECT'];
		$detailCollectPayment	=	$this->ModelCollectPayment->detailCollectPaymentDate($idReservation, $this->idPartnerType, $this->idPartner, $dateCollect);
		$strArrIdCollectPayment	=	$detailCollectPayment['STRARRIDCOLLECTPAYMENT'];
		$amountCurrency			=	$detailCollectPayment['AMOUNTCURRENCY'];
		$explodeAmountCurrency	=	explode(",", $amountCurrency);
		
		if(count($explodeAmountCurrency) > 1){
			$detailCollectPayment['AMOUNTCURRENCY']		=	'IDR';
			$detailCollectPayment['EXCHANGECURRENCY']	=	"1";
			$detailCollectPayment['AMOUNT']				=	number_format($detailCollectPayment['AMOUNTIDR'] * 1, 2, ".", "");
		}
		
		$historyDetailCollectPayment	=	$this->ModelCollectPayment->getDataHistoryDetailCollectPayment($strArrIdCollectPayment);
		
		setResponseOk(
			array(
				"token"							=>	$this->newToken,
				"detailCollectPayment"			=>	$detailCollectPayment,
				"historyDetailCollectPayment"	=>	$historyDetailCollectPayment
			)
		);
	}

	public function submitSettlementCollectPayment(){
		$this->load->model('MainOperation');
		$this->load->model('ModelCollectPayment');
		
		$idCollectPayment		=	validatePostVar($this->postVar, 'idCollectPayment', true);
		$totalAmount			=	validatePostVar($this->postVar, 'totalAmount', true);
		$paymentReceiptFileName	=	validatePostVar($this->postVar, 'paymentReceiptFileName', true);
		$detailCollectPayment	=	$this->ModelCollectPayment->getDetailCollectPayment($this->idPartnerType, $this->idPartner, $idCollectPayment);
		
		if(!$detailCollectPayment){
			setResponseNotFound(array("token" => $this->newToken, "msg" => "Failed! You are not allowed to perform this action"));
		}
		
		$idWithdrawalRecap		=	$detailCollectPayment['IDWITHDRAWALRECAP'];
		$statusCollectPayment	=	$detailCollectPayment['STATUS'];
		$statusSettlementCollect=	$detailCollectPayment['STATUSSETTLEMENTREQUEST'];
		$totalAmountDB			=	$detailCollectPayment['AMOUNTIDR'];
		
		if($statusCollectPayment != 1){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "Failed! Please confirm that you have received collect payment from order first"));
		}

		if($idWithdrawalRecap != 0){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "Failed! This collect payment is in the process of being completed along with withdrawal request"));
		}
		
		if($statusSettlementCollect != 0 && $statusSettlementCollect != -1){
			switch($statusCollectPayment){
				case "1"	:	setResponseForbidden(array("token" => $this->newToken, "msg" => "Failed! Status of settlement collect payment is waiting for approval")); break;
				case "2"	:	setResponseForbidden(array("token" => $this->newToken, "msg" => "Failed! Status of settlement collect payment has been completed")); break;
			}
		}

		if($totalAmountDB != $totalAmount){
			setResponseForbidden(array("token" => $this->newToken, "msg" => "Failed! Invalid total amount"));
		}
		
		$arrUpdateCollectPayment=	array(
										"PAYMENTRECEIPTFILENAME"	=>	$paymentReceiptFileName,
										"STATUSSETTLEMENTREQUEST"	=>	1,
										"DATETIMESETTLEMENTREQUEST"	=>	date('Y-m-d H:i:s')
									);
		$procUpdateCollectPayment=	$this->MainOperation->updateData("t_collectpayment", $arrUpdateCollectPayment, "IDCOLLECTPAYMENT", $idCollectPayment);
		
		if($procUpdateCollectPayment['status']){
			$partnerDetail			=	$this->idPartnerType == 1 ? $this->MainOperation->getDetailVendor($this->idPartner) : $this->MainOperation->getDetailDriver($this->idPartner);
			$partnerTypeStr			=	$this->idPartnerType == 1 ? "Vendor" : "Driver";
			$partnerName			=	$partnerDetail['NAME'];
			$arrInsertCollectHistory=	array(
											"IDCOLLECTPAYMENT"	=>	$idCollectPayment,
											"DESCRIPTION"		=>	"Partner requests to validate collect payment ",
											"SETTLEMENTRECEIPT"	=>	$paymentReceiptFileName,
											"USERINPUT"			=>	$partnerName." (".$partnerTypeStr.")",
											"DATETIMEINPUT"		=>	date('Y-m-d H:i:s'),
											"STATUS"			=>	2
										);
			$this->MainOperation->addData("t_collectpaymenthistory", $arrInsertCollectHistory);
		}
		
		if(PRODUCTION_URL){

			$partnerName			=	$this->detailPartner['PARTNERNAME'];
			$totalSettlementRequest	=	$this->ModelCollectPayment->getTotalSettlementRequest();
			$factory				=	(new Factory)
										->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)
										->withDatabaseUri(FIREBASE_RTDB_URI);
			$database				=	$factory->createDatabase();
			$reference				=	$database->getReference(FIREBASE_RTDB_MAINREF_NAME."unprocessedFinanceDriver/collectPayment")
										->set([
											'newCollectPaymentStatus'	=>	true,
											'newCollectPaymentTotal'	=>	$totalSettlementRequest,
											'newCollectPaymentMessage'	=>	"New collect payment settlement request from ".$partnerName,
											'timestampUpdate'			=>	gmdate("YmdHis")
										]);
		}
		
		setResponseOk(
			array(
				"token"	=>	$this->newToken,
				"msg"	=>	"Collect payment settlement request has been received. Please wait for validation from the admin finance"
			)
		);
	}
	
	public function uploadImageSettlementCollectPayment(){
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
		$dir			=	PATH_STORAGE_COLLECT_PAYMENT_RECEIPT;
		
		if(!file_exists($dir.$filename)){
			$move		=	move_uploaded_file($_FILES["uploaded_file"]["tmp_name"], $dir.$filename);
			if($move){
				setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_COLLECT_PAYMENT_RECEIPT.$filename));
			} else {
				setResponseInternalServerError(array("token"=>$this->newToken, "msg"=>"Failed to upload files. Please try again"));
			}
		} else {
			setResponseOk(array("token"=>$this->newToken, "msg"=>"Image has been uploaded successfully", "urlImage"=>URL_COLLECT_PAYMENT_RECEIPT.$filename));
		}
	}
	
	public function imageSettlementCollectPayment($filename){
		$loc			=	PATH_STORAGE_COLLECT_PAYMENT_RECEIPT.$filename;
		if(file_exists($loc)) {
			$image 		= 	@imagecreatefromjpeg($loc) or $image = 'testpng';
			if($image == "testpng"){
				$image 		= 	@imagecreatefrompng($loc) or $image = 'brokenimage';
				if($image	==	'brokenimage') {
					$this->noimage('brokenimage');	
					die();
				}

				$background = imagecolorallocatealpha($image,0,0,0,127);
				imagecolortransparent($image, $background);
				imagealphablending($image, false);
				imagesavealpha($image, true);

				header	("Content-Type: image/png");
				imagepng		($image,NULL);
				imagedestroy	($image);
			}

			header	("Content-Type: image/jpeg");
			imagejpeg		($image,NULL);
			imagedestroy	($image);
		} else {
			$this->noimage("noimage");
		}
	}
	
	private function noimage($type){
		header	("Content-Type: image/jpeg");
		$filename	=	$type == "noimage" ? PATH_STORAGE."noimage.jpg" : PATH_STORAGE."errimage.jpg";
		$image 		= 	imagecreatefromjpeg($filename);
		imagejpeg		($image,NULL);
		imagedestroy	($image);
	}
}