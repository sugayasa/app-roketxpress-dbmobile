<?php
class ModelCar extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function getDataCalendarDayOff($idVendor, $yearMonth){
		
		$query		= $this->db->query("SELECT COUNT(A.IDCARVENDOR) AS TOTALCARDAYOFF, A.DATE
										FROM t_dayoff A
										LEFT JOIN t_carvendor B ON A.IDCARVENDOR = B.IDCARVENDOR
										WHERE B.IDVENDOR = ".$idVendor." AND LEFT(A.DATE, 7) = '".$yearMonth."'
										GROUP BY A.DATE
										ORDER BY A.DATE");
		$result		= $query->result();

		if (isset($result)){
			return $result;
		}
		
		return array();
		
	}

	public function getDetailCalendarDayOff($idVendor, $date){
		
		$query		= $this->db->query("SELECT  C.CARTYPE, B.BRAND, B.MODEL, B.PLATNUMBER, B.YEAR, A.REASON,
												DATE_FORMAT('%d %b %Y %H:%i', A.DATETIME) AS DATETIME
										FROM t_dayoff A
										LEFT JOIN t_carvendor B ON A.IDCARVENDOR = B.IDCARVENDOR
										LEFT JOIN m_cartype C ON A.IDCARTYPE = C.IDCARTYPE
										WHERE B.IDVENDOR = ".$idVendor." AND A.DATE = '".$date."'
										ORDER BY C.CARTYPE, B.BRAND, B.MODEL");
		$result		= $query->result();

		if (isset($result)){
			return $result;
		}
		
		return array();
		
	}

	public function getTotalSchedule($idCarVendor, $date){

		$query		= $this->db->query("SELECT COUNT(IDSCHEDULECAR) AS TOTALSCHEDULE
										FROM t_schedulecar A
										LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
										WHERE A.IDCARVENDOR = ".$idCarVendor." AND B.SCHEDULEDATE = '".$date."' AND B.STATUS != -1 AND A.STATUS NOT IN (-1,3)
										GROUP BY A.IDCARVENDOR");
		$row		= $query->row_array();

		if(isset($row)){
			return $row['TOTALSCHEDULE'];
		}
		
		return 0;
		
	}
	
}