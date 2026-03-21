function getFacebookData()
{
  	//solicita os campos de nome e e-mail
    FB.api('/me', { locale: 'pt_BR', fields: 'name, email' },
    	function(response) {
    		console.log(response);
    		//aqui nome e e-mail do usuário devem ser preenchidos automaticamente no formulário de cadastro
     	}
    );	
}
$(function(){
	window.fbAsyncInit = function() {
		//inicializa o app do Facebook
	    FB.init({
	      appId      : agsocialcustomers_fb_app_id,
	      cookie     : true,
	      xfbml      : true,
	      version    : 'v2.8'
	    });


	    //adiciona o botão para obter as informações do usuário atrávés do Facebook
		var btn_get_facebook_data = $('<button/>', {
			text: 'Facebook',
			'class': 'btn btn-facebook'
		}).css('background-image', agcustomers.urls.facebook_image);

		$('#customer-form').prepend(btn_get_facebook_data);

		//quando o botão for clicado, tenta obter os dados do usúario através do facebook
		btn_get_facebook_data.click(function(){
			FB.getLoginStatus(function(response) {      
				//se o aplicativo estiver autorizado, busca os dados do usuário através da API do Facebook
		      if (response.status == 'connected') {
		      	getFacebookData();
		      } else {
		      	//se o usuário não tiver concedido permissão ao aplicativo ainda, solicita as permissões
		        FB.login(
		          function(response){
		          	if (response.status == 'connected') {
		          		getFacebookData();
		          	}
		          },
		          {scope: 'public_profile,email'}
		        );
		      }
		    });

			//evita que o botão provoque redirecionamento da página
		    return false;
		});

		//registra que a página atual foi visualizada para fins estatísticos
	    FB.AppEvents.logPageView();
	}
});


//adiciona o script do Facebook 
(function(d, s, id){
 var js, fjs = d.getElementsByTagName(s)[0];
 if (d.getElementById(id)) {return;}
 js = d.createElement(s); js.id = id;
 js.src = "//connect.facebook.net/en_US/sdk.js";
 fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));