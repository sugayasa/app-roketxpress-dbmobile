<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HelpCenter extends CI_controller {
	
	public function __construct(){
        parent::__construct();
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}
	
	public function category($idPartnerType){
		
		$this->load->model('ModelHelpCenter');
		
		$strPartnerType	=	$idPartnerType == 1 ? "Vendor" : "Driver";
		$dataCategory	=	$this->ModelHelpCenter->getCategory($idPartnerType);
		
		if($dataCategory){
			foreach($dataCategory as $keyCategory){
				$idCategory	=	$keyCategory->IDHELPCENTERCATEGORY;
				$listArticle=	$this->ModelHelpCenter->getListArticleHelpCenter($idCategory);
				
				if($listArticle){
					$keyCategory->LISTARTICLE	=	$listArticle;
				}
			}
		}
		
		$arrData		=	array(
								"dataCategory"		=>	$dataCategory,
								"strPartnerType"	=>	$strPartnerType
							);
		
		$this->load->view('helpCenter/category', $arrData);
		
	}
	
	public function article($idArticle){
		
		$this->load->model('ModelHelpCenter');
		
		$detailArticle	=	$this->ModelHelpCenter->getDetailArticle($idArticle);
		$arrData		=	array(
								"detailArticle"		=>	$detailArticle
							);
		
		$this->load->view('helpCenter/article', $arrData);
		
	}
	
}