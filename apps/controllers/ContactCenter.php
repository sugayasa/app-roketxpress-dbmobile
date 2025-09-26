<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ContactCenter extends CI_controller {
	
	var $postVar;
	var $imei;
	var $token;
	var $newToken;
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
		$this->idUserMobile	=	$detailPartner['IDUSERMOBILE'];
		$this->idPartnerType=	$detailPartner['IDPARTNERTYPE'];
		$this->idPartner	=	$detailPartner['IDPARTNER'];
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function contactList(){
		$this->load->model('ModelContactCenter');

		$dataContact=	$this->ModelContactCenter->getDataContactCenter();
		$dataResult	=	[];
		if(count($dataContact) > 0){
			foreach($dataContact as $keyContact){
				$lastActivity	=	$keyContact->LASTACTIVITY;
				$contactNumber	=	$keyContact->PARTNERCONTACTNUMBER;
				$lastActivityDB	=	$keyContact->LASTACTIVITY;
				
				if(substr($lastActivity, 0, 10) == date('Y-m-d')){
					$keyContact->LASTACTIVITY	=	"Today, ".substr($lastActivity, 11, 5);
				} else if(substr($lastActivity, 0, 10) == date('Y-m-d', strtotime('-1 day'))){
					$keyContact->LASTACTIVITY	=	"Yesterday, ".substr($lastActivity, 11, 5);
				} else {
					$lastActivity				=	DateTime::createFromFormat('Y-m-d H:i:s', $lastActivity);
					$keyContact->LASTACTIVITY	=	$lastActivity->format('d M Y H:i');
				}
				
				$keyContact->PARTNERCONTACTNUMBERURL	=	$contactNumber;
				if($lastActivityDB != '0000-00-00 00:00:00') $dataResult[]	=	$keyContact;
			}
		}
		
		setResponseOk(
			array(
				"token"			=>	$this->newToken,
				"dataContact"	=>	$dataResult
			)
		);
	}
	
}