<?php
class ModelDriver extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function getDataFeeRecap($idDriver, $yearMonth){
		
		$query	= $this->db->query("SELECT COUNT(A.IDSCHEDULEDRIVER) AS TOTALJOBS, SUM(B.NOMINAL) AS TOTALFEE
									FROM t_scheduledriver A
									LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
									WHERE A.IDDRIVER = ".$idDriver." AND LEFT(B.SCHEDULEDATE, 7) = '".$yearMonth."' AND B.STATUS = 1
									GROUP BY A.IDDRIVER
									LIMIT 1");
		$row	= $query->row_array();

		if (isset($row)){
			return $row;
		}
		
		return array(
						"TOTALJOBS"	=>	0,
						"TOTALFEE"	=>	0
				);
	}

	public function getDataListFee($idDriver, $yearMonth){
		
		$query	= $this->db->query("SELECT  DATE_FORMAT(B.SCHEDULEDATE, '%d') AS SCHEDULEDATE,
											DATE_FORMAT(B.SCHEDULEDATE, '%W') AS SCHEDULEDAY, 
											C.RESERVATIONTIMESTART, B.NOMINAL, B.PRODUCTNAME, C.CUSTOMERNAME
									FROM t_scheduledriver A
									LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
									LEFT JOIN t_reservation C ON B.IDRESERVATION = C.IDRESERVATION
									WHERE A.IDDRIVER = ".$idDriver." AND LEFT(B.SCHEDULEDATE, 7) = '".$yearMonth."' AND B.STATUS = 1
									GROUP BY A.IDSCHEDULEDRIVER
									ORDER BY B.SCHEDULEDATE, C.RESERVATIONTIMESTART");
		$result	= $query->result();

		if (isset($result)){
			return $result;
		}
		
		return array();
	}

	public function getDataFeeRecapNewScheme($idDriver, $yearMonth, $showActiveOnly){
		
		$con_active	=	$showActiveOnly == 1 ? "WITHDRAWSTATUS = 0" : "LEFT(DATESCHEDULE, 7) = '".$yearMonth."'";
		$query		=	$this->db->query("SELECT COUNT(IDFEE) AS TOTALJOBS, SUM(FEENOMINAL) AS TOTALFEE
										 FROM t_fee
										 WHERE IDDRIVER = ".$idDriver." AND IDVENDOR = 0 AND ".$con_active."
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

	public function getDataListFeeNewScheme($idDriver, $yearMonth, $showActiveOnly){
		
		$con_active	=	isset($showActiveOnly) && ($showActiveOnly == 1 || $showActiveOnly == "1") ? "A.WITHDRAWSTATUS = 0" : "LEFT(A.DATESCHEDULE, 7) = '".$yearMonth."'";
		$query		=	$this->db->query("SELECT  DATE_FORMAT(A.DATESCHEDULE, '%d') AS SCHEDULEDATE,
												  DATE_FORMAT(A.DATESCHEDULE, '%W') AS SCHEDULEDAY, 
												  C.RESERVATIONTIMESTART, A.FEENOMINAL AS NOMINAL, B.PRODUCTNAME, C.CUSTOMERNAME
										  FROM t_fee A
										  LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
										  LEFT JOIN t_reservation C ON A.IDRESERVATION = C.IDRESERVATION
										  WHERE A.IDDRIVER = ".$idDriver." AND ".$con_active."
										  GROUP BY A.IDFEE
										  ORDER BY A.DATESCHEDULE, C.RESERVATIONTIMESTART");
		$result		=	$query->result();

		if (isset($result)){
			return $result;
		}
		
		return array();
	}
	
}
