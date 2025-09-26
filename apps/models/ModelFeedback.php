<?php
class ModelFeedback extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}
	
	public function getListFeedback($idDriver){
		$query	=	$this->db->query("SELECT TITLE, DESCRIPTION, CONCAT('".URL_FEEDBACK_IMAGE."', IMAGE) AS IMAGE, DATE_FORMAT(DATETIME, '%d %b %Y %H:%i') AS DATETIME
									  FROM t_driverfeedback
									  WHERE IDDRIVER = ".$idDriver."
									  ORDER BY DATETIME"
					);
		$result	=	$query->result();

		if(isset($result)) return $result;
		return [];
	}
}