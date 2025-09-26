<?PHP
defined('BASEPATH') OR exit('No direct script access allowed');

function sendOneSignalMessage($arrPlayerId, $arrData, $arrHeadings, $arrContent){
	return true;
	$fields = array(
		'app_id'				=> ONESIGNAL_APP_ID,
		'include_player_ids'	=> $arrPlayerId,
		'data'					=> $arrData,
		'headings'				=> $arrHeadings,
		'contents'				=> $arrContent
	);
	
	$fields = json_encode($fields);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	$response = curl_exec($ch);
	curl_close($ch);
	
	return true;
}

?>