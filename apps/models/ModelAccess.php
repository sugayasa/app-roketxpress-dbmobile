<?php
class ModelAccess extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function isImeiRegistered($imei){
		$query	=	$this->db->query(
						"SELECT IDUSERMOBILE, TEMPOTP FROM m_usermobile
						WHERE IMEI = '".$imei."'
						LIMIT 0,1"
					);
		$row	=	$query->row_array();

		if (isset($row)) return $row;
		return false;
	}

	public function resetTokenByImei($imei){
		$this->db->set('TOKENACCESS', "");
		$this->db->set('TOKEN1', "");
		$this->db->set('TOKEN2', "");
		$this->db->set('TOKENFCM', "");
		$this->db->where('IMEI', $imei);
		$this->db->update('m_usermobile');
		
		return true;
	}

	public function setAllNewAccessToken($imei, $token, $fcmtoken, $updatefcm = false){
		$tokenExpiredTime	=	date("Y-m-d H:i:s", time() + TOKEN_MAXAGE_SECONDS);

		$this->db->set('LASTACTIVITY', date('Y-m-d H:i:s'));
		$this->db->set('TOKENEXPIRED', $tokenExpiredTime);
		if($updatefcm) $this->db->set('TOKENFCM', $fcmtoken);
		$this->db->set('TOKEN1', $token);
		$this->db->set('TOKEN2', $token);
		$this->db->where('IMEI', $imei);
		$this->db->update('m_usermobile');
		
		return true;
	}

	public function isTokenExist($token){
		$query	=	$this->db->query(
						"SELECT TEMPOTP, IMEI, TOKENEXPIRED, LASTACTIVITY FROM m_usermobile
						WHERE (TOKEN1 = '".$token."' OR TOKEN2 = '".$token."')
						LIMIT 0,1"
					);
		$row	=	$query->row_array();

		if (isset($row)) return $row;
		return false;
	}
	
	public function updateNewAccessToken($versionNumber, $imei, $token, $fcmtoken, $updateToken2 = true, $updatefcm = false){
		$tokenExpiredTime	=	date('Y-m-d H:i:s', time() + TOKEN_MAXAGE_SECONDS);
		$idUserMobile		=	$this->getIDUserMobileByImei($imei);
		
		$this->db->set('APPVERSIONNUMBER', $versionNumber);
		$this->db->set('LASTACTIVITY', date('Y-m-d H:i:s'));
		$this->db->set('TOKENEXPIRED', $tokenExpiredTime);
		if($updatefcm && strlen($fcmtoken) > 10) $this->db->set('TOKENFCM', $fcmtoken);
		if($updateToken2) $this->db->set('TOKEN2', 'TOKEN1', FALSE);
		$this->db->set('TOKEN1', $token);
		$this->db->where('IDUSERMOBILE',$idUserMobile);
		$this->db->update('m_usermobile');
		
		return true;
	}
	
	private function getIDUserMobileByImei($imei){
		$query	=	$this->db->query(
						"SELECT IDUSERMOBILE FROM m_usermobile
						WHERE IMEI = '".$imei."'
						ORDER BY LASTACTIVITY DESC
						LIMIT 0,1"
					);
		$row	=	$query->row_array();

		if (isset($row)) return $row['IDUSERMOBILE'];
		return 0;
	}
	
	public function updateLastActivity($versionNumber, $imei, $fcmtoken = false){
		$this->db->set('LASTACTIVITY', date('Y-m-d H:i:s'));
		$this->db->set('APPVERSIONNUMBER', $versionNumber);
		if($fcmtoken && strlen($fcmtoken) > 10) $this->db->set('TOKENFCM', $fcmtoken);
		$this->db->where('IMEI',$imei);
		$this->db->update('m_usermobile');
		
		return true;
	}

	public function getNewestToken($imei, $token){
		$query	=	$this->db->query(
						"SELECT TOKEN1 AS TOKEN FROM m_usermobile
						WHERE IMEI = '".$imei."' AND (TOKEN1 = '".$token."' OR TOKEN2 = '".$token."')
						LIMIT 0,1"
					);
		$row	=	$query->row_array();

		if (isset($row)) return $row['TOKEN'];
		return false;
	}
	
	public function insertLogDataSend($dataSend){
		$this->db->insert(
			'log_datasendmobile',
			array(
				'URL'		=>	"[".$_SERVER['REQUEST_METHOD']."] ".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
				'DATASEND'	=>	$dataSend,
				'DATETIME'	=>	date('Y-m-d H:i:s')
			)
		);
		return true;
	}

	public function isUserExist($imei, $token){
		$query	=	$this->db->query(
						"SELECT IDUSERMOBILE FROM m_usermobile
						WHERE IMEI = '".$imei."' AND (TOKEN1 = '".$token."' OR TOKEN2 = '".$token."')
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if (isset($row)) return $row;
		return false;
	}

	public function getDataOptionHelperAdditionalCostType(){
		$baseQuery	=	sprintf(
							"SELECT IDADDITIONALCOSTTYPE AS ID, ADDITIONALCOSTTYPE AS VALUE FROM m_additionalcosttype
							 ORDER BY ADDITIONALCOSTTYPE"
						);
		$query		=	$this->db->query($baseQuery);
		$result		=	$query->result();
		
		if(!$result) return array();		
		return $result;
	}
	
	public function getDataOptionHelperBank(){
		$baseQuery	=	"SELECT IDBANK AS ID, BANKNAME AS VALUE FROM m_bank
						 WHERE STATUS = 1
						 ORDER BY BANKNAME";
		$query		=	$this->db->query($baseQuery);
		$result		=	$query->result();
		
		if(!$result) return array();
		return $result;		
	}

	public function getDataLoanPrepaidCapitalType($idDriver){
		$baseQuery	=	sprintf(
							"SELECT A.IDLOANTYPE AS ID, CONCAT(IF(B.STATUSLOANCAPITAL = 1, 'Loan - ', ''), B.LOANTYPE) AS VALUE
							 FROM t_driverloanpermission A
							 LEFT JOIN m_loantype B ON A.IDLOANTYPE = B.IDLOANTYPE
							 WHERE A.STATUSPERMISSION = 1
							 GROUP BY A.IDLOANTYPE
							 ORDER BY B.STATUSLOANCAPITAL, B.LOANTYPE"
						);
		$query		=	$this->db->query($baseQuery);
		$result		=	$query->result();
		
		if(!$result) return array();
		return $result;
	}

	public function updateLastPosition($idDriver, $gpsL, $gpsB, $accuration){
		$gpsPoint	=	'POINT('.$gpsL.' '.$gpsB.')';
		$this->db
		->set('GPSCOORDINATE', "ST_GeomFromText('$gpsPoint')", false)
		->set('GPSACCURATION', $accuration)
		->where('IDDRIVER', $idDriver)
		->update('m_driver');
		
		return true;
	}

	public function insertDriverPositionLog($idDriver, $gpsL, $gpsB, $accuration, $isFakeGPS){
		$gpsPoint	=	'POINT('.$gpsL.' '.$gpsB.')';
		$this->db->set('GPSCOORDINATE', "ST_GeomFromText('$gpsPoint')", false);
		$this->db->set('GPSACCURATION', $accuration);
		$this->db->set('ISFAKEGPS', $isFakeGPS);
		$this->db->set('IDDRIVER', $idDriver);
		$this->db->set('DATETIMELOG', date('Y-m-d H:i:s'));
		$this->db->insert('log_driverlocation');
		
		return true;
	}
}