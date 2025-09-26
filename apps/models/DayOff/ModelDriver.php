<?php
class ModelDriver extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function getDataCalendarDayOff($idDriver, $yearMonth){
		
		$query		= $this->db->query("SELECT DATEDAYOFF FROM t_dayoff
										WHERE IDDRIVER = ".$idDriver." AND LEFT(DATEDAYOFF, 7) = '".$yearMonth."'
										ORDER BY DATEDAYOFF");
		$result		= $query->result();

		if (isset($result)){
			return $result;
		}
		
		return array();
		
	}

	public function getDetailCalendarDayOff($idDriver, $date){

		$query		= $this->db->query("SELECT REASON, DATE_FORMAT(DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMEINPUT
										FROM t_dayoff
										WHERE IDDRIVER = ".$idDriver." AND DATEDAYOFF = '".$date."'");
		$result		= $query->result();

		if (isset($result)){
			return $result;
		}
		
		return false;
		
	}

	public function isDayOffQuotaSufficient($date){
		$query	=	$this->db->query(
						"SELECT TOTALOFFDRIVER, TOTALDAYOFFQUOTA FROM t_scheduledrivermonitor
						WHERE DATESCHEDULE = '".$date."'
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) {
			$totalOffDriver		=	$row['TOTALOFFDRIVER'];
			$totalDayOffQuota	=	$row['TOTALDAYOFFQUOTA'];
			
			return $totalDayOffQuota > $totalOffDriver ? true : false;
		}
		
		return false;
	}

	public function getTotalSchedule($idDriver, $date){

		$query		= $this->db->query("SELECT COUNT(IDSCHEDULEDRIVER) AS TOTALSCHEDULE
										FROM t_scheduledriver A
										LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
										WHERE A.IDDRIVER = ".$idDriver." AND B.SCHEDULEDATE = '".$date."' AND B.STATUS != -1 AND A.STATUS NOT IN (-1,3)
										GROUP BY A.IDDRIVER");
		$row		= $query->row_array();

		if(isset($row)){
			return $row['TOTALSCHEDULE'];
		}
		
		return 0;
		
	}

	public function isRequestExist($idDriver, $date){

		$query		= $this->db->query("SELECT IDDAYOFFREQUEST FROM t_dayoffrequest
										WHERE IDDRIVER = ".$idDriver." AND DATEDAYOFF = '".$date."' AND STATUS = 0
										LIMIT 1");
		$row		= $query->row_array();

		if(isset($row)){
			return true;
		}
		
		return false;
		
	}

	public function getDataDayOffRequestList($idDriver, $yearMonth, $idDayOffRequest){
		
		$condition	=	isset($idDayOffRequest) && $idDayOffRequest != "" && $idDayOffRequest != "0" ? "IDDAYOFFREQUEST = ".$idDayOffRequest : "LEFT(DATEDAYOFF, 7) = '".$yearMonth."'";
		$query		=	$this->db->query("SELECT DATE_FORMAT(DATEDAYOFF, '%d %b %Y') AS DATEDAYOFF, REASON,
										  CASE STATUS
											WHEN 0 THEN 'Waiting for approval'
											WHEN 1 THEN 'Approved'
											WHEN -1 THEN 'Rejected'
											WHEN -2 THEN 'Deleted'
											ELSE '-'
										  END AS STATUSSTR,
										  STATUS, DATE_FORMAT(DATETIMEAPPROVAL, '%d %b %Y %H:%i') AS DATETIMEAPPROVAL, USERAPPROVAL
										  FROM t_dayoffrequest
										  WHERE IDDRIVER = ".$idDriver." AND ".$condition."
										  ORDER BY DATEDAYOFF");
		$result		=	$query->result();

		if (isset($result)){
			return $result;
		}
		
		return new stdClass;
		
	}
	
	public function getTotalDayOffInDate($date){
		
		$query	= $this->db->query("SELECT COUNT(IDDAYOFF) AS TOTALDAYOFF FROM t_dayoff
									WHERE DATEDAYOFF = '".$date."'
									GROUP BY DATEDAYOFF
									LIMIT 1");
		$row	= $query->row_array();

		if(isset($row)){
			return $row['TOTALDAYOFF'];
		}
		
		return 0;
		
	}
	
	public function getTotalDayOffDriverInMonth($idDriver, $yearMonth){
		
		$query	= $this->db->query("SELECT COUNT(IDDAYOFF) AS TOTALDAYOFF FROM t_dayoff
									WHERE IDDRIVER = ".$idDriver." AND LEFT(DATEDAYOFF, 7) = '".$yearMonth."'
									GROUP BY IDDRIVER
									LIMIT 1");
		$row	= $query->row_array();

		if(isset($row)){
			return $row['TOTALDAYOFF'];
		}
		
		return 0;
		
	}
	
}