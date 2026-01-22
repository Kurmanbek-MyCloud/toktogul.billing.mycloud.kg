/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

var Vtiger_PBXManager_Js = {
        //SalesPlatform.ru begin PBXManager porting
        showPnotify : function(customParams) {
            return $.pnotify($.extend({
                sticker: false,
                delay: '3000',
                type: 'error',
                pnotify_history: false
            }, customParams));
        },
        //SalesPlatform.ru end PBXManager porting
	/**
	 * Function registers PBX for popups
	 */
	registerPBXCall : function() {
            Vtiger_PBXManager_Js.requestPBXgetCalls();
	},

	/**
	 * Function registers PBX for Outbound Call
	 */
	registerPBXOutboundCall : function(number,record) {
		Vtiger_PBXManager_Js.makeOutboundCall(number,record);
	},
	/**
	 * Function request for PBX popups
	 */
	requestPBXgetCalls : function() {
		var url = 'index.php?module=PBXManager&action=IncomingCallPoll&mode=searchIncomingCalls';
                // Salesplatform.ru begin PBXManager porting
		app.request.get({url : url}).then(function(err, data){
                    if (typeof data === 'string') {
                        location.href = 'index.php';
                    }
                    
                    
			if(!err) {
				for(var i = 0; i < data.length; i++) {
					var record = data[i];
                                        //app.request.get(url).then(function(data){
                                        //if(data.success && data.result) {
                                        //        for(i=0; i< data.result.length; i++) {
                                        //                var record = data.result[i];
                                        //SalesPlatform.ru end PBXManager porting
					if(jQuery('#pbxcall_'+record.pbxmanagerid+'').size()== 0 ) {
                                            Vtiger_PBXManager_Js.showPBXIncomingCallPopup(record);
                                        } else {
                                            Vtiger_PBXManager_Js.updatePBXIncomingCallPopup(record);
					}
				}
                        }
		});
		Vtiger_PBXManager_Js.removeCompletedCallPopup();
	},

	/**
	 * Function display the PBX popup
	 */
	showPBXIncomingCallPopup : function(record) {
		
            // Salesplatform.ru begin PBXManager porting
            var contactFieldStyle = ((record.customer != null && record.customer != '') ? 'hide' : '');
            //SalesPlatform.ru end PBXManager porting
		var params = {
			title: '<h4 class="ui-pnotify-title">Входящий звонок<button id="hide_call" class="btn btn-danger btn-sm" style="/* display: block; */float: right;">x</button></h4>',
			//SalesPlatform.ru begin
            text: '<div class="row-fluid pbxcall" id="pbxcall_'+record.pbxmanagerid+'" callid='+record.pbxmanagerid+' style="color:black">' + 
                    '<span class="span12" style="color:red; font-size:1.15em;" id="caller" value="'+record.customernumber+'">'+app.vtranslate('JS_PBX_CALL_FROM')+' : '+record.customernumber+'</span><span class="span12 ' + contactFieldStyle + '" id="contactsave_'+record.pbxmanagerid+'">\n\
                   <span><input style="margin-bottom: 5px; " class="span3 form-control form-control-sm" id="email_'+record.pbxmanagerid+'" type="text" placeholder="' + app.vtranslate('Enter Email-id') + '"></input><input style="margin-bottom: 5px; " class="span3 form-control form-control-sm" id="name_'+record.pbxmanagerid+'" type="text" placeholder="Введите имя"></input><input style="margin-bottom: 5px; " class="span3 form-control form-control-sm" id="surname_'+record.pbxmanagerid+'" type="text" placeholder="Введите фамилию"></input><select class="input-large" id="module_'+record.pbxmanagerid+'"><option value="Select" selected>Выберите тип</option></select><h5 class="alert-danger hide span3" id="alert_msg">'+app.vtranslate('JS_PBX_FILL_ALL_FIELDS')+'</h5>\n\
                   <button class="btn btn-success pull-right"  id="saveFromPopupCall" type="submit">' + app.vtranslate('Save') + '</button>\n\
                   </span></span><br/><span class="span12" style="display:none" id="owner">'+app.vtranslate('JS_LBL_ASSIGNED_TO')+'&nbsp;:&nbsp;<span id="ownername"></span></span></div>',
                
            //text: '<div class="row-fluid pbxcall" id="pbxcall_'+record.pbxmanagerid+'" callid='+record.pbxmanagerid+' style="color:black"><span class="span12" id="caller" value="'+record.customernumber+'">'+app.vtranslate('JS_PBX_CALL_FROM')+' : '+record.customernumber+'</span><span class="hide span12" id="contactsave_'+record.pbxmanagerid+'">\n\
            //        <span><input class="span3" id="email_'+record.pbxmanagerid+'" type="text" placeholder="Enter Email-id"></input>&nbsp;&nbsp;&nbsp;<select class="input-small" id="module_'+record.pbxmanagerid+'" placeholder="Select"><option>Select</option></select><h5 class="alert-danger hide span3" id="alert_msg">'+app.vtranslate('JS_PBX_FILL_ALL_FIELDS')+'</h5>\n\
            //        <button class="btn btn-success pull-right"  id="pbxcontactsave_'+record.pbxmanagerid+'" recordid="'+record.pbxmanagerid+'" type="submit">Save</button>\n\
            //        </span></span><br/><span class="span12" style="display:none" id="answeredby"><i class="icon-headphones"></i>&nbsp;<span id="answeredbyname"></span></span></div>',
            //SalesPlatform.ru end
            width: '28%',
			min_height: '75px',
			addclass:'vtCall',
			icon: 'vtCall-icon',
			hide : false,
			closer : false,
			type:'info',
			after_open:function(p) {
				jQuery(p).data('info', record);	
				$("#hide_call").on('click',()=>{
					$("div.ui-pnotify.vtCall").hide(1000);
				});
				$("button#saveFromPopupCall").on('click',()=>{
					var email = $("input#email_"+record.pbxmanagerid+"").val().trim();
					var name = $("input#name_"+record.pbxmanagerid+"").val().trim();
					var surname = $("input#surname_"+record.pbxmanagerid+"").val().trim();
					var type = $("select#module_"+record.pbxmanagerid+" option:selected").val().trim();
					if(email.indexOf("@") > 0 && type != "Select")
					{
						$.ajax({
							type: "POST",
							url: "/modules/PBXManager/resources/PBXManager.php",
							data: {"email" : email, "name" : name, "surname" : surname, "pbxmanagerid" : record.pbxmanagerid, "key" : Vtiger_PBXManager_Js.md5(""+name+""+surname+""+record.pbxmanagerid), "type" : type},
							dataType: "json",
							success: function (response) {
								Vtiger_PBXManager_Js.updatePBXIncomingCallPopup();
							},
						});
					}
					else
					{
						alert("Введите почту в правильном формате или выберите тип");
					}
					// 
				});
			}
		};
                // Salesplatform.ru begin PBXManager porting
                Vtiger_PBXManager_Js.showPnotify(params);
                //Vtiger_Helper_Js.showPnotify(params);
                //SalesPlatform.ru end PBXManager porting
                    
		//To remove the popup for all users except answeredby (existing record)
		if(record.user) {
                    if(record.user != record.current_user_id) {
                            Vtiger_PBXManager_Js.removeCallPopup(record.pbxmanagerid);
                    }
		}

		// To check if it is new or existing contact
		Vtiger_PBXManager_Js.checkIfRelatedModuleRecordExist(record);

		if(record.answeredby!=null){
			jQuery('#answeredbyname','#pbxcall_'+record.pbxmanagerid+'').text(record.answeredby);
			jQuery('#answeredby','#pbxcall_'+record.pbxmanagerid+'').show();
		}

		jQuery('#pbxcontactsave_'+record.pbxmanagerid+'').bind('click', function(e) {
			var pbxmanagerid = jQuery(e.currentTarget).attr('recordid');

			if(jQuery('#module_'+pbxmanagerid+'').val() == 'Select'){
				jQuery('#alert_msg').show();
				return false;
			}
			if(jQuery('#email_'+pbxmanagerid+'').val() == ""){
				jQuery('#alert_msg').show();
				return false;
			}

			Vtiger_PBXManager_Js.createRecord(e, record);
			//To restrict the save button action to one click
			jQuery('#pbxcontactsave_'+record.pbxmanagerid+'').unbind('click');
		});
	},
	md5: function(string){
		function RotateLeft(lValue, iShiftBits) {
			return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
	}
 
	function AddUnsigned(lX,lY) {
			var lX4,lY4,lX8,lY8,lResult;
			lX8 = (lX & 0x80000000);
			lY8 = (lY & 0x80000000);
			lX4 = (lX & 0x40000000);
			lY4 = (lY & 0x40000000);
			lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
			if (lX4 & lY4) {
					return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
			}
			if (lX4 | lY4) {
					if (lResult & 0x40000000) {
							return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
					} else {
							return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
					}
			} else {
					return (lResult ^ lX8 ^ lY8);
			}
	}
 
	function F(x,y,z) { return (x & y) | ((~x) & z); }
	function G(x,y,z) { return (x & z) | (y & (~z)); }
	function H(x,y,z) { return (x ^ y ^ z); }
	function I(x,y,z) { return (y ^ (x | (~z))); }
 
	function FF(a,b,c,d,x,s,ac) {
			a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
			return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function GG(a,b,c,d,x,s,ac) {
			a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
			return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function HH(a,b,c,d,x,s,ac) {
			a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
			return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function II(a,b,c,d,x,s,ac) {
			a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
			return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function ConvertToWordArray(string) {
			var lWordCount;
			var lMessageLength = string.length;
			var lNumberOfWords_temp1=lMessageLength + 8;
			var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
			var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
			var lWordArray=Array(lNumberOfWords-1);
			var lBytePosition = 0;
			var lByteCount = 0;
			while ( lByteCount < lMessageLength ) {
					lWordCount = (lByteCount-(lByteCount % 4))/4;
					lBytePosition = (lByteCount % 4)*8;
					lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
					lByteCount++;
			}
			lWordCount = (lByteCount-(lByteCount % 4))/4;
			lBytePosition = (lByteCount % 4)*8;
			lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
			lWordArray[lNumberOfWords-2] = lMessageLength<<3;
			lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
			return lWordArray;
	};
 
	function WordToHex(lValue) {
			var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
			for (lCount = 0;lCount<=3;lCount++) {
					lByte = (lValue>>>(lCount*8)) & 255;
					WordToHexValue_temp = "0" + lByte.toString(16);
					WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
			}
			return WordToHexValue;
	};
 
	function Utf8Encode(string) {
			string = string.replace(/\r\n/g,"\n");
			var utftext = "";
 
			for (var n = 0; n < string.length; n++) {
 
					var c = string.charCodeAt(n);
 
					if (c < 128) {
							utftext += String.fromCharCode(c);
					}
					else if((c > 127) && (c < 2048)) {
							utftext += String.fromCharCode((c >> 6) | 192);
							utftext += String.fromCharCode((c & 63) | 128);
					}
					else {
							utftext += String.fromCharCode((c >> 12) | 224);
							utftext += String.fromCharCode(((c >> 6) & 63) | 128);
							utftext += String.fromCharCode((c & 63) | 128);
					}
 
			}
 
			return utftext;
	};
 
	var x=Array();
	var k,AA,BB,CC,DD,a,b,c,d;
	var S11=7, S12=12, S13=17, S14=22;
	var S21=5, S22=9 , S23=14, S24=20;
	var S31=4, S32=11, S33=16, S34=23;
	var S41=6, S42=10, S43=15, S44=21;
 
	string = Utf8Encode(string);
 
	x = ConvertToWordArray(string);
 
	a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;
 
	for (k=0;k<x.length;k+=16) {
			AA=a; BB=b; CC=c; DD=d;
			a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
			d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
			c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
			b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
			a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
			d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
			c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
			b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
			a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
			d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
			c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
			b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
			a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
			d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
			c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
			b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
			a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
			d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
			c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
			b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
			a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
			d=GG(d,a,b,c,x[k+10],S22,0x2441453);
			c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
			b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
			a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
			d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
			c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
			b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
			a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
			d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
			c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
			b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
			a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
			d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
			c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
			b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
			a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
			d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
			c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
			b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
			a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
			d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
			c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
			b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
			a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
			d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
			c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
			b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
			a=II(a,b,c,d,x[k+0], S41,0xF4292244);
			d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
			c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
			b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
			a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
			d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
			c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
			b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
			a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
			d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
			c=II(c,d,a,b,x[k+6], S43,0xA3014314);
			b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
			a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
			d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
			c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
			b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
			a=AddUnsigned(a,AA);
			b=AddUnsigned(b,BB);
			c=AddUnsigned(c,CC);
			d=AddUnsigned(d,DD);
			}
 
		var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);
 
		return temp.toLowerCase();
	},
	createRecord: function(e, record) {
		var pbxmanagerid = jQuery(e.currentTarget).attr('recordid');
		var email = jQuery('#email_'+pbxmanagerid+'').val();
		var moduleName = jQuery('#module_'+pbxmanagerid+'').val();

		var number = jQuery('#caller','#pbxcall_'+pbxmanagerid+'').attr("value");
		var url = 'index.php?module=PBXManager&action=IncomingCallPoll&mode=createRecord&number='+encodeURIComponent(number)+'&email='+encodeURIComponent(email)+'&callid='+record.sourceuuid+'&modulename='+moduleName;
		// Salesplatform.ru begin PBXManager porting
                app.request.get({'url': url}).then(function(err, data){
			if(!err) {
                //app.request.get(url).then(function(data){
                //      if(data.success && data.result) {
                //SalesPlatform.ru end PBXManager porting
				jQuery('#contactsave_'+pbxmanagerid+'').hide();
			}
		 });
	},

	checkIfRelatedModuleRecordExist: function(record) {
		switch(record.callername){
			case null:	var url = 'index.php?module=PBXManager&action=IncomingCallPoll&mode=checkModuleViewPermission&view=EditView';
                            // Salesplatform.ru begin PBXManager porting
                            app.request.get({'url': url}).then(function(err, data){
                            //app.request.get(url).then(function(data){
                            //var responsedata = JSON.parse(data);
                            //SalesPlatform.ru end PBXManager porting   
							var showSaveOption = false;
                                                        // Salesplatform.ru begin PBXManager porting
							var moduleList = data.modules;
							var contents = jQuery('#module_'+record.pbxmanagerid+'');
							var newEle;
							for(var module in moduleList){
								if(moduleList.hasOwnProperty(module)) {
									if(moduleList[module]){
									   newEle = '<option id="select_'+module+'" value="'+module+'">'+app.vtranslate(module)+'</option>'; 
									   contents.append(newEle);
									   showSaveOption = true;
									}
								}
							}
                                                        // Salesplatform.ru begin PBXManager porting
							if(data && showSaveOption) {
                                                            // if(responsedata.success && showSaveOption)
                                                            //SalesPlatform.ru end PBXManager porting 
                                                        jQuery('#contactsave_'+record.pbxmanagerid+'').show();
                                                        // Salesplatform.ru begin PBXManager porting
                                                    }
                                                    //SalesPlatform.ru end PBXManager porting 
						});
						break;
			default:	jQuery('#caller','#pbxcall_'+record.pbxmanagerid+'').html(app.vtranslate('JS_PBX_CALL_FROM')+' :&nbsp;<a href="index.php?module='+record.customertype+'&view=Detail&record='+record.customer+'">'+record.callername+'</a>');
						// SalesPlatform.ru begin
                        jQuery('#ownername','#pbxcall_'+record.pbxmanagerid+'').text(record.ownername);
                        jQuery('#owner','#pbxcall_'+record.pbxmanagerid+'').show();
                        // SalesPlatform.ru end
                        break;
		}
	},

	 /**
	 * Function to update the popup with answeredby, hide contactsave option e.t.c.,
	 */
	updatePBXIncomingCallPopup: function(record){
		if(record.answeredby!=null){
			jQuery('#answeredbyname','#pbxcall_'+record.pbxmanagerid+'').text(record.answeredby);
			jQuery('#answeredby','#pbxcall_'+record.pbxmanagerid+'').show();
		}
		if(record.customer!=null && record.customer!=''){
			jQuery('#caller','#pbxcall_'+record.pbxmanagerid+'').html(app.vtranslate('JS_PBX_CALL_FROM')+' :&nbsp;<a href="index.php?module='+record.customertype+'&view=Detail&record='+record.customer+'">'+record.callername+'</a>');
			jQuery('#contactsave_'+record.pbxmanagerid+'').hide();
		}
		//To remove the popup for all users except answeredby (new record)
		if(record.user) {
			if(record.user != record.current_user_id) {
				 Vtiger_PBXManager_Js.removeCallPopup(record.pbxmanagerid);
			}
		}
	},

	 /**
	 * Function to remove the call popup which is completed
	 */
	removeCompletedCallPopup:function(){
		var callid = null;
		var pbxcall = jQuery('.pbxcall');
		for(var i=0; i<pbxcall.length;i++){
			callid = pbxcall[i].getAttribute('callid');
			var url = 'index.php?module=PBXManager&action=IncomingCallPoll&mode=getCallStatus&callid='+encodeURIComponent(callid)+'';
                        // Salesplatform.ru begin PBXManager porting
			//app.request.get(url).then(function(data){
                        //if(data.result){
                        app.request.get({url : url}).then(function(err, data){
                            if(data && data !='in-progress' && data !='ringing'){	
                        //SalesPlatform.ru end PBXManager porting 
                                Vtiger_PBXManager_Js.removeCallPopup(callid);	
                            }
			});
		}
	},

	/**
	 * Function to remove call popup
	 */
	removeCallPopup: function(callid) {
		jQuery('#pbxcall_'+callid+'').parent().parent().parent().remove();
	},

	 /**
	 * To get contents holder based on the view
	 */
	getContentHolder:function(view){
		if(view == 'List')
			return jQuery('.listViewContentDiv');
		else
			return jQuery('.detailViewContainer');
	},

	 /**
	 * Function to forward call to number
	 */
	makeOutboundCall : function(number, record){
		var params = {
                    // Salesplatform.ru begin PBXManager porting
                    data: {
                    // Salesplatform.ru end PBXManager porting    
                        number : number,
			record : record,
			module : 'PBXManager',
			action : 'OutgoingCall',
                    // Salesplatform.ru begin PBXManager porting
                    }
                    // Salesplatform.ru end PBXManager porting

                        
		}
                // Salesplatform.ru begin PBXManager porting
		app.request.get(params).then(function(err, data){
                    if(!err){
                    //app.request.get(params).then(function(data){
                    //if(data.result){                            
                    // Salesplatform.ru end PBXManager porting  
                        params = {
                            text : app.vtranslate('JS_PBX_OUTGOING_SUCCESS'),
                            type : 'info'
                        }
                    }else{
                        params = {
                            text : app.vtranslate('JS_PBX_OUTGOING_FAILURE'),
                            type : 'error'
                        }
                    }
                    // Salesplatform.ru begin PBXManager porting
                    Vtiger_PBXManager_Js.showPnotify(params);
                    //Vtiger_Helper_Js.showPnotify(params);
                    // Salesplatform.ru end PBXManager porting  
		});
	},

	 /**
		* Function to register required events
		*/
	 registerEvents : function(){
		var thisInstance = this;
		//for polling
		var url = 'index.php?module=PBXManager&action=IncomingCallPoll&mode=checkPermissionForPolling';
                
                // Salesplatform.ru begin PBXManager porting
		app.request.get({url : url}).then(function(err, data){
			if(!err) {
                            Vtiger_PBXManager_Js.registerPBXCall();
                            Visibility.every(4000, function () {
                                Vtiger_PBXManager_Js.registerPBXCall();
                            });
                //app.request.get(url).then(function(data){
                //      if(data && data.result) {
                //Vtiger_PBXManager_Js.registerPBXCall();
                //setInterval("Vtiger_PBXManager_Js.registerPBXCall()", 3000);
                //SalesPlatform.ru end PBXManager porting
			}
		});
	}

}

//On Page Load
jQuery(document).ready(function() {
    Vtiger_PBXManager_Js.registerEvents();
});