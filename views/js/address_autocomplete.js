$(function(){
	var input_postcode;
	var input_city;
	var input_state;
	var input_address;
	var input_district;
	var jqxhr;

	var brazil_states = {
		'AC':'Acre',
		'AL':'Alagoas',
		'AP':'Amapá',
		'AM':'Amazonas',
		'BA':'Bahia',
		'CE':'Ceará',
		'DF':'Distrito Federal',
		'ES':'Espírito Santo',
		'GO':'Goiás',
		'MA':'Maranhão',
		'MT':'Mato Grosso',
		'MS':'Mato Grosso do Sul',
		'MG':'Minas Gerais',
		'PA':'Pará',
		'PB':'Paraíba',
		'PR':'Paraná',
		'PE':'Pernambuco',
		'PI':'Piauí',
		'RJ':'Rio de Janeiro',
		'RN':'Rio Grande do Norte',
		'RS':'Rio Grande do Sul',
		'RO':'Rondônia',
		'RR':'Roraima',
		'SC':'Santa Catarina',
		'SP':'São Paulo',
		'SE':'Sergipe',
		'TO':'Tocantins'
	};


	//módulo onepagecheckout
	var is_onepagecheckout = $('#onepagecheckoutps').length === 1;
	var is_authentication = $('#authentication').length === 1;

	function loadInputsVars()
	{
		if (is_onepagecheckout) {
			input_postcode = $('#delivery_postcode');
			input_city= $('#delivery_city');
			input_state = $('#delivery_id_state');
			input_address =$('#delivery_address1');
			input_district =$('#delivery_' + agcustomers_district_field);
		} else if (is_authentication) {
			input_postcode = $('#postcode');
			input_city= $('#city');
			input_state = $('#id_state');
			input_address =$('#address1');
			input_district =$('#' + agcustomers_district_field);
		} else {
			input_postcode = $('[name=postcode]');
			input_city= $('[name=city]');
			input_state = $('[name=id_state]');
			input_address =$('[name=address1]');
			input_district =$('[name=' + agcustomers_district_field + ']');
		}
	}

	function postcodeChanged()
	{
		loadInputsVars();
			
			var selected_country = $('[name=id_country], #id_country, [name=delivery_id_country]').find('option:checked').text().trim();
			if (selected_country != 'Brasil' && selected_country != 'Brazil') {
				return;
			}

			var postcode = input_postcode.val();
			let match = postcode.match(/\d+/g); 
			if (match == null) {
				return;
			}

			postcode = match.join('');

			if (postcode.length != 8) {
				return;
			}
			
			if (typeof jqxhr !== 'undefined') {
				jqxhr.abort();
			}


			$([input_city, input_state, input_address, input_postcode]).prop('readonly', true);

			jqxhr = $.ajax({
				url: agcustomers_address_search_url + '?postcode=' + postcode,
				dataType : 'JSON',
				success : function(address){
					$([input_city, input_state, input_address, input_postcode]).prop('readonly', false);
					input_address.val(address.street);
					input_district.val(address.district);
					input_city.val(address.city);

					var option = input_state.find('option').filter(function () { return $(this).html() == address.state; });

					if (option.length === 0) {
						var state = brazil_states[address.state];
						var option = input_state.find('option').prop('selected', false).filter(function () { return $(this).html() == state; });
					}

					option.prop('selected', true);
					input_state.val(option.val());
					input_state.change();

					if(agcustomers.config.address.disabled == 1){
						if (address.street.trim()) {
							input_address.prop('readonly', true);
						} else {
							input_address.prop('readonly', false);
						}

						if (address.district.trim()) {
							input_district.prop('readonly', true);
						} else {
							input_district.prop('readonly', false);
						}

						input_city.prop('readonly', true);
						input_state.prop('readonly', true);
						
					}else{
						input_city.prop('readonly', false);
						input_state.prop('readonly', false);
						input_address.prop('readonly', false);
						input_district.prop('readonly', false);
					}
				},
				complete: function(){
					if(agcustomers.config.address.disabled != 1){
						$(input_city).removeAttr('readonly').trigger('change');
						$(input_state).removeAttr('readonly').trigger('change');
						$(input_address).removeAttr('readonly').trigger('change');
						$(input_district).removeAttr('readonly').trigger('change');
						$(input_postcode).removeAttr('readonly').trigger('change');
					}
					validateInputs();
				}
			});
	}

	function validateInputs() {
        if(agcustomers.config.address.error_msg == 1){
            $.each(agcustomers.fields.address, function(key, value){
                var input = $('[name=' + value.name + ']');
                validateInput(input);
                input.on('keyup', '', function(){
                    validateInput(input);
                });
            });
        }
    }

    function validateInput(input) {
        markInputAsValid(input);
        var valInput = $(input).val();

        if (valInput == '' || typeof valInput === 'undefined') {
            if (input.is('[required]')) {
                markInputAsInvalid(input);
            }
            return;
        }
    }

	function markInputAsInvalid(input) {

        error_msg = input.closest('.form-group').find('label').text().trim() + " inválido.";

        if (error_msg != "") {
            var div_error = $('<label/>', {
                class: 'agcustomers-error',
                style: 'width: 100%; text-align: start;',
                text: error_msg
            });

            $(input).closest('.form-group').find(".form-control").parent().append(div_error);
        }

        input.addClass('error').closest('.form-group').addClass('has-error').removeClass('has-success');
        input.removeClass('valid');
    }
    function markInputAsValid(input) {
       	$(input).closest('.form-group').removeClass('has-error').removeClass('form-error').find('.agcustomers-error').remove();;
    }

	var interval = setInterval(function(){
		loadInputsVars();

		if (input_postcode.val() == '' && typeof agcliente_cookie_postcode !== 'undefined' && agcliente_cookie_postcode != null && agcliente_cookie_postcode != '') {
			input_postcode.val(agcliente_cookie_postcode);
			postcodeChanged();
		}

		//versão 1.7
		if (typeof prestashop !== 'undefined' && !is_onepagecheckout) {
			//reposiciona o campo de CEP para antes do campo address1
			if (agcustomers.config.address.position == "1") {			
				if (prestashop.page.page_name === 'checkout' || prestashop.page.page_name === 'address') {
					input_postcode.closest('.form-group').insertBefore(input_address.closest('.form-group'));
				}
			}
		}

		if (is_onepagecheckout) {
			input_postcode.on('input', postcodeChanged);
		} else {
			$(document).on('input', '[name=postcode]', postcodeChanged);	
		};

		if (input_postcode.length != 0) {
			clearInterval(interval);
		}
	}, 1000);
})