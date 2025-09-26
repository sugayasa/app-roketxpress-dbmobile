<?php
defined('BASEPATH') OR exit('No direct script access allowed');
	
function sendPushNotification($clientToken, $title, $body, $additionalArray = array()){
	
	$data			=	array("to"				=> $clientToken,
							  "notification"	=> array_merge(
															array(
																"title"				=> $title,
																"body"				=> $body,
																"android_channel_id"=> "bst_channel"
															),
															$additionalArray
												   ),
							  "data"			=> array_merge(
															array(
																"title"				=> $title,
																"body"				=> $body,
																"android_channel_id"=> "bst_channel"
															),
															$additionalArray
												   )
						);
	$data_string	=	json_encode($data);
	$headers		=	array(
							 'Authorization: key=' . FB_API_ACCESS_KEY, 
							 'Content-Type: application/json'
						);																							 
	$ch				=	curl_init();

	curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );                                                                  
	curl_setopt( $ch,CURLOPT_POST, true );  
	curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch,CURLOPT_POSTFIELDS, $data_string);                                                                  
																														 
	$result			=	curl_exec($ch);
	curl_close ($ch);
	return true;
	
}