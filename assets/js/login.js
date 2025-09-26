if (loginFunc == null){
	var loginFunc	=	function(){
		$(document).ready(function () {
			// setInterval(function(){
				// var notifPermission	=	Notification.permission,
					// baseUrlLocation	=	window.location.host;
				// if(baseUrlLocation == "db.roketxpress.com"){
					// if(notifPermission == "granted"){
						// $("#username, #password, #loginSubmitBtn").attr("disabled", false);
						// $("#notifAllowAlert").addClass("d-none");
					// } else {
						// $("#username, #password, #loginSubmitBtn").attr("disabled", true);
						// $("#notifAllowAlert").removeClass("d-none");
					// }					
				// }
			// }, 500);
		});	
	}
}
$(document).ready(function() {
	$(".show-password").on("click", function () {
		showPassword(this)
	});
	
	$("#warning-element").find("span").on("click", function (){
		$("#warning-element").addClass("d-none").find("strong").html("");
	});

    $("#login-form").submit(function(e){
		e.preventDefault();
        var username		=   $("#username").val(),
			password		=   $("#password").val(),
			userCredentials	=	{username : username, password : password},
			userToken		=	getUserToken(),
			dataSend		=	$.extend({}, userCredentials, userToken);

		$.ajax({
			type: 'POST',
			url: API_URL+'/userLogin',
			contentType: 'application/json',
			dataType: 'json',
			data: JSON.stringify(dataSend),
			xhrFields: { 
				withCredentials: true 
			},
			beforeSend:function(){
				NProgress.start();
				$("#warning-element").addClass("d-none").find("strong").html("");
				$("#username, #password").prop('readonly', true);
			},
			success:function(response){
				if(response.status == 200){
					localStorage.setItem('userData', JSON.stringify(response.userData));
					localStorage.setItem('userToken', response.token);
					callMainPage();
				} else {
					NProgress.done();
					$("#username, #password").prop('readonly', false);
					$("#warning-element").removeClass("d-none").find("strong").html("Username and/or password you entered is incorrect");
				}
			},
			error:function(){
				$('#login_content').html('<center>Error on connection</center>');
			}
		});
    });
});

function showPassword(a) {
	var e = $(a).parent().find("input");
	"password" === e.attr("type") ? e.attr("type", "text") : e.attr("type", "password")
}

loginFunc();