var MAIN_URL		=	window.location.origin;
var API_URL			=	MAIN_URL;
var ASSET_JS_URL	=	MAIN_URL+'/assets/js/';
var ASSET_CSS_URL	=	MAIN_URL+'/assets/css/';
var ASSET_IMG_URL	=	MAIN_URL+'/assets/img/';
var ASSET_FONT_URL	=	MAIN_URL+'/assets/fonts/';
var ASSET_AUDIO_URL	=	MAIN_URL+'/assets/sounds/';
var devStatus		=	"development";
var urlLogout		=	"";

function getUserToken(){
	
	if (localStorage.getItem("userToken") === null || localStorage.getItem("userToken") === undefined) {
		return {token: ""};
	}
	
	return {token: localStorage.getItem('userToken')};

}