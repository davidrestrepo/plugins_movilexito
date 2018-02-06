(function($){

	//valida formulario pop up de ingreso de números
	Drupal.behaviors.submit_form_modal = function(){
		//var url = window.location.protocol + "//" + window.location.host;
		var data = {
			email: $('#email').val(),
			telephone: $('#telephone').val(),
			birthdate: $('#birthdate').val(),
		}
		var settings = {
			"async": true,
			"crossDomain": true,
			//"url": "http://localhost/movilexito/movilexito_1x1/submit_data",
			"url": "/movilexito_1x1/submit_data",
			"method": "POST",
			"headers": {
				"content-type": "application/x-www-form-urlencoded",
				"cache-control": "no-cache",
			},
			"data": data
		}

		$('#modalExito').toggle(false);
		$('#modalLoader').toggle(true);

		$.ajax(settings).done(function (response) {
			if(response.message != ''){
				if(response.message=='Ejecucion Exitosa'){
					$('#modalerror').toggleClass('messages--error messages--status');
				}

				$('#ulError').html(response.message);
				$('#modalLoader').hide();
				$('#modalerror').toggle(true);				
			}			
			console.log(response);
		}).fail( function( jqXHR, textStatus, errorThrown ) {			
			$('#ulError').html(drupalSettings.errorMsg);
			$('#modalLoader').hide();
			$('#modalerror').toggle(true);    	
	});
	}

	Drupal.behaviors.scroll_top = function() {
		$("html, body").animate({ scrollTop: "30px" });
	};
})(jQuery);
