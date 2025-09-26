<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
$url			=	$_SERVER['HTTP_HOST'];
$domain			=	explode(".",$_SERVER['HTTP_HOST']);
$subdomain		=	$domain[0];
$productionURL	=	ENVIRONMENT == 'production' ? true : false;

defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

defined('PRODUCTION_URL')						OR define('PRODUCTION_URL', $productionURL);
defined('MAX_LOGIN_LIFETIME')					OR define('MAX_LOGIN_LIFETIME', $_ENV['MAX_LOGIN_LIFETIME'] ?: 14400); //max login lifetime in seconds
defined('TOKEN_LENGTH')							OR define('TOKEN_LENGTH', $_ENV['TOKEN_LENGTH'] ?: 40); //login token char length, max length 50
defined('TOKEN_MAXAGE_SECONDS')					OR define('TOKEN_MAXAGE_SECONDS', $_ENV['TOKEN_MAXAGE_SECONDS'] ?: 300); //cust mobile app token max age in seconds

defined('LOGIN_TOKEN_LENGTH')					OR define('LOGIN_TOKEN_LENGTH', $_ENV['LOGIN_TOKEN_LENGTH'] ?: 30); //login token char length
defined('LOGIN_TOKEN_MAXAGE_SECONDS')			OR define('LOGIN_TOKEN_MAXAGE_SECONDS', $_ENV['LOGIN_TOKEN_MAXAGE_SECONDS'] ?: 300); //login token max age in seconds
defined('LOGIN_TOKEN_MAXAGE_DIFF')				OR define('LOGIN_TOKEN_MAXAGE_DIFF', $_ENV['LOGIN_TOKEN_MAXAGE_DIFF'] ?: 60); //login token max age difference tolerance in seconds

defined('URL_BASE')								OR define('URL_BASE', $_ENV['URL_BASE'] ?: 'https://example.com/');
defined('URL_BASE_WEB_ADMIN')					OR define('URL_BASE_WEB_ADMIN', $_ENV['URL_BASE_WEB_ADMIN'] ?: 'https://example.com/');
defined('URL_SOURCE_LOGO')						OR define('URL_SOURCE_LOGO', URL_BASE_WEB_ADMIN.$_ENV['URL_SOURCE_LOGO'] ?: 'foto/sourceLogo/');
defined('URL_BASE_ASSETS')						OR define('URL_BASE_ASSETS', URL_BASE.$_ENV['URL_BASE_ASSETS'] ?: 'assets/');
defined('URL_BASE_HELP_CENTER')					OR define('URL_BASE_HELP_CENTER', URL_BASE.$_ENV['URL_BASE_HELP_CENTER'] ?: 'helpCenter/');
defined('URL_BASE_AGREEMENT_MASTER')			OR define('URL_BASE_AGREEMENT_MASTER', URL_BASE.$_ENV['URL_BASE_AGREEMENT_MASTER'] ?: 'agreementDriver/fileMaster/');
defined('URL_BASE_AGREEMENT_SIGNED_LETTER')		OR define('URL_BASE_AGREEMENT_SIGNED_LETTER', URL_BASE.$_ENV['URL_BASE_AGREEMENT_SIGNED_LETTER'] ?: 'agreementDriver/signedLetter/');
defined('IMAGE_NEWS_URL')						OR define('IMAGE_NEWS_URL', URL_BASE_WEB_ADMIN.$_ENV['IMAGE_NEWS_URL'] ?: 'assets/img-news/');

defined('URL_BANK_LOGO')						OR define('URL_BANK_LOGO', URL_BASE_WEB_ADMIN.$_ENV['URL_BANK_LOGO'] ?: 'foto/bankLogo/');
defined('URL_TRANSFER_RECEIPT')					OR define('URL_TRANSFER_RECEIPT', URL_BASE_WEB_ADMIN.$_ENV['URL_TRANSFER_RECEIPT'] ?: 'foto/transferReceipt/');
defined('URL_PROFILE_PICTURE')					OR define('URL_PROFILE_PICTURE', URL_BASE.$_ENV['URL_PROFILE_PICTURE'] ?: 'profile/profilePicture/');
defined('URL_ADDITIONAL_COST_IMAGE')			OR define('URL_ADDITIONAL_COST_IMAGE', URL_BASE.$_ENV['URL_ADDITIONAL_COST_IMAGE'] ?: 'additionalCost/imageAdditionalCost/');
defined('URL_ADDITIONAL_INCOME_IMAGE')			OR define('URL_ADDITIONAL_INCOME_IMAGE', URL_BASE.$_ENV['URL_ADDITIONAL_INCOME_IMAGE'] ?: 'additionalCost/imageAdditionalIncome/');
defined('URL_FEEDBACK_IMAGE')					OR define('URL_FEEDBACK_IMAGE', URL_BASE.$_ENV['URL_FEEDBACK_IMAGE'] ?: 'feedback/imageFeedback/');
defined('URL_COLLECT_PAYMENT_RECEIPT')			OR define('URL_COLLECT_PAYMENT_RECEIPT', URL_BASE.$_ENV['URL_COLLECT_PAYMENT_RECEIPT'] ?: 'collectPayment/imageSettlementCollectPayment/');
defined('URL_REIMBURSEMENT_IMAGE')				OR define('URL_REIMBURSEMENT_IMAGE', URL_BASE.$_ENV['URL_REIMBURSEMENT_IMAGE'] ?: 'reimbursement/imageReimbursement/');
defined('URL_HTML_TRANSFER_RECEIPT')			OR define('URL_HTML_TRANSFER_RECEIPT', URL_BASE_WEB_ADMIN.$_ENV['URL_HTML_TRANSFER_RECEIPT'] ?: 'file/transferReceiptHTML/');
defined('URL_TRIPADVISOR_REVIEW')				OR define('URL_TRIPADVISOR_REVIEW', $_ENV['URL_TRIPADVISOR_REVIEW'] ?: 'https://www.tripadvisor.co.id/Bali.html');
defined('URL_KLOOK_REVIEW')						OR define('URL_KLOOK_REVIEW', $_ENV['URL_KLOOK_REVIEW'] ?: 'https://www.klook.com/my_reviews/?spm=Booking.MyReview');

defined('ONESIGNAL_APP_ID')						OR define('ONESIGNAL_APP_ID', $_ENV['ONESIGNAL_APP_ID'] ?: 'aaaa-bbbb-bbbb-cccc-dddddddddddd');
defined('WA_CENTER_NUMBER')						OR define('WA_CENTER_NUMBER', $_ENV['WA_CENTER_NUMBER'] ?: '+621234567890'); //call center WA number
defined('MAX_DAY_ADDITIONAL_COST_INPUT')		OR define('MAX_DAY_ADDITIONAL_COST_INPUT', $_ENV['MAX_DAY_ADDITIONAL_COST_INPUT'] ?: 10);
defined('MIN_TIME_HOUR_ORDER_TO_CREATE_FEE')	OR define('MIN_TIME_HOUR_ORDER_TO_CREATE_FEE', $_ENV['MIN_TIME_HOUR_ORDER_TO_CREATE_FEE'] ?: 12); //24 HOUR FORMAT
defined('MIN_DURATION_ORDER_TO_CREATE_FEE')		OR define('MIN_DURATION_ORDER_TO_CREATE_FEE', $_ENV['MIN_DURATION_ORDER_TO_CREATE_FEE'] ?: 6); //IN TIME (HOUR)
defined('MIN_ADDITIONAL_INCOME_NOMINAL')		OR define('MIN_ADDITIONAL_INCOME_NOMINAL', $_ENV['MIN_ADDITIONAL_INCOME_NOMINAL'] ?: 0);
defined('MIN_CHARITY_NOMINAL')					OR define('MIN_CHARITY_NOMINAL', $_ENV['MIN_CHARITY_NOMINAL'] ?: 100000);

defined('PATH_STORAGE_BASE')					OR define('PATH_STORAGE_BASE', $_ENV['PATH_STORAGE_BASE'] ?: FCPATH . 'storage/');
defined('PATH_STORAGE')							OR define('PATH_STORAGE', PATH_STORAGE_BASE.$_ENV['PATH_STORAGE'] ?: 'BST/');
defined('PATH_STORAGE_ADDITIONAL_COST_IMAGE')	OR define('PATH_STORAGE_ADDITIONAL_COST_IMAGE', PATH_STORAGE.$_ENV['PATH_STORAGE_ADDITIONAL_COST_IMAGE'] ?: 'additionalCost/');
defined('PATH_STORAGE_ADDITIONAL_INCOME_IMAGE')	OR define('PATH_STORAGE_ADDITIONAL_INCOME_IMAGE', PATH_STORAGE.$_ENV['PATH_STORAGE_ADDITIONAL_INCOME_IMAGE'] ?: 'additionalIncome/');
defined('PATH_STORAGE_COLLECT_PAYMENT_RECEIPT')	OR define('PATH_STORAGE_COLLECT_PAYMENT_RECEIPT', PATH_STORAGE.$_ENV['PATH_STORAGE_COLLECT_PAYMENT_RECEIPT'] ?: 'collectPayment/');
defined('PATH_STORAGE_FEEDBACK_IMAGE')			OR define('PATH_STORAGE_FEEDBACK_IMAGE', PATH_STORAGE_BASE.$_ENV['PATH_STORAGE_FEEDBACK_IMAGE'] ?: 'feedback/');
defined('PATH_TRANSFER_RECEIPT')				OR define('PATH_TRANSFER_RECEIPT', PATH_STORAGE_BASE.$_ENV['PATH_TRANSFER_RECEIPT'] ?: 'TransferReceipt/');
defined('PATH_REIMBURSEMENT_RECEIPT')			OR define('PATH_REIMBURSEMENT_RECEIPT', PATH_STORAGE.$_ENV['PATH_REIMBURSEMENT_RECEIPT'] ?: 'reimbursement/');
defined('PATH_PROFILE_PICTURE')					OR define('PATH_PROFILE_PICTURE', PATH_STORAGE_BASE.$_ENV['PATH_PROFILE_PICTURE'] ?: 'ProfilePicture/');
defined('PATH_AGREEMENT_MASTER')				OR define('PATH_AGREEMENT_MASTER', PATH_STORAGE_BASE.$_ENV['PATH_AGREEMENT_MASTER'] ?: 'driverAgreement/master/');
defined('PATH_AGREEMENT_SIGNATURE')				OR define('PATH_AGREEMENT_SIGNATURE', PATH_STORAGE_BASE.$_ENV['PATH_AGREEMENT_SIGNATURE'] ?: 'driverAgreement/signature/');
defined('PATH_AGREEMENT_IDENTITY')				OR define('PATH_AGREEMENT_IDENTITY', PATH_STORAGE_BASE.$_ENV['PATH_AGREEMENT_IDENTITY'] ?: 'driverAgreement/identity/');
defined('PATH_AGREEMENT_SIGNED_LETTER')			OR define('PATH_AGREEMENT_SIGNED_LETTER', PATH_STORAGE_BASE.$_ENV['PATH_AGREEMENT_SIGNED_LETTER'] ?: 'driverAgreement/signedLetter/');

defined('MAIL_HOST')						OR define('MAIL_HOST'					, $_ENV['MAIL_HOST'] ?: 'mail.example.com');
defined('MAIL_NAME')						OR define('MAIL_NAME'					, $_ENV['MAIL_NAME'] ?: 'Bali Sun Tours');
defined('MAIL_USERNAME')					OR define('MAIL_USERNAME'				, $_ENV['MAIL_USERNAME'] ?: 'info@example.com');
defined('MAIL_PASSWORD')					OR define('MAIL_PASSWORD'				, $_ENV['MAIL_PASSWORD'] ?: 'password');
defined('MAIL_SMTPPORT')					OR define('MAIL_SMTPPORT'				, $_ENV['MAIL_SMTPPORT'] ?: 465);
defined('MAIL_IMAPPORT')					OR define('MAIL_IMAPPORT'				, $_ENV['MAIL_IMAPPORT'] ?: 993);
defined('MAIL_CSSSTYLE')					OR define('MAIL_CSSSTYLE'				, "<style>table{border-spacing:0;border-collapse:collapse;}
																						th{padding:0;}
																						@media print{
																						*,:after,:before{color:#000!important;text-shadow:none!important;background:0 0!important;-webkit-box-shadow:none!important;box-shadow:none!important;}
																						thead{display:table-header-group;}
																						tr{page-break-inside:avoid;}
																						.table{border-collapse:collapse!important;}
																						.table th{background-color:#fff!important;}
																						.table-bordered th{border:1px solid #ddd!important;}
																						}
																						*{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}
																						:after,:before{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;}
																						table{background-color:transparent;}
																						th{text-align:left;}
																						.table{width:100%;max-width:100%;margin-bottom:20px;}
																						.table>thead>tr>th{padding:8px;line-height:1.42857143;vertical-align:top;border-top:1px solid #ddd;}
																						.table>thead>tr>th{vertical-align:bottom;border-bottom:2px solid #ddd;}
																						.table>thead:first-child>tr:first-child>th{border-top:0;}
																						.table-bordered{border:1px solid #ddd;}
																						.table-bordered>thead>tr>th{border:1px solid #ddd;}
																						.table-bordered>thead>tr>th{border-bottom-width:2px;}
																						.table thead tr th{padding:10px;border-bottom:1px solid #eee;}
																						.table-bordered{border-top:1px solid #eee;}
																						.table-bordered thead tr th{padding:10px;border:1px solid #eee;}
																						.note-editor .note-editing-area .note-editable table{width:100%;border-collapse:collapse;}
																						.note-editor .note-editing-area .note-editable table th{border:1px solid #ececec;padding:5px 3px;}
																						.table th{text-align:center!important;}</style>");

defined('FIREBASE_PRIVATE_KEY_PATH')		OR define('FIREBASE_PRIVATE_KEY_PATH'	, FCPATH . $_ENV['FIREBASE_PRIVATE_KEY_PATH'] ?: 'apps/config/firebase.json');
defined('FIREBASE_RTDB_URI')				OR define('FIREBASE_RTDB_URI'			, $_ENV['FIREBASE_RTDB_URI'] ?: 'https://default-rtdb.asia-southeast1.firebasedatabase.app/');
defined('FIREBASE_RTDB_MAINREF_NAME')		OR define('FIREBASE_RTDB_MAINREF_NAME'	, $_ENV['FIREBASE_RTDB_MAINREF_NAME'] ?: 'webapp-data/');

defined('UPDATE_MSG')						OR define('UPDATE_MSG', $_ENV['UPDATE_MSG'] ?: '');
defined('UPDATE_FORCE')						OR define('UPDATE_FORCE', $_ENV['UPDATE_FORCE'] ?: false);

defined('REQUEST_REVIEW_FORCE')				OR define('REQUEST_REVIEW_FORCE', $_ENV['REQUEST_REVIEW_FORCE'] ?: false);

defined('ROKET_ECOMMERCE_PRIVATE_KEY')		OR define('ROKET_ECOMMERCE_PRIVATE_KEY', $_ENV['ROKET_ECOMMERCE_PRIVATE_KEY'] ?: 'PRIVATE_KEY_HERE');
defined('ROKET_ECOMMERCE_PUBLIC_KEY')		OR define('ROKET_ECOMMERCE_PUBLIC_KEY', $_ENV['ROKET_ECOMMERCE_PUBLIC_KEY'] ?: 'PUBLIC_KEY_HERE');
defined('ROKET_ECOMMERCE_API_BASE_URL')		OR define('ROKET_ECOMMERCE_API_BASE_URL', $_ENV['ROKET_ECOMMERCE_API_BASE_URL'] ?: 'http://example.com/');