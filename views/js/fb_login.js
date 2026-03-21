$(function(){	
	var is_onepagecheckout = $('#onepagecheckoutps').length === 1;
	if (is_onepagecheckout) {
		return;
	}

	//o checkout possui dois formulários, um de login e um de cadastro
	if ($('#checkout').length) {
	    var container_login = $('<div/>', {class: 'agcustomers facebook-container'}); 
	    container_login.load(
	    	agcustomers.urls.facebook,
	    	{
	    		action: 'getFacebookButton',
	    		form: 'login_checkout'
	    	}
	    );
	    $('#login-form').prepend(container_login);

	    var container_registration = $('<div/>', {class: 'agcustomers facebook-container'}); 
	    container_registration.load(
	    	agcustomers.urls.facebook,
	    	{
	    		action: 'getFacebookButton',
	    		form: 'registration_checkout'
	    	}
	    );
	    $('#customer-form').prepend(container_registration);
	} else {

		var form;
		if ($('#customer-form').length) {
			form = 'registration';
		} else if ($('#login-form').length) {
			form = 'login';
		}


	    //adiciona o botão para obter as informações do usuário atrávés do Facebook
	    var container = $('<div/>', {class: 'agcustomers facebook-container'}); 
	    container.load(
	    	agcustomers.urls.facebook,
	    	{
	    		action: 'getFacebookButton',
	    		form: form
	    	}
	    );

		$('#customer-form, #login-form').prepend(container);
	}


});
