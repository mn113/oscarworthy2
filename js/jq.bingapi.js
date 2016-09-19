$(function () {
	
	var baseUrl = "http://api.microsofttranslator.com/V2/Ajax.svc/";
	var myAppId = '5CFE24776BE4AEA7B030358620CF2BB834CD5076';
	
	// API callback:
	window.bingCallback = function(response) {
	    console.log(response);
		for (var i = 0; i < response.length; i++) {
			$("#main").append("("+(i+4501)+",'"+response[i]+"'),");
		}
	}
	
	var bingify = function(method, input) {
		var fnName, params = {'appId': myAppId};
	    switch(method) {
	        case 'detect':
	            fnName = 'Detect';
	            params.text = encodeURIComponent(input);
	            break;
	        case 'detectArray':
	            fnName = 'DetectArray',
	            params.texts = JSON.stringify(input);
	            break;
	        default:
	            return false;
	    }
	
	    $.ajax({
	        url: baseUrl + fnName,
	        data: params,
	        dataType: 'jsonp',
	        jsonp: 'oncomplete',				//
	        jsonpCallback: 'bingCallback',		// adds oncomplete=bingCallback to url
	        cache: true
	    });
	}

	//var text = "The language of this text is going to be detected.";
	//var texts = ["This is English text.", "Das ist deutsche Text.", "Questo un testo italiano."];
	
	//console.log(jsonfilms.length, 'film titles to process');
	
	//bingify('detectArray', jsonfilms);

});

