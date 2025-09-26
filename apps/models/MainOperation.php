<?php
class MainOperation extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}
	
	public function addData($table, $arrFieldValue){
		$this->db->insert($table, $arrFieldValue);
		$insert_id = $this->db->insert_id();

		if($insert_id > 0){
			return array("status"=>true, "errCode"=>false, "lastID"=>$insert_id);
		}

		$error		=	$this->db->error();
		$errorCode	=	$error['code'];
		return array("status"=>false, "errCode"=>$errorCode);
	}
	
	public function updateData($table, $arrFieldValue, $fieldWhere, $idWhere = null){
		if(!is_array($fieldWhere)){
			$this->db->where($fieldWhere, $idWhere);
		} else {
			foreach($fieldWhere as $field => $value){
				$this->db->where($field, $value);
			}
		}
		
		$this->db->update($table, $arrFieldValue);
		$affectedRows = $this->db->affected_rows();

		if($affectedRows > 0){
			return array("status"=>true, "errCode"=>false);
		}

		$error		=	$this->db->error();
		$errorCode	=	$error['code'];
		return array("status"=>false, "errCode"=>$errorCode);
	}
	
	public function deleteData($table, $arrWhere, $arrWhereNotEqual = false){
		if($arrWhereNotEqual && is_array($arrWhereNotEqual)){
			foreach($arrWhereNotEqual as $whereNotEqualField => $whereNotEqualValue){
				$this->db->where($whereNotEqualField.' !=', $whereNotEqualValue);
			}
		}
		
		$this->db->delete($table, $arrWhere);
		$affectedRows = $this->db->affected_rows();

		if($affectedRows > 0) return array("status"=>true, "errCode"=>false);

		$error		=	$this->db->error();
		$errorCode	=	$error['code'];
		return array("status"=>false, "errCode"=>$errorCode);
	}
	
	public function getDetailPartner($token){
		$query	=	$this->db->query(
						"SELECT A.IDUSERMOBILE, A.IDPARTNERTYPE, A.IDSUBPARTNERTYPE, A.IDPARTNER, IF(A.IDPARTNERTYPE = 1, B.NAME, C.NAME) AS PARTNERNAME,
							   IF(A.IDPARTNERTYPE = 1, B.NEWFINANCESCHEME, C.NEWFINANCESCHEME) AS NEWFINANCESCHEME,
							   IF(A.IDPARTNERTYPE = 1, B.NEWFINANCESCHEMESTART, C.NEWFINANCESCHEMESTART) AS NEWFINANCESCHEMESTART,
							   IF(A.IDPARTNERTYPE = 2, C.PARTNERSHIPTYPE, 0) AS PARTNERSHIPTYPE, IF(A.IDPARTNERTYPE = 1, B.TRANSPORTSERVICE, 1) AS TRANSPORTSERVICE,
							   IF(A.IDPARTNERTYPE = 1, B.FINANCESCHEMETYPE, 1) AS FINANCESCHEMETYPE, IF(A.IDPARTNERTYPE = 1, B.PHONE, C.PHONE) AS PARTNERPHONENUMBER,
							   IF(A.IDPARTNERTYPE = 1, '-', C.CARNUMBERPLATE) AS CARNUMBERPLATE, IF(A.IDPARTNERTYPE = 1, '-', C.CARBRAND) AS CARBRAND,
							   IF(A.IDPARTNERTYPE = 1, '-', C.CARMODEL) AS CARMODEL, IF(A.IDPARTNERTYPE = 1, 0, IF(C.PARTNERSHIPTYPE = 3, 1, 0)) AS ISGROUPDRIVER
						FROM m_usermobile A
						LEFT JOIN m_vendor B ON A.IDPARTNER = B.IDVENDOR
						LEFT JOIN m_driver C ON A.IDPARTNER = C.IDDRIVER
						WHERE A.TOKEN1 = '".$token."' OR A.TOKEN2 = '".$token."'
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"IDUSERMOBILE"			=>	0,
			"IDPARTNERTYPE"			=>	0,
			"IDSUBPARTNERTYPE"		=>	0,
			"IDPARTNER"				=>	0,
			"PARTNERNAME"			=>	'-',
			"NEWFINANCESCHEME"		=>	1,
			"NEWFINANCESCHEMESTART"	=>	'0000-00-00 00:00:00',
			"PARTNERSHIPTYPE"		=>	0,
			"TRANSPORTSERVICE"		=>	0,
			"FINANCESCHEMETYPE"		=>	1,
			"PARTNERPHONE"			=>	'-',
			"CARNUMBERPLATE"		=>	'-',
			"CARBRAND"				=>	'-',
			"CARMODEL"				=>	'-',
		);
	}
	
	public function getDetailDriver($idDriver){
		$query	=	$this->db->query(
						"SELECT B.DRIVERTYPE, A.NAME, A.ADDRESS, A.PHONE, A.EMAIL, A.NEWFINANCESCHEME, A.NEWFINANCESCHEMESTART
						FROM m_driver A
						LEFT JOIN m_drivertype B ON A.IDDRIVERTYPE = B.IDDRIVERTYPE
						WHERE A.IDDRIVER = '".$idDriver."'
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"DRIVERTYPE"			=>	'-',
			"NAME"					=>	'-',
			"ADDRESS"				=>	'-',
			"PHONE"					=>	'-',
			"EMAIL"					=>	'-',
			"NEWFINANCESCHEME"		=>	1,
			"NEWFINANCESCHEMESTART"	=>	'0000-00-00 00:00:00'
		);
	}
	
	public function getDetailVendor($idVendor){
		$query	=	$this->db->query(
						"SELECT NAME, ADDRESS, PHONE, EMAIL, NEWFINANCESCHEME, NEWFINANCESCHEMESTART FROM m_vendor
						WHERE IDVENDOR = '".$idVendor."'
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"NAME"		=>	'-',
			"ADDRESS"	=>	'-',
			"PHONE"		=>	'-',
			"EMAIL"		=>	'-'
		);
	}
	
	public function getDetailStatusProcessDescription($idPartnerType, $statusProcess){
		$table					=	$idPartnerType == 1 ? "m_statusprocessvendor" : "m_statusprocessdriver";
		$where 					=	$idPartnerType == 1 ? "IDSTATUSPROCESSVENDOR" : "IDSTATUSPROCESSDRIVER";
		$isTrackingLocationField=	$idPartnerType == 1 ? '0' : 'ISTRACKINGLOCATION';
		$query					=	$this->db->query(
										"SELECT STATUSPROCESSNAME, ".$isTrackingLocationField." AS ISTRACKINGLOCATION FROM ".$table."
										WHERE ".$where." = '".$statusProcess."'
										LIMIT 1"
									);
		$row					=	$query->row_array();

		if(isset($row)) return $row;
		return [
			"STATUSPROCESSNAME"	=>	"",
			"ISTRACKINGLOCATION"=>	0
		];
	}
	
	public function getMaxStatusProcess($idPartnerType){
		$table	= $idPartnerType == 1 ? "m_statusprocessvendor" : "m_statusprocessdriver";
		$field 	= $idPartnerType == 1 ? "IDSTATUSPROCESSVENDOR" : "IDSTATUSPROCESSDRIVER";
		$query	= $this->db->query("SELECT MAX(".$field.") AS MAXSTATUSPROCESS FROM ".$table." LIMIT 1");
		$row	= $query->row_array();

		if(isset($row)) return $row['MAXSTATUSPROCESS'];
		return 0;
	}
	
	public function getDataPlayerIdOneSignal($notificationType){
		$dateMin=	date('Y-m-d H:i:s', strtotime('-2 day'));
		$query	=	$this->db->query(
						"SELECT C.OSUSERID, B.IDUSERADMIN FROM m_userlevel A
						LEFT JOIN m_useradmin B ON A.IDUSERLEVEL = B.LEVEL
						LEFT JOIN (SELECT * FROM t_usernotifsignal
								   WHERE LASTACTIVITY > '".$dateMin."'
								   ORDER BY LASTACTIVITY DESC) C ON B.IDUSERADMIN = C.IDUSERADMIN
						WHERE A.".$notificationType." = 1 AND C.OSUSERID IS NOT NULL AND C.OSUSERID != ''"
					);
		$result	=	$query->result();

		if(isset($result)){
			$arrOSUserId	=	$arrIdUserAdmin	=	array();
			foreach($result as $keyData){
				$arrOSUserId[]		=	$keyData->OSUSERID;
				$arrIdUserAdmin[]	=	$keyData->IDUSERADMIN;
			}

			return array("arrOSUserId"=>$arrOSUserId, "arrIdUserAdmin"=>$arrIdUserAdmin);
		}
		return false;
	}
	
	function insertAdminMessage($idMessageAdminType, $arrIdUserAdmin, $title, $message, $arrData){
		$paramList	=	json_encode($arrData);
		if(is_array($arrIdUserAdmin) && count($arrIdUserAdmin) > 0){
			
			$arrIdUserAdmin	=	array_unique($arrIdUserAdmin);
			foreach($arrIdUserAdmin as $idUserAdmin){
				$arrInsert	=	array(
									"IDMESSAGEADMINTYPE"=>	$idMessageAdminType,
									"IDUSERADMIN"		=>	$idUserAdmin,
									"TITLE"				=>	$title,
									"MESSAGE"			=>	$message,
									"PARAMLIST"			=>	$paramList,
									"DATETIMEINSERT"	=>	date('Y-m-d H:i:s'),
									"DATETIMEREAD"		=>	'0000-00-00 00:00:00',
									"STATUS"			=>	0
								);
				$this->addData("t_messageadmin", $arrInsert);
			}
		}
		return true;
	}
	
	public function getStatusSecretPINPartner($idPartnerType, $idPartner){
		$table		=	$idPartnerType == 1 ? "m_vendor" : "m_driver";
		$fieldWhere	=	$idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";
		$query		=	$this->db->query(
							"SELECT SECRETPINSTATUS FROM ".$table."
							 WHERE ".$fieldWhere." = ".$idPartner." LIMIT 1"
						);
		$row		=	$query->row_array();
		
		if(!$row) return 0;
		return $row['SECRETPINSTATUS'];		
	}
	
	public function checkSecretPINPartner($idPartnerType, $idPartner, $secretPIN){
		$table		=	$idPartnerType == 1 ? "m_vendor" : "m_driver";
		$fieldWhere	=	$idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";
		$query		=	$this->db->query(
							"SELECT SECRETPINSTATUS FROM ".$table."
							WHERE ".$fieldWhere." = ".$idPartner." AND SECRETPIN = '".$secretPIN."'
							LIMIT 1"
						);
		$row		=	$query->row_array();
		
		if(!$row) return false;
		return true;		
	}
	
	public function getDetailLoanType($idLoanType){
		$query	=	$this->db->query(
						"SELECT STATUSLOANCAPITAL, CONCAT(IF(STATUSLOANCAPITAL = 1, 'Loan - ', ''), LOANTYPE) AS LOANTYPE
						FROM m_loantype
						WHERE IDLOANTYPE = '".$idLoanType."'
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"STATUSLOANCAPITAL"	=>	1,
			"LOANTYPE"			=>	'-'
		);
	}

	public function getDataActiveBankAccount($idPartnerType, $idPartner){
		$query	=	$this->db->query(
						"SELECT A.IDBANKACCOUNTPARTNER, A.ACCOUNTNUMBER, A.ACCOUNTHOLDERNAME, B.BANKNAME,
							 CONCAT('".URL_BANK_LOGO."', B.BANKLOGO) AS BANKLOGO, A.IDBANK
						FROM t_bankaccountpartner A
						LEFT JOIN m_bank B ON A.IDBANK = B.IDBANK
						WHERE A.IDPARTNERTYPE = ".$idPartnerType." AND A.IDPARTNER = ".$idPartner." AND A.STATUS = 1
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return false;		
	}
	
	public function getValueSystemSettingVariable($idSystemSettingVariable){
		$baseQuery	=	"SELECT VALUE FROM a_systemsettingvariable
						WHERE IDSYSTEMSETTINGVARIABLE = ".$idSystemSettingVariable."
						LIMIT 1";
		$query		=	$this->db->query($baseQuery);
		$row		=	$query->row_array();
		
		if(!$row) return 0;
		return $row['VALUE'];		
	}
	
	public function getDataDriverMonitor($date){
		$baseQuery	=	"SELECT TOTALDAYOFFQUOTA, TOTALOFFDRIVER FROM t_scheduledrivermonitor
						WHERE DATESCHEDULE = '".$date."'
						LIMIT 1";
		$query		=	$this->db->query($baseQuery);
		$row		=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"TOTALDAYOFFQUOTA"	=>	0,
			"TOTALOFFDRIVER"	=>	0
		);
	}
	
	public function getDataAllowedLoan($idDriver){
		$baseQuery	=	"SELECT GROUP_CONCAT(IDLOANTYPE) AS ARRIDLOANTYPE FROM t_driverloanpermission
						WHERE IDDRIVER = '".$idDriver."' AND STATUSPERMISSION = 1
						GROUP BY IDDRIVER
						LIMIT 1";
		$query		=	$this->db->query($baseQuery);
		$row		=	$query->row_array();

		if(isset($row)) return $row['ARRIDLOANTYPE'];
		return "";
	}	
}