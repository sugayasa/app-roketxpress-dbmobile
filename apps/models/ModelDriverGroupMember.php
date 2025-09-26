<?php
class ModelDriverGroupMember extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}
	
	public function checkDataExists($idDriver, $driverName, $driverPhoneNumber, $idDriverGroupMember = 0){
		
		$idDriverGroupMember=	$idDriverGroupMember == "" ? 0 : $idDriverGroupMember;
		$query				=	$this->db->query(
									"SELECT IDDRIVERGROUPMEMBER FROM m_drivergroupmember
									WHERE IDDRIVER = ".$idDriver." AND DRIVERNAME = '".$driverName."' AND DRIVERPHONENUMBER = '".$driverPhoneNumber."' AND
										  IDDRIVERGROUPMEMBER <> ".$idDriverGroupMember."
									LIMIT 1"
								);
		$row				=	$query->row_array();

		if(isset($row)) return true;
		return false;
	}

	public function getDriverMemberList($idDriver){
		$query	=	$this->db->query(
						"SELECT IDDRIVERGROUPMEMBER, DRIVERNAME, DRIVERPHONENUMBER, CARNUMBERPLATE, CARBRAND, CARMODEL, '' AS ISHAVESCHEDULE
						FROM m_drivergroupmember
						WHERE IDDRIVER = '".$idDriver."'
						ORDER BY DRIVERNAME"
					);
		$result	=	$query->result();

		if (isset($result)) return $result;
		return array();
	}
	
	public function isDriverHaveSchedule($idDriverGroupMember, $dateSchedule){
		$query		=	$this->db->query(
							"SELECT IFNULL(COUNT(A.IDSCHEDULEDRIVER), 0) AS TOTALSCHEDULE
							 FROM t_scheduledriver A
							 LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
							 WHERE A.IDDRIVERGROUPMEMBER = ".$idDriverGroupMember." AND B.SCHEDULEDATE = '".$dateSchedule."'
							 GROUP BY A.IDDRIVERGROUPMEMBER
							 LIMIT 1"
						);
		$row		=	$query->row_array();

		if (isset($row)) {
			$totalSchedule	=	$row['TOTALSCHEDULE'];
			if($totalSchedule > 0) return true;
			if($totalSchedule <= 0) return false;
		}
		
		return false;		
	}
}