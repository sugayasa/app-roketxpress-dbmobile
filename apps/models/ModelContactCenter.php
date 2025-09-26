<?php
class ModelContactCenter extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function getDataContactCenter(){
		$query	=	$this->db->query(
						"SELECT NAME, EMAIL, PARTNERCONTACTNUMBER, '' AS PARTNERCONTACTNUMBERURL,
								DATE_FORMAT(LASTACTIVITY, '%Y-%m-%d %H:%i:%s') AS LASTACTIVITY
						FROM m_useradmin
						WHERE STATUSPARTNERCONTACT = 1
						ORDER BY LASTACTIVITY DESC"
					);
		$result	=	$query->result();

		if (isset($result)) return $result;		
		return [];
	}
}
