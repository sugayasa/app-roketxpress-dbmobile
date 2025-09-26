<?php
class ModelFinance extends CI_Model {

	public function __construct(){
		parent::__construct(); 
		$this->load->database();
	}

	public function getTotalFee($idPartnerType, $idPartner, $lastDatePeriod){
		$fieldWhere	=	$idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";
		$baseQuery	=	"SELECT IFNULL(SUM(FEENOMINAL), 0) AS TOTALFEE FROM t_fee
						WHERE ".$fieldWhere." = ".$idPartner." AND DATESCHEDULE <= '".$lastDatePeriod."' AND WITHDRAWSTATUS = 0 AND IDWITHDRAWALRECAP = 0";
		$query		=	$this->db->query($baseQuery);
		$row		=	$query->row_array();
		
		if(isset($row)) return $row['TOTALFEE'];
		return 0;		
	}
	
	public function getTotalAdditionalCost($idPartnerType, $idPartner, $lastDatePeriod){
		if($idPartnerType == 1){
			return 0;
		} else {
			$baseQuery	=	"SELECT IFNULL(SUM(A.NOMINAL), 0) AS TOTALADDITIONALCOST FROM t_reservationadditionalcost A
							LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
							WHERE A.IDDRIVER = ".$idPartner." AND B.SCHEDULEDATE <= '".$lastDatePeriod."' AND A.STATUSAPPROVAL = 1 AND A.IDWITHDRAWALRECAP = 0";
		}
		
		$query	=	$this->db->query($baseQuery);
		$row	=	$query->row_array();
		
		if(isset($row)) return $row['TOTALADDITIONALCOST'];
		return 0;		
	}
	
	public function isAdditionalIncomePaid($idPartnerType, $idPartner, $lastDatePeriod){
		if($idPartnerType == 1){
			return true;
		} else {
			$period		=	substr($lastDatePeriod, 0, 7);
			$baseQuery	=	"SELECT NOMINAL FROM t_additionalincomerecap
							WHERE IDDRIVER = ".$idPartner." AND PERIOD = '".$period."'
							LIMIT 1";
		}
		
		$query	=	$this->db->query($baseQuery);
		$row	=	$query->row_array();
		
		if(isset($row)) {
			$nominal	=	$row['NOMINAL'];
			if($nominal > 0 ) return true;
			if($nominal <= 0 ) return false;
		}
		
		return false;
	}
	
	public function getTotalReimbursement($idPartnerType, $idPartner, $lastDatePeriod){
		$conPartner	=	$idPartnerType == "1" ? "IDVENDOR = ".$idPartner : "IDDRIVER = ".$idPartner;
		$baseQuery	=	"SELECT IFNULL(SUM(NOMINAL), 0) AS TOTALREIMBURSEMENT FROM t_reimbursement
						WHERE ".$conPartner." AND RECEIPTDATE <= '".$lastDatePeriod."' AND STATUS = 1 AND IDWITHDRAWALRECAP = 0";
		$query		=	$this->db->query($baseQuery);
		$row		=	$query->row_array();
		
		if(isset($row)) return $row['TOTALREIMBURSEMENT'];
		return 0;
	}
	
	public function getTotalReviewBonusPunishment($idPartnerType, $idPartner, $lastDatePeriod){
		if($idPartnerType == 1){
			return 0;
		} else {
			$baseQuery	=	"SELECT IFNULL(SUM(A.NOMINALRESULT), 0) AS TOTALREVIEWBONUSPUNISHMENT
							FROM t_driverreviewbonus A
							LEFT JOIN t_driverreviewbonusperiod B ON A.IDDRIVERREVIEWBONUSPERIOD = B.IDDRIVERREVIEWBONUSPERIOD
							WHERE A.IDDRIVER = ".$idPartner." AND B.PERIODDATEEND <= '".$lastDatePeriod."' AND A.IDWITHDRAWALRECAP = 0";
		}

		$query		=	$this->db->query($baseQuery);
		$row		=	$query->row_array();
		
		if(isset($row)) return $row['TOTALREVIEWBONUSPUNISHMENT'];
		return 0;		
	}
	
	public function getTotalCollectPayment($idPartnerType, $idPartner, $lastDatePeriod){
		$conPartner	=	$idPartnerType == "1" ? "IDVENDOR = ".$idPartner : "IDDRIVER = ".$idPartner;
		$query		=	$this->db->query(
							"SELECT IFNULL(SUM(B.AMOUNTIDR), 0) AS TOTALAMOUNTCOLLECTPAYMENT
							FROM t_collectpayment A
							LEFT JOIN t_reservationpayment B ON A.IDRESERVATIONPAYMENT = B.IDRESERVATIONPAYMENT
							WHERE A.IDPARTNERTYPE = ".$idPartnerType." AND ".$conPartner." AND A.DATECOLLECT <= '".$lastDatePeriod."' AND A.STATUSSETTLEMENTREQUEST IN (0, -1) AND A.STATUS = 1
							GROUP BY A.IDPARTNERTYPE"
						);
		$row		=	$query->row_array();

		if(isset($row)) return $row['TOTALAMOUNTCOLLECTPAYMENT'];
		return 0;		
	}
	
	public function getDataLoanPrepaidCapital($idPartnerType, $idPartner, $typeLoanPrepaidCapital, $idLoanType = false){
		if($idPartnerType == 1){
			return 0;
		} else {
			$con_idLoanType	=	isset($idLoanType) && $idLoanType != false ? "B.IDLOANTYPE = ".$idLoanType : "1=1";
			$query			=	$this->db->query(
									"SELECT IFNULL(SUM(IF(A.TYPE = 'D', A.AMOUNT, A.AMOUNT * -1)), 0) AS TOTALBALANCE
									FROM t_loandriverrecord A
									LEFT JOIN m_loantype B ON A.IDLOANTYPE = B.IDLOANTYPE
									WHERE A.IDDRIVER = ".$idPartner." AND B.STATUSLOANCAPITAL = ".$typeLoanPrepaidCapital." AND ".$con_idLoanType."
									GROUP BY A.IDDRIVER"
								);
			$row			=	$query->row_array();
		}

		if(isset($row)) return $row['TOTALBALANCE'];
		return 0;		
	}
	
	public function getInstallmentLoan($idPartnerType, $idPartner, $idLoanType = false, $lastDatePeriod){
		$lastDatePeriodDT	=	new DateTime($lastDatePeriod);
		$yearMonthPeriod	=	$lastDatePeriodDT->format('Ym');

		if($idPartnerType == 1){
			return 0;
		} else {
			$con_idLoanType		=	isset($idLoanType) && $idLoanType != false ? "IDLOANTYPE = ".$idLoanType : "1=1";
			$con_lastPeriod		=	"REPLACE(LOANINSTALLMENTLASTPERIOD, '-', '') < '".$yearMonthPeriod."'";
			$query				=	$this->db->query(
										"SELECT SUM(LOANNOMINALSALDO) AS LOANNOMINALSALDO, SUM(LOANINSTALLMENTPERMONTH) AS LOANINSTALLMENTPERMONTH FROM t_loandriverrecap
										WHERE IDDRIVER = ".$idPartner." AND ".$con_lastPeriod." AND ".$con_idLoanType." AND
											  LOANNOMINALSALDO > 0 AND LOANSTATUS = 1
										GROUP BY IDDRIVER"
									);
			$row				=	$query->row_array();
		}

		if(isset($row)) {
			$totalNominalSaldo			=	$row['LOANNOMINALSALDO'];
			$totalInstallmentPerMonth	=	$row['LOANINSTALLMENTPERMONTH'];
			$currentInstallmentNominal	=	$totalNominalSaldo < $totalInstallmentPerMonth ? $totalNominalSaldo : $totalInstallmentPerMonth;
			$isExlusionExist			=	$this->isExlusionExist($idPartnerType, $idPartner, $yearMonthPeriod, $idLoanType);

			return $isExlusionExist ? 0 : $currentInstallmentNominal;
		}
		return 0;		
	}
	
	private function isExlusionExist($idPartnerType, $idPartner, $yearMonthPeriod, $idLoanType = false){
		$con_idLoanType	=	isset($idLoanType) && $idLoanType != false ? "IDLOANTYPE = ".$idLoanType : "1=1";
		$query			=	$this->db->query(
								"SELECT IDLOANDRIVEREXCLUSION FROM t_loandriverinstallmentexclusion
								WHERE IDDRIVER = ".$idPartner." AND INSTALLMENTPERIOD = '".$yearMonthPeriod."' AND ".$con_idLoanType."
								GROUP BY IDDRIVER"
							);
		$row			=	$query->row_array();
		
		if(isset($row)) return true;
		return false;
	}	
	
	public function getTotalUnfinishedSchedule($idPartnerType, $idPartner, $dateNewFinanceScheme, $lastDatePeriod){
		if($idPartnerType == 1) return 0;
		$query	=	$this->db->query(
						"SELECT COUNT(A.IDRESERVATIONDETAILS) AS TOTALUNFINISHEDSCHEDULE
						 FROM t_reservationdetails A
						 LEFT JOIN t_scheduledriver B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
						 WHERE B.IDDRIVER = ".$idPartner." AND A.STATUS = 1 AND B.STATUS != 3 AND
							   A.SCHEDULEDATE >= '".$dateNewFinanceScheme."' AND A.SCHEDULEDATE <= '".$lastDatePeriod."'"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row['TOTALUNFINISHEDSCHEDULE'];
		return 0;		
	}
	
	public function getTotalPendingAdditionalCost($idDriver, $lastDatePeriod){
		$query	=	$this->db->query(
						"SELECT COUNT(IDRESERVATIONADDITIONALCOST) AS TOTALPENDINGADDITIONALCOST
						FROM t_reservationadditionalcost
						WHERE IDDRIVER = ".$idDriver." AND DATE(DATETIMEINPUT) <= '".$lastDatePeriod."' AND STATUSAPPROVAL = 0
						GROUP BY IDDRIVER"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row['TOTALPENDINGADDITIONALCOST'];
		return 0;		
	}
	
	public function getTotalUnconfirmedCollectPayment($idPartnerType, $idPartner, $lastDatePeriod){
		$conPartner	=	$idPartnerType == "1" ? "IDVENDOR = ".$idPartner : "IDDRIVER = ".$idPartner;
		$query		=	$this->db->query(
							"SELECT COUNT(IDCOLLECTPAYMENT) AS TOTALUNCONFIRMEDCOLLECTPAYMENT
							FROM t_collectpayment
							WHERE IDPARTNERTYPE = ".$idPartnerType." AND ".$conPartner." AND STATUS = 0 AND DATECOLLECT <= '".$lastDatePeriod."'
							GROUP BY IDPARTNERTYPE"
						);
		$row		=	$query->row_array();

		if(isset($row)) return $row['TOTALUNCONFIRMEDCOLLECTPAYMENT'];
		return 0;		
	}
	
	public function getDataActiveWithdrawal($idPartnerType, $idPartner){
		$conPartner	=	$idPartnerType == "1" ? "A.IDVENDOR = ".$idPartner : "A.IDDRIVER = ".$idPartner;
		$query		=	$this->db->query(
							"SELECT A.IDWITHDRAWALRECAP, A.TOTALWITHDRAWAL, A.MESSAGE, B.BANKNAME, A.ACCOUNTNUMBER, A.ACCOUNTHOLDERNAME
							FROM t_withdrawalrecap A
							LEFT JOIN m_bank B ON A.IDBANK = B.IDBANK
							WHERE ".$conPartner." AND A.STATUSWITHDRAWAL = 0
							LIMIT 1"
						);
		$row		=	$query->row_array();

		if(isset($row)) return $row;
		return false;		
	}
	
	public function getListDetailWithdrawal($idVendor, $idDriver, $idWithdrawalRecap = 0, $lastDatePeriod = false){
		$con_joinA	=	$idWithdrawalRecap == 0 ? "(AB.STATUSWITHDRAWAL = 0 OR AB.IDWITHDRAWALRECAP IS NULL)" : "AA.IDWITHDRAWALRECAP = ".$idWithdrawalRecap;
		$con_joinB	=	$idWithdrawalRecap == 0 ? "(BB.STATUSWITHDRAWAL = 0 OR BB.IDWITHDRAWALRECAP IS NULL)" : "BA.IDWITHDRAWALRECAP = ".$idWithdrawalRecap;
		$con_joinC	=	$idWithdrawalRecap == 0 ? "(CC.STATUSWITHDRAWAL = 0 OR CC.IDWITHDRAWALRECAP IS NULL)" : "CA.IDWITHDRAWALRECAP = ".$idWithdrawalRecap;
		$con_joinD	=	$idWithdrawalRecap == 0 ? "(DB.STATUSWITHDRAWAL = 0 OR DB.IDWITHDRAWALRECAP IS NULL)" : "DA.IDWITHDRAWALRECAP = ".$idWithdrawalRecap;
		$con_joinG	=	$idWithdrawalRecap == 0 ? "(IDDRIVER = ".$idDriver." AND IDVENDOR = ".$idVendor." AND IDWITHDRAWALRECAP = 0)" : "IDWITHDRAWALRECAP = ".$idWithdrawalRecap;
		$con_joinH	=	$idWithdrawalRecap == 0 ? "(HA.IDDRIVER = ".$idDriver." AND HB.PERIODDATEEND <= '".$lastDatePeriod."' AND HA.IDWITHDRAWALRECAP = 0)" : "HA.IDWITHDRAWALRECAP = ".$idWithdrawalRecap;

		$con_dateA	=	$lastDatePeriod == false ? "1=1" : "AA.DATESCHEDULE <= '".$lastDatePeriod."'";
		$con_dateB	=	$lastDatePeriod == false ? "1=1" : "BC.SCHEDULEDATE <= '".$lastDatePeriod."'";
		$con_dateC	=	$lastDatePeriod == false ? "1=1" : "CA.DATECOLLECT <= '".$lastDatePeriod."'";
		$con_dateG	=	$lastDatePeriod == false ? "1=1" : "RECEIPTDATE <= '".$lastDatePeriod."'";
		$baseQuery	=	"SELECT DATESTR, TYPESTR, DESCRIPTION, NOMINAL FROM (
							SELECT 1 AS TYPE, 'Fee' AS TYPESTR, AA.DATESCHEDULE AS DATEDB, DATE_FORMAT(AA.DATESCHEDULE, '%d %b %Y') AS DATESTR,
								 CONCAT(IF(AC.BOOKINGCODE IS NULL, '', CONCAT('[', AC.BOOKINGCODE, '] ')), AA.JOBTITLE) AS DESCRIPTION, AA.FEENOMINAL AS NOMINAL
							FROM t_fee AA
							LEFT JOIN t_withdrawalrecap AB ON AA.IDWITHDRAWALRECAP = AB.IDWITHDRAWALRECAP
							LEFT JOIN t_reservation AC ON AA.IDRESERVATION = AC.IDRESERVATION
							WHERE AA.IDVENDOR = ".$idVendor." AND AA.IDDRIVER = ".$idDriver." AND ".$con_joinA." AND ".$con_dateA."
							UNION ALL
							SELECT 2 AS TYPE, 'Additional Cost' AS TYPESTR, DATE(BA.DATETIMEINPUT) AS DATEDB, DATE_FORMAT(BA.DATETIMEINPUT, '%d %b %Y') AS DATESTR,
								 IFNULL(BA.DESCRIPTION, '-') AS DESCRIPTION, BA.NOMINAL
							FROM t_reservationadditionalcost BA
							LEFT JOIN t_withdrawalrecap BB ON BA.IDWITHDRAWALRECAP = BB.IDWITHDRAWALRECAP
							LEFT JOIN t_reservationdetails BC ON BA.IDRESERVATIONDETAILS = BC.IDRESERVATIONDETAILS
							WHERE BA.IDDRIVER = ".$idDriver." AND BA.STATUSAPPROVAL = 1 AND ".$con_joinB." AND ".$con_dateB."
							UNION ALL
							SELECT 3 AS TYPE, 'Collect Payment' AS TYPESTR, DATE(CA.DATECOLLECT) AS DATEDB, DATE_FORMAT(CA.DATECOLLECT, '%d %b %Y') AS DATESTR,
								 IFNULL(CB.DESCRIPTION, '-') AS DESCRIPTION, CB.AMOUNTIDR * -1 AS NOMINAL
							FROM t_collectpayment CA
							LEFT JOIN t_reservationpayment CB ON CA.IDRESERVATIONPAYMENT = CB.IDRESERVATIONPAYMENT
							LEFT JOIN t_withdrawalrecap CC ON CA.IDWITHDRAWALRECAP = CC.IDWITHDRAWALRECAP
							WHERE CA.IDVENDOR = ".$idVendor." AND CA.IDDRIVER = ".$idDriver." AND CA.STATUSSETTLEMENTREQUEST IN (0, -1) AND
								CA.STATUS = 1 AND ".$con_joinC." AND ".$con_dateC."
							UNION ALL
							SELECT 4 AS TYPE, 'Prepaid Capital' AS TYPESTR, DATE(DA.DATETIMEINPUT) AS DATEDB, DATE_FORMAT(DA.DATETIMEINPUT, '%d %b %Y') AS DATESTR,
								 IFNULL(DA.DESCRIPTION, '-') AS DESCRIPTION, DA.AMOUNT * -1 AS NOMINAL
							FROM t_loandriverrecord DA
							LEFT JOIN t_withdrawalrecap DB ON DA.IDWITHDRAWALRECAP = DB.IDWITHDRAWALRECAP
							WHERE DA.IDDRIVER = ".$idDriver." AND DA.IDLOANTYPE = 3 AND DA.TYPE = 'D' AND ".$con_joinD."
							UNION ALL
							SELECT 5 AS TYPE, 'Loan Installment' AS TYPESTR, DATE(DATETIMEREQUEST) AS DATEDB, DATE_FORMAT(DATETIMEREQUEST, '%d %b %Y') AS DATESTR,
								 'Car loan installment' AS DESCRIPTION, TOTALLOANCARINSTALLMENT * -1 AS NOMINAL
							FROM t_withdrawalrecap
							WHERE IDWITHDRAWALRECAP = ".$idWithdrawalRecap." AND TOTALLOANCARINSTALLMENT > 0
							UNION ALL
							SELECT 6 AS TYPE, 'Loan Installment' AS TYPESTR, DATE(DATETIMEREQUEST) AS DATEDB, DATE_FORMAT(DATETIMEREQUEST, '%d %b %Y') AS DATESTR,
								 'Personal loan installment' AS DESCRIPTION, TOTALLOANPERSONALINSTALLMENT * -1 AS NOMINAL
							FROM t_withdrawalrecap
							WHERE IDWITHDRAWALRECAP = ".$idWithdrawalRecap." AND TOTALLOANPERSONALINSTALLMENT > 0
							UNION ALL
							SELECT 7 AS TYPE, 'Reimbursement' AS TYPESTR, DATE(RECEIPTDATE) AS DATEDB, DATE_FORMAT(RECEIPTDATE, '%d %b %Y') AS DATESTR,
								DESCRIPTION, NOMINAL
							FROM t_reimbursement
							WHERE ".$con_joinG." AND ".$con_dateG."
							UNION ALL
							SELECT 8 AS TYPE, 'Review Bonus Punishment' AS TYPESTR, DATE(HB.PERIODDATEEND) AS DATEDB, DATE_FORMAT(HB.PERIODDATEEND, '%d %b %Y') AS DATESTR,
								 CONCAT('Review Bonus/Punishment - ', DATE_FORMAT(CONCAT(HB.PERIODMONTHYEAR, '-01'), '%M %Y')) AS DESCRIPTION, HA.NOMINALRESULT AS NOMINAL
							FROM t_driverreviewbonus HA
							LEFT JOIN t_driverreviewbonusperiod HB ON HA.IDDRIVERREVIEWBONUSPERIOD = HB.IDDRIVERREVIEWBONUSPERIOD
							WHERE ".$con_joinH."
							UNION ALL
							SELECT 9 AS TYPE, 'Charity Program' AS TYPESTR, DATE(DATETIMEREQUEST) AS DATEDB, DATE_FORMAT(DATETIMEREQUEST, '%d %b %Y') AS DATESTR,
								 'Charity program' AS DESCRIPTION, TOTALCHARITY * -1 AS NOMINAL
							FROM t_withdrawalrecap
							WHERE IDWITHDRAWALRECAP = ".$idWithdrawalRecap." AND TOTALCHARITY > 0
							UNION ALL
							SELECT 10 AS TYPE, 'Additional Income (SS)' AS TYPESTR, DATE(DATETIMEREQUEST) AS DATEDB, DATE_FORMAT(DATETIMEREQUEST, '%d %b %Y') AS DATESTR,
								 'Additional Income (SS)' AS DESCRIPTION, TOTALADDITIONALINCOME * -1 AS NOMINAL
							FROM t_withdrawalrecap
							WHERE IDWITHDRAWALRECAP = ".$idWithdrawalRecap." AND TOTALADDITIONALINCOME > 0
						) AS A
						ORDER BY DATEDB, TYPE";
		$query		=	$this->db->query($baseQuery);
		$result		=	$query->result();

		if(isset($result)) return $result;
		return false;		
	}
	
	public function getDataFeeWithdrawal($idVendor, $idDriver, $lastDatePeriod){
		$query	=	$this->db->query(
						"SELECT IDFEE FROM t_fee
						WHERE IDVENDOR = ".$idVendor." AND IDDRIVER = ".$idDriver." AND DATESCHEDULE <= '".$lastDatePeriod."' AND WITHDRAWSTATUS = 0 AND IDWITHDRAWALRECAP = 0"
					);
		$result	=	$query->result();

		if(isset($result)) return $result;
		return false;		
	}
	
	public function getDataAdditionalCostWithdrawal($idDriver, $lastDatePeriod){
		$query	=	$this->db->query(
						"SELECT A.IDRESERVATIONADDITIONALCOST FROM t_reservationadditionalcost A
						LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
						WHERE A.IDDRIVER = ".$idDriver." AND B.SCHEDULEDATE <= '".$lastDatePeriod."' AND A.STATUSAPPROVAL = 1 AND A.IDWITHDRAWALRECAP = 0"
					);
		$result	=	$query->result();

		if(isset($result)) return $result;
		return false;		
	}
	
	public function getDataReimbursementWithdrawal($idDriver, $idVendor, $lastDatePeriod){
		$query	=	$this->db->query(
						"SELECT IDREIMBURSEMENT FROM t_reimbursement
						WHERE IDDRIVER = ".$idDriver." AND IDVENDOR = ".$idVendor." AND RECEIPTDATE <= '".$lastDatePeriod."' AND STATUS = 1 AND IDWITHDRAWALRECAP = 0"
					);
		$result	=	$query->result();

		if(isset($result)) return $result;
		return false;		
	}
	
	public function getDataReviewBonusPunishmentWithdrawal($idDriver, $lastDatePeriod){		
		$query	=	$this->db->query(
						"SELECT A.IDDRIVERREVIEWBONUS FROM t_driverreviewbonus A
						 LEFT JOIN t_driverreviewbonusperiod B ON A.IDDRIVERREVIEWBONUSPERIOD = B.IDDRIVERREVIEWBONUSPERIOD
						 WHERE A.IDDRIVER = ".$idDriver." AND B.PERIODDATEEND <= '".$lastDatePeriod."' AND A.IDWITHDRAWALRECAP = 0"
					);
		$result	=	$query->result();

		if(isset($result)) return $result;
		return false;		
	}
	
	public function getDataCollectPaymentWithdrawal($idVendor, $idDriver, $lastDatePeriod){
		$query	=	$this->db->query(
						"SELECT IDCOLLECTPAYMENT FROM t_collectpayment
						WHERE IDVENDOR = ".$idVendor." AND IDDRIVER = ".$idDriver." AND DATECOLLECT <= '".$lastDatePeriod."' AND IDWITHDRAWALRECAP = 0 AND STATUS = 1 AND STATUSSETTLEMENTREQUEST IN (0, -1)"
					);
		$result	=	$query->result();

		if(isset($result)) return $result;
		return false;		
	}
	
	public function getDataPrepaidCapitalDriver($idDriver){
		$query	=	$this->db->query(
						"SELECT IDLOANDRIVERRECORD FROM t_loandriverrecord
						WHERE IDDRIVER = ".$idDriver." AND IDLOANTYPE = 3 AND IDWITHDRAWALRECAP = 0 AND TYPE = 'D'"
					);
		$result	=	$query->result();

		if(isset($result)) return $result;
		return false;		
	}
	
	public function getListWithdrawalHistory($idVendor, $idDriver, $yearMonth){
		$query	=	$this->db->query(
						"SELECT IDWITHDRAWALRECAP, DATE_FORMAT(DATETIMEREQUEST, '%d %b %Y %H:%i') AS DATETIMEREQUEST,
								TOTALWITHDRAWAL, STATUSWITHDRAWAL
						FROM t_withdrawalrecap
						WHERE IDVENDOR = ".$idVendor." AND IDDRIVER = ".$idDriver." AND LEFT(DATETIMEREQUEST, 7) = '".$yearMonth."'"
					);
		$result	=	$query->result();

		if(isset($result)) return $result;
		return false;		
	}
	
	public function getDetailWithdrawalRecap($idWithdrawalRecap){
		$query	=	$this->db->query(
						"SELECT CONCAT('".URL_BANK_LOGO."', B.BANKLOGO) AS BANKLOGO, B.BANKNAME, A.ACCOUNTNUMBER, A.ACCOUNTHOLDERNAME,
							DATE_FORMAT(A.DATETIMEREQUEST, '%d %b %Y %H:%i') AS DATETIMEREQUEST, A.STATUSWITHDRAWAL, A.TOTALFEE, A.TOTALADDITIONALCOST,
							A.TOTALADDITIONALINCOME, A.TOTALREIMBURSEMENT, A.TOTALREVIEWBONUSPUNISHMENT, A.TOTALCOLLECTPAYMENT * -1 AS TOTALCOLLECTPAYMENT,
							A.TOTALPREPAIDCAPITAL * -1 AS TOTALPREPAIDCAPITAL,  A.TOTALLOANCARINSTALLMENT * -1 ASTOTALLOANCARINSTALLMENT,
							A.TOTALLOANPERSONALINSTALLMENT * -1 AS TOTALLOANPERSONALINSTALLMENT, A.TOTALCHARITY * -1 AS TOTALCHARITY, A.TOTALWITHDRAWAL, A.MESSAGE,
							CONCAT('".URL_HTML_TRANSFER_RECEIPT."', IF(C.RECEIPTFILE IS NOT NULL AND C.RECEIPTFILE != '', C.RECEIPTFILE, 'unavailable.html')) AS URLRECEIPTFILE
						FROM t_withdrawalrecap A
						LEFT JOIN m_bank B ON A.IDBANK = B.IDBANK
						LEFT JOIN t_transferlist C ON A.IDWITHDRAWALRECAP = C.IDWITHDRAWAL
						WHERE IDWITHDRAWALRECAP = ".$idWithdrawalRecap."
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return false;		
	}
	
	public function getTotalWithdrawalRequest($idPartnerType = 2){
		$whereField	=	$idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";
		$query		=	$this->db->query(
							"SELECT COUNT(IDWITHDRAWALRECAP) AS TOTALWITHDRAWALREQUEST
							FROM t_withdrawalrecap
							WHERE STATUSWITHDRAWAL = 0 AND ".$whereField." != 0
							LIMIT 1"
						);
		$row		=	$query->row_array();

		if(isset($row)) return $row['TOTALWITHDRAWALREQUEST'];
		return 0;		
	}
	
	public function getDataDepositBalanceVendor($idVendor){
		$query	=	$this->db->query(
						"SELECT SUM(AMOUNT) AS DEPOSITBALANCE, DATE_FORMAT(MAX(DATETIMEINPUT), '%d %b %Y') AS LASTDEPOSITTRANSACTION
						FROM t_depositvendorrecord
						WHERE IDVENDOR = ".$idVendor."
						GROUP BY IDVENDOR
						LIMIT 1"
					);
		$row	=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"DEPOSITBALANCE"		=>	0,
			"LASTDEPOSITTRANSACTION"=>	"-"
		);		
	}
	
	public function getDataFeeThisMonthDepositSchemeVendor($idVendor){
		$monthYear	=	date('Y-m');
		$query		=	$this->db->query(
							"SELECT COUNT(IDRESERVATIONDETAILS) AS TOTALFEETHISMONTHJOB, SUM(NOMINAL) AS TOTALFEETHISMONTHNOMINAL
							FROM t_reservationdetails
							WHERE IDVENDOR = ".$idVendor." AND LEFT(SCHEDULEDATE, 7) = '".$monthYear."'
							GROUP BY IDVENDOR
							LIMIT 1"
						);
		$row		=	$query->row_array();

		if(isset($row)) return $row;
		return array(
			"TOTALFEETHISMONTHJOB"		=>	0,
			"TOTALFEETHISMONTHNOMINAL"	=>	0
		);		
	}
	
	public function getDataDepositRecordHistory($idVendor, $dateStart, $dateEnd){
		$baseQuery	=	"SELECT DATE_FORMAT(DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMEINPUTSTR, DESCRIPTION, AMOUNT, IDRESERVATIONDETAILS,
								IDCOLLECTPAYMENT, IF(TRANSFERRECEIPT = '', '', CONCAT('".URL_TRANSFER_RECEIPT."', TRANSFERRECEIPT)) AS TRANSFERRECEIPT
						FROM t_depositvendorrecord
						WHERE IDVENDOR = ".$idVendor." AND DATE(DATETIMEINPUT) BETWEEN '".$dateStart."' AND '".$dateEnd."'
						ORDER BY DATETIMEINPUT";
		$query		=	$this->db->query($baseQuery);
		$result		=	$query->result();
		
		if(isset($result)) return $result;
		return new stdClass();		
	}
}