<?php
class ModelFee extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function getDataFeeRecapNewScheme($idPartnerType, $idPartner, $yearMonth, $showActiveOnly){
		
		$con_active	=	$showActiveOnly == 1 ? "WITHDRAWSTATUS = 0" : "LEFT(DATESCHEDULE, 7) = '".$yearMonth."'";
		$con_partner=	$idPartnerType == 1 ? "IDVENDOR = ".$idPartner : "IDDRIVER = ".$idPartner;
		$query		=	$this->db->query("SELECT COUNT(IDFEE) AS TOTALJOBS, SUM(FEENOMINAL) AS TOTALFEE
										 FROM t_fee
										 WHERE ".$con_partner." AND ".$con_active."
										 GROUP BY IDDRIVER
										 LIMIT 1");
		$row		=	$query->row_array();

		if (isset($row)){
			return $row;
		}
		
		return array(
						"TOTALJOBS"	=>	0,
						"TOTALFEE"	=>	0
				);
	}

	public function getDataListFeeNewScheme($idPartnerType, $idPartner, $yearMonth, $showActiveOnly){
		
		$con_active	=	isset($showActiveOnly) && ($showActiveOnly == 1 || $showActiveOnly == "1") ? "A.WITHDRAWSTATUS = 0" : "LEFT(A.DATESCHEDULE, 7) = '".$yearMonth."'";
		$con_partner=	$idPartnerType == 1 ? "A.IDVENDOR = ".$idPartner : "A.IDDRIVER = ".$idPartner;
		$query		=	$this->db->query("SELECT  DATE_FORMAT(A.DATESCHEDULE, '%b') AS SCHEDULEMONTH,
												  DATE_FORMAT(A.DATESCHEDULE, '%d') AS SCHEDULEDATE,
												  DATE_FORMAT(A.DATESCHEDULE, '%W') AS SCHEDULEDAY, 
												  C.RESERVATIONTIMESTART, A.FEENOMINAL AS NOMINAL, B.PRODUCTNAME, C.CUSTOMERNAME
										  FROM t_fee A
										  LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
										  LEFT JOIN t_reservation C ON A.IDRESERVATION = C.IDRESERVATION
										  WHERE ".$con_partner." AND ".$con_active."
										  GROUP BY A.IDFEE
										  ORDER BY A.DATESCHEDULE, C.RESERVATIONTIMESTART");
		$result		=	$query->result();

		if (isset($result)){
			return $result;
		}
		
		return array();
	}
	
}
