<?php
class ModelDashboard extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function getTotalActiveFee($idPartnerType, $idPartner){
		$fieldWhere	=	$idPartnerType == "1" ? "IDVENDOR" : "IDDRIVER";
		$query		=	$this->db->query(
							"SELECT COUNT(IDFEE) AS TOTALJOBS, SUM(FEENOMINAL) AS TOTALFEE
							FROM t_fee
							WHERE ".$fieldWhere." = ".$idPartner." AND WITHDRAWSTATUS = 0
							GROUP BY ".$fieldWhere."
							LIMIT 1"
						);
		$row		=	$query->row_array();

		if (isset($row)) return $row;
		return array(
			"TOTALJOBS"	=>	0,
			"TOTALFEE"	=>	0
		);
	}

	public function getTotalActiveOrder($idPartnerType, $idPartner){
		$table			=	$idPartnerType == "1" ? "t_schedulevendor" : "t_scheduledriver";
		$fieldOrder		=	$idPartnerType == "1" ? "C.IDSCHEDULEVENDOR" : "C.IDSCHEDULEDRIVER";
		$fieldWhere		=	$idPartnerType == "1" ? "A.IDVENDOR" : "C.IDDRIVER";
		$tableStatusJoin=	$idPartnerType == "1" ? "m_statusprocessvendor" : "m_statusprocessdriver";
		$fieldStatusJoin=	$idPartnerType == "1" ? "IDSTATUSPROCESSVENDOR" : "IDSTATUSPROCESSDRIVER";
		$query			=	$this->db->query(
								"SELECT COUNT(A.IDRESERVATIONDETAILS) AS TOTALACTIVEORDER
								 FROM t_reservationdetails A
								 LEFT JOIN t_reservation B ON A.IDRESERVATION = B.IDRESERVATION
								 LEFT JOIN ".$table." C ON A.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS
								 LEFT JOIN ".$tableStatusJoin." D ON C.STATUSPROCESS = D.".$fieldStatusJoin."
								 WHERE ".$fieldWhere." = '".$idPartner."' AND A.STATUS = 1 AND (D.ISFINISHED = 0 OR C.STATUS IN (1,2))
								 GROUP BY ".$fieldWhere."
								 ORDER BY ".$fieldOrder." DESC, B.RESERVATIONTIMESTART"
							);
		$row			=	$query->row_array();

		if(isset($row)) return $row['TOTALACTIVEORDER'];		
		return 0;
	}

	public function getTotalActiveOrderDropOffPickupCar($idPartnerType, $idPartner){
		if($idPartnerType == 1) return 0;
		$query	=	$this->db->query(
						"SELECT COUNT(A.IDSCHEDULECARDROPOFFPICKUP) AS TOTALACTIVEORDER
						FROM t_schedulecardropoffpickup A
						LEFT JOIN m_statusprocesscardropoffpickup B ON A.IDSTATUSPROCESSCARDROPOFFPICKUP = B.IDSTATUSPROCESSCARDROPOFFPICKUP
						WHERE A.IDDRIVER = ".$idPartner." AND B.ISFINISHED = 0
						GROUP BY A.IDDRIVER
						ORDER BY A.IDDRIVER DESC"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row['TOTALACTIVEORDER'];		
		return 0;
	}

	public function getDataActiveOrder($idPartnerType, $idPartner){
		$table		=	$idPartnerType == "1" ? "t_schedulevendor" : "t_scheduledriver";
		$fieldOrder	=	$idPartnerType == "1" ? "C.IDSCHEDULEVENDOR" : "C.IDSCHEDULEDRIVER";
		$fieldWhere	=	$idPartnerType == "1" ? "A.IDVENDOR" : "C.IDDRIVER";
		$tableJoin	=	$idPartnerType == "1" ? "m_statusprocessvendor" : "m_statusprocessdriver";
		$fieldJoin	=	$idPartnerType == "1" ? "IDSTATUSPROCESSVENDOR" : "IDSTATUSPROCESSDRIVER";
		$query		=	$this->db->query(
							"SELECT A.IDRESERVATIONDETAILS, A.PRODUCTNAME, B.RESERVATIONTITLE, LEFT(B.RESERVATIONTIMESTART, 5) AS RESERVATIONTIMESTART,
									B.CUSTOMERNAME, CONCAT((B.NUMBEROFADULT + B.NUMBEROFCHILD + B.NUMBEROFINFANT), ' Pax') AS PAX,
									IFNULL(D.STATUSPROCESSNAME, 'Unprocessed') AS STATUSPROCESSNAME, DATE_FORMAT(A.SCHEDULEDATE, '%d %b %Y') AS SCHEDULEDATE
							FROM t_reservationdetails A
							LEFT JOIN t_reservation B ON A.IDRESERVATION = B.IDRESERVATION
							LEFT JOIN ".$table." C ON A.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS
							LEFT JOIN ".$tableJoin." D ON C.STATUSPROCESS = D.".$fieldJoin."
							WHERE ".$fieldWhere." = '".$idPartner."' AND A.STATUS = 1 AND B.STATUS IN (1,2,3) AND C.STATUS IN (1,2)
							GROUP BY A.IDRESERVATIONDETAILS
							ORDER BY ".$fieldOrder." DESC, B.RESERVATIONTIMESTART"
						);
		$result		=	$query->result();

		if (isset($result)) return $result;
		return array();
	}

	public function getDateRangeOrder($idPartnerType, $idPartner){
		$table		=	$idPartnerType == "1" ? "t_schedulevendor" : "t_scheduledriver";
		$fieldWhere	=	$idPartnerType == "1" ? "A.IDVENDOR" : "C.IDDRIVER";
		$query		=	$this->db->query(
							"SELECT MIN(A.SCHEDULEDATE) AS DATEORDERSTART, MAX(A.SCHEDULEDATE) AS DATEORDEREND
							 FROM t_reservationdetails A
							 LEFT JOIN t_reservation B ON A.IDRESERVATION = B.IDRESERVATION
							 LEFT JOIN ".$table." C ON A.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS
							 WHERE ".$fieldWhere." = '".$idPartner."' AND A.STATUS = 1 AND B.STATUS IN (2,3) AND C.STATUS IN (1,2)
							 GROUP BY ".$fieldWhere."
							 LIMIT 1"
						);
		$row		=	$query->row_array();

		if (isset($row)) return $row;
		return array(
			"DATEORDERSTART"	=>	date('Y-m-d'),
			"DATEORDEREND"		=>	date('Y-m-d')
		);		
	}

	public function getDataNotification($page, $idPartnerType, $idPartner){
		$startid	=	($page * 1 - 1) * 20;
		$query		=	$this->db->query(
							"SELECT A.IDMESSAGEPARTNER, A.IDPRIMARY, B.ACTIVITY, B.MESSAGEPARTNERTYPE, A.TITLE, A.MESSAGE,
								   DATE_FORMAT(A.DATETIMEINSERT, '%d %b %Y %H:%i') AS DATETIME, B.COLOR
							FROM t_messagepartner A
							LEFT JOIN m_messagepartnertype B ON A.IDMESSAGEPARTNERTYPE = B.IDMESSAGEPARTNERTYPE
							WHERE A.IDPARTNERTYPE = ".$idPartnerType." AND A.IDPARTNER = ".$idPartner."
							ORDER BY A.DATETIMEINSERT DESC
							LIMIT ".$startid.", 20"
						);
		$result	= $query->result();

		if (isset($result)) return $result;
		return array();		
	}

	public function getCarVendorList($idVendor){
		$query	=	$this->db->query(
						"SELECT C.CARTYPE, A.BRAND, A.MODEL, A.PLATNUMBER, A.YEAR, A.TRANSMISSION, A.COLOR, A.DESCRIPTION,
								A.STATUS, A.IDCARVENDOR
						FROM t_carvendor A
						LEFT JOIN m_vendor B ON A.IDVENDOR = B.IDVENDOR
						LEFT JOIN m_cartype C ON A.IDCARTYPE = C.IDCARTYPE
						WHERE A.IDVENDOR = ".$idVendor."
						ORDER BY B.NAME, C.CARTYPE, A.BRAND, A.MODEL, A.PLATNUMBER"
					);
		$result	=	$query->result();

		if (isset($result)) return $result;
		return array();		
	}
	
	public function getSecretPINStatus($idPartnerType, $idPartner){
		$table		=	$idPartnerType == "1" ? "m_vendor" : "m_driver";
		$fieldWhere	=	$idPartnerType == "1" ? "IDVENDOR" : "IDDRIVER";
		$query		=	$this->db->query(
							"SELECT SECRETPINSTATUS, DATE_FORMAT(SECRETPINLASTUPDATE, '%d %b %Y %H:%i') AS SECRETPINLASTUPDATE
							 FROM ".$table."
							 WHERE ".$fieldWhere." = '".$idPartner."'
							 LIMIT 1"
						);
		$row		=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"SECRETPINSTATUS"		=>	1,
			"SECRETPINLASTUPDATE"	=>	"-"
		);
	}
	
	public function getDataCollectPayment($idPartnerType, $idPartner){
		$conPartner	=	$idPartnerType == "1" ? "IDVENDOR = ".$idPartner : "IDDRIVER = ".$idPartner;
		$query		=	$this->db->query(
							"SELECT COUNT(A.IDRESERVATION) TOTALRESERVATIONCOLLECTPAYMENT, SUM(B.AMOUNTIDR) AS TOTALAMOUNTCOLLECTPAYMENT
							FROM t_collectpayment A
							LEFT JOIN t_reservationpayment B ON A.IDRESERVATIONPAYMENT = B.IDRESERVATIONPAYMENT
							WHERE A.IDPARTNERTYPE = ".$idPartnerType." AND ".$conPartner." AND A.STATUSSETTLEMENTREQUEST != 2
							GROUP BY A.IDPARTNERTYPE"
						);
		$row		=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"TOTALRESERVATIONCOLLECTPAYMENT"=>	0,
			"TOTALAMOUNTCOLLECTPAYMENT"		=>	0
		);		
	}
	
	public function getDataDriverLoanPrepaidCapital($idDriver, $typeLoanPrepaidCapital){
		$query	=	$this->db->query(
						"SELECT SUM(IF(A.TYPE = 'D', A.AMOUNT, A.AMOUNT * -1)) AS TOTALBALANCE,
								DATE_FORMAT(A.DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMELASTTRANSACTION
						FROM t_loandriverrecord A
						LEFT JOIN m_loantype B ON A.IDLOANTYPE = B.IDLOANTYPE
						WHERE A.IDDRIVER = ".$idDriver." AND B.STATUSLOANCAPITAL = ".$typeLoanPrepaidCapital."
						GROUP BY A.IDDRIVER
						ORDER BY A.DATETIMEINPUT DESC"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"TOTALBALANCE"				=>	0,
			"DATETIMELASTTRANSACTION"	=>	"-"
		);		
	}
	
	public function getDataDepositBalanceVendor($idVendor){
		$query	=	$this->db->query(
						"SELECT SUM(AMOUNT) AS DEPOSITBALANCE, DATE_FORMAT(MAX(DATETIMEINPUT), '%d %b %Y') AS LASTDEPOSITTRANSACTION
						FROM t_depositvendorrecord
						WHERE IDVENDOR = ".$idVendor."
						GROUP BY IDVENDOR
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"DEPOSITBALANCE"		=>	0,
			"LASTDEPOSITTRANSACTION"=>	0
		);		
	}
	
	public function isDriverAllowReviewBonusPunishment($idDriver){
		$query	=	$this->db->query(
						"SELECT REVIEWBONUSPUNISHMENT FROM m_driver
						WHERE IDDRIVER = ".$idDriver."
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) {
			$reviewBonusPunishment			=	$row['REVIEWBONUSPUNISHMENT'];
			$isAllowReviewBonusPunishment	=	$reviewBonusPunishment == 1 ? true : false;
			return $isAllowReviewBonusPunishment;
		}
		
		return false;
	}

}