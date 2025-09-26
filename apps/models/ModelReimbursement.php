<?php
class ModelReimbursement extends CI_Model {
	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}
	
	public function getListReimbursement($idPartnerType, $idPartner, $dateStart, $dateEnd){
		$con_partnerType=	$idPartnerType == 1 ? "IDVENDOR = ".$idPartner : "IDDRIVER = ".$idPartner;
		$query			=	$this->db->query(
								"SELECT DATE_FORMAT(RECEIPTDATE, '%d %b %Y') AS RECEIPTDATESTR, DESCRIPTION, NOMINAL, REQUESTBYNAME, 
										CONCAT('".URL_REIMBURSEMENT_IMAGE."', RECEIPTIMAGE) AS RECEIPTIMAGE, IFNULL(APPROVALBYNAME, '-') AS APPROVALBYNAME,
										IFNULL(DATE_FORMAT(APPROVALDATETIME, '%d %b %Y %H:%i'), '-') AS APPROVALDATETIME, NOTES, STATUS,
										CASE
											WHEN STATUS = 0 THEN 'Waiting for approval'
											WHEN STATUS = 1 THEN 'Approved'
											WHEN STATUS = -1 THEN 'Rejected'
											WHEN STATUS = -2 THEN 'Cancelled'
											ELSE '-'
										END AS STRSTATUS
								 FROM t_reimbursement
								 WHERE DATE(RECEIPTDATE) BETWEEN '".$dateStart."' AND '".$dateEnd."' AND ".$con_partnerType."
								 ORDER BY RECEIPTDATE ASC, DESCRIPTION"
							);
		$result	=	$query->result();

		if (isset($result)) return $result;
		return false;
	}
	
	public function getTotalReimbursementRequest(){
		$query	=	$this->db->query(
						"SELECT COUNT(IDREIMBURSEMENT) AS TOTALREIMBURSEMENTREQUEST
						FROM t_reimbursement
						WHERE STATUS = 0
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row['TOTALREIMBURSEMENTREQUEST'];
		return 0;		
	}
}