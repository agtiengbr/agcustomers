$(function(){
	var is_onepagecheckout = $('#onepagecheckoutps').length === 1;

	var validate_functions = {
		validateCpf: function(cpf)
		{
		    // Removing special characters from value
		    var value = cpf.replace( /([~!@#$%^&*()_+=`{}\[\]\-|\\:;'<>,.\/? ])+/g, "" );

		    // Checking value to have 11 digits only
		    if ( value.length !== 11 ) {
		        return false;
		    }

		    var sum = 0,
		        firstCN, secondCN, checkResult, i;

		    firstCN = parseInt( value.substring( 9, 10 ), 10 );
		    secondCN = parseInt( value.substring( 10, 11 ), 10 );

		    checkResult = function( sum, cn ) {
		        var result = ( sum * 10 ) % 11;
		        if ( ( result === 10 ) || ( result === 11 ) ) {
		            result = 0;
		        }
		        return ( result === cn );
		    };

		    // Checking for dump data
		    if ( value === "" ||
		        value === "00000000000" ||
		        value === "11111111111" ||
		        value === "22222222222" ||
		        value === "33333333333" ||
		        value === "44444444444" ||
		        value === "55555555555" ||
		        value === "66666666666" ||
		        value === "77777777777" ||
		        value === "88888888888" ||
		        value === "99999999999"
		    ) {
		        return false;
		    }

		    // Step 1 - using first Check Number:
		    for ( i = 1; i <= 9; i++ ) {
		        sum = sum + parseInt( value.substring( i - 1, i ), 10 ) * ( 11 - i );
		    }

		    // If first Check Number (CN) is valid, move to Step 2 - using second Check Number:
		    if ( checkResult( sum, firstCN ) ) {
		        sum = 0;
		        for ( i = 1; i <= 10; i++ ) {
		            sum = sum + parseInt( value.substring( i - 1, i ), 10 ) * ( 12 - i );
		        }
		        return checkResult( sum, secondCN );
		    }
		    return false;
		},
		validateCnpj: function(cnpj)
		{
		    //extrai os algarismos do CNPJ
		    var digits = [];
		    var length = cnpj.length;

		    for (var i=0; i<length; i++) {
		        if (parseInt(cnpj[i]) == cnpj[i]) {
		            digits.push(cnpj[i]);
		        }
		    }
		    
		    if (digits.length != 14) {
		        return false;
		    }

			//se todos os dígitos forem iguais, é inválido
			let equals = true;
			for (var i=1; i<digits.length; i++) {
				if (digits[i] != digits[i-1]) {
					equals = false;
				}
			}
			if (equals) {
				return false;
			}

		    var factors = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

		    //verifica o primeiro dígito
		    var sum = 0;
		    for (var i=0; i<12; i++) {
		        sum += parseInt(digits[i]) * factors[i];
		    }

		    first_digit = 0;
		    mod = sum % 11;

		    if (mod>= 2) {
		        first_digit = 11 - mod;
		    }
		    
		    if (digits[12] != first_digit) {
		        return false;
		    }

		    factors = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

		    //verifica o segundo dígito
		    sum = 0;
		    for (var i=0; i<13; i++) {
		        sum += parseInt(digits[i]) * factors[i];
		    }

		    second_digit = 0;
		    mod = sum % 11;

		    if (mod>= 2) {
		        second_digit = 11 - mod;
		    }

		    if (digits[13] != second_digit) {
		        return false;
		    }

		    return true;
		}
	};

	function getLangData(array_datas, id_lang)
    {
        if (typeof array_datas !== 'object') {
            return array_datas;
        }

        if (typeof array_datas[id_lang] !== 'undefined') {
            return array_datas[id_lang];
        }

        return array_datas['id_lang'];
    }

	function getTypePerson()
	{
		var type_person_input = $('[name=person_type]'),
    		type_person;

    	if (type_person_input.length != 1) {
			type_person_input = type_person_input.filter(':checked');
			type_person = type_person_input.val();
    	} else {
    		type_person = type_person_input.val();
    	}

    	return type_person;
	}

	function markInputAsInvalid(input, error_msg)
	{
		// Sempre marca a classe 'error' para integrarmos validação e bloqueio de botões
		input.addClass('error');

		if (!is_onepagecheckout) {
			input.closest('.form-group')
                .addClass('has-error').addClass('form-error')
                .removeClass('has-success').removeClass('form-success');
			input.removeClass('valid');
			
			if (typeof error_msg === 'undefined' || error_msg == "") {
				error_msg = input.closest('.form-group').find('label').text().trim() + " inválido.";
			}

			if (error_msg != "") {
				var div_error = $('<div/>', {
					class: 'agcustomers-error',
					text: error_msg
				});

				$(input).closest('.form-group input').after(div_error);
			}
		} else {
			input.addClass('error')
            	.closest('.form-group')
                .addClass('has-error')
                .removeClass('has-success');

			input.removeClass('valid');
		}
	}

	function markInputAsValid(input)
	{
		// Sempre remove classe 'error'
		$(input).removeClass('error');
		if (!is_onepagecheckout)  {
			$(input).closest('.form-group').removeClass('has-error').removeClass('form-error').find('.agcustomers-error').remove();;
		} else {
			$(input).closest('.form-group').removeClass('has-error').find('.agcustomers-error').remove();
			input.removeClass('error');
		}
	}

	function validateInput(input, check_duplicate = false)
    {
		$return = new Promise(function(resolve, reject){
			markInputAsValid(input);
			var value = $(input).val();

			if (value == '' || typeof value === 'undefined') {
				if (input.is('[required]')) {
					markInputAsInvalid(input);
				}

				reject();
				return;
			}

			// Age validation for birthday
			if ($(input).attr('name') === 'birthday') {
				var ageValid = validateBirthdayAge(value);
				if (!ageValid.valid) {
					var t = (window.agcustomers && window.agcustomers.translations) || {};
					var msg = '';
					if (ageValid.reason === 'min' && t.age_min_msg) {
						msg = t.age_min_msg.replace('%min%', ageValid.limit);
					} else if (ageValid.reason === 'max' && t.age_max_msg) {
						msg = t.age_max_msg.replace('%max%', ageValid.limit);
					}
					markInputAsInvalid(input, msg);
					reject();
					return;
				}
			}
			
			if (
				($(input).attr('name') == 'cnpj' && !validate_functions.validateCnpj(value))
				|| ($(input).attr('name') == 'cpf' && !validate_functions.validateCpf(value))
			) {
				markInputAsInvalid(input);
			}

			$.each(agcustomers.fields.customer, function(key, value){
				if (value.name != input.prop('name')) {
					return;
				}
				if (check_duplicate) {
					//campo não é obrigatório
					if (typeof value.unique === 'undefined' || value.unique === "0") {
						check_duplicate = false;
					}
				}
			})

			if (!check_duplicate) {
				resolve();
				return;
			}

			checkInputDuplicity(input)
			.then(resolve)
			.catch(reject);
		});

		return $return;
	}

	function validateBirthdayAge(value)
	{
		try {
			var cfg = (window.agcustomers && agcustomers.config && agcustomers.config.customer) ? agcustomers.config.customer : {};
			var minAge = parseInt(cfg.min_age || 0, 10) || 0;
			var maxAge = parseInt(cfg.max_age || 0, 10) || 0;
			if (minAge === 0 && maxAge === 0) return { valid: true };

			var ymd = parseBirthdayToYMD(value);
			if (!ymd) return { valid: true };

			var y = ymd.y, m = ymd.m, d = ymd.d;
			var bday = new Date(y, m - 1, d);
			if (isNaN(bday.getTime())) return { valid: true };

			var today = new Date();
			var age = today.getFullYear() - y;
			var hasHadBirthdayThisYear = (today.getMonth() + 1 > m) || ((today.getMonth() + 1 === m) && (today.getDate() >= d));
			if (!hasHadBirthdayThisYear) age--;

			if (minAge > 0 && age < minAge) return { valid: false, reason: 'min', limit: minAge };
			if (maxAge > 0 && age > maxAge) return { valid: false, reason: 'max', limit: maxAge };
			return { valid: true };
		} catch (e) {
			return { valid: true };
		}
	}

	function parseBirthdayToYMD(value)
	{
		// 1) Accept ISO YYYY-MM-DD directly
		var m = /^\s*(\d{4})-(\d{2})-(\d{2})\s*$/.exec(value);
		if (m) {
			return { y: parseInt(m[1], 10), m: parseInt(m[2], 10), d: parseInt(m[3], 10) };
		}

		// 2) Parse using configured mask without building a dynamic RegExp
		//    We just extract the numeric parts and map them to Y/m/d by the
		//    token order found in the format string (e.g., 'd/m/Y').
		var fmt = (typeof window.agcustomers_mask_birthday_format !== 'undefined') ? window.agcustomers_mask_birthday_format : 'Y-m-d';
		var nums = value.match(/\d+/g);
		if (!nums || nums.length < 3) return null;
		nums = [parseInt(nums[0], 10), parseInt(nums[1], 10), parseInt(nums[2], 10)];

		var order = [
			{ t: 'Y', i: fmt.indexOf('Y') },
			{ t: 'm', i: fmt.indexOf('m') },
			{ t: 'd', i: fmt.indexOf('d') }
		].filter(function(o){ return o.i >= 0; })
		 .sort(function(a,b){ return a.i - b.i; })
		 .map(function(o){ return o.t; });

		if (order.length !== 3) return null;

		var map = {};
		for (var i = 0; i < 3; i++) {
			map[order[i]] = nums[i];
		}

		var year = map['Y'];
		var month = map['m'];
		var day = map['d'];
		if (!year || !month || !day) return null;

		return { y: year, m: month, d: day };
	}
	
	function checkInputDuplicity(input)
	{
		return new Promise(function(resolve, reject){
			$.ajax({
				url: agcustomers.urls.ajaxRequests,
				type: 'GET',
				dataType: 'JSON',
				data: {
					action: 'checkDuplicity',
					field_name: input.prop('name'),
					value: input.val()
				}
			}).then(function(data){
				if (data.success && !data.duplicated) {
					resolve();
				} else {
					var t = (window.agcustomers && window.agcustomers.translations) || {};
					var alreadyRegistered = t.already_registered || 'already registered.';
					markInputAsInvalid(input, input.closest('.form-group').find('label').text().trim() + ' ' + alreadyRegistered);
					reject(input.closest('.form-group').find('label').text().trim() + ' ' + alreadyRegistered);
				}
			})
			.fail(reject);
		});
	}

	// Helpers simples e nomeados para manter o código direto e legível
	function isPersonTypeSelected()
	{
		var pt = $('[name=person_type]');
		if (!pt.length) return true; // se não existe no formulário, não bloqueia
		if (pt.filter(':radio').length) {
			return pt.filter(':checked').length > 0;
		}
		var val = pt.val();
		return (val !== undefined && val !== null && String(val).trim() !== '');
	}

	function showOrHideGlobalError(has_error)
	{
		var $form = $('#customer-form');
		if (!$form.length) return;

		var $msg = $form.find('.agcustomers-global-error');
		if (!$msg.length) {
			var t = (window.agcustomers && window.agcustomers.translations) || {};
			var fillAllRequired = t.fill_all_required || 'Please make sure you have filled in all required fields.';
			$msg = $('<div class="alert alert-danger agcustomers-global-error">' + fillAllRequired + '</div>', {
			}).hide();
			$msg.insertBefore($form.find('.btn-primary').first());
		}

		has_error ? $msg.show() : $msg.hide();
	}

    function validateInputs(check_duplicates = false)
    {
		let promises = [];

        $.each(agcustomers.fields.customer, function(key, value){
			var input = $('[name=' + value.name + ']');
			if (input.is(':visible')) {
				promises.push(validateInput(input, check_duplicates));
			}
        });

		var overlay = loadingOverlay().activate();
		
		return Promise.all(promises)
		.catch(function(){ /* evita UnhandledPromiseRejection no console */ })
		.finally(function(){
			loadingOverlay().cancel(overlay);
			if ($('input.error').length > 0) {
				var t = (window.agcustomers && window.agcustomers.translations) || {};
				var fixToContinue = t.fix_to_continue || 'Fix your registration to continue';
				$('#btn_save_customer, #btn_place_order').attr('disabled', true).attr('title', fixToContinue);
			} else {
				$('#btn_save_customer, #btn_place_order').removeAttr('disabled').removeAttr('title');
			}
		});
    }

    function addValidations()
    {
        setInterval(function(){
			$.each(agcustomers.fields.customer, function(key, value){
				var input = $('[name=' + value.name + ']');
				if (input.is(':visible') && !input.data('validation_event_added')) {
					var overlay = loadingOverlay().activate();
					validateInput(input, true)
					.catch(function(){})
					.finally(function(){
						loadingOverlay().cancel(overlay);
					})

					input.on('input', '', function(){
						validateInput(input, false).catch(function(){});
					});

					var interval;
					input.on('keyup', '', function(){
						clearInterval(interval);
						interval = setTimeout(function(){
							var overlay2 = loadingOverlay().activate();
						
							validateInput(input, true)
							.catch(function(){})
							.finally(function(){
								loadingOverlay().cancel(overlay2);
							})
						}, 500);
					});

					input.data('validation_event_added', true);
				}
			});

			let has_error = false; // começa explícito e simples
			$.each(agcustomers.fields.customer, function(key, value){
				var input = $('[name=' + value.name + ']');
				if (input.closest('.form-group').is('.has-error')) {
					has_error = true;	
					return false;
				}
			});

			// também exige tipo de pessoa selecionado, quando aplicável
			if (!isPersonTypeSelected()) {
				has_error = true;
			}

			if (has_error) {
				$('#customer-form .btn-primary').prop('disabled', true).addClass('disabled');
			} else {
				$('#customer-form .btn-primary').prop('disabled', false).removeClass('disabled');
			}

			// mensagem global única baseada no estado de erro
			showOrHideGlobalError(has_error);
        }, 100);

	    $('#customer-form').submit(function(){
			var error = false;
			$.each(agcustomers.fields.customer, function(key, value){
				//só valida os campos que estão de fato visíveis na tela do cliente
				if ($('[name=' + value.name + ']').is(':visible')) {
					if ($('[name=' + value.name + ']').closest('.form-group').is('.has-error')) {
						error = true;
						// return false;
					}
				}
			});

			return !error;
	    });
	}


	function addMasks()
	{
		$.each(agcustomers.fields.customer, function(key, value){
			if (typeof value.mask !== 'undefined' && value.mask != '')
			$('[name=' + value.name + ']').not('#ag-checkout-validation-form *').mask(value.mask);
		});

		var birthday_input = $('[name=birthday]')
			.filter(function(){ return this.type !== 'date'; })
			.not('#ag-checkout-validation-form *');
		if (birthday_input.length) {
			birthday_input.mask(agcustomers_mask_birthday_format.replace('Y', '0000').replace('m', '00').replace('d', '00'));
		}

		// Para inputs nativos type=date, define min/max baseados em min_age/max_age
		var cfg = (window.agcustomers && agcustomers.config && agcustomers.config.customer) ? agcustomers.config.customer : {};
		var minAge = parseInt(cfg.min_age || 0, 10) || 0;
		var maxAge = parseInt(cfg.max_age || 0, 10) || 0;
		var $dateBirthday = $('[name=birthday]').filter(function(){ return this.type === 'date'; });
		if ($dateBirthday.length && (minAge > 0 || maxAge > 0)) {
			var today = new Date();
			function fmt(d){ return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0'); }
			if (maxAge > 0) {
				var minDate = new Date(today.getFullYear() - maxAge, today.getMonth(), today.getDate());
				$dateBirthday.attr('min', fmt(minDate));
			}
			if (minAge > 0) {
				var maxDate = new Date(today.getFullYear() - minAge, today.getMonth(), today.getDate());
				$dateBirthday.attr('max', fmt(maxDate));
			}
		}

		maskPostcode();
		maskPhones();
		$(document).on('change', '[name=id_country], #delivery_id_country', function(){
			maskPostcode();
			maskPhones();
			setTimeout(function(){
				$('[name=number]').closest('.form-group').find('label').text(agcustomers_number_translation);
				$('[name=number]').attr('max-length', 10);
			}, 1500);
		});
	}

	function maskPostcode()
	{
		if ($('[name=id_country], #delivery_id_country').length == 0) {
			return;
		}
		
		var overlay = loadingOverlay().activate();

		//busca a máscara de CEP do país atual
		$.ajax({
			url: agcustomers.urls.ajaxRequests,
			type: 'POST',
			dataType: 'json',
			data: {
				id_country: $('[name=id_country], #delivery_id_country').val(),
				action: 'getMasks'
			}
		})
        .done(function(data){
            if (data.success) {
                $('#postcode, #delivery_postcode, [name=postcode]').mask(data.masks.zipcode.replace(/N/g, '0').replace(/L/g, 'S'));
            }
        })
        .complete(function(){
            loadingOverlay().cancel(overlay);
        });
	}

	function maskPhones()
	{
		//máscara de telefone
        //http://www.igorescobar.com/blog/2012/07/29/mascara-javascript-para-os-novos-telefones-de-sao-paulo/
        var maskBehavior = function (val) {
            var input_country = $('[name=id_country], #id_country');
            var country_name = input_country.find('option:selected').text();

            if (country_name != 'Brazil' && country_name != 'Brasil') {
                return '+9999999999999999';
            }

            return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
        }

        options = {onKeyPress: function(val, e, field, options) {
            field.mask(maskBehavior.apply({}, arguments), options);
        }};

        $('#phone_mobile, #delivery_phone_mobile, [name=phone_mobile], #phone, #delivery_phone, [name=phone]').mask(maskBehavior, options);
	}

	function positionFields()
	{
		$.each(agcustomers.fields.customer.reverse(), function(key, value){
			if (typeof value.is_default_input !== 'undefined' && value.is_default_input == "1") {
				return;
			}

			if (value.type == 'checkbox') {
				var input_birthday = $('[name=birthday]').not('#ag-checkout-validation-form *');
				$.each(value.options, function(key2, value2){
					$(`[name='${value.name}${value2.value}'`).closest('.form-group').insertAfter(input_birthday.closest('.form-group'));
				})

				return;
			}
			
			var input_birthday = $('[name=birthday]').not('#ag-checkout-validation-form *');
			var input_password = $('[name=password]');
			if (input_birthday.length) 
				$('[name=' + value.name + ']').closest('.form-group').insertAfter(input_birthday.closest('.form-group'));
			else
				$('[name=' + value.name + ']').closest('.form-group').insertAfter(input_password.closest('.form-group'))
		});

		if (!agcustomers.ps178) {
			$('[name=person_type]').closest('.form-group').insertBefore($('[name=firstname]').closest('section'));
		} else {
			$('[name=person_type]').closest('.form-group').prependTo($('#customer-form > div:first-child'));
		}
	}

    function handleFieldVisibility() {
        const type_person = getTypePerson();
        const fields = agcustomers.fields.customer;
    
        const isFieldVisible = (field) => {
			//campo não adicionado para nenhum tipo de pessoa
			if (typeof field.insert === 'undefined') {
				return false;
			}

			
			if (type_person) {
				//tipo de pessoa selecionado e campo adicionado para o tipo de pessoa atual
				if (typeof field.insert[type_person] !== 'undefined' && field.insert[type_person]) {
					return true;
				} else {
					return false;
				}
			}

			//tipo de pessoa não selecioando, verifica se o campo está habilitado para todos os tipos de pessoa
			let ret = true;
			$('[name=person_type]').each(function(){
				if (!field.insert[$(this).val()]) {
					ret = false;
					return false;
				}
			})

			return ret;
        };
    
        for (let field of fields) {
            const input = $(`[name=${field.name}]`).filter(function() {
                return $(this).closest('.js-address-form').length === 0;
            });
    
            const formGroup = input.closest('.form-group');
            if (isFieldVisible(field)) {
				$(formGroup).show();
			} else {
				$(formGroup).hide();
			}
        }
    }    
    

    function handleRequiredFields()
    {
        var type_person = getTypePerson();

        $.each(agcustomers.fields.customer, function(key, value){
            var input = $('[name=' + value.name + ']');
			input = input.filter(function(){
				return $(this).closest('.js-address-form').length === 0;
			});
			//ignora inputs do form de endereço

            var label = $(input).siblings('label');

            if (typeof value.required !== 'undefined' && typeof value.required[type_person] !== 'undefined' && value.required[type_person] == 1) {
                input.attr('required', true);
                input.closest('.form-group').addClass('required').find('label').addClass('required');

                $(input).closest('.form-group').find('.form-control-comment').hide();
            } else {
                input.removeAttr('required');
                input.closest('.form-group').removeClass('required').find('label').removeClass('required');
                $(input).closest('.form-group').find('.form-control-comment').show();
            }
        });
    }

    function addEvents()
    {
    	$('[name=person_type]').change(function(){
            handleFieldVisibility();
            handleRequiredFields();
			validateInputs();
			return true;
        });
    }

    function addInputsOnepagecheckout()
    {
    	var row = $('<div/>', {
    		class: 'row row_type_person'
    	});
    	row.insertAfter($("[name=id_gender]").closest('.row'));

    	var label = $('<label/>', {
    		text: agcustomers.translations.type_person
    	}).appendTo(row);

    	$.each(agcustomers.type_persons, function(key, type_person){
    		if (type_person.active == 0) {
    			return;
    		}
    		var div = $('<div/>', {}).appendTo(row);
    		var label = $('<label/>', {for: 'person_type_' + type_person.name, text: getLangData(type_person.label, agcustomers.id_lang)}).appendTo(div);
    		var input = $('<input/>', {
    			type: 'radio',
    			name: 'person_type',
    			value: type_person.name,
    			id: 'person_type_' + type_person.name
    		}).prependTo(label);


    		if (agcustomers.customer_data.person_type == type_person.name) {
    			input.attr('checked', true);
    		}
    	})

    	row = $('<div/>', {
    		class: 'row'
    	});
    	row.insertBefore($('#field_customer_newsletter').closest('.row'));

    	$.each(agcustomers.fields.customer, function(key, field) {
    		if (row.find('>div').length == 2) {
    			row = $('<div/>', {
		    		class: 'row'
		    	});
		    	row.insertBefore($('#field_customer_newsletter').closest('.row'));
    		}

    		var div = $('<div/>', {
    			class: 'form-group col-xs-6 col-6 required'
    		});
    		div.appendTo(row);

    		var label = $('<label/>', {
    			text: getLangData(field.label, agcustomers.id_lang) + ': ',
    			for: 'customer.' + field.name
    		});

    		label.appendTo(div);

    		var input = $('<input/>', {
    			type: 'text',
    			name: field.name,
    			id: 'customer.' + field.name,
    			class: 'form-control'
    		});
    		input.appendTo(div);


    		input.val(agcustomers.customer_data[field.name]);
    		
    	});
    }
	
	function handleFieldsDisabled(){
		$.each(agcustomers.fields.customer, function(key, value){
			handleFieldDisabled(value);
		});
	}

	function handleFieldDisabled(value){
		if(!value){
			return
		}
		var input = $('[name=' + value.name + ']');
		if(input.val() == ''){
			return
		}
		if(value.edit_fo == 0 || !value.edit_fo){
			if(!agcustomers.is_auth){
				$(input).prop( "readonly", false );
			}else{
				$(input).prop( "readonly", true );
			}
		}
	}

	function init()
	{
		if (is_onepagecheckout) {
			if ($('[name=person_type]').length == 0) {
				addInputsOnepagecheckout();
			} else {
				$('[name=person_type]').closest('.row').insertAfter($('#form_customer > .row')[0]);
			}
		}


    	//se só houver um tipo de pessoa, já o seleciona
    	if ($('[name=person_type]').length < 2) {
    		$('[name=person_type]').attr('checked', true);
    		$('[name=person_type]').closest('.row').hide();
    	}


		//enquanto as regras de validação não são configuráveis, insere elas manualmente
		$('input[name=cpf]')
			.attr('data-validate-function', 'validateCpf')
			.attr('data-db-table', 'customer');

		//enquanto as regras de validação não são configuráveis, insere elas manualmente
		$('input[name=cnpj]')
			.attr('data-validate-function', 'validateCnpj')
			.attr('data-db-table', 'customer');

		if (agcustomers.type_persons.length == 1) {
            $('[name=person_type]').val(agcustomers.type_persons[0].name);
        }

		$('[name=number]').closest('.form-group').find('label').text(agcustomers_number_translation);
		$('[name=number]').attr('maxlength', 10);

		addValidations();
		addMasks();

		if (agcustomers.config.customer.position == "1") {
			positionFields();
		}

		addEvents();
		handleFieldsDisabled();
		handleFieldVisibility();
		handleRequiredFields();
	}

	init();
});
