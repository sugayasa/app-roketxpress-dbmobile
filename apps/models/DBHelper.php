<?php

	class DBHelper extends CI_Model {

		public function __construct(){
			parent::__construct(); 
			$this->load->database();
		}
		
		public function queryDB($page, $dataperpage, $basequery, $keycount){
			$pageProperties	=	getPageProperties($page, $dataperpage);
			$query			=	$this->db->query($basequery." LIMIT ".$pageProperties[0].", ".$pageProperties[2]);
			$result			=	$query->result();
			
			$queryCount		=	$this->db->query("SELECT IFNULL(COUNT(".$keycount."),0) AS TOTAL FROM (".$basequery.") AS A");
			$row			=	$queryCount->row_array();
			$datatotal		=	$row['TOTAL'];
			$pagetotal		=	ceil($datatotal/$dataperpage);
			$startnumber	=	($page-1) * $dataperpage + 1;
			$dataend		=	$pageProperties[2] > $datatotal ? $datatotal : $pageProperties[2];
			
			return array("result"=>$result ,"datastart"=>$pageProperties[1], "dataend"=>$dataend, "datatotal"=>$datatotal, "pagetotal"=>$pagetotal);

		}
		
		public function generateEmptyResult(){
			return array("status"=>404, "datastart"=>0, "dataend"=>0, "datatotal"=>0, "pagetotal"=>0);
		}
		
	}