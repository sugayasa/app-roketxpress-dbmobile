<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function Send_SMS($to,$text){

    $pecah              = explode(",",$to);
    $jumlah             = count($pecah);
    $from               = ""; //Sender ID or SMS Masking Name, if leave blank, it will use default from telco
    $username           = SMS_PROVIDER_USERNAME;
    $password           = SMS_PROVIDER_PASSWORD;
    $postUrl            = "http://107.20.199.106/restapi/sms/1/text/advanced";
	
    for($i=0; $i<$jumlah; $i++){
        if(substr($pecah[$i],0,2) == "62" || substr($pecah[$i],0,3) == "+62"){
            $pecah = $pecah;
        }elseif(substr($pecah[$i],0,1) == "0"){
            $pecah[$i][0] = "X";
            $pecah = str_replace("X", "62", $pecah);
        }else{
            echo "Invalid mobile number format";
        }
		
        $destination = array("to" => $pecah[$i]);
        $message     = array("from" => $from,
                             "destinations" => $destination,
                             "text" => $text,
                             "smsCount" => 2);
        $postData           = array("messages" => array($message));
        $postDataJson       = json_encode($postData);

        $ch                 = curl_init();
        $header             = array("Content-Type:application/json", "Accept:application/json");
		
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataJson);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response		= curl_exec($ch);
        $httpCode		= curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseBody	= json_decode($response);
        curl_close($ch);

    }   
}

function numberValidator($number){

	if($number == "") return "";	
	
	$numberReturn	=	"";
	if(substr($number,0,1) == "0"){
		$numberReturn	=	"+62".substr($number, 1);
	} else if(substr($number,0,3) == "620"){
		$numberReturn	=	"+62".substr($number, 3);
	} else if(substr($number,0,4) == "+620"){
		$numberReturn	=	"+62".substr($number, 4);
	} else if(substr($number,0,2) == "62"){
		$numberReturn	=	"+62".substr($number, 2);
	} else if(substr($number,0,1) == "8"){
		$numberReturn	=	"+62".$number;
	} else {
		$numberReturn	=	$number;
	}

	return $numberReturn;
	
}