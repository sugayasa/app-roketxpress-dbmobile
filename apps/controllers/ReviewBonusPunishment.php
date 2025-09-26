<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ReviewBonusPunishment extends CI_controller {
	
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
	
	public function summaryData(){

		$this->load->model('MainOperation');
		$this->load->model('ModelReviewBonusPunishment');

		$yearMonth			=	validatePostVar($this->postVar, 'yearMonth', true);
		$dataRecap			=	$this->ModelReviewBonusPunishment->getDataRecapBonusPunishment($this->idPartner, $yearMonth);
		$idDriverReviewBonus=	$dataRecap['IDDRIVERREVIEWBONUS'];
		$bonusRate			=	$dataRecap['BONUSRATE'];
		$dataListReview		=	[];
		
		if($idDriverReviewBonus != 0){
			$dataListReview	=	$this->ModelReviewBonusPunishment->getDataListReview($idDriverReviewBonus, $bonusRate);
		}
		
		setResponseOk(array("token"=>$this->newToken, "dataRecap"=>$dataRecap, "dataListReview"=>$dataListReview));
		
	}
	
	public function tableSimulation(){
		$bonusRate		=	validatePostVar($this->postVar, 'bonusRate', true);
		$pointTarget	=	validatePostVar($this->postVar, 'pointTarget', true);
		$pointCurrent	=	validatePostVar($this->postVar, 'pointCurrent', true);
		$bonusRate		=	intval($bonusRate);
		$pointTarget	=	intval($pointTarget);
		$pointCurrent	=	intval($pointCurrent);
		$additionalPoint=	$pointCurrent > $pointTarget + 4 ? $pointCurrent - $pointTarget : 4;
		
		if($pointTarget < 0 || $bonusRate <= 0){
			setResponseNotFound(array("token"=>$this->newToken, "msg"=>"There is no data simulation available for the period you selected"));
		} else {
			$simulationData	=	[];
			for($point=0; $point<=($pointTarget + 4); $point++){
				$bonusNominal		=	$point * $bonusRate;
				$punishmentPoint	=	$point - $pointTarget;
				$punishmentNominal	=	$punishmentPoint * $bonusRate;
				$punishmentNominal	=	$punishmentNominal > 0 ? 0 : $punishmentNominal;
				$resultNominal		=	$bonusNominal + $punishmentNominal;
				$isPointCurrent		=	$point == $pointCurrent ? true : false;
				$simulationData[]	=	[
					"point"			=>	$point,
					"bonus"			=>	number_format($bonusNominal, 0, '.', ','),
					"punishment"	=>	number_format($punishmentNominal, 0, '.', ','),
					"result"		=>	number_format($resultNominal, 0, '.', ','),
					"isPointCurrent"=>	$isPointCurrent
				];
			}
			
			setResponseOk(array("token"=>$this->newToken, "simulationData"=>$simulationData));
		}
	}
	
}