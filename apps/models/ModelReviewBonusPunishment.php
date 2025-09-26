<?php
class ModelReviewBonusPunishment extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function getDataRecapBonusPunishment($idDriver, $yearMonth){
		$query	=	$this->db->query(
						"SELECT A.IDDRIVERREVIEWBONUS, DATE_FORMAT(B.PERIODDATESTART, '%d %b %Y') AS PERIODDATESTART, DATE_FORMAT(B.PERIODDATEEND, '%d %b %Y') AS PERIODDATEEND,
								B.BONUSRATE, IF(A.TARGETEXCEPTION != -1, A.TARGETEXCEPTION, B.TOTALTARGET) AS TOTALTARGET, A.TOTALREVIEWPOINT AS TOTALREVIEW, A.NOMINALBONUS,
								A.NOMINALPUNISHMENT, A.NOMINALRESULT, IF(A.IDWITHDRAWALRECAP = 0, 0, 1) AS STATUSWITHDRAW, IFNULL(DATE_FORMAT(C.DATETIMEREQUEST, '%d %b %Y %H:%i'), '-') AS DATEWITHDRAW
						 FROM t_driverreviewbonus A
						 LEFT JOIN t_driverreviewbonusperiod B ON A.IDDRIVERREVIEWBONUSPERIOD = B.IDDRIVERREVIEWBONUSPERIOD
						 LEFT JOIN t_withdrawalrecap C ON A.IDWITHDRAWALRECAP = C.IDWITHDRAWALRECAP
						 WHERE A.IDDRIVER = ".$idDriver." AND B.PERIODMONTHYEAR = '".$yearMonth."'
						 LIMIT 1"
					);
		$row	=	$query->row_array();

		if (isset($row)) return $row;
		return [
			"IDDRIVERREVIEWBONUS"	=>	0,
			"PERIODDATESTART"		=>	"-",
			"PERIODDATEEND"			=>	"-",
			"BONUSRATE"				=>	0,
			"TOTALTARGET"			=>	0,
			"TOTALREVIEW"			=>	0,
			"NOMINALBONUS"			=>	0,
			"NOMINALPUNISHMENT"		=>	0,
			"NOMINALRESULT"			=>	0,
			"STATUSWITHDRAW"		=>	0,
			"DATEWITHDRAW"			=>	"-"
		];
	}

	public function getDataListReview($idDriverReviewBonus, $bonusRate){
		$query	=	$this->db->query(
						"SELECT CONCAT('".URL_SOURCE_LOGO."', B.LOGO) AS LOGOURL, IFNULL(B.SOURCENAME, '-') AS SOURCENAME,
								 DATE_FORMAT(A.DATERATINGPOINT, '%d %b %Y') AS DATERATINGPOINT, A.RATING, B.REVIEW5STARPOINT, '".$bonusRate."' AS BONUSRATE,
								 B.REVIEW5STARPOINT * '".$bonusRate."' AS BONUSNOMINAL, DATE_FORMAT(A.DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMEINPUT,
								 A.IDDRIVERRATINGPOINT, IF(A.REVIEWTITLE IS NULL OR A.REVIEWTITLE = '', 0, 1) AS REVIEWCONTENTAVAILABLE
						FROM t_driverratingpoint A
						LEFT JOIN m_source B ON A.IDSOURCE = B.IDSOURCE
						WHERE A.IDDRIVERREVIEWBONUS = ".$idDriverReviewBonus."
						ORDER BY A.DATERATINGPOINT DESC"
					);
		$result	=	$query->result();

		if (isset($result)) return $result;		
		return [];
	}
}
