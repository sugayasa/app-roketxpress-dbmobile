<?php
class ModelOrder extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function listOrderByDate($idPartnerType, $idPartner, $dateStart, $dateEnd, $showActiveOnly, $unconfirmStatus){
		$table					=	$idPartnerType == "1" ? "t_schedulevendor" : "t_scheduledriver";
		$fieldGroup				=	$idPartnerType == "1" ? "C.IDSCHEDULEVENDOR" : "C.IDSCHEDULEDRIVER";
		$fieldWhere				=	$idPartnerType == "1" ? "C.IDVENDOR" : "C.IDDRIVER";
		$tableJoin				=	$idPartnerType == "1" ? "m_statusprocessvendor" : "m_statusprocessdriver";
		$fieldJoin				=	$idPartnerType == "1" ? "IDSTATUSPROCESSVENDOR" : "IDSTATUSPROCESSDRIVER";
		$con_date				=	isset($showActiveOnly) && ($showActiveOnly == 1 || $showActiveOnly == "1") ? "1=1" : "A.SCHEDULEDATE BETWEEN '".$dateStart."' AND '".$dateEnd."'";
		$con_activeOnly			=	isset($showActiveOnly) && ($showActiveOnly == 1 || $showActiveOnly == "1") ? "(D.ISFINISHED = 0 OR C.STATUS IN (1,2))" : "1=1";
		$con_unconfirmStatus	=	isset($unconfirmStatus) && ($unconfirmStatus == 1 || $unconfirmStatus == "1") ? "C.STATUSCONFIRM = 0" : "1=1";
		$fieldReservationTitle	=	"B.RESERVATIONTITLE";
		$query					=	$this->db->query(
										"SELECT A.IDRESERVATIONDETAILS, '".$idPartnerType."' AS PARTNERTYPE, ".$fieldReservationTitle.",
												 B.RESERVATIONTIMESTART, B.CUSTOMERNAME, (B.NUMBEROFADULT + B.NUMBEROFCHILD + B.NUMBEROFINFANT) AS PAX,
												 A.PRODUCTNAME, C.STATUSPROCESS, IFNULL(D.STATUSPROCESSNAME, 'Unprocessed') AS STATUSPROCESSNAME, A.STATUS,
												 IF(A.STATUS = 1, 'Active', 'Canceled') AS STATUSSTR, DATE_FORMAT(A.SCHEDULEDATE, '%d %b %Y') AS SCHEDULEDATE,
												 A.IDRESERVATION, A.SCHEDULEDATE AS SCHEDULEDATEDB, 0 AS TOTALAMOUNTIDRCOLLECTPAYMENT, C.STATUSCONFIRM
											FROM t_reservationdetails A
											LEFT JOIN t_reservation B ON A.IDRESERVATION = B.IDRESERVATION
											LEFT JOIN ".$table." C ON A.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS
											LEFT JOIN ".$tableJoin." D ON C.STATUSPROCESS = D.".$fieldJoin."
											WHERE ".$fieldWhere." = '".$idPartner."' AND ".$con_date." AND ".$con_activeOnly." AND ".$con_unconfirmStatus." AND C.IDRESERVATIONDETAILS IS NOT NULL
											GROUP BY ".$fieldGroup." ASC
											ORDER BY SCHEDULEDATEDB"
									);
		$result					=	$query->result();

		if (isset($result)) return $result;
		return false;		
	}
	
	public function getDetailOrder($idReservationDetails, $idPartnerType, $idPartner=0){	
		$table				=	$idPartnerType == "1" ? "t_schedulevendor" : "t_scheduledriver";
		$tableJoin			=	$idPartnerType == "1" ? "m_statusprocessvendor" : "m_statusprocessdriver";
		$fieldJoin			=	$idPartnerType == "1" ? "IDSTATUSPROCESSVENDOR" : "IDSTATUSPROCESSDRIVER";
		$addField			=	$idPartnerType == "1" ? "C.IDVENDOR" : "C.IDDRIVER";
		$fieldPartnerName	=	$idPartnerType == "1" ? "'-'" : "C.DRIVERNAME";
		$fieldPartnerPhone	=	$idPartnerType == "1" ? "'-'" : "C.DRIVERPHONENUMBER";
		$fieldCarBrandModel	=	$idPartnerType == "1" ? "'-'" : "C.CARBRANDMODEL";
		$fieldCarNumberPlate=	$idPartnerType == "1" ? "'-'" : "C.CARNUMBERPLATE";
		$fieldIdDriverGroup	=	$idPartnerType == "1" ? "'-'" : "C.IDDRIVERGROUPMEMBER";
		$query				=	$this->db->query(
									"SELECT B.RESERVATIONTITLE, B.RESERVATIONTIMESTART, B.CUSTOMERNAME, B.NUMBEROFADULT, B.NUMBEROFCHILD,
											B.NUMBEROFINFANT, A.PRODUCTNAME, C.STATUSPROCESS, IFNULL(D.STATUSPROCESSNAME, 'Unprocessed') AS STATUSPROCESSNAME,
											A.STATUS, IF(A.STATUS = 1, 'Active', 'Canceled') AS STATUSSTR, B.CUSTOMERCONTACT, B.HOTELNAME, B.PICKUPLOCATION,
											B.DROPOFFLOCATION, B.REMARK, B.TOURPLAN, IF(LENGTH(B.SPECIALREQUEST) <= 3, '', B.SPECIALREQUEST) AS SPECIALREQUEST,
											DATE_FORMAT(A.SCHEDULEDATE, '%d %b %Y') AS SCHEDULEDATE, A.STATUS AS RESERVATIONSTATUS, C.STATUS AS SCHEDULESTATUS,
											".$addField." AS IDPARTNERPRIMARY, A.SCHEDULEDATE AS SCHEDULEDATESTR, B.IDRESERVATION, C.STATUSCONFIRM, A.NOMINAL, A.NOTES,
											CONCAT('".URL_SOURCE_LOGO."', E.LOGO) AS SOURCELOGOURL, E.SOURCENAME, DATE_FORMAT(A.SCHEDULEDATE, '%d-%m-%Y') AS SCHEDULEDATEPARAMNOTIF,
											IFNULL(DATE_FORMAT(C.DATETIMECONFIRM, '%d %b %Y %H:%i'), '-') AS DATETIMECONFIRM, DATE_FORMAT(A.SCHEDULEDATE, '%d %m %Y') AS SCHEDULEDATETEXT,
											B.BOOKINGCODE, IFNULL(B.URLDETAILPRODUCT, '') AS URLDETAILPRODUCT, IFNULL(B.URLPICKUPLOCATION, '') AS URLPICKUPLOCATION,
											".$fieldPartnerName." AS DRIVERNAME, ".$fieldPartnerPhone." AS DRIVERPHONENUMBER, ".$fieldCarBrandModel." AS CARBRANDMODEL,
											".$fieldCarNumberPlate." AS CARNUMBERPLATE, B.IDSOURCE, ".$fieldIdDriverGroup." AS IDDRIVERGROUPMEMBER
									 FROM t_reservationdetails A
									 LEFT JOIN t_reservation B ON A.IDRESERVATION = B.IDRESERVATION
									 LEFT JOIN ".$table." C ON A.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS
									 LEFT JOIN ".$tableJoin." D ON C.STATUSPROCESS = D.".$fieldJoin."
									 LEFT JOIN m_source E ON B.IDSOURCE = E.IDSOURCE
									 WHERE A.IDRESERVATIONDETAILS = ".$idReservationDetails." AND ".$addField." = ".$idPartner." AND A.STATUS = 1
									 GROUP BY A.IDRESERVATIONDETAILS
									 ORDER BY B.RESERVATIONTIMESTART"
								);
		$row				=	$query->row_array();

		if (isset($row)) return $row;
		return false;
	}
	
	public function getDetailOrderCancel($idReservation){	
		$query	=	$this->db->query(
						"SELECT A.RESERVATIONTITLE, A.RESERVATIONTIMESTART, A.CUSTOMERNAME, A.NUMBEROFADULT, A.NUMBEROFCHILD,
								A.NUMBEROFINFANT, DATE_FORMAT(A.RESERVATIONDATESTART, '%d %b %Y') AS SCHEDULEDATE,
								CONCAT('".URL_SOURCE_LOGO."', B.LOGO) AS SOURCELOGOURL, B.SOURCENAME, A.BOOKINGCODE
						 FROM t_reservation A
						 LEFT JOIN m_source B ON A.IDSOURCE = B.IDSOURCE
						 WHERE A.IDRESERVATION = ".$idReservation."
						 GROUP BY A.IDRESERVATION
						 ORDER BY A.IDRESERVATION
						 LIMIT 1"
					);
		$row	=	$query->row_array();

		if (isset($row)) return $row;
		return false;
	}
	
	public function getTimelineOrder($idReservationDetails){
		$query	=	$this->db->query(
						"SELECT DESCRIPTION, DATE_FORMAT(DATETIME, '%d %b %Y %H:%i') AS DATETIME
						 FROM t_reservationdetailstimeline
						 WHERE IDRESERVATIONDETAILS = '".$idReservationDetails."'
						 ORDER BY DATETIME ASC"
					);
		$result	=	$query->result();

		if (isset($result)) return $result;
		return [];		
	}
	
	public function getListAdditionalCost($idDriver, $idReservationDetails){
		$query	=	$this->db->query(
						"SELECT B.ADDITIONALCOSTTYPE, A.DESCRIPTION, A.NOMINAL,
								DATE_FORMAT(A.DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMEINPUT,
								CASE
									WHEN A.STATUSAPPROVAL = 0 THEN 'Waiting for approval'
									WHEN A.STATUSAPPROVAL = 1 THEN 'Approved'
									WHEN A.STATUSAPPROVAL = -1 THEN 'Rejected'
									ELSE '-'
								END AS STRSTATUSAPPROVAL
						 FROM t_reservationadditionalcost A
						 LEFT JOIN m_additionalcosttype B ON A.IDADDITIONALCOSTTYPE = B.IDADDITIONALCOSTTYPE
						 WHERE A.IDDRIVER = '".$idDriver."' AND A.IDRESERVATIONDETAILS = '".$idReservationDetails."'
						 ORDER BY A.DATETIMEINPUT ASC"
					);
		$result	=	$query->result();

		if (isset($result)) return $result;
		return [];		
	}
	
	public function detailCollectPayment($idReservation, $idPartnerType, $idPartner, $dateSchedule){
		$fieldIdPartner	=	$idPartnerType == 1 ? "A.IDVENDOR" : "A.IDDRIVER";
		$query			=	$this->db->query(
								"SELECT A.IDCOLLECTPAYMENT, A.IDRESERVATIONPAYMENT, GROUP_CONCAT(B.DESCRIPTION) AS DESCRIPTION, SUM(B.AMOUNTIDR) AS TOTALAMOUNTIDRCOLLECTPAYMENT,
										GROUP_CONCAT(DISTINCT(B.AMOUNTCURRENCY)) AS AMOUNTCURRENCY, SUM(B.AMOUNT) AS AMOUNT,
										B.EXCHANGECURRENCY, MIN(A.STATUS) AS STATUS
								 FROM t_collectpayment A
								 LEFT JOIN t_reservationpayment B ON A.IDRESERVATIONPAYMENT = B.IDRESERVATIONPAYMENT
								 WHERE A.IDRESERVATION = ".$idReservation." AND ".$fieldIdPartner." = ".$idPartner." AND 
									   (A.DATECOLLECT = '".$dateSchedule."' OR (A.DATECOLLECT <= '".$dateSchedule."' AND A.STATUS = 0))
								 GROUP BY A.IDRESERVATION
								 ORDER BY A.STATUS ASC"
							);
		$row			=	$query->row_array();

		if (isset($row)) return $row;
		return array(
			"IDCOLLECTPAYMENT"				=>	0,
			"IDRESERVATIONPAYMENT"			=>	0,
			"TOTALAMOUNTIDRCOLLECTPAYMENT"	=>	0,
			"AMOUNTCURRENCY"				=>	"",
			"AMOUNT"						=>	0,
			"EXCHANGECURRENCY"				=>	0,
			"STATUS"						=>	0
		);		
	}
	
	public function isValidCollectPayment($idPartnerType, $idPartner, $idCollectPayment){
		$conPartner	=	$idPartnerType == "1" ? "A.IDVENDOR = ".$idPartner : "A.IDDRIVER = ".$idPartner;
		$baseQuery	=	"SELECT A.IDRESERVATION, A.DATECOLLECT, A.IDRESERVATIONPAYMENT, B.DESCRIPTION FROM t_collectpayment A
						LEFT JOIN t_reservationpayment B ON A.IDRESERVATIONPAYMENT = B.IDRESERVATIONPAYMENT
						WHERE A.IDPARTNERTYPE = ".$idPartnerType." AND ".$conPartner." AND A.IDCOLLECTPAYMENT = '".$idCollectPayment."'
						LIMIT 1";
		$query		=	$this->db->query($baseQuery);
		$row		=	$query->row_array();

		if(isset($row)) return $row;
		return false;	
	}
	
	public function getStrArrIdCollectPaymentByDateReservation($idReservation, $idPartnerType, $idPartner, $dateCollect){	
		$fieldIdPartner	=	$idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";
		$query			=	$this->db->query(
								"SELECT GROUP_CONCAT(IDCOLLECTPAYMENT) AS STRARRIDCOLLECTPAYMENT FROM t_collectpayment
								 WHERE IDRESERVATION = ".$idReservation." AND ".$fieldIdPartner." = ".$idPartner." AND
									   DATECOLLECT <= '".$dateCollect."' AND STATUS = 0
								 GROUP BY IDRESERVATION"
							);
		$row			=	$query->row_array();

		if (isset($row)) return $row['STRARRIDCOLLECTPAYMENT'];
		return false;		
	}
	
	public function isFeeExist($idReservation, $idReservationDetails, $idVendor, $idDriver){
		$baseQuery	=	"SELECT IDFEE FROM t_fee
						WHERE IDRESERVATION = ".$idReservation." AND IDRESERVATIONDETAILS = ".$idReservationDetails." AND IDVENDOR = '".$idVendor."' AND IDDRIVER = '".$idDriver."'
						LIMIT 1";
		$query		=	$this->db->query($baseQuery);
		$row		=	$query->row_array();

		if(isset($row)) return $row;
		return false;
	}
	
	public function getDataTemplateReview($idSource){		
		$query	=	$this->db->query(
						"SELECT TEMPLATENAME, TEMPLATECONTENT FROM t_templaterevieworder
						 WHERE IDSOURCE = ".$idSource." AND STATUS = 1
						 ORDER BY TEMPLATENAME"
					);
		$result	=	$query->result();

		if (isset($result)) return $result;
		return false;
	}
	
	public function getURLReviewOrder($idReservationDetails){
		$query	=	$this->db->query(
						"SELECT IFNULL(GROUP_CONCAT(IFNULL(C.PRODUCTURL, '".URL_TRIPADVISOR_REVIEW."')), '') AS ARRPRODUCTURL, '' AS WAMEURLENCODE FROM t_reservationdetails A
						LEFT JOIN m_product B ON REPLACE(A.PRODUCTNAME, '[Klook] ', '') = B.PRODUCTNAME
						LEFT JOIN m_productreview C ON B.IDPRODUCTREVIEW = C.IDPRODUCTREVIEW
						WHERE A.IDRESERVATIONDETAILS = '".$idReservationDetails."'
						GROUP BY A.IDRESERVATIONDETAILS
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return false;
	}
	
	public function isDataEBookingCoinExist($idReservation){
		$query	=	$this->db->query(
						"SELECT IDEBOOKINGCOIN, STATUS FROM t_ebookingcoin
						WHERE IDRESERVATION = '".$idReservation."'
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return false;
	}
}