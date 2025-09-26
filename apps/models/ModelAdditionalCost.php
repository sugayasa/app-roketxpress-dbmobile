<?php
class ModelAdditionalCost extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}
	
	public function getListSchedule($idDriver, $dateStart, $dateEnd){
		
		$query	= $this->db->query("SELECT A.IDRESERVATIONDETAILS, A.PRODUCTNAME, B.RESERVATIONTITLE, B.CUSTOMERNAME,
											DATE_FORMAT(A.SCHEDULEDATE, '%d %b %Y') AS SCHEDULEDATE,
											LEFT(B.RESERVATIONTIMESTART, 5) AS SCHEDULETIME, D.STATUSPROCESSNAME AS LASTSTATUS
									 FROM t_reservationdetails A
									 LEFT JOIN t_reservation B ON A.IDRESERVATION = B.IDRESERVATION
									 LEFT JOIN t_scheduledriver C ON A.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS
									 LEFT JOIN m_statusprocessdriver D ON C.STATUSPROCESS = D.IDSTATUSPROCESSDRIVER
									 WHERE C.IDDRIVER = '".$idDriver."' AND DATE(A.SCHEDULEDATE) BETWEEN '".$dateStart."' AND '".$dateEnd."' AND
										   A.STATUS = 1 AND C.STATUS IN (2,3)
									 ORDER BY A.SCHEDULEDATE ASC, B.RESERVATIONTIMESTART");
		$result	= $query->result();

		if (isset($result)){
			return $result;
		}
		
		return false;
		
	}
	
	public function getListAdditionalCost($idDriver, $dateStart, $dateEnd, $showActiveOnly){
		
		$con_date		=	isset($showActiveOnly) && ($showActiveOnly == 1 || $showActiveOnly == "1") ? "1=1" : "DATE(A.DATETIMEINPUT) BETWEEN '".$dateStart."' AND '".$dateEnd."'";
		$con_activeOnly	=	isset($showActiveOnly) && ($showActiveOnly == 1 || $showActiveOnly == "1") ? "A.STATUSAPPROVAL = 0" : "1=1";
		$query			=	$this->db->query("SELECT A.IDRESERVATIONADDITIONALCOST, D.RESERVATIONTITLE, B.PRODUCTNAME,
													D.CUSTOMERNAME, C.ADDITIONALCOSTTYPE, A.DESCRIPTION, A.NOMINAL,
													CONCAT('".URL_ADDITIONAL_COST_IMAGE."', A.IMAGERECEIPT) AS IMAGERECEIPT,
													DATE_FORMAT(A.DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMEINPUT,
													IF(A.STATUSAPPROVAL != 0, DATE_FORMAT(A.DATETIMEAPPROVAL, '%d %b %Y %H:%i'), '-') AS DATETIMEAPPROVAL,
													IF(A.STATUSAPPROVAL != 0, A.USERAPPROVAL, '-') AS USERAPPROVAL,
													CASE
														WHEN A.STATUSAPPROVAL = 0 THEN 'Waiting for approval'
														WHEN A.STATUSAPPROVAL = 1 THEN 'Approved'
														WHEN A.STATUSAPPROVAL = -1 THEN 'Rejected'
														ELSE '-'
													END AS STRSTATUSAPPROVAL,
													A.STATUSAPPROVAL
											 FROM t_reservationadditionalcost A
											 LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
											 LEFT JOIN m_additionalcosttype C ON A.IDADDITIONALCOSTTYPE = C.IDADDITIONALCOSTTYPE
											 LEFT JOIN t_reservation D ON B.IDRESERVATION = D.IDRESERVATION
											 WHERE A.IDDRIVER = '".$idDriver."' AND ".$con_date." AND ".$con_activeOnly."
											 ORDER BY A.DATETIMEINPUT ASC");
		$result			=	$query->result();

		if (isset($result)){
			return $result;
		}
		
		return false;
		
	}

	public function getTotalAdditionalCostRequest(){
		
		$query	=	$this->db->query("SELECT COUNT(IDRESERVATIONADDITIONALCOST) AS TOTALADDITIONALCOSTREQUEST
									  FROM t_reservationadditionalcost
									  WHERE STATUSAPPROVAL = 0
									  LIMIT 1");
		$row	=	$query->row_array();

		if(isset($row)){
			return $row['TOTALADDITIONALCOSTREQUEST'];
		}
		
		return 0;
		
	}
	
}