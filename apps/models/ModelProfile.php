<?php
class ModelProfile extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}
	
	public function getDetailProfileDriver($idDriver){
		$query	=	$this->db->query(
						"SELECT A.RANKNUMBER, A.BASICPOINT, (A.TOTALPOINT - A.BASICPOINT) AS REVIEWPOINT, B.DRIVERTYPE, '' AS INITIALNAME, A.NAME,
								A.ADDRESS, A.PHONE, A.EMAIL, IFNULL(CONCAT(C.MINCAPACITY, ' - ', C.MAXCAPACITY), '-') AS CARCAPACITYDETAIL,
								IFNULL(C.CARCAPACITYNAME, '-') AS CARCAPACITYNAME, IFNULL(COUNT(E.IDDRIVERGROUPMEMBER), 0) AS TOTALDRIVERGROUPMEMBER,
								IFNULL(D.DRIVERAREA, '-') AS DRIVERAREA, A.SECRETPINSTATUS, IFNULL(DATE_FORMAT(A.SECRETPINLASTUPDATE, '%d %b %Y %H:%i'), '-') AS SECRETPINLASTUPDATE,
								IF(A.PROFILEPICFILENAME = '', '', CONCAT('".URL_PROFILE_PICTURE."', A.PROFILEPICFILENAME)) AS PROFILEPICTURE,
								A.PARTNERSHIPTYPE
						  FROM m_driver A
						  LEFT JOIN m_drivertype B ON A.IDDRIVERTYPE = B.IDDRIVERTYPE
						  LEFT JOIN m_carcapacity C ON A.IDCARCAPACITY = C.IDCARCAPACITY
						  LEFT JOIN (SELECT DA.IDDRIVER, GROUP_CONCAT(DB.AREACODE ORDER BY DA.ORDERNUMBER) AS DRIVERAREA
									FROM t_driverareaorder DA
									LEFT JOIN m_area DB ON DA.IDAREA = DB.IDAREA
									GROUP BY DA.IDDRIVER
									ORDER BY DA.ORDERNUMBER
						  ) AS D ON A.IDDRIVER = D.IDDRIVER
						  LEFT JOIN m_drivergroupmember E ON A.IDDRIVER = E.IDDRIVER
						  WHERE A.IDDRIVER = ".$idDriver."
						  GROUP BY A.IDDRIVER
						  LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"RANKNUMBER"			=>	0,
			"BASICPOINT"			=>	0,
			"REVIEWPOINT"			=>	0,
			"DRIVERTYPE"			=>	"-",
			"INITIALNAME"			=>	"-",
			"NAME"					=>	"-",
			"ADDRESS"				=>	"-",
			"PHONE"					=>	"-",
			"EMAIL"					=>	"-",
			"CARCAPACITYDETAIL"		=>	"-",
			"CARCAPACITYNAME"		=>	"-",
			"TOTALDRIVERGROUPMEMBER"=>	0,
			"DRIVERAREA"			=>	"-",
			"SECRETPINSTATUS"		=>	2,
			"SECRETPINLASTUPDATE"	=>	"-",
			"PROFILEPICTURE"		=>	"",
			"PARTNERSHIPTYPE"		=>	0
		);
	}
	
	public function getDetailProfileVendor($idVendor){
		$query	=	$this->db->query(
						"SELECT '' AS INITIALNAME, NAME, ADDRESS, PHONE, EMAIL, SECRETPINSTATUS,
								DATE_FORMAT(SECRETPINLASTUPDATE, '%d %b %Y %H:%i') AS SECRETPINLASTUPDATE,
								IF(PROFILEPICFILENAME = '', '', CONCAT('".URL_PROFILE_PICTURE."', PROFILEPICFILENAME)) AS PROFILEPICTURE
						FROM m_vendor
						WHERE IDVENDOR = ".$idVendor."
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"NAME"					=>	"-",
			"ADDRESS"				=>	"-",
			"PHONE"					=>	"-",
			"EMAIL"					=>	"-",
			"SECRETPINSTATUS"		=>	1,
			"SECRETPINLASTUPDATE"	=>	"-",
			"PROFILEPICTURE"		=>	""
		);
	}
	
	public function getDataActiveBankAccount($idPartnerType, $idPartner){
		$query	=	$this->db->query(
						"SELECT A.ACCOUNTNUMBER, A.ACCOUNTHOLDERNAME, B.BANKNAME, CONCAT('".URL_BANK_LOGO."', B.BANKLOGO) AS BANKLOGO
						FROM t_bankaccountpartner A
						LEFT JOIN m_bank B ON A.IDBANK = B.IDBANK
						WHERE A.IDPARTNERTYPE = ".$idPartnerType." AND A.IDPARTNER = ".$idPartner." AND A.STATUS = 1
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return new stdClass();		
	}
	
	public function getPointHistoryDriver($idDriver, $dateStart){
		$query	=	$this->db->query(
						"SELECT CONCAT('".URL_SOURCE_LOGO."', B.LOGO) AS LOGOURL, IFNULL(B.SOURCENAME, '-') AS SOURCENAME,
								DATE_FORMAT(A.DATERATINGPOINT, '%d %b %Y') AS DATERATINGPOINT, A.RATING, A.POINT, A.USERINPUT,
								DATE_FORMAT(A.DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMEINPUT, A.IDDRIVERRATINGPOINT,
								IF(A.REVIEWTITLE IS NULL OR A.REVIEWTITLE = '', 0, 1) AS REVIEWCONTENTAVAILABLE, 0 AS REVIEW5STARPOINT,
								12500 AS BONUSRATE
						FROM t_driverratingpoint A
						LEFT JOIN m_source B ON A.IDSOURCE = B.IDSOURCE
						WHERE A.IDDRIVER = ".$idDriver." AND A.DATERATINGPOINT >= '".$dateStart."'
						ORDER BY A.DATERATINGPOINT DESC"
					);
		$result	=	$query->result();
		
		if(!$result) return array();
		return $result;		
	}
	
	public function getListAreaPriority($idDriver){
		$query	=	$this->db->query(
						"SELECT B.AREANAME, B.AREATAGS AS LISTAREA
						FROM t_driverareaorder A
						LEFT JOIN m_area B ON A.IDAREA = B.IDAREA
						WHERE A.IDDRIVER = ".$idDriver."
						ORDER BY A.ORDERNUMBER"
					);
		$result	=	$query->result();
		
		if(!$result) return array();
		return $result;		
	}
	
	public function checkDataBankAccountExist($idPartnerType, $idPartner, $idBank, $accountNumber){
		$query	=	$this->db->query(
						"SELECT IDBANKACCOUNTPARTNER, TEMPOTP FROM t_bankaccountpartner
						WHERE IDPARTNERTYPE = ".$idPartnerType." AND IDPARTNER = ".$idPartner." AND
						IDBANK = '".$idBank."' AND ACCOUNTNUMBER = '".$accountNumber."'
						LIMIT 1"
					);
		$row	=	$query->row_array();
		
		if(!$row) return false;
		return $row;		
	}
	
	public function checkOTPAddBankAccountValid($idPartnerType, $idPartner, $otpCode){
		
		$query	=	$this->db->query(
						"SELECT A.IDBANKACCOUNTPARTNER, A.ACCOUNTNUMBER, A.ACCOUNTHOLDERNAME, B.BANKNAME
						FROM t_bankaccountpartner A
						LEFT JOIN m_bank B ON A.IDBANK = B.IDBANK
						WHERE A.IDPARTNERTYPE = ".$idPartnerType." AND A.IDPARTNER = ".$idPartner." AND
							A.TEMPOTP = '".$otpCode."' AND A.STATUS = 0
						LIMIT 1"
					);
		$row	=	$query->row_array();
		
		if(!$row) return false;
		return $row;		
	}
	
	public function getDetailReview($idDriver, $idDriverRatingPoint){
		$query	=	$this->db->query(
						"SELECT REVIEWTITLE, REPLACE(REVIEWCONTENT, '<br>', '\n') AS REVIEWCONTENT
						FROM t_driverratingpoint
						WHERE IDDRIVER = ".$idDriver." AND IDDRIVERRATINGPOINT = ".$idDriverRatingPoint."
						LIMIT 1"
					);
		$row	=	$query->row_array();
		
		if(!$row) return false;
		return $row;		
	}
}