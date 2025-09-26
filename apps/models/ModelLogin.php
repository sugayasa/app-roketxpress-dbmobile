<?php
class ModelLogin extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function loginCheck($email, $imei){
		
		$query	= $this->db->query("SELECT A.*, IFNULL(B.IDUSERMOBILE, 0) AS IDUSERMOBILE, B.TEMPOTP, B.LASTLOGIN FROM (
										SELECT '1' AS IDPARTNERTYPE, A.IDVENDOR AS IDPARTNER, A.IDVENDORTYPE AS IDSUBPARTNERTYPE,
												B.VENDORTYPE AS SUBPARTNERTYPE, A.NAME, B.VENDORTYPE AS PARTNERTYPE
										FROM m_vendor A
										LEFT JOIN m_vendortype B ON A.IDVENDORTYPE = B.IDVENDORTYPE
										WHERE A.EMAIL = '".$email."'
										UNION ALL
										SELECT '2' AS IDPARTNERTYPE, A.IDDRIVER AS IDPARTNER, A.IDDRIVERTYPE AS IDSUBPARTNERTYPE,
												CONCAT(B.DRIVERTYPE, ' Driver') AS SUBPARTNERTYPE, A.NAME, CONCAT(B.DRIVERTYPE, ' Driver') AS PARTNERTYPE
										FROM m_driver A
										LEFT JOIN m_drivertype B ON A.IDDRIVERTYPE = B.IDDRIVERTYPE
										WHERE A.EMAIL = '".$email."'
									) AS A
									LEFT JOIN m_usermobile B ON A.IDPARTNER = B.IDPARTNER AND A.IDPARTNERTYPE = B.IDPARTNERTYPE AND A.IDSUBPARTNERTYPE = B.IDSUBPARTNERTYPE AND B.IMEI = '".$imei."'
									GROUP BY A.IDPARTNER
									LIMIT 1");
		$row	= $query->row_array();

		if (isset($row)){
			return $row;
		}
		
		return false;
	}
	
	public function checkOTP($token, $otpCode){
		
		$query	= $this->db->query("SELECT A.*, B.IDUSERMOBILE FROM (
										SELECT '1' AS IDPARTNERTYPE, A.IDVENDOR AS IDPARTNER, A.IDVENDORTYPE AS IDSUBPARTNERTYPE,
												B.VENDORTYPE AS PARTNERTYPE, A.NAME, A.EMAIL, A.PHONE, 0 AS PARTNERSHIPTYPE,
												A.TRANSPORTSERVICE, A.FINANCESCHEMETYPE
										FROM m_vendor A
										LEFT JOIN m_vendortype B ON A.IDVENDORTYPE = B.IDVENDORTYPE
										UNION ALL
										SELECT '2' AS IDPARTNERTYPE, A.IDDRIVER AS IDPARTNER, A.IDDRIVERTYPE AS IDSUBPARTNERTYPE,
												CONCAT(B.DRIVERTYPE, ' Driver') AS PARTNERTYPE, A.NAME, A.EMAIL, A.PHONE, A.PARTNERSHIPTYPE,
												1 AS TRANSPORTSERVICE, 1 AS FINANCESCHEMETYPE
										FROM m_driver A
										LEFT JOIN m_drivertype B ON A.IDDRIVERTYPE = B.IDDRIVERTYPE
									) AS A
									LEFT JOIN m_usermobile B ON A.IDPARTNER = B.IDPARTNER AND A.IDPARTNERTYPE = B.IDPARTNERTYPE
									WHERE B.TOKENACCESS = '".$token."' AND B.TEMPOTP = '".$otpCode."'
									GROUP BY A.IDPARTNER
									LIMIT 1");
		$row	= $query->row_array();

		if (isset($row)){
			return $row;
		}
		
		return false;
		
	}
	
}