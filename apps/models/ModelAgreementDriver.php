<?php
class ModelAgreementDriver extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}
	
	public function getDataNewAgreement($idDriver){
		$query	=	$this->db->query(
						"SELECT A.IDDRIVERAGREEMENT, B.TITLE, B.DESCRIPTION, CONCAT('".URL_BASE_AGREEMENT_MASTER."', B.FILEAGREEMENTMASTER) AS FILEAGREEMENTMASTER,
								DATE_FORMAT(B.DATEDETERMINATION, '%d %b %Y') AS DATEDETERMINATION, A.APPROVALSTATUS,
								CASE 
									WHEN A.APPROVALSTATUS = 0 THEN 'Waiting for signing'
									WHEN A.APPROVALSTATUS = 1 THEN 'Under review'
									WHEN A.APPROVALSTATUS = -1 THEN 'Rejected'
								END AS APPROVALSTATUSSTR
						FROM t_driveragreement A
						LEFT JOIN m_driveragreementmaster B ON A.IDDRIVERAGREEMENTMASTER = B.IDDRIVERAGREEMENTMASTER
						WHERE A.IDDRIVER = ".$idDriver." AND A.APPROVALSTATUS < 2
						ORDER BY B.DATEDETERMINATION ASC
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return new stdClass();
	}
	
	public function getAgreementList($idDriver){
		$query	=	$this->db->query(
						// "SELECT B.TITLE, B.DESCRIPTION, CONCAT('".URL_BASE_AGREEMENT_SIGNED_LETTER."', A.FILEAGREEMENTSIGNATURE) AS FILEAGREEMENTSIGNATUREURL,
						"SELECT B.TITLE, B.DESCRIPTION, CONCAT('".URL_BASE_AGREEMENT_MASTER."', B.FILEAGREEMENTMASTER) AS FILEAGREEMENTSIGNATUREURL,
								DATE_FORMAT(B.DATEDETERMINATION, '%d %b %Y') AS DATEDETERMINATION, DATE_FORMAT(A.DATESIGNATURE, '%d %b %Y %H:%i') AS DATESIGNATURE,
								A.APPROVALSTATUS, IFNULL(A.APPROVALUSER, '-') AS APPROVALUSER,
								CASE 
									WHEN A.APPROVALSTATUS = 0 THEN 'Waiting for signing'
									WHEN A.APPROVALSTATUS = 1 THEN 'Under review'
									WHEN A.APPROVALSTATUS = -1 THEN 'Rejected'
								END AS APPROVALSTATUSSTR
						FROM t_driveragreement A
						LEFT JOIN m_driveragreementmaster B ON A.IDDRIVERAGREEMENTMASTER = B.IDDRIVERAGREEMENTMASTER
						WHERE A.IDDRIVER = ".$idDriver."
						ORDER BY B.DATEDETERMINATION ASC"
					);
		$result	=	$query->result();

		if(isset($result)) return $result;
		return [];
	}
}
