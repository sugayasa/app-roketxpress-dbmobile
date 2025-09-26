<?php
class ModelCar extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function getDataFeeRecap($idVendor, $yearMonth){
		
		$query	= $this->db->query("SELECT COUNT(A.IDSCHEDULECAR) AS TOTALJOBS, SUM(B.NOMINAL) AS TOTALFEE
									FROM t_schedulecar A
									LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
									LEFT JOIN t_carvendor C ON A.IDCARVENDOR = C.IDCARVENDOR
									WHERE C.IDVENDOR = ".$idVendor." AND LEFT(B.SCHEDULEDATE, 7) = '".$yearMonth."'
									GROUP BY C.IDVENDOR
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

	public function getDataFeePerDate($idVendor, $yearMonth){
		
		$query	= $this->db->query("SELECT  DATE_FORMAT(B.SCHEDULEDATE, '%W, %d') AS SCHEDULEDATE, COUNT(A.IDSCHEDULECAR) AS TOTALJOBS,
											SUM(B.NOMINAL) AS TOTALFEE
									FROM t_schedulecar A
									LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
									LEFT JOIN t_carvendor C ON A.IDCARVENDOR = C.IDCARVENDOR
									WHERE C.IDVENDOR = ".$idVendor." AND LEFT(B.SCHEDULEDATE, 7) = '".$yearMonth."'
									GROUP BY B.SCHEDULEDATE
									ORDER BY B.SCHEDULEDATE");
		$result	= $query->result();

		if (isset($result)){
			return $result;
		}
		
		return array();
	}

	public function getDataFeePerCar($idVendor, $yearMonth){
		
		$query	= $this->db->query("SELECT  A.IDCARVENDOR, CONCAT(C.BRAND, ' ', C.MODEL, ' [', C.PLATNUMBER, ']') AS CARDETAIL,
											COUNT(A.IDSCHEDULECAR) AS TOTALJOBS, SUM(B.NOMINAL) AS TOTALFEE
									FROM t_schedulecar A
									LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
									LEFT JOIN t_carvendor C ON A.IDCARVENDOR = C.IDCARVENDOR
									WHERE C.IDVENDOR = ".$idVendor." AND LEFT(B.SCHEDULEDATE, 7) = '".$yearMonth."'
									GROUP BY A.IDCARVENDOR
									ORDER BY C.BRAND, C.MODEL, C.PLATNUMBER");
		$result	= $query->result();

		if (isset($result)){
			return $result;
		}
		
		return array();
	}

	public function getDataFeeByDate($idVendor, $date){
		
		$query	= $this->db->query("SELECT  CONCAT(C.BRAND, ' ', C.MODEL, ' [', C.PLATNUMBER, ']') AS CARDETAIL,
											D.RESERVATIONTIMESTART, B.NOMINAL, B.PRODUCTNAME, D.CUSTOMERNAME
									FROM t_schedulecar A
									LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
									LEFT JOIN t_carvendor C ON A.IDCARVENDOR = C.IDCARVENDOR
									LEFT JOIN t_reservation D ON B.IDRESERVATION = D.IDRESERVATION
									WHERE C.IDVENDOR = ".$idVendor." AND B.SCHEDULEDATE = '".$date."'
									ORDER BY B.SCHEDULEDATE");
		$result	= $query->result();

		if (isset($result)){
			return $result;
		}
		
		return array();
	}

	public function getDataFeeByCar($idCarVendor, $yearMonth){
		
		$query	= $this->db->query("SELECT  DATE_FORMAT(B.SCHEDULEDATE, '%W, %d') AS SCHEDULEDATE,
											D.RESERVATIONTIMESTART, B.NOMINAL, B.PRODUCTNAME, D.CUSTOMERNAME
									FROM t_schedulecar A
									LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
									LEFT JOIN t_carvendor C ON A.IDCARVENDOR = C.IDCARVENDOR
									LEFT JOIN t_reservation D ON B.IDRESERVATION = D.IDRESERVATION
									WHERE A.IDCARVENDOR = ".$idCarVendor." AND LEFT(B.SCHEDULEDATE, 7) = '".$yearMonth."'
									ORDER BY B.SCHEDULEDATE");
		$result	= $query->result();

		if (isset($result)){
			return $result;
		}
		
		return array();
	}
	
}
