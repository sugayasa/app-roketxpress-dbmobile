<?php
class ModelDropOffPickUpCar extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function listOrderByDate($idDriver, $dateStart, $dateEnd, $showActiveOnly){
		$con_date       =	isset($showActiveOnly) && ($showActiveOnly == 1 || $showActiveOnly == "1") ? "1=1" : "(D.RESERVATIONDATESTART BETWEEN '".$dateStart."' AND '".$dateEnd."' OR D.RESERVATIONDATEEND BETWEEN '".$dateStart."' AND '".$dateEnd."')";
		$con_activeOnly =	isset($showActiveOnly) && ($showActiveOnly == 1 || $showActiveOnly == "1") ? "E.ISFINISHED = 0" : "1=1";
		$query          =	$this->db->query(
                                "SELECT A.IDSCHEDULECARDROPOFFPICKUP, D.RESERVATIONTITLE, A.JOBTYPE, IF(A.JOBTYPE = 1, 'Drop Off', 'Pick Up') AS JOBTYPESTR, 
                                        IF(A.JOBTYPE = 1, DATE(D.RESERVATIONDATESTART), DATE(D.RESERVATIONDATEEND)) AS JOBDATEDB,
                                        IF(A.JOBTYPE = 1, DATE_FORMAT(D.RESERVATIONDATESTART, '%d %b %Y'), DATE_FORMAT(D.RESERVATIONDATEEND, '%d %b %Y')) AS JOBDATE,
                                        IF(A.JOBTYPE = 1, DATE_FORMAT(D.RESERVATIONTIMESTART, '%H:%i'), DATE_FORMAT(D.RESERVATIONTIMEEND, '%H:%i')) AS JOBTIME,
                                        D.CUSTOMERNAME, E.STATUSPROCESSNAME
                                FROM t_schedulecardropoffpickup A
                                LEFT JOIN t_schedulecar B ON A.IDSCHEDULECAR = B.IDSCHEDULECAR
                                LEFT JOIN t_reservationdetails C ON B.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS
								LEFT JOIN t_reservation D ON C.IDRESERVATION = D.IDRESERVATION
							    LEFT JOIN m_statusprocesscardropoffpickup E ON A.IDSTATUSPROCESSCARDROPOFFPICKUP = E.IDSTATUSPROCESSCARDROPOFFPICKUP
                                WHERE A.IDDRIVER = '".$idDriver."' AND ".$con_date." AND ".$con_activeOnly."
                                ORDER BY JOBDATEDB ASC"
                            );
		$result         =	$query->result();

		if (isset($result)) return $result;
		return false;		
	}
	
	public function getDetailDropOffPickUpOrder($idScheduleCarDropOffPickUp){	
		$query  =	$this->db->query(
                        "SELECT CONCAT('".URL_SOURCE_LOGO."', F.LOGO) AS SOURCELOGOURL, F.SOURCENAME, 
                                D.RESERVATIONTITLE, A.JOBTYPE, IF(A.JOBTYPE = 1, 'Drop Off', 'Pick Up') AS JOBTYPESTR, 
                                IF(A.JOBTYPE = 1, D.RESERVATIONDATESTART, D.RESERVATIONDATEEND) AS JOBDATEDB,
                                IF(A.JOBTYPE = 1, DATE_FORMAT(D.RESERVATIONDATESTART, '%d %b %Y'), DATE_FORMAT(D.RESERVATIONDATEEND, '%d %b %Y')) AS JOBDATE,
                                IF(A.JOBTYPE = 1, DATE_FORMAT(D.RESERVATIONTIMESTART, '%H:%i'), DATE_FORMAT(D.RESERVATIONTIMEEND, '%H:%i')) AS JOBTIME,
                                D.CUSTOMERNAME, A.IDSTATUSPROCESSCARDROPOFFPICKUP, E.STATUSPROCESSNAME, D.CUSTOMERCONTACT, IFNULL(G.BRAND, '-') AS BRAND,
                                IFNULL(G.MODEL, '-') AS MODEL, IF(G.TRANSMISSION = 1, 'Manual', 'Matic') AS TRANSMISSION, IFNULL(G.PLATNUMBER, '-') AS PLATNUMBER,
                                D.REMARK, A.LOCATIONDROPOFF, A.LOCATIONPICKUP, A.NOTES, C.IDRESERVATION, B.IDRESERVATIONDETAILS
                        FROM t_schedulecardropoffpickup A
                        LEFT JOIN t_schedulecar B ON A.IDSCHEDULECAR = B.IDSCHEDULECAR
                        LEFT JOIN t_reservationdetails C ON B.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS
                        LEFT JOIN t_reservation D ON C.IDRESERVATION = D.IDRESERVATION
                        LEFT JOIN m_statusprocesscardropoffpickup E ON A.IDSTATUSPROCESSCARDROPOFFPICKUP = E.IDSTATUSPROCESSCARDROPOFFPICKUP
                        LEFT JOIN m_source F ON D.IDSOURCE = F.IDSOURCE
                        LEFT JOIN t_carvendor G ON B.IDCARVENDOR = G.IDCARVENDOR
                        WHERE A.IDSCHEDULECARDROPOFFPICKUP = ".$idScheduleCarDropOffPickUp."
                        LIMIT 1"
                    );
		$row    =	$query->row_array();

		if (isset($row)) return $row;
		return false;
	}
	
	public function getMaxStatusProcessDropOffPickUpCar(){	
		$query  =	$this->db->query(
                        "SELECT IDSTATUSPROCESSCARDROPOFFPICKUP
                        FROM m_statusprocesscardropoffpickup
                        WHERE ISFINISHED = 1
                        LIMIT 1"
                    );
		$row    =	$query->row_array();

		if (isset($row)) return $row['IDSTATUSPROCESSCARDROPOFFPICKUP'];
		return 0;
	}

	public function getTotalCarRentCostRequest(){
		$query	=	$this->db->query(
                        "SELECT COUNT(IDRESERVATIONADDITIONALCOST) AS TOTALADDITIONALCOSTREQUEST
                        FROM t_schedulecardropoffpickup A
                        LEFT JOIN t_schedulecar B ON A.IDSCHEDULECAR = B.IDSCHEDULECAR
                        LEFT JOIN t_reservationadditionalcost C ON B.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS
                        WHERE C.STATUSAPPROVAL = 0 AND C.IDRESERVATIONDETAILS IS NOT NULL
                        LIMIT 1"
                    );
		$row	=	$query->row_array();

		if(isset($row)) return $row['TOTALADDITIONALCOSTREQUEST'];
		return 0;		
	}
}