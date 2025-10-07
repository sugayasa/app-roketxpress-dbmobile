<?php
defined('BASEPATH') OR exit('No direct script access allowed');
	
$route['default_controller']										= 'Main';
$route['404_override']												= '';
$route['translate_uri_dashes']										= FALSE;

$route['accessCheck']['POST']										= 'Access/accessCheck';
$route['logout']['POST']											= 'Access/logout';
$route['getOptionHelper']['POST']									= 'Access/getOptionHelper';
$route['updateLastPosition']['POST']								= 'Access/updateLastPosition';
$route['submitEmail']['POST']										= 'Login/submitEmail';
$route['submitOTP']['POST']											= 'Login/submitOTP';

$route['dashboard/dataDashboard']['POST']							= 'Dashboard/dataDashboard';
$route['dashboard/dataNotification']['POST']						= 'Dashboard/dataNotification';
$route['dashboard/carVendorList']['POST']							= 'Dashboard/carVendorList';

$route['agreementDriver/fileMaster/(:any)']['GET']					= 'AgreementDriver/fileMaster/$1';
$route['agreementDriver/signedLetter/(:any)']['GET']				= 'AgreementDriver/signedLetter/$1';
$route['agreementDriver/uploadAgreementSignature']['POST']			= 'AgreementDriver/uploadAgreementSignature';
$route['agreementDriver/uploadDriverIdentity']['POST']				= 'AgreementDriver/uploadDriverIdentity';
$route['agreementDriver/submitAgreement']['POST']					= 'AgreementDriver/submitAgreement';
$route['agreementDriver/agreementList']['POST']						= 'AgreementDriver/agreementList';

$route['profile/detailProfile']['POST']								= 'Profile/detailProfile';
$route['profile/detailHistoryPoint']['POST']						= 'Profile/detailHistoryPoint';
$route['profile/detailRatingPoint']['POST']							= 'Profile/detailRatingPoint';
$route['profile/detailReview']['POST']								= 'Profile/detailReview';
$route['profile/listAreaPriority']['POST']							= 'Profile/listAreaPriority';
$route['profile/setSecretPIN']['POST']								= 'Profile/setSecretPIN';
$route['profile/addBankAccountCheckSecretPIN']['POST']				= 'Profile/addBankAccountCheckSecretPIN';
$route['profile/addBankAccountCheckOTPAndSubmit']['POST']			= 'Profile/addBankAccountCheckOTPAndSubmit';
$route['profile/deleteBankAccountCheckSecretPIN']['POST']			= 'Profile/deleteBankAccountCheckSecretPIN';
$route['profile/uploadProfilePicture']['POST']						= 'Profile/uploadProfilePicture';
$route['profile/profilePicture/(:any)']['GET']						= 'Profile/profilePicture/$1';

$route['driverGroupMember/driverMemberList']['POST']				= 'DriverGroupMember/driverMemberList';
$route['driverGroupMember/insertDriverMember']['POST']				= 'DriverGroupMember/insertDriverMember';
$route['driverGroupMember/updateDriverMember']['POST']				= 'DriverGroupMember/updateDriverMember';
$route['driverGroupMember/deleteDriverMember']['POST']				= 'DriverGroupMember/deleteDriverMember';

$route['order/listOrderByDate']['POST']								= 'Order/listOrderByDate';
$route['order/detailOrder']['POST']									= 'Order/detailOrder';
$route['order/detailOrderCancel']['POST']							= 'Order/detailOrderCancel';
$route['order/confirmOrder']['POST']								= 'Order/confirmOrder';
$route['order/updateDetailDriverHandle']['POST']					= 'Order/updateDetailDriverHandle';
$route['order/confirmCollectPayment']['POST']						= 'Order/confirmCollectPayment';
$route['order/updateStatusOrder']['POST']							= 'Order/updateStatusOrder';
$route['order/templateReviewOrder']['POST']							= 'Order/templateReviewOrder';
$route['order/updateCoinBookingEcommerceTest']['GET']				= 'Order/updateCoinBookingEcommerceTest';

$route['dropOffPickUpCar/listOrderByDate']['POST']					= 'DropOffPickUpCar/listOrderByDate';
$route['dropOffPickUpCar/detailOrder']['POST']						= 'DropOffPickUpCar/detailOrder';
$route['dropOffPickUpCar/updateStatusOrder']['POST']				= 'DropOffPickUpCar/updateStatusOrder';
$route['dropOffPickUpCar/addAdditionalCost']['POST']				= 'DropOffPickUpCar/addAdditionalCost';

$route['additionalCost/listSchedule']['POST']						= 'AdditionalCost/listSchedule';
$route['additionalCost/addAdditionalCost']['POST']					= 'AdditionalCost/addAdditionalCost';
$route['additionalCost/uploadImageAdditionalCost']['POST']			= 'AdditionalCost/uploadImageAdditionalCost';
$route['additionalCost/imageAdditionalCost/(:any)']['GET']			= 'AdditionalCost/imageAdditionalCost/$1';
$route['additionalCost/listAdditionalCost']['POST']					= 'AdditionalCost/listAdditionalCost';
$route['additionalCost/listActiveAdditionalCost']['POST']			= 'AdditionalCost/listActiveAdditionalCost';

$route['reimbursement/listReimbursement']['POST']					= 'Reimbursement/listReimbursement';
$route['reimbursement/addReimbursement']['POST']					= 'Reimbursement/addReimbursement';
$route['reimbursement/uploadImageReimbursement']['POST']			= 'Reimbursement/uploadImageReimbursement';
$route['reimbursement/imageReimbursement/(:any)']['GET']			= 'Reimbursement/imageReimbursement/$1';

$route['reviewBonusPunishment/summaryData']['POST']					= 'ReviewBonusPunishment/summaryData';
$route['reviewBonusPunishment/detailReview']['POST']				= 'ReviewBonusPunishment/detailReview';
$route['reviewBonusPunishment/tableSimulation']['POST']				= 'ReviewBonusPunishment/tableSimulation';

$route['additionalIncome/reportData']['POST']						= 'AdditionalIncome/reportData';
$route['additionalIncome/uploadImageAdditionalIncome']['POST']		= 'AdditionalIncome/uploadImageAdditionalIncome';
$route['additionalIncome/submitAdditionalIncome']['POST']			= 'AdditionalIncome/submitAdditionalIncome';
$route['additionalIncome/imageAdditionalIncome/(:any)']['GET']		= 'AdditionalIncome/imageAdditionalIncome/$1';

$route['fee/driver/recapListFee']['POST']							= 'Fee/recapListFee';
$route['fee/recapListFee']['POST']									= 'Fee/recapListFee';

$route['collectPayment/dashboardCollectPayment']['POST']			= 'CollectPayment/dashboardCollectPayment';
$route['collectPayment/historyCollectPayment']['POST']				= 'CollectPayment/historyCollectPayment';
$route['collectPayment/detailCollectPayment']['POST']				= 'CollectPayment/detailCollectPayment';
$route['collectPayment/submitSettlementCollectPayment']['POST']		= 'CollectPayment/submitSettlementCollectPayment';
$route['collectPayment/uploadImageSettlementCollectPayment']['POST']= 'CollectPayment/uploadImageSettlementCollectPayment';
$route['collectPayment/imageSettlementCollectPayment/(:any)']['GET']= 'CollectPayment/imageSettlementCollectPayment/$1';

$route['loanPrepaidCapital/summaryListHistory']['POST']				= 'LoanPrepaidCapital/summaryListHistory';
$route['loanPrepaidCapital/requestList']['POST']					= 'LoanPrepaidCapital/requestList';
$route['loanPrepaidCapital/createRequest']['POST']					= 'LoanPrepaidCapital/createRequest';
$route['loanPrepaidCapital/detailRequestApproval']['POST']			= 'LoanPrepaidCapital/detailRequestApproval';
$route['loanPrepaidCapital/confirmReceiptFunds']['POST']			= 'LoanPrepaidCapital/confirmReceiptFunds';
$route['loanPrepaidCapital/uploadImageInstallmentLoan']['POST']		= 'LoanPrepaidCapital/uploadImageInstallmentLoan';
$route['loanPrepaidCapital/addInstallmentLoanRequest']['POST']		= 'LoanPrepaidCapital/addInstallmentLoanRequest';

$route['finance/withdrawal/summaryFinance']['POST']					= 'Finance/summaryFinance';
$route['finance/withdrawal/listDetailWithdrawal']['POST']			= 'Finance/listDetailWithdrawal';
$route['finance/withdrawal/submitWithdrawalRequest']['POST']		= 'Finance/submitWithdrawalRequest';
$route['finance/withdrawal/withdrawalHistory']['POST']				= 'Finance/withdrawalHistory';
$route['finance/withdrawal/detailWithdrawalHistory']['POST']		= 'Finance/detailWithdrawalHistory';
$route['finance/deposit/summaryFinance']['POST']					= 'Finance/summaryFinanceDeposit';
$route['finance/deposit/depositRecordHistory']['POST']				= 'Finance/depositRecordHistory';

$route['dayOff/car/dayOffCalendar']['POST']							= 'DayOff/Car/dayOffCalendar';
$route['dayOff/car/dayOffDetail']['POST']							= 'DayOff/Car/dayOffDetail';
$route['dayOff/car/submitDayOffRequest']['POST']					= 'DayOff/Car/submitDayOff';
$route['dayOff/car/dayOffRequestList']['POST']						= 'DayOff/Car/dayOffRequestList';

$route['dayOff/driver/dayOffCalendar']['POST']						= 'DayOff/Driver/dayOffCalendar';
$route['dayOff/driver/dayOffDetail']['POST']						= 'DayOff/Driver/dayOffDetail';
$route['dayOff/driver/submitDayOffRequest']['POST']					= 'DayOff/Driver/submitDayOffRequest';
$route['dayOff/driver/dayOffRequestList']['POST']					= 'DayOff/Driver/dayOffRequestList';
$route['dayOff/driver/submitDayOff']['POST']						= 'DayOff/Driver/submitDayOffRequest';
$route['dayOff/driver/submitAvailable']['POST']						= 'DayOff/Driver/submitAvailable';

$route['contactCenter/contactList']['POST']							= 'ContactCenter/contactList';

$route['feedback/listFeedback']['POST']								= 'Feedback/listFeedback';
$route['feedback/submitFeedback']['POST']							= 'Feedback/submitFeedback';
$route['feedback/uploadImageFeedback']['POST']						= 'Feedback/uploadImageFeedback';
$route['feedback/imageFeedback/(:any)']['GET']						= 'Feedback/imageFeedback/$1';

$route['helpCenter/category/(:any)']['POST']						= 'HelpCenter/category/$1';
$route['helpCenter/article/(:any)']['GET']							= 'HelpCenter/article/$1';