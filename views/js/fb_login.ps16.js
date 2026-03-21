$(function(){	
	var is_onepagecheckout = $('#onepagecheckoutps').length === 1;
	if (is_onepagecheckout) {
		return;
	}

    //checkout
    if ($('#order-opc').length) {
        var container = $('<div/>', {class: 'agcustomers facebook-container'}); 
        container.load(
            agcustomers.urls.facebook,
            {
                action: 'getFacebookButton',
                form: 'opc'
            }
        );

        $('#login_form').prepend(container);
    } else {
    	//adiciona o botão para obter as informações do usuário atrávés do Facebook
        var container_login = $('<div/>', {class: 'agcustomers facebook-container'}); 
        var container_registration = container_login.clone();

        container_login.load(
        	agcustomers.urls.facebook,
        	{
        		action: 'getFacebookButton',
        		form: 'login'
        	}
        );

        container_registration.load(
        	agcustomers.urls.facebook,
        	{
        		action: 'getFacebookButton',
        		form: 'registration'
        	}
        );

    	$('#create-account_form .form_content').prepend(container_registration);
    	$('#login_form .form_content').prepend(container_login);
    }
});