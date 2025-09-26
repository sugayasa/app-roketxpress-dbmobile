<?php
class ModelLoanPrepaidCapital extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}
	
	public function getDataSummaryLoanPrepaidCapital($idDriver){
		
		$query		=	$this->db->query("SELECT IFNULL(SUM(IF(A.TYPE = 'D', A.AMOUNT, A.AMOUNT * -1)), 0) AS TOTALLOANPREPAIDCAPITAL,
												 IFNULL(SUM(IF(B.STATUSLOANCAPITAL = 1, IF(A.TYPE = 'D', A.AMOUNT, A.AMOUNT * -1), 0)), 0) AS TOTALLOAN,
												 IFNULL(SUM(IF(B.STATUSLOANCAPITAL = 2, IF(A.TYPE = 'D', A.AMOUNT, A.AMOUNT * -1), 0)), 0) AS TOTALPREPAIDCAPITAL,
												 IFNULL(DATE_FORMAT(MAX(IF(B.STATUSLOANCAPITAL = 1, A.DATETIMEINPUT, '')), '%d %b %Y'), '-') AS LASTTRANSACTIONLOAN,
												 IFNULL(DATE_FORMAT(MAX(IF(B.STATUSLOANCAPITAL = 2, A.DATETIMEINPUT, '')), '%d %b %Y'), '-') AS LASTTRANSACTIONPREPAIDCAPITAL
										  FROM t_loandriverrecord A
										  LEFT JOIN m_loantype B ON A.IDLOANTYPE = B.IDLOANTYPE
										  WHERE A.IDDRIVER = ".$idDriver."
										  LIMIT 1"
										);
		$row		=	$query->row_array();

		if(isset($row)){
			return $row;
		}
		
		return array(
					"TOTALLOANPREPAIDCAPITAL"		=>	0,
					"TOTALLOAN"						=>	0,
					"TOTALPREPAIDCAPITAL"			=>	0,
					"LASTTRANSACTIONLOAN"			=>	"-",
					"LASTTRANSACTIONPREPAIDCAPITAL"	=>	"-"
				);
		
	}
	
	public function getHistoryLoanPrepaidCapital($idDriver, $typeLoanCapital){
		
		$baseQuery		=	"SELECT DATE_FORMAT(A.DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMEINPUT, B.LOANTYPE, A.DESCRIPTION,
									A.TYPE, A.AMOUNT, 0 AS SALDO, A.IDLOANDRIVERRECORD
							 FROM t_loandriverrecord A
							 LEFT JOIN m_loantype B ON A.IDLOANTYPE = B.IDLOANTYPE
							 WHERE A.IDDRIVER = ".$idDriver." AND B.STATUSLOANCAPITAL = ".$typeLoanCapital."
							 ORDER BY A.DATETIMEINPUT DESC";
		$query			=	$this->db->query($baseQuery);
		$result			=	$query->result();
		
		if(isset($result)){
			return $result;
		}
		
		return false;
	
	}
	
	public function getDataLoanPrepaidCapitalRequest($idDriver, $strArrStatusRequest){
		
		$baseQuery		=	"SELECT A.IDLOANDRIVERREQUEST, A.IDLOANTYPE, B.STATUSLOANCAPITAL, DATE_FORMAT(A.DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMEREQUEST,
									CONCAT(IF(B.STATUSLOANCAPITAL = 1, 'Loan - ', ''), B.LOANTYPE) AS LOANTYPE, A.NOTES, A.AMOUNT, A.STATUS,
									CASE
										WHEN A.STATUS = 0 THEN 'Waiting for Approval'
										WHEN A.STATUS = 1 THEN 'Approved'
										WHEN A.STATUS = 2 THEN 'Transferred'
										WHEN A.STATUS = -1 THEN 'Rejected'
										WHEN A.STATUS = -2 THEN 'Canceled'
										ELSE '-'
									END AS STRSTATUS
							 FROM t_loandriverrequest A
							 LEFT JOIN m_loantype B ON A.IDLOANTYPE = B.IDLOANTYPE
							 WHERE A.IDDRIVER = ".$idDriver." AND A.STATUS IN (".$strArrStatusRequest.")
							 ORDER BY A.DATETIMEINPUT DESC";
		$query			=	$this->db->query($baseQuery);
		$result			=	$query->result();
		
		if(isset($result)){
			return $result;
		}
		
		return false;
	
	}

	public function checkActiveRequestExist($idDriver, $loanPrepaidCapitalType){
		
		$query	=	$this->db->query(
						"SELECT A.IDLOANDRIVERREQUEST FROM t_loandriverrequest A
						LEFT JOIN m_loantype B ON A.IDLOANTYPE = B.IDLOANTYPE
						WHERE A.IDDRIVER = ".$idDriver." AND B.STATUSLOANCAPITAL = ".$loanPrepaidCapitalType." AND A.STATUS IN (0,1)
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return true;
		return false;		
	}

	public function checkBankAccountValid($idPartnerType, $idDriver, $idBankAccountPartner){
		$query	=	$this->db->query(
						"SELECT IDBANKACCOUNTPARTNER FROM t_bankaccountpartner
						  WHERE IDBANKACCOUNTPARTNER = ".$idBankAccountPartner." AND IDPARTNERTYPE = ".$idPartnerType." AND
								IDPARTNER = ".$idDriver." AND STATUS = 1
						  LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return true;
		return false;		
	}
	
	public function getDetailRequestApproval($idDriver, $idLoanDriverRequest){
		
		$query	=	$this->db->query("SELECT A.IDLOANTYPE, C.BANKNAME, CONCAT('".URL_BANK_LOGO."', C.BANKLOGO) AS BANKLOGO, B.ACCOUNTNUMBER, B.ACCOUNTHOLDERNAME,
											CONCAT(IF(D.STATUSLOANCAPITAL = 1, 'Loan - ', ''), D.LOANTYPE) AS LOANTYPE, A.NOTES, A.AMOUNT, A.STATUS,
											IF(A.FILETRANSFERRECEIPT IS NULL OR A.FILETRANSFERRECEIPT = '', '-', CONCAT('".URL_TRANSFER_RECEIPT."', A.FILETRANSFERRECEIPT)) AS FILETRANSFERRECEIPT,
											CASE
												WHEN A.STATUS = 0 THEN 'Waiting for Approval'
												WHEN A.STATUS = 1 THEN 'Approved'
												WHEN A.STATUS = 2 THEN 'Transferred'
												WHEN A.STATUS = -1 THEN 'Rejected'
												WHEN A.STATUS = -2 THEN 'Canceled'
												ELSE '-'
											END AS STRSTATUS,
											IFNULL(DATE_FORMAT(A.DATETIMEINPUT, '%d %b %Y %H:%i'), '-') AS DATETIMEREQUEST, IFNULL(A.USERUPDATE, '-') AS USERUPDATE,
											IFNULL(DATE_FORMAT(A.DATETIMEUPDATE, '%d %b %Y %H:%i'), '-') AS DATETIMEUPDATE, IFNULL(A.USERCONFIRM, '-') AS USERCONFIRM,
											IFNULL(DATE_FORMAT(A.DATETIMECONFIRM, '%d %b %Y %H:%i'), '-') AS DATETIMECONFIRM, D.STATUSLOANCAPITAL,
											IF(E.RECEIPTFILE IS NOT NULL AND E.RECEIPTFILE != '', CONCAT('".URL_HTML_TRANSFER_RECEIPT."', E.RECEIPTFILE), '') AS RECEIPTFILE
									  FROM t_loandriverrequest A
									  LEFT JOIN t_bankaccountpartner B ON A.IDBANKACCOUNTPARTNER = B.IDBANKACCOUNTPARTNER
									  LEFT JOIN m_bank C ON B.IDBANK = C.IDBANK
									  LEFT JOIN m_loantype D ON A.IDLOANTYPE = D.IDLOANTYPE
									  LEFT JOIN t_transferlist E ON A.IDLOANDRIVERREQUEST = E.IDLOANDRIVERREQUEST AND E.IDLOANDRIVERREQUEST != 0
									  WHERE A.IDDRIVER = ".$idDriver." AND A.IDLOANDRIVERREQUEST = ".$idLoanDriverRequest."
									  LIMIT 1");
		$row	=	$query->row_array();

		if(isset($row)){
			return $row;
		}
		
		return false;
		
	}

	public function getTotalLoanPrepaidCapitalRequest(){
		
		$query	=	$this->db->query("SELECT COUNT(IDLOANDRIVERREQUEST) AS TOTALLOANPREPAIDCAPITALREQUEST
									  FROM t_loandriverrequest
									  WHERE STATUS = 0
									  LIMIT 1");
		$row	=	$query->row_array();

		if(isset($row)){
			return $row['TOTALLOANPREPAIDCAPITALREQUEST'];
		}
		
		return 0;
		
	}
	
	public function checkActiveInstallmentRequestExist($idDriver, $idLoanType){
		
		$query	=	$this->db->query("SELECT IDLOANDRIVERINSTALLMENTREQUEST FROM t_loandriverinstallmentrequest
									  WHERE IDDRIVER = ".$idDriver." AND IDLOANTYPE = ".$idLoanType." AND STATUS = 0
									  LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)){
			return true;
		}
		
		return false;
		
	}

	public function getNominalSaldoLoan($idDriver, $idLoanType){
		
		$query	=	$this->db->query("SELECT IFNULL(SUM(IF(IDLOANTYPE = ".$idLoanType.", IF(TYPE = 'D', AMOUNT, AMOUNT * -1), 0)), 0) AS TOTALLOAN
									  FROM t_loandriverrecord
									  WHERE IDDRIVER = ".$idDriver."
									  GROUP BY IDDRIVER
									  LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)){
			return $row['TOTALLOAN'];
		}
		
		return 0;
		
	}

	public function getTotalLoanInstallmentRequest(){
		
		$query	=	$this->db->query("SELECT COUNT(IDLOANDRIVERINSTALLMENTREQUEST) AS TOTALLOANINSTALLMENTREQUEST
									  FROM t_loandriverinstallmentrequest
									  WHERE STATUS = 0
									  LIMIT 1");
		$row	=	$query->row_array();

		if(isset($row)){
			return $row['TOTALLOANINSTALLMENTREQUEST'];
		}
		
		return 0;
		
	}
	
	public function getNominalSaldoLoanPerType($idDriver){
		
		$query	=	$this->db->query("SELECT A.IDLOANTYPE, IFNULL(B.TOTALLOAN, 0) AS TOTALLOAN
									  FROM m_loantype A
									  LEFT JOIN (
										SELECT IDLOANTYPE, IFNULL(SUM(IF(TYPE = 'D', AMOUNT, AMOUNT * -1)), 0) AS TOTALLOAN
										FROM t_loandriverrecord
										WHERE IDDRIVER = ".$idDriver."
										GROUP BY IDLOANTYPE
									  ) AS B ON A.IDLOANTYPE = B.IDLOANTYPE"
					);
		$result	=	$query->result();

		if(isset($result)){
			return $result;
		}
		
		return array();
		
	}
}