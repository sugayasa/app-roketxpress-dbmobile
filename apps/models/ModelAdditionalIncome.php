<?php
class ModelAdditionalIncome extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function getDataAdditionalIncome($idDriver, $dateStart, $dateEnd){
		$query	=	$this->db->query(
						"SELECT DATE_FORMAT(A.INCOMEDATE, '%d %b %Y') AS INCOMEDATESTR, A.DESCRIPTION, A.INCOMENOMINAL,
								B.POINT, A.INPUTUSER, DATE_FORMAT(A.INPUTDATETIME, '%d %b %Y %H:%i') AS INPUTDATETIME,
								CASE
									WHEN A.APPROVALSTATUS = 0 THEN 'Waiting for approval'
									WHEN A.APPROVALSTATUS = 1 THEN 'Approved'
									WHEN A.APPROVALSTATUS = -1 THEN 'Rejected'
									ELSE '-'
								END AS STRAPPROVALSTATUS,
								A.APPROVALSTATUS
						FROM t_additionalincome A
						LEFT JOIN t_driverratingpoint B ON A.IDDRIVERRATINGPOINT = B.IDDRIVERRATINGPOINT
						WHERE A.IDDRIVER = ".$idDriver." AND DATE(A.INCOMEDATE) BETWEEN '".$dateStart."' AND '".$dateEnd."'
						ORDER BY A.INCOMEDATE"
					);
		$result	=	$query->result();

		if (isset($result)) return $result;		
		return [];
	}
	
	public function getDataAdditionalIncomePointRate(){
		$baseQuery	=	"SELECT NOMINALMIN, NOMINALMAX, REVIEWPOINT
						 FROM t_additionalincomerate
						 ORDER BY NOMINALMIN";
		$query		=	$this->db->query($baseQuery);
		$result		=	$query->result();
		
		if(isset($result)) return $result;
		return false;	
	}
	
	public function getTotalAdditionalIncomeApproval(){
		$baseQuery	=	"SELECT COUNT(IDADDITIONALINCOME) AS TOTALADDITIONALINCOMEAPPROVAL FROM t_additionalincome
						WHERE APPROVALSTATUS = 0
						LIMIT 1";
		$query		=	$this->db->query($baseQuery);
		$row		=	$query->row_array();
		
		if(isset($row)) return $row['TOTALADDITIONALINCOMEAPPROVAL'];
		return 0;	
	}
}
