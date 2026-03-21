$(function(){
	setTimeout(() => {
		prepareDiv();
	}, 500);
});

function prepareDiv () {

	if($('.facebook-container').length == 0){
		if ($('#checkout').length) {
			var container_login = $('<div/>', {class: 'agcustomers facebook-container'}); 
		
			$('#login-form').prepend(container_login);
	
			var container_registration = $('<div/>', {class: 'agcustomers facebook-container'}); 
			
			$('#customer-form').prepend(container_registration);
		} else {
	
			var container = $('<div/>', {class: 'agcustomers facebook-container'}); 
	
			$('#customer-form, #login-form').prepend(container);
		}
	}

	if($(".btn-facebook").length == 0){
		var btn_facebook =false; 
	}else{
		var btn_facebook =true; 
	}


	if(!btn_facebook){
		$col="col-sm-12";
	}else if(agcustomers.configs_btn.google_type_btn=='standard'){
		$col="col-sm-6";
	}else{
		$col="col-sm-1";
	}

	$(".facebook-container").prepend("<div class='' id='buttonDiv'></div>");
	$("#buttonDiv").addClass($col);
	renderGoogleBtn();

	if(!btn_facebook){
		var t = (window.agcustomers && window.agcustomers.translations) || {};
		var info = t.social_login_info || 'Use your account to make identification easier. Only your first name, last name and email address will be stored.';
		$(".facebook-container").append('<p>'+ info +'</p><hr>');
	}


}

function parseJwt (token) {
    var base64Url = token.split('.')[1];
    var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    var jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));

    return JSON.parse(jsonPayload);
};

function handleCredentialResponse(response) {
	var info_google = parseJwt(response.credential);
	redirect(info_google);
	
}

function redirect(info_google)
{
		$.ajax({
			url: agcustomers.urls.google,
			type: 'GET',
			dataType: 'JSON',
			data: {
				email: info_google.email
			}
		}).done(function(response) {
			if(response.success){
				if(response.duplicated){
					document.location.reload(true);
				}else{
					window.location.replace(agcustomers.urls.create_acount+"&firstname="+info_google.given_name+"&lastname="+info_google.family_name+"&email="+info_google.email);
				}
			}
		  });
}

function renderGoogleBtn(){
	google.accounts.id.initialize({
	  client_id: agcustomers_google_client_id,
	  callback: handleCredentialResponse
	});

	if(agcustomers_google_prompt && !agcustomers_logged){
		google.accounts.id.prompt(
			function (prmts) {
				console.log(prmts);
			}
		);
	}
	google.accounts.id.renderButton(
	  document.getElementById("buttonDiv"),
	  	{
			type: agcustomers.configs_btn.google_type_btn,
			theme: agcustomers.configs_btn.google_theme_btn,
			size: agcustomers.configs_btn.google_size_btn,
			text: agcustomers.configs_btn.google_text_btn,
			shape: agcustomers.configs_btn.google_shape_btn,
			logo_alignment: agcustomers.configs_btn.google_logo_btn
    	}  
	);
}