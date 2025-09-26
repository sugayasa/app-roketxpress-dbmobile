<?php
class ModelHelpCenter extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}
	
	public function getCategory($idPartnerType){
		
		$query	=	$this->db->query("SELECT IDHELPCENTERCATEGORY, ICON, CATEGORYNAME, DESCRIPTION, '' AS LISTARTICLE
									FROM w_helpcentercategory
									WHERE IDPARTNERTYPE = '".$idPartnerType."'
									ORDER BY CATEGORYNAME");
		$result	=	$query->result();
		
		if(!$result){
			return false;
		}
		
		return $result;
		
	}
	
	public function getListArticleHelpCenter($idCategory){
		
		$query	=	$this->db->query("SELECT IDHELPCENTERARTICLE, ARTICLETITLE
									FROM w_helpcenterarticle
									WHERE IDHELPCENTERCATEGORY = '".$idCategory."' AND STATUSVIEW = 1
									ORDER BY ARTICLETITLE");
		$result	=	$query->result();
		
		if(!$result){
			return false;
		}
		
		return $result;
		
	}
	
	public function getDetailArticle($idArticle){
		
		$query	=	$this->db->query("SELECT B.IDPARTNERTYPE, C.PARTNERTYPE, B.CATEGORYNAME, A.ARTICLETITLE, A.ARTICLECONTENT, A.INPUTUSER,
											 DATE_FORMAT(A.INPUTDATETIME, '%d %b %Y %H:%i') AS INPUTDATETIME
									FROM w_helpcenterarticle A
									LEFT JOIN w_helpcentercategory B ON A.IDHELPCENTERCATEGORY = B.IDHELPCENTERCATEGORY
									LEFT JOIN m_partnertype C ON B.IDPARTNERTYPE = C.IDPARTNERTYPE
									WHERE A.IDHELPCENTERARTICLE = '".$idArticle."'
									LIMIT 1");
		$row	=	$query->row_array();
		
		if(!$row){
			return false;
		}
		
		return $row;
		
	}

}