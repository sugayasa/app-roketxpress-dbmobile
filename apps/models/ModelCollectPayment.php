<?php
class ModelCollectPayment extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}
	
	public function getDataSummaryCollectPayment($idPartnerType, $idPartner){
		
		$conPartner	=	$idPartnerType == "1" ? "IDVENDOR = ".$idPartner : "IDDRIVER = ".$idPartner;
		$query		=	$this->db->query("SELECT COUNT(A.IDRESERVATION) TOTALRESERVATIONCOLLECTPAYMENT, SUM(B.AMOUNTIDR) AS TOTALAMOUNTCOLLECTPAYMENT,
												 DATE_FORMAT(MAX(A.DATECOLLECT), '%d %b %Y') AS LASTDATECOLLECT
										  FROM t_collectpayment A
										  LEFT JOIN t_reservationpayment B ON A.IDRESERVATIONPAYMENT = B.IDRESERVATIONPAYMENT
										  WHERE A.IDPARTNERTYPE = ".$idPartnerType." AND ".$conPartner." AND A.STATUSSETTLEMENTREQUEST != 2
										  GROUP BY A.IDPARTNERTYPE
										  ORDER BY A.DATECOLLECT"
										);
		$row		=	$query->row_array();

		if(isset($row)){
			return $row;
		}
		
		return array(
					"TOTALRESERVATIONCOLLECTPAYMENT"=>	0,
					"TOTALAMOUNTCOLLECTPAYMENT"		=>	0,
					"LASTDATECOLLECT"				=>	"-"
				);
		
	}
	
	public function getDataActiveCollectPayment($idPartnerType, $idPartner){
		
		$conPartner	=	$idPartnerType == "1" ? "IDVENDOR = ".$idPartner : "IDDRIVER = ".$idPartner;
		$baseQuery	=	"SELECT A.IDCOLLECTPAYMENT, DATE_FORMAT(A.DATECOLLECT, '%d %b %Y') AS DATECOLLECT, CONCAT('".URL_SOURCE_LOGO."', C.LOGO) AS SOURCELOGOURL,
								C.SOURCENAME, B.BOOKINGCODE, B.CUSTOMERNAME, D.DESCRIPTION, D.AMOUNTCURRENCY, D.AMOUNT, D.EXCHANGECURRENCY, D.AMOUNTIDR, A.STATUSSETTLEMENTREQUEST,
								CASE
									WHEN A.STATUSSETTLEMENTREQUEST = 0 THEN 'Unsubmitted'
									WHEN A.STATUSSETTLEMENTREQUEST = 1 THEN 'Waiting Approval'
									WHEN A.STATUSSETTLEMENTREQUEST = 2 THEN 'Approved'
									WHEN A.STATUSSETTLEMENTREQUEST = -1 THEN 'Rejected'
									ELSE '-'
								END AS STRSTATUSSETTLEMENTREQUEST
						FROM t_collectpayment A
						LEFT JOIN t_reservation B ON A.IDRESERVATION = B.IDRESERVATION
						LEFT JOIN m_source C ON B.IDSOURCE = C.IDSOURCE
						LEFT JOIN t_reservationpayment D ON A.IDRESERVATIONPAYMENT = D.IDRESERVATIONPAYMENT
						WHERE A.IDPARTNERTYPE = ".$idPartnerType." AND ".$conPartner." AND A.STATUSSETTLEMENTREQUEST != 2
						ORDER BY DATECOLLECT DESC";
		$query		=	$this->db->query($baseQuery);
		$result		=	$query->result();
		
		if(isset($result)){
			return $result;
		}
		
		return array();
	
	}
	
	public function getDataHistoryCollectPayment($idPartnerType, $idPartner, $yearMonth){
		
		$conPartner	=	$idPartnerType == "1" ? "IDVENDOR = ".$idPartner : "IDDRIVER = ".$idPartner;
		$baseQuery	=	"SELECT A.IDCOLLECTPAYMENT, DATE_FORMAT(A.DATECOLLECT, '%d %b %Y') AS DATECOLLECT, CONCAT('".URL_SOURCE_LOGO."', C.LOGO) AS SOURCELOGOURL,
								C.SOURCENAME, B.BOOKINGCODE, B.CUSTOMERNAME, D.DESCRIPTION, D.AMOUNTCURRENCY, D.AMOUNT, D.EXCHANGECURRENCY, D.AMOUNTIDR, A.STATUSSETTLEMENTREQUEST,
								CASE
									WHEN A.STATUSSETTLEMENTREQUEST = 0 THEN 'Unsubmitted'
									WHEN A.STATUSSETTLEMENTREQUEST = 1 THEN 'Waiting Approval'
									WHEN A.STATUSSETTLEMENTREQUEST = 2 THEN 'Approved'
									WHEN A.STATUSSETTLEMENTREQUEST = -1 THEN 'Rejected'
									ELSE '-'
								END AS STRSTATUSSETTLEMENTREQUEST
						FROM t_collectpayment A
						LEFT JOIN t_reservation B ON A.IDRESERVATION = B.IDRESERVATION
						LEFT JOIN m_source C ON B.IDSOURCE = C.IDSOURCE
						LEFT JOIN t_reservationpayment D ON A.IDRESERVATIONPAYMENT = D.IDRESERVATIONPAYMENT
						WHERE A.IDPARTNERTYPE = ".$idPartnerType." AND ".$conPartner." AND LEFT(A.DATECOLLECT, 7) = '".$yearMonth."' AND A.STATUSSETTLEMENTREQUEST = 2
						ORDER BY DATECOLLECT DESC";
		$query		=	$this->db->query($baseQuery);
		$result		=	$query->result();
		
		if(isset($result)){
			return $result;
		}
		
		return array();
	
	}
	
	public function getDetailCollectPayment($idPartnerType, $idPartner, $idCollectPayment){
		
		$conPartner	=	$idPartnerType == "1" ? "A.IDVENDOR = ".$idPartner : "A.IDDRIVER = ".$idPartner;
		$baseQuery	=	"SELECT CONCAT('".URL_SOURCE_LOGO."', C.LOGO) AS SOURCELOGOURL, C.SOURCENAME, B.BOOKINGCODE, B.RESERVATIONTITLE, B.RESERVATIONTIMESTART,
								B.CUSTOMERNAME, B.NUMBEROFADULT, B.NUMBEROFCHILD, B.NUMBEROFINFANT, B.CUSTOMERCONTACT, B.HOTELNAME, B.PICKUPLOCATION, B.DROPOFFLOCATION,
								B.REMARK, B.TOURPLAN, DATE_FORMAT(A.DATECOLLECT, '%d %b %Y') AS DATECOLLECT, D.DESCRIPTION, D.AMOUNTCURRENCY, D.AMOUNT, D.EXCHANGECURRENCY,
								D.AMOUNTIDR, A.STATUS, A.STATUSSETTLEMENTREQUEST, A.IDRESERVATION, A.DATECOLLECT, A.IDWITHDRAWALRECAP,
								CASE
									WHEN A.STATUSSETTLEMENTREQUEST = 0 THEN 'Unsubmitted'
									WHEN A.STATUSSETTLEMENTREQUEST = 1 THEN 'Waiting Approval'
									WHEN A.STATUSSETTLEMENTREQUEST = 2 THEN 'Approved'
									WHEN A.STATUSSETTLEMENTREQUEST = -1 THEN 'Rejected'
									ELSE '-'
								END AS STRSTATUSSETTLEMENTREQUEST
						FROM t_collectpayment A
						LEFT JOIN t_reservation B ON A.IDRESERVATION = B.IDRESERVATION
						LEFT JOIN m_source C ON B.IDSOURCE = C.IDSOURCE
						LEFT JOIN t_reservationpayment D ON A.IDRESERVATIONPAYMENT = D.IDRESERVATIONPAYMENT
						WHERE A.IDPARTNERTYPE = ".$idPartnerType." AND ".$conPartner." AND A.IDCOLLECTPAYMENT = '".$idCollectPayment."'
						LIMIT 1";
		$query		=	$this->db->query($baseQuery);
		$row		=	$query->row_array();

		if(isset($row)){
			return $row;
		}
		
		return false;
	
	}
	
	public function detailCollectPaymentDate($idReservation, $idPartnerType, $idPartner, $dateCollect){
		
		$fieldIdPartner	=	$idPartnerType == 1 ? "A.IDVENDOR" : "A.IDDRIVER";
		$query			=	$this->db->query("SELECT CONCAT('".URL_SOURCE_LOGO."', C.LOGO) AS SOURCELOGOURL, C.SOURCENAME, B.BOOKINGCODE, B.RESERVATIONTITLE, B.RESERVATIONTIMESTART,
													 B.CUSTOMERNAME, B.NUMBEROFADULT, B.NUMBEROFCHILD, B.NUMBEROFINFANT, B.CUSTOMERCONTACT, B.HOTELNAME, B.PICKUPLOCATION, B.DROPOFFLOCATION,
													 B.REMARK, B.TOURPLAN, DATE_FORMAT(A.DATECOLLECT, '%d %b %Y') AS DATECOLLECT, GROUP_CONCAT(D.DESCRIPTION) AS DESCRIPTION,
													 GROUP_CONCAT(DISTINCT(D.AMOUNTCURRENCY)) AS AMOUNTCURRENCY, SUM(D.AMOUNT) AS AMOUNT, D.EXCHANGECURRENCY, SUM(D.AMOUNTIDR) AS AMOUNTIDR,
													 MIN(A.STATUS) AS STATUS, MIN(A.STATUSSETTLEMENTREQUEST) AS STATUSSETTLEMENTREQUEST, GROUP_CONCAT(A.IDCOLLECTPAYMENT) AS STRARRIDCOLLECTPAYMENT,
													CASE
														WHEN MIN(A.STATUSSETTLEMENTREQUEST) = 0 THEN 'Unsubmitted'
														WHEN MIN(A.STATUSSETTLEMENTREQUEST) = 1 THEN 'Waiting Approval'
														WHEN MIN(A.STATUSSETTLEMENTREQUEST) = 2 THEN 'Approved'
														WHEN MIN(A.STATUSSETTLEMENTREQUEST) = -1 THEN 'Rejected'
														ELSE '-'
													END AS STRSTATUSSETTLEMENTREQUEST
											 FROM t_collectpayment A
											 LEFT JOIN t_reservation B ON A.IDRESERVATION = B.IDRESERVATION
											 LEFT JOIN m_source C ON B.IDSOURCE = C.IDSOURCE
											 LEFT JOIN t_reservationpayment D ON A.IDRESERVATIONPAYMENT = D.IDRESERVATIONPAYMENT
											 WHERE A.IDRESERVATION = ".$idReservation." AND ".$fieldIdPartner." = ".$idPartner." AND A.DATECOLLECT = '".$dateCollect."'
											 GROUP BY A.IDRESERVATION
											 ORDER BY A.STATUS ASC");
		$row			=	$query->row_array();

		if (isset($row)){
			return $row;
		}
		
		return false;
		
	}
	
	public function getDataHistoryDetailCollectPayment($strArrIdCollectPayment){
		
		$baseQuery	=	"SELECT DESCRIPTION, IF(SETTLEMENTRECEIPT = '', '', CONCAT('".URL_COLLECT_PAYMENT_RECEIPT."', SETTLEMENTRECEIPT)) AS SETTLEMENTRECEIPT,
								USERINPUT, DATE_FORMAT(DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMEINPUT, STATUS
						FROM t_collectpaymenthistory
						WHERE IDCOLLECTPAYMENT IN (".$strArrIdCollectPayment.")
						ORDER BY DATE_FORMAT(DATETIMEINPUT, '%Y-%m-%d %H:%i') DESC";
		$query		=	$this->db->query($baseQuery);
		$result		=	$query->result();
		
		if(isset($result)){
			return $result;
		}
		
		return array();
	
	}

	public function getTotalSettlementRequest(){
		
		$query	=	$this->db->query("SELECT COUNT(IDCOLLECTPAYMENT) AS TOTALSETTLEMENTREQUEST
									  FROM t_collectpayment
									  WHERE STATUSSETTLEMENTREQUEST = 1
									  LIMIT 1");
		$row	=	$query->row_array();

		if(isset($row)){
			return $row['TOTALSETTLEMENTREQUEST'];
		}
		
		return 0;
		
	}
	
}