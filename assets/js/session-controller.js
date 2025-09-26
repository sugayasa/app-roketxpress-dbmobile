$(document).ready(function() {
    NProgress.start();
	var userData	=	mergeDataSend();
	
    $.ajax({
        type: 'POST',
        url: API_URL+'/accessCheck',
		contentType: 'application/json',
        dataType: 'json',
		data: userData,
        xhrFields: { 
            withCredentials: true 
        },
        beforeSend:function(){},
        success:function(response){
            if(response.status == 200){
                $('#loadtext').html('Redirecting to main page...');
                setTimeout(callMainPage, 1000);
            } else {
                $('#loadtext').html('Redirecting to login...');
                setTimeout(loadLogin, 1000);
            }
        },
        error:function(){
          $('#login_content').html('<center>Error on connection</center>');
          NProgress.done();
        }
    });

});

function loadLogin(){
	localStorage.clear();
    $.ajax({
        type: 'GET',
        url: MAIN_URL+'/loginPage',
        beforeSend:function(){
            $('#loadtext').html('Loading login page...');
        },
        success:function(htmlRes){
            $('#mainbody').html(htmlRes);
            NProgress.done();
        },
        error:function(){
          $('#center_content').html('<center>Error on connection</center>');
          NProgress.done();
        }
    });
}

function callMainPage(){
	
	OneSignal.isPushNotificationsEnabled(function(isEnabled) {
		var userAgent	=	navigator.userAgent,
			userOS		=	navigator.oscpu;
		if (isEnabled) {
			OneSignal.getUserId( function(userId) {
				localStorage.setItem('OSUserDetail', JSON.stringify({OSUserId:userId, userAgent:userAgent, userOS:userOS}));
			});
		} else {
			localStorage.setItem('OSUserDetail', JSON.stringify({OSUserId:"", userAgent:userAgent, userOS:userOS}));
		}
		
	});
	
    var lastAlias	=	localStorage.getItem('lastAlias') == null ? "" : localStorage.getItem('lastAlias'),
		dataSend	=	{lastPageAlias : lastAlias};
	$.ajax({
        type: 'POST',
        url: MAIN_URL+'/mainPage',
		contentType: 'application/json',
		dataType: 'json',
		data: mergeDataSend(dataSend),
		xhrFields: { 
			withCredentials: true 
		},
        beforeSend:function(){
            $('#loadtext').html('Loading main page...');
        },
        success:function(response){
			
			setUserToken(response);
            $('body').html(response.htmlRes);
			NProgress.done();
			
        },
        error:function(){
          $('#center_content').html('<center>Error on connection</center>');
          NProgress.done();
        }
    });
}

function setUserToken(jsonResponse){

	if(jsonResponse.status == 410){
		$('#modalWarning').on('show.bs.modal', function() {
			$('#modalWarningBody').html(jsonResponse.msg);			
		});
		$('#modalWarning').off('hidden.bs.modal');
		$('#modalWarning').on('hidden.bs.modal', function () {
			window.location.href	=	urlLogout;
			return;
		});
		$('#modalWarning').modal('show');
	}
	
	var token	=	jsonResponse.token;
	
	if(token != "" && token !== null && token !== undefined){
		localStorage.setItem('userToken', token);
		urlLogout	=	MAIN_URL+"/logout/"+token;
		if ($("#linkLogout").length) {
			$("#linkLogout").attr("href", urlLogout)
		}
	}
	return true;
}

function mergeDataSend(dataMerge = null){
	
	var userData		=	JSON.parse(localStorage.getItem('userData')),
		OSUserDetail	=	JSON.parse(localStorage.getItem('OSUserDetail')),
		userToken		=	getUserToken(),
		dataSend		=	dataMerge == null ? $.extend({}, userToken, userData, OSUserDetail) : $.extend({}, dataMerge, userToken, userData, OSUserDetail);
	return JSON.stringify(dataSend);
	
}