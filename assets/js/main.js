"use strict";

function dismissAllNotification(openedMenu=false){
	$.ajax({
		type: 'POST',
		url: baseURL+"notification/dismissAllNotification",
		contentType: 'application/json',
		dataType: 'json',
		cache: false,
		data: mergeDataSend(),
		beforeSend:function(){
			$('#window-loader').modal('show');
		},
		success:function(response){
			$('#window-loader').modal('hide');
			if(response.status == 200){
				getUnreadNotificationList();
				if(openedMenu){
					getDataUnreadNotification();
					getDataReadNotification();
				}
			}
		}
	});
}

function dismissNotification(idMessageAdmin){
	var dataSend		=	{idMessageAdmin:idMessageAdmin};
	$.ajax({
		type: 'POST',
		url: baseURL+"notification/dismissNotification",
		contentType: 'application/json',
		dataType: 'json',
		cache: false,
		data: mergeDataSend(dataSend),
		beforeSend:function(){},
		success:function(response){
			if(response.status == 200){
				getUnreadNotificationList();
			}
		}
	});
}
		
function generateElemNotification(totalUnreadNotification, unreadNotificationArray){
	
	var notificationList	=	"";
	$("#containerNotificationCounter").html(numberFormat(totalUnreadNotification));
	
	if(totalUnreadNotification <= 0) {
		$("#iconNewNotification").remove();
		notificationList	=	"<li id='containerEmptyNotification'><center>No new notifications shown</center></li>";
	} else {
		$("#containerEmptyNotification").remove();
		if($("#iconNewNotification").length <= 0) $("#containerNotificationIcon").append('<span class="badge" id="iconNewNotification"></span>');
		$.each(unreadNotificationArray, function(index, array) {
					
			var dataParamNotif	=	generateDataParamNotif(array.IDMESSAGEADMIN, array.IDMESSAGEADMINTYPE, array.PARAMLIST);
			notificationList	+=	'<li id="liNotification'+array.IDMESSAGEADMIN+'" class="liNotification">'+
										'<a href="#" class="btnDetailNotification" data-id="'+array.IDMESSAGEADMIN+'" '+dataParamNotif+'>'+
											'<i class="'+array.ICON+'"></i>'+
											'<p style="min-width: 220px;" class="mr-2">'+
												array.TITLE+'<br/>'+
												'<small>'+array.MESSAGE.substring(0, 50)+'..</small><br/>'+
												'<small>'+array.DATETIMEINSERT+'</small>'+
											'</p>'+
										'</a>'+
										'<button class="delete" onclick="dismissNotification('+array.IDMESSAGEADMIN+')"><i class="zmdi zmdi-close-circle-o"></i></button>'+
									'</li>';
			
		});
	}
	
	if(notificationList != ""){
		$("#containerNotificationList").html(notificationList);
		$(".btnDetailNotification").off('click');
		$(".btnDetailNotification").on('click', function() {
			openMenuFromNotification(this);
		});

	}
	
}

function getUnreadNotificationList(){
	$.ajax({
		type: 'POST',
		url: baseURL+"unreadNotificationList",
		contentType: 'application/json',
		dataType: 'json',
		cache: false,
		data: mergeDataSend(),
		beforeSend:function(){
			$("#containerNotificationCounter").html(0);
			$("#containerNotificationList").html("<li id='containerEmptyNotification'><center><i class='fa fa-spinner fa-pulse'></i><br/>Loading data...</center></li>");
		},
		success:function(response){
			setUserToken(response);			
			var unreadNotificationArray	=	response.unreadNotificationArray,
				totalUnreadNotification	=	response.totalUnreadNotification * 1;
			generateElemNotification(totalUnreadNotification, unreadNotificationArray);
		}
	});
}

function generateTotalUnreadMailElem(totalUnreadMail){
	totalUnreadMail	=	totalUnreadMail * 1;
	if($("#menuMB").length > 0){
		$("#containerUnreadMailCounter").remove();
		if(totalUnreadMail > 0){
			$("#menuMB a").append('<span class="badge badge-success badge-pill ml-auto mr-4" id="containerUnreadMailCounter" data-toggle="tooltip" data-original-title="Total unprocessed mail" data-placement="right">'+numberFormat(totalUnreadMail)+'</span>');
			$('[data-toggle="tooltip"]').tooltip();
		}
	}
}

function generateTotalUnprocessReservarionElem(totalUnprocessedReservation){
	totalUnprocessedReservation	=	totalUnprocessedReservation * 1;
	if($("#menuRV").length > 0){
		$("#containerUnprocessReservationCounter").remove();
		if(totalUnprocessedReservation > 0){
			$("#menuRV a").append('<span class="badge badge-success badge-pill ml-auto mr-4" id="containerUnprocessReservationCounter" data-toggle="tooltip" data-original-title="Total unprocessed reservation" data-placement="right">'+numberFormat(totalUnprocessedReservation)+'</span>');
			$('[data-toggle="tooltip"]').tooltip();
		}
	}
}

function generateTotalUndeterminedScheduleElem(totalUndeterminedSchedule){
	totalUndeterminedSchedule	=	totalUndeterminedSchedule * 1;
	if($("#menuRV").length > 0){
		$("#containerUndeterminedSchedule").remove();
		if(totalUndeterminedSchedule > 0){
			$("#groupMenuSchedule").after('<span class="badge badge-warning badge-pill ml-auto mr-4" id="containerUndeterminedSchedule">'+numberFormat(totalUndeterminedSchedule)+'</span>');
			$('[data-toggle="tooltip"]').tooltip();
		}
	}
}

function getTotalUnreadMail(){
	if($("#menuMB").length > 0){
		$.ajax({
			type: 'POST',
			url: baseURL+"mailbox/getTotalUnreadMail",
			contentType: 'application/json',
			dataType: 'json',
			cache: false,
			data: mergeDataSend(),
			beforeSend:function(){
				$("#containerUnreadMailCounter").remove();
			},
			success:function(response){
				setUserToken(response);			
				var totalUnreadMail	=	response.totalUnreadMail * 1;
				generateTotalUnreadMailElem(totalUnreadMail);				
			}
		});
	}
}

var win		=	window.top;
win.onfocus =	function() {
					getUnreadNotificationList();
				};
	
function openListNotification(){
	$("#containerNotificationButton, .dropdown-menu-notifications").removeClass("show");
	getViewURL('notification', 'NOTIF');
}

$.ajaxSetup({ cache: true });
$(document).ready(function () {
	
	var userToken	=	getUserToken();
	$.getJSON(baseURL+"option-helper/getDataOption/"+userToken.token, {}, function(jsonData) {
		var optionHelper=	jsonData.data,
			optionMonth	=	$.parseJSON(localStorage.getItem('optionMonth')),
			optionYear	=	$.parseJSON(localStorage.getItem('optionYear'));
		optionHelper['optionMonth']	=	optionMonth;
		optionHelper['optionYear']	=	optionYear;
		localStorage.setItem('optionHelper', JSON.stringify(optionHelper));
	});

    var $window = $(window);
    var $body = $('body');

    if ($('.adomx-dropdown').length) {
        var $adomxDropdown = $('.adomx-dropdown'),
            $adomxDropdownMenu = $adomxDropdown.find('.adomx-dropdown-menu');

        $adomxDropdown.on('click', '.toggle', function(e) {
            e.preventDefault();
            var $this = $(this);
            if (!$this.parent().hasClass('show')) {
                $adomxDropdown.removeClass('show');
                $adomxDropdownMenu.removeClass('show');
                $this.siblings('.adomx-dropdown-menu').addClass('show').parent().addClass('show');
            } else {
                $this.siblings('.adomx-dropdown-menu').removeClass('show').parent().removeClass('show');
            }
        });

        $body.on('click', function(e) {
            var $target = e.target;
            if (!$($target).is('.adomx-dropdown') && !$($target).parents().is('.adomx-dropdown') && $adomxDropdown.hasClass('show')) {
                $adomxDropdown.removeClass('show');
                $adomxDropdownMenu.removeClass('show');
            }
        });
    }

    var $headerSearchOpen = $('.header-search-open'),
        $headerSearchClose = $('.header-search-close'),
        $headerSearchForm = $('.header-search-form');
    $headerSearchOpen.on('click', function() {
        $headerSearchForm.addClass('show');
    });
    $headerSearchClose.on('click', function() {
        $headerSearchForm.removeClass('show');
    });

    var $sideHeaderToggle = $('.side-header-toggle'),
        $sideHeaderClose = $('.side-header-close'),
        $sideHeader = $('.side-header');

    function $sideHeaderClassToggle() {
        var $windowWidth = $window.width();
        if ($windowWidth >= 1200) {
            $sideHeader.removeClass('hide').addClass('show');
        } else {
            $sideHeader.removeClass('show').addClass('hide');
        }
    }
    $sideHeaderClassToggle();
    $sideHeaderToggle.on('click', function() {
        if ($sideHeader.hasClass('show')) {
            $sideHeader.removeClass('show').addClass('hide');
        } else {
            $sideHeader.removeClass('hide').addClass('show');
        }
    });

    $sideHeaderClose.on('click', function() {
        $sideHeader.removeClass('show').addClass('hide');
    });

    var $sideHeaderNav = $('.side-header-menu'),
        $sideHeaderSubMenu = $sideHeaderNav.find('.side-header-sub-menu');

    $sideHeaderSubMenu.siblings('a').append('<span class="menu-expand"><i class="fa fa-chevron-down"></i></span>');
    $sideHeaderSubMenu.slideUp();
    $sideHeaderNav.on('click', 'li a, li .menu-expand', function(e) {
        var $this = $(this);
        if ($this.parent('li').hasClass('has-sub-menu') || ($this.attr('href') === '#' || $this.hasClass('menu-expand'))) {
            e.preventDefault();
            if ($this.siblings('ul:visible').length) {
                $this.parent('li').removeClass('active').children('ul').slideUp().siblings('a').find('.menu-expand i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $this.parent('li').siblings('li').removeClass('active').find('ul:visible').slideUp().siblings('a').find('.menu-expand i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            } else {
                $this.parent('li').addClass('active').children('ul').slideDown().siblings('a').find('.menu-expand i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $this.parent('li').siblings('li').removeClass('active').find('ul:visible').slideUp().siblings('a').find('.menu-expand i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }
        }
    });

    var pageUrl = window.location.href.substr(window.location.href.lastIndexOf("/") + 1);
    $('.side-header-menu a').each(function() {
        if ($(this).attr("href") === pageUrl || $(this).attr("href") === '') {
            $(this).closest('li').addClass("active").parents('li').addClass('active').children('ul').slideDown().siblings('a').find('.menu-expand i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else if (window.location.pathname === '/' || window.location.pathname === '/index.html') {
            $('.side-header-menu a[href="index.html"]').closest('li').addClass("active").parents('li').addClass('active').children('ul').slideDown().siblings('a').find('.menu-expand i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    })

    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
    tippy('.tippy, [data-tippy-content], [data-tooltip]', {
        flipOnUpdate: true,
        boundary: 'window',
    });

    function tableSelectable() {
        var $tableSelectable = $('.table-selectable');
        $tableSelectable.find('tbody .selected').find('input[type="checkbox"]').prop('checked', true);
        $tableSelectable.on('click', 'input[type="checkbox"]', function() {
            var $this = $(this);
            if ($this.parent().parent().is('th')) {
                if (!$this.is(':checked')) {
                    $this.closest('table').find('tbody').children('tr').removeClass('selected').find('input[type="checkbox"]').prop('checked', false);
                } else {
                    $this.closest('table').find('tbody').children('tr').addClass('selected').find('input[type="checkbox"]').prop('checked', true);
                }
            } else {
                if (!$this.is(':checked')) {
                    $this.closest('tr').removeClass('selected');
                } else {
                    $this.closest('tr').addClass('selected');
                }
                if ($this.closest('tbody').children('.selected').length < $this.closest('tbody').children('tr').length) {
                    $this.closest('table').find('thead').find('input[type="checkbox"]').prop('checked', false);
                } else if ($this.closest('tbody').children('.selected').length === $this.closest('tbody').children('tr').length) {
                    $this.closest('table').find('thead').find('input[type="checkbox"]').prop('checked', true);
                }
            }
        });
    }
    tableSelectable();

    var $chatContactOpen = $('.chat-contacts-open'),
        $chatContactClose = $('.chat-contacts-close'),
        $chatContacts = $('.chat-contacts');
    $chatContactOpen.on('click', function() {
        $chatContacts.addClass('show');
    });
    $chatContactClose.on('click', function() {
        $chatContacts.removeClass('show');
    });


    function resize() {
        $sideHeaderClassToggle();
    }

    $window.on('resize', function() {
        resize();
    });


    $('.custom-scroll').each(function() {
        var ps = new PerfectScrollbar($(this)[0]);
    });

	if(localStorage.getItem('OSNotificationData') === null || localStorage.getItem('OSNotificationData') === undefined){
		$("#dashboard-menu").trigger("click");
	} else {
		var OSNotificationData	=	JSON.parse(localStorage.getItem('OSNotificationData'));
		var OSNotifType			=	OSNotificationData.type;
		switch(OSNotifType){
			case "reservation"		:	$("#menuRV").trigger("click"); break;
			case "mailbox"			:	$("#menuMB").trigger("click"); break;
			case "carschedule"		:	$('.menu-item[data-alias="SCRC"]').trigger("click"); break;
			case "driverschedule"	:	$('.menu-item[data-alias="SCDR"]').trigger("click"); break;
			default					:	$("#dashboard-menu").trigger("click"); break;
		}
	}
	
	getUnreadNotificationList();

}),

$(".menu-item").on("click", function () {

	$('.menu-item').removeClass('active');
	$('.modal').modal('hide');
	$(this).addClass('active');
	NProgress.start();
	setLoaderMainContent();
	
	var alias	=	$(this).attr("data-alias"),
		url		=	$(this).attr("data-url");
	
	localStorage.setItem('lastUrl', url);
	localStorage.setItem('lastAlias', alias);
	$("#lastMenuAlias").val(alias);
		
	if(localStorage.getItem('form_'+alias) === null) {
		getViewURL(url, alias);
	} else {
		var htmlRes	=	localStorage.getItem('form_'+alias);
		renderMainView(htmlRes);
	}
	
});

function setLoaderMainContent(){
	$("#main-content").html(loaderElem);
}

function getLevelUser(){
	var userData	=	$.parseJSON(localStorage.getItem('userData'));
	return userData.LEVEL;
}

function getViewURL(url, alias, callback){
	
    $.ajax({
        type: 'POST',
		url: baseURL+"view/"+url,
		contentType: 'application/json',
		dataType: 'json',
		cache: true,
		data: mergeDataSend(),
        beforeSend:function(){
			NProgress.set(0.4);
        },
        success:function(response){
			
			setUserToken(response);
			localStorage.setItem('form_'+alias, response.htmlRes);
			renderMainView(response.htmlRes);
			if (typeof callback == "function") callback();
			NProgress.done();
			
        },
        error:function(){
          $('#main-content').html('<center>Error on connection</center>');
          NProgress.done();
        }
    });
	
	
}

function renderMainView(htmlRes, callback){

	$('#modalWarning').off('hidden.bs.modal');
	$("#main-content").html(htmlRes);
	if ($("#opt-dataperpage").length) {
		$("#opt-dataperpage").on("change", function () {
			$('#page').val("1");
			ajaxDataTable();
		});
	}
	
	if ($("#form-search").length) {
		$("#form-search").keydown(function(e){
			if(e.which === 13){
				resetPage();
				ajaxDataTable();
			}
		});
	}
	
	if( $('.input-date-single').length ) {
        $('.input-date-single').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
			locale: {
				format: 'DD-MM-YYYY',
				separator: ' - ',
				applyLabel: 'Save',
				cancelLabel: 'Cancel',
				daysOfWeek: [
					'Sun',
					'Mon',
					'Tue',
					'Wed',
					'Thu',
					'Fri',
					'Sat'
				],
				monthNames: [
					'January',
					'February',
					'March',
					'April',
					'May',
					'June',
					'July',
					'August',
					'September',
					'October',
					'November',
					'December'
				],
				firstDay: 1
			}
        });
    }
	
	NProgress.done();
	
	if (typeof callback == "function") callback();

}

function resetPage(){
	if ($("#page").length) {
		$('#page').val(1);
	}
}

function ajaxDataTable(footer=true){
	
	var columnNum   =   $('#table-view table thead tr').find('th').length,
		tableBody   =   $('#table-view table tbody'),
		data 		=	$('#filterForm').serializeArray(),
		urlData		=	$('#urltarget').val(),
		page		=	$('#page').val();
	
	if($('#opt-dataperpage').length > 0){
		data.push({name:'dataperpage', value:$('#opt-dataperpage').val()});
	}
	
	if($('#form-search').length > 0){
		data.push({name:'keyword', value:$('#form-search').val()});
	}

	$.ajax({
		type: 'POST',
		url: baseURL+urlData,
		data: data,
		dataType: 'json',
		beforeSend:function(){
			tableBody.html("<tr><td colspan='"+columnNum+"' class='text-center'>Getting Data..</td></tr>");
			NProgress.start();
		},
		success:function(apiRes){

			var pageTotal   =   apiRes.pagetotal;

			if(apiRes.status == 404){
				tableData   =   "<tr><td colspan='"+columnNum+"' align='center'><b>No data found</b></td></tr>";
				if ($.isFunction(window.noDataFunction)) {
					noDataFunction();
				}
			} else {
				var numData 	=   apiRes.startNumber,
					tableData	=	renderTableData(numData, apiRes.data);
			}

			tableBody.html(tableData);
			if(footer){
				generateDataInfo("dataTables_info", apiRes.datastart, apiRes.dataend, apiRes.datatotal);
				generatePagination("dataTables_paginate", page, pageTotal);
			}

			NProgress.done();

		},
		error:function(){
			swal("Gagal mendapatkan data. Harap cek koneksi anda");
		}
	});

}

function setPageAjaxDataTable(page, funcGenerateDataTable){
	$('#page').val(page);
	if (typeof window[funcGenerateDataTable] === "function") {
		window[funcGenerateDataTable](page);
	} else if(typeof funcGenerateDataTable == "function"){
		funcGenerateDataTable(page);
	} else {
		generateDataTable(page);
	}
}

function generateDataInfo(idcontainer, datastart, dataend, datatotal){
	$('#'+idcontainer).html("Show data from "+numberFormat(datastart)+" to "+numberFormat(dataend)+". Total "+numberFormat(datatotal)+" data");
}

function setOptionHelper(elementID, table, iddata=false, callback=false, parentValue=false){

	if($('#'+elementID).length){
		var dataOpt		=	JSON.parse(localStorage.getItem('optionHelper')),
			options     =   dataOpt[table];
			$('#'+elementID).empty();
			
		var optionAll	=	$('#'+elementID).attr('option-all'),
			optionAllVal=	$('#'+elementID).attr('option-all-value');

		if (typeof optionAll !== typeof undefined && optionAll !== false) {
			optionAllVal	=	typeof optionAllVal !== typeof undefined && optionAllVal !== false ? optionAllVal : "";
			$('#'+elementID).append($('<option></option>').val(optionAllVal).html(optionAll));
		}
		
		var firstValue		=	0;
		$('#'+elementID).each(function(i, obj) {
			$.each(options, function(index, array) {
				var selected	=	"";
				if(table == 'optionYear'){
					var thisYear=	moment().year();
					if(array.ID == thisYear) selected	=	"selected";
				}
				if(parentValue === false || (parentValue !== false && parentValue !== 0 && array.PARENTVALUE == parentValue)){
					firstValue	=	firstValue == 0 ? array.ID : firstValue;
					$('#'+elementID).append($('<option '+selected+'></option>').val(array.ID).html(array.VALUE));
				}
			});
			if(iddata != false){
				$('#'+elementID).val(iddata);
			}
		});
	}
	
	if (typeof callback == "function") callback(firstValue);
}

function updateDataOptionHelper(arrayName, arrayValue){
	
	var dataOptionHelper		=	JSON.parse(localStorage.getItem('optionHelper'));
	dataOptionHelper[arrayName]	=	arrayValue;
	
	localStorage.setItem('optionHelper', JSON.stringify(dataOptionHelper));
	
}

function createOptDropDown(alias, url, deleted=false, iddata=false){
		
	var strquery	=	deleted == true ? "/?delete=true" : "/?delete=false";
	if(iddata != false){
		strquery	+=	"&iddata="+iddata;
	} else {
		strquery	+=	"&iddata=";
	}

	if($('.opt-'+alias).length){
		$.ajax({
			type: 'GET',
			url: API_URL+'/'+url+strquery,
			cache: true,
			alias: alias,
			success:function(resData){
				var options     =   resData.data;
				$('.opt-'+this.alias).each(function(i, obj) {
					var elemID  =   obj.id;
					$('#'+elemID).empty();
					if($('#'+elemID).hasClass("item-all")) {
						$('#'+elemID).append($('<option></option>').val("").html($('#'+elemID).attr('data-text-all')));
					}
					$.each(options, function(index, array) {
						$('#'+elemID).append($('<option></option>').val(array.VALUE).html(array.TEXT));
					});
					if(resData.iddata != ""){
						$('#'+elemID).val(resData.iddata);
					}
				});
			}
		});
	}
}

function maskNumberInput(minValue = 0, maxValue = false, elemID = false, callback=false){

	var $input;
	
	if(elemID == false){
		$input  =   $(".maskNumber");
	} else {
		$input  =   $("#"+elemID);
	}
	
	if($input.val() == ""){
		$input.val(0);
	}
	$input.on("keyup", function(event) {
		var selection = window.getSelection().toString();
		if ( selection !== '' ) {
			return;
		}
		if ( $.inArray( event.keyCode, [38,40,37,39] ) !== -1 ) {
			return;
		}

		var $this		= $( this );
		var showcomma	= $this.hasClass("nocomma") ? false : true;
		var showzero	= $this.hasClass("nozero") ? false : true;
		var input		= $this.val(),
			input		= input.replace(/[^0-9.]/g, ''),
			input		= input < minValue ? minValue : parseInt(input, 10),
			input		= maxValue != false && input > maxValue ? maxValue : parseInt(input, 10);
		$this.val( function() {
			if(showcomma){
				input           = input ? parseInt( input, 10 ) : 0;
				if(showzero){
					return ( input === 0 ) ? "0" : input.toLocaleString( "en-US" );
				} else {
					return ( input === 0 ) ? "" : input.toLocaleString( "en-US" );
				}
			} else {
				return input;
			}
			if (typeof callback == "function"){
				callback(input);
				console.log(input);
			}
		});
	});


}

function replaceAll(str, find, replace) {
	if(str === undefined || str === null){
		return '';
	}
    return str.replace(new RegExp(find, 'g'), replace);
}

function numberFormat(number){
	if(number % 1 == 0){
		number	= number ? parseInt(number, 10) : 0;
	}
	return (number === 0 || number === undefined || number === null) ? "0" : number.toLocaleString( "en-US" );
}

function convertSerializeArrayToObject(dataArray){
	var dataObj = {};

	$(dataArray).each(function(i, field){
	  dataObj[field.name] = field.value;
	});
	
	return dataObj
}

function generatePagination(idcontainer, page, pageTotal, funcGenerateDataTable = "generateDataTable"){

	var nextpage    =   (page * 1 + 1);
	var next        =   page == pageTotal || pageTotal == 0 || nextpage > pageTotal ? "disabled" : "";
	var nextOnClick =   page == pageTotal || pageTotal == 0 || nextpage > pageTotal ? "" : funcGenerateDataTable+"("+nextpage+")";
	var nextButton  =   "<li class='page-item "+next+"' onclick='"+nextOnClick+"'><a href='#' class='page-link'>Next</a></li>";

	var prevpage    =   (page * 1 - 1);
	var previous    =   page == 1 || pageTotal <= 1 ? "disabled" : "";
	var prevOnClick =   page == 1 || pageTotal <= 1 ? "" : "setPageAjaxDataTable("+prevpage+", "+funcGenerateDataTable+")";
	var prevButton  =   "<li class='page-item "+previous+"' onclick='"+prevOnClick+"'><a href='#' class='page-link'>Prev</a></li>";
	var pagesBtn	=	"";

	if(pageTotal > 0){

		if(pageTotal <= 8 ){
			
			for(var i=1; i<=pageTotal; i++){

				var activeStr	=	i==page ? "active" : "";
				var onClick		=	i==page ? "" : funcGenerateDataTable+"("+i+")";
				pagesBtn		+=	"<li class='page-item "+activeStr+"' onclick='"+onClick+"'><a href='#' class='page-link'>"+i+"</a></li>";

			}

		} else {

			var lastNum, nextNum;		

			if(page > pageTotal - 5){
				lastNum		=	page - (8 - (pageTotal - page + 1));
				nextNum		=	pageTotal;
			} else {
				lastNum		=	page <= 4 ? 1 : page - 4;
				nextNum		=	page <= 4 ? 8 : (page * 1) + 5;
			}

			var pagesPrev	=	"";
			var pagesNext	=	"";

			if(page != 1){

				for(var i=lastNum; i<page; i++){
					var activeStr	=	i==page ? "active" : "";
					var onClick		=	i==page ? "" : funcGenerateDataTable+"("+i+")";
					pagesPrev		+=	"<li class='page-item "+activeStr+"' onclick='"+onClick+"'><a href='#' class='page-link'>"+i+"</a></li>";
				}

			}

			for(var j=page; j<=nextNum; j++){

				var activeStr	=	j==page ? "active" : "";
				var onClick		=	j==page ? "" : funcGenerateDataTable+"("+j+")";
				pagesNext		+=	"<li class='page-item "+activeStr+"' onclick='"+onClick+"'><a href='#' class='page-link'>"+j+"</a></li>";

			}

			pagesBtn	=	pagesPrev+pagesNext;

		}

	}

	$('#'+idcontainer).html(prevButton+pagesBtn+nextButton);

}

$('#modal-pengaturan').on('show.bs.modal', function() {
	$.ajax({
		type: 'POST',
		url: baseURL+"settingUser/detailSetting",
		contentType: 'application/json',
		dataType: 'json',
		data: mergeDataSend(),
		beforeSend:function(){
			NProgress.set(0.4);
		},
		success:function(response){
			setUserToken(response);
			NProgress.done();

			if(response.status == 200){
				$('#name').val(response.data.NAME);
				$('#email').val(response.data.EMAIL);
				$('#username').val(response.data.USERNAME);
				
				$('#saveSetting').off('click');
				$('#saveSetting').on('click', function(e) {
					
					e.preventDefault();				
					var dataForm	=	$("#form-pengaturan :input").serializeArray();
					var dataSend	=	{};
					$.each(dataForm, function() {
						dataSend[this.name] = this.value;
					});
					$("#form-pengaturan :input").attr("disabled", true);
					
					$.ajax({
						type: 'POST',
						url: baseURL+"settingUser/saveSetting",
						contentType: 'application/json',
						dataType: 'json',
						data: mergeDataSend(dataSend),
						beforeSend:function(){
							NProgress.set(0.4);
						},
						success:function(response){
							
							$("#form-pengaturan :input").attr("disabled", false);
							NProgress.done();
							setUserToken(response);

							$('#modalWarning').on('show.bs.modal', function() {
								$('#modalWarningBody').html(response.msg);
							});
							$('#modalWarning').modal('show');

							if(response.status == 200){
								$('#modal-pengaturan').modal('hide');
								$('#spanNameUser, #linkNameUser').html(response.name);
								$('#linkEmailUser').html(response.email);
								if(response.urlLogout != ''){
									window.location.replace(urlLogout);
								}
							}
							
						}
					});
					
				});

			}
		}
	});
});

function createScannerInput(elemID, callback = false){

	$("#"+elemID).focus();
	if ($("#"+elemID).length){
		$("#"+elemID).focus();
		$("#"+elemID).off("keydown");
		$("#"+elemID).keydown(function(e){
			var charInput		=	String.fromCharCode(e.keyCode);
			if(e.keyCode != 13 && e.keyCode != 220 && e.keyCode != 189 && e.keyCode != 173 && e.keyCode != 220 && e.keyCode != 191){
				if (/[a-zA-Z0-9-_ |]/.test(charInput)){
				} else {
					e.preventDefault();
				}
			}
		});
			
		var str		= '';
		var timer	= null;
		
		$("#"+elemID).off("keypress");
		$("#"+elemID).keypress(function(e){
			
			if(e.keyCode != 13){
				str += e.key;
				console.log(str);
			}
			if (timer){
				clearTimeout(timer);
			}
		
			timer	=	setTimeout(() => {
				if (typeof callback == "function") callback(str);
				str = '';   
			}, 500);
			
		});
	} else {
		$("#"+elemID).off('keydown');
		$("#"+elemID).off("keypress");
	}

}

function searchForArray(haystack, needle){
  var i, j, current;
  for(i = 0; i < haystack.length; ++i){
    if(needle.length === haystack[i].length){
      current = haystack[i];
      for(j = 0; j < needle.length && needle[j] === current[j]; ++j);
      if(j === needle.length)
        return i;
    }
  }
  return -1;
}

function toggleSlideContainer(leftContainer, rightContainer) {
	if ($("#"+leftContainer).hasClass('show')) {
		$("#"+leftContainer).find(".box, .row").addClass('d-none');
		$("#"+leftContainer).removeClass('show').addClass('hide');
		$("#"+rightContainer).removeClass('hide').addClass('show');
		$("#"+rightContainer).find(".box, .row").removeClass('d-none');
	} else {
		$("#"+rightContainer).find(".box, .row").addClass('d-none');
		$("#"+rightContainer).removeClass('show').addClass('hide');
		$("#"+leftContainer).removeClass('hide').addClass('show');
		$("#"+leftContainer).find(".box, .row").removeClass('d-none');
	}
}

function getDateToday(){
	var currentDate	= new Date();
	var day			= currentDate.getDate();
	var month		= currentDate.getMonth() + 1;
	var year		= currentDate.getFullYear();
	
	return ((''+day).length<2 ? '0' : '') + day + '-' + ((''+month).length<2 ? '0' : '') + month + '-' + year;
}

function getDateTomorrow(){
	var currentDate	= new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
	var day			= currentDate.getDate();
	var month		= currentDate.getMonth() + 1;
	var year		= currentDate.getFullYear();
	
	return ((''+day).length<2 ? '0' : '') + day + '-' + ((''+month).length<2 ? '0' : '') + month + '-' + year;
}

function generateButtonDetail(idMessageAdmin, idMessageType, paramList){
	
	var dataParamNotif	=	generateDataParamNotif(idMessageAdmin, idMessageType, paramList);
	return '<div class="button button-round button-primary button-sm pull-right btnDetailNotification" '+dataParamNotif+'>'+
				'<i aria-hidden="true" class="fa fa-info mr-0"></i>'+
			'</div>';
	
}

function generateDataParamNotif(idMessageAdmin, idMessageType, paramList){
	
	var paramList			=	JSON.parse(paramList),
		urlView				=	"",
		aliasView			=	"",
		tabMenuView			=	"",
		dateSchedule		=	"",
		idMailbox			=	0,
		idReservation		=	0,
		idReservationDetails=	0,
		idDayOffRequest		=	0;
		
	switch(idMessageType){
		case "1"	:	urlView				=	"mailbox";
						aliasView			=	"MB";
						idMailbox			=	paramList.idMailbox;
						break;
		case "2"	:	urlView				=	"reservation";
						aliasView			=	"RV";
						idReservation		=	paramList.idReservation;
						break;
		case "3"	:	urlView				=	"schedule-car";
						aliasView			=	"SCRC";
						idReservationDetails=	paramList.idReservationDetails;
						break;
		case "4"	:	urlView				=	"schedule-driver-auto";
						aliasView			=	"SCDRA";
						idReservationDetails=	paramList.idReservationDetails;
						dateSchedule		=	paramList.date;
						break;
		case "5"	:	urlView				=	"schedule-driver";
						aliasView			=	"SCDR";
						tabMenuView			=	"dayOffRequestTab";
						idDayOffRequest		=	paramList.idDayOffRequest;
						break;
		case "6"	:	urlView				=	"schedule-driver";
						aliasView			=	"SCDR";
						tabMenuView			=	"driverListTab";
						idReservationDetails=	paramList.idReservationDetails;
						dateSchedule		=	paramList.date;
						break;
		default		:	break;
	}
	
	return 'data-idMessageAdmin="'+idMessageAdmin+'" '+
		   'data-idMessageType="'+idMessageType+'" '+
		   'data-urlView="'+urlView+'" '+
		   'data-aliasView="'+aliasView+'" '+
		   'data-tabMenuView="'+tabMenuView+'" '+
		   'data-dateSchedule="'+dateSchedule+'" '+
		   'data-idMailbox="'+idMailbox+'" '+
		   'data-idReservation="'+idReservation+'" '+
		   'data-idReservationDetails="'+idReservationDetails+'" '+
		   'data-idDayOffRequest="'+idDayOffRequest+'"';
}

function openMenuFromNotification(elem){
	var idMessageAdmin		=	$(elem).attr("data-idMessageAdmin"),
		idMessageType		=	$(elem).attr("data-idMessageType"),
		urlView				=	$(elem).attr("data-urlView"),
		aliasView			=	$(elem).attr("data-aliasView"),
		tabMenuView			=	$(elem).attr("data-tabMenuView"),
		dateSchedule		=	$(elem).attr("data-dateSchedule"),
		idMailbox			=	$(elem).attr("data-idMailbox"),
		idReservation		=	$(elem).attr("data-idReservation"),
		idReservationDetails=	$(elem).attr("data-idReservationDetails"),
		idDayOffRequest		=	$(elem).attr("data-idDayOffRequest");
	
	localStorage.removeItem("OSNotificationData");
	var OSNotificationData	=	{};
	switch(idMessageType){
		case "1"	:	OSNotificationData	=	{type:urlView, idMailbox:idMailbox};
						break;
		case "2"	:	OSNotificationData	=	{type:urlView, idReservation:idReservation};
						break;
		case "3"	:	break;
		case "4"	:	OSNotificationData	=	{type:urlView, idReservationDetails:idReservationDetails, dateSchedule:dateSchedule};
						break;
		case "5"	:	OSNotificationData	=	{type:urlView, idDayOffRequest:idDayOffRequest, tabMenuView:tabMenuView};
						break;
		case "6"	:	OSNotificationData	=	{type:urlView, idReservationDetails:idReservationDetails, tabMenuView:tabMenuView, dateSchedule:dateSchedule};
						break;
		default		:	break;
	}
	localStorage.setItem('OSNotificationData', JSON.stringify(OSNotificationData));
	
	getViewURL(urlView, aliasView, function(){
		dismissNotification(idMessageAdmin);
		getUnreadNotificationList();
		$("#containerNotificationButton, #containerNotificationIconBodyList").removeClass("show");
	});
}

function jumpFocusToElement(elementID){	
	if($("#"+elementID).length != 0) {
		document.getElementById(elementID).scrollIntoView();
	}
}