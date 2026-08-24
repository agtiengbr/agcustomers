function validateCpf(cpf)
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
}

function validateCnpj(cnpj)
{
    //extrai os algarismos do CNPJ
    var digits = [];
    var length = cnpj.length;

    for (var i=0; i<length; i++) {
        if (parseInt(cnpj[i]) == cnpj[i]) {
            digits.push(cnpj[i]);
        }
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


    if (digits.length != 14) {
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

$(function(){
    var cpf;
    var cnpj;
    var is_onepagecheckout;
    var input_number;

    //compatibilidade com PS 1.6 para inserir os campos de CPF/CNPJ e etc.
    var interval = setInterval(function(){
        var input_firstname;
        var input_lastname;
        var input_email;
        var input_birthday;

        is_onepagecheckout = $('#onepagecheckoutps').length === 1;
        var is_ps_opc = $('#order-opc').length === 1;

        var is_authentication = $('#authentication').length === 1;
        var is_identity = $('#identity').length === 1;
        var is_address_page = $('#address').length === 1 || $('#checkout-addresses-step form #delivery-address').length === 1;

        input_number  = $('[name=number]');
        input_number.attr({
            type: 'text',
            inputmode: 'numeric',
            pattern: '[0-9]*'
        });

        //não insere campos de CPF, CNPJ, etc na página de edição do endereço
        if (is_address_page) {
            agcustomers_insert_customer_fields = false;
            agcustomers_insert_company_fields = false;
        }

        input_birthday = $('#customer_birthday, #birthday, [name=birthday]');
        if (is_onepagecheckout || (is_authentication && typeof prestashop === 'undefined') || is_ps_opc) {
            input_firstname = $('#customer_firstname');
            input_lastname= $('#customer_lastname');
            input_email = $('#customer_email, #email');
        } else {
            input_firstname = $('[name=firstname]');
            input_lastname= $('[name=lastname]');
            input_email = $('[name=email]');

            if (input_email.length > 1) {
                input_email = $('#customer-form [name=email]');
            }
        }

        maskInputs();

        if (agcustomers_mask_birthday_input) {
            $(input_birthday).mask(agcustomers_mask_birthday_format.replace('Y', '0000').replace('m', '00').replace('d', '00'));
        }

        // Para inputs type=date (quando existirem), defina min/max conforme min_age/max_age
        try {
            var cfg = (window.agcustomers && agcustomers.config && agcustomers.config.customer) ? agcustomers.config.customer : {};
            var minAge = parseInt(cfg.min_age || 0, 10) || 0;
            var maxAge = parseInt(cfg.max_age || 0, 10) || 0;
            var $dateBirthday = $('[name=birthday]').filter(function(){ return this.type === 'date'; });
            if ($dateBirthday.length && (minAge > 0 || maxAge > 0)) {
                var today = new Date();
                var fmt = function(d){ return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0'); };
                if (maxAge > 0) {
                    var minDate = new Date(today.getFullYear() - maxAge, today.getMonth(), today.getDate());
                    $dateBirthday.attr('min', fmt(minDate));
                }
                if (minAge > 0) {
                    var maxDate = new Date(today.getFullYear() - minAge, today.getMonth(), today.getDate());
                    $dateBirthday.attr('max', fmt(maxDate));
                }
            }
        } catch(e) {}

        var valid_type_persons = [];
        $.each(agcustomers.type_persons, function (key, value){
            if (value.active == "1") {
                valid_type_persons.push(value);
            }
        });

        //insere as opções para tipos de pessoa
        if (valid_type_persons.length > 1) {
            var container = $("<div class='required clearfix agcustomers_person_type'></div>");
            var label     = "<label>" + agcustomers.translations.type_person + "</label>";

            $(label).appendTo(container);
            $.each(agcustomers.type_persons, function(key, value){
                if (value.active == 0) {
                    return;
                }
                
                var radio_container = $("<div class='radio-inline'/>");
                var radio_label = $("<label for='person_type_" + value.name + "' class='top'>" + getLangData(value.label, agcustomers.id_lang) + "</label>");
                var div = "<div class='radio'><span><input type='radio' name='person_type' id='person_type_" + value.name + "' value='" + value.name + "'></span></div>";

                $(radio_label).prepend($(div));
                $(radio_container).append(radio_label).appendTo(container);
            });

            if (!$('#authentication , #address').length) {
                var input =
                $('input.is_required')[0];
                $(container).insertBefore($(input).closest('.form-group'));
            } else {
                var input = $('#customer_firstname');
                $(container).insertBefore($(input).closest('.form-group'));
            }

            $(container).find('input').change(function(){
                handleFieldVisibility();
                handleRequiredFields();
                return true;
            });
        } else {
            $('<input/>', {
                type: 'hidden',
                name: 'person_type',
                value: valid_type_persons[0].name,
                checked: true
            }).insertAfter($('p.required')[0]);
        }

        $.each(agcustomers.fields.customer, function(key, value){
            var input_days = $('[name=days]');
            if (input_days.closest('form').find('[name=' + value.name + ']').length) {
                return;
            }
            
            var input =   $('<input/>', {
                name     : value.name,
                id       : value.name,
                // value    : agcustomerscpf,
                class    : 'form-control'
            });

            var label = $('<label> ' +  getLangData(value.label, agcustomers.id_lang) + '</label>');

            var form_group = $('<div/>', {
                class: 'form-group hidden'
            }).append(label)
            .append(input);

            form_group.insertBefore(input_days.closest('.form-group'));
        });

        cpf = $('[name=cpf]');
        cnpj = $('[name=cnpj]');
        ie = $('[name=ie]');
        company = $('[name=company]');
        rg = $('[name=rg]');

        //CADASTRO SOCIAL
        if (input_firstname && !is_identity) {
            var firstname = getParameterByName('firstname', location.href);
            if (firstname != null) {
                input_firstname.val(firstname);
            }
        }

        if (input_lastname && !is_identity) {
            var lastname = getParameterByName('lastname', location.href);
            if (lastname != null) {
                input_lastname.val(lastname);
            }
        }

        if (input_email && !is_identity) {
            var email = getParameterByName('email', location.href);
            if (email != null) {
                input_email.val(email);
            }
        }

        //máscara
        cpf.mask('000.000.000-00', {reverse: true});
        cnpj.mask('00.000.000/0000-00', {reverse: true});

        //onepagecheckout
        cpf.removeAttr('data-validation');
        cnpj.removeAttr('data-validation');

        if (input_firstname.length > 0) {
            if (agcustomers_require_number_field) {
                input_number.closest('.form-group').find('.form-control-comment').remove();
                input_number.attr('required', true);
            }

            if (input_number.length != 0) {
                //traduz o campo de número
                input_number.closest('.form-group').find('label').text(agcustomers_number_translation);
            } else {
                //adiciona o campo de número no PS 1.6
                if (document.getElementById('address1') != null && agcustomers_insert_number_field) {
                    var number = $('<input/>', {
                        type     : 'text',
                        name     : 'number',
                        id       : 'number',
                        required : agcustomers_require_number_field,
                        class    : 'form-control',
                        inputmode: 'numeric',
                        pattern  : '[0-9]*'
                    });

                    if (typeof agcustomers_address_number !== 'undefined') {
                        $(number).val(agcustomers_address_number);
                    }

                    var number_label = $('<label/>', {
                        text     : agcustomers_require_number_field ? (agcustomers_number_translation + ' *') : agcustomers_number_translation,
                        required : true          
                    });

                    var number_label = $('<div/>', {
                        class: 'form-group'
                    }).append(number_label)
                    .append(number)
                    .insertAfter($('#address1').closest('.form-group'));
                }
            }
            
            addValidations();
            fillDataToForm();
            handleFieldVisibility();
            handleRequiredFields();

            clearInterval(interval);
        }
    }, 500);

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

        if (type_person_input.length > 1) {
            type_person_input = type_person_input.filter(':checked');
            type_person = type_person_input.val();
        } else {
            type_person = agcustomers.type_persons[0].name;
        }

        return type_person;
    }

    function validateInputs()
    {
        $.each(agcustomers.fields.customer, function(key, value){
            validateInput($('[name=' + value.name + ']'));
        });
    }
    
    function validateInput(input)
    {
        $(input).closest('.form-group').removeClass('has-error').removeClass('form-error');
        var value = $(input).val();

        if (value == '' || typeof value === 'undefined') {
            if (input.is('[required]')) {
                input.closest('.form-group')
                    .addClass('has-error').addClass('form-error')
                    .removeClass('has-success').removeClass('form-success');

                input.removeClass('valid');
            }

            return;
        }

        // Age validation for birthday based on configured min/max ages
        if ($(input).attr('name') === 'birthday') {
            if (!validateBirthdayAge(value)) {
                $(input).closest('.form-group')
                    .addClass('has-error').addClass('form-error')
                    .removeClass('has-success').removeClass('form-success');
                input.removeClass('valid');
                return;
            }
        }
        
        if (
            ($(input).attr('name') == 'cnpj' && !validateCnpj(value))
            || ($(input).attr('name') == 'cpf' && !validateCpf(value))
        ) {
            input.closest('.form-group')
                .addClass('has-error').addClass('form-error')
                .removeClass('has-success').removeClass('form-success');

            input.removeClass('valid').addClass('error');
        }
    }

    function validateBirthdayAge(value)
    {
        try {
            var cfg = (window.agcustomers && agcustomers.config && agcustomers.config.customer) ? agcustomers.config.customer : {};
            var minAge = parseInt(cfg.min_age || 0, 10) || 0;
            var maxAge = parseInt(cfg.max_age || 0, 10) || 0;
            if (minAge === 0 && maxAge === 0) return true;

            var ymd = parseBirthdayToYMD(value);
            if (!ymd) return true; // ignore if cannot parse; server will block

            var y = ymd.y, m = ymd.m, d = ymd.d;
            var bday = new Date(y, m - 1, d);
            if (isNaN(bday.getTime())) return true;

            var today = new Date();
            var age = today.getFullYear() - y;
            var hasHadBirthdayThisYear = (today.getMonth() + 1 > m) || ((today.getMonth() + 1 === m) && (today.getDate() >= d));
            if (!hasHadBirthdayThisYear) age--;

            if (minAge > 0 && age < minAge) return false;
            if (maxAge > 0 && age > maxAge) return false;
            return true;
        } catch (e) {
            return true;
        }
    }

    function parseBirthdayToYMD(value)
    {
        // Accept ISO first
        var m = /^\s*(\d{4})-(\d{2})-(\d{2})\s*$/.exec(value);
        if (m) {
            return { y: parseInt(m[1], 10), m: parseInt(m[2], 10), d: parseInt(m[3], 10) };
        }

        // Parse using mask/format e.g., d/m/Y or m/d/Y etc.
        var fmt = (typeof window.agcustomers_mask_birthday_format !== 'undefined') ? window.agcustomers_mask_birthday_format : 'Y-m-d';
        // Build regex by escaping and replacing tokens
        var esc = function(s){ return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); };
        var pattern = esc(fmt);
        var order = [];
        // capture order in appearance
        for (var i = 0; i < fmt.length; i++) {
            var ch = fmt.charAt(i);
            if ((ch === 'Y' || ch === 'm' || ch === 'd') && order.indexOf(ch) === -1) {
                order.push(ch);
            }
        }
        pattern = pattern.replace(/Y/g, '(\\d{4})').replace(/m/g, '(\\d{1,2})').replace(/d/g, '(\\d{1,2})');
        var rx = new RegExp('^' + pattern + '$');
        var mm = rx.exec(value.trim());
        if (!mm) return null;
        // Find positions of tokens to map groups
        function idxOfToken(tok){ return fmt.indexOf(tok) !== -1 ? (value.match(new RegExp('^.*')) && 0) : -1; }
        // Simpler mapping: compute indices by scanning fmt and recording group order
        var groups = [];
        for (var j = 0, gi = 1; j < fmt.length; j++) {
            var c = fmt.charAt(j);
            if (c === 'Y' || c === 'm' || c === 'd') {
                groups.push(c);
                // skip consecutive same chars; but our pattern treats all Ys as one group; this is fine
                // do nothing special here
            }
        }
        // groups may include repeated tokens; reduce to first Y, first m, first d in order
        var seen = {};
        var grpOrder = [];
        for (var k = 0; k < groups.length; k++) {
            var t = groups[k];
            if (!seen[t]) { grpOrder.push(t); seen[t] = true; }
            if (Object.keys(seen).length === 3) break;
        }
        // Map mm[1], mm[2], mm[3] to Y/m/d by grpOrder
        var vals = {};
        for (var g = 0; g < grpOrder.length; g++) {
            vals[grpOrder[g]] = parseInt(mm[g+1], 10);
        }
        if (!vals['Y'] || !vals['m'] || !vals['d']) return null;
        return { y: vals['Y'], m: vals['m'], d: vals['d'] };
    }

    function addValidations()
    {
        $.each(agcustomers.fields, function(key, value){
            $('[name=' + value.name + ']').on('change', function(){
                validateInput($(this));
            });
        });

        // if (is_onepagecheckout) {
        setInterval(function(){
            validateInputs();
        }, 100);
        // }


        if (agcustomers_required_phone) {
            var inputs = $('[name=phone_mobile]');

            //se o número de celular não estiver na tela, obriga o preenchimento do telefone fixo
            if (inputs.length == 0) {
                inputs = $('[name=phone]');
            }

            inputs.attr('required', 'required')
                .closest('.form-group')
                .find('.col-md-3.form-control-comment')
                .remove();
        }

        if (agcustomers_required_birthday) {
            var input_birthday = $('#customer_birthday, #birthday, [name=birthday]');
            input_birthday.attr('required', 'required')
                .closest('.form-group')
                .find('.col-md-3.form-control-comment')
                .remove();
        }
    }

    function enableOrDisableSubmitButton()
    {
        var errors = $('.has-error');
        var buttons = $('#submitAccount, #submitGuestAccount, [name=submitIdentity]');

        if (errors.length == 0) {
            buttons.removeAttr('disabled');
        } else {
            buttons.attr('disabled', true);
        }
    }

    function maskInputs() {
        var input_country = $('[name=id_country], #id_country');
        var old_country = maskInputs.old_country;
        var current_country = input_country.val();
        var country_changed = old_country != current_country;

        maskInputs.old_country = current_country;

        if (!country_changed || !current_country) {
            return;
        }
        
        var overlay = loadingOverlay().activate();

        //busca a máscara de CEP do país atual
        $.ajax({
            url: agcustomers.urls.ajaxRequests,
            dataType : 'JSON',
            data: {
                id_country: input_country.val(),
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

    var btn_onepagecheckout = document.querySelector('#onepagecheckoutps #btn_save_customer');
    if (btn_onepagecheckout != null) {
        btn_onepagecheckout.addEventListener('click', function(e) {
            if (document.querySelector('#onepagecheckoutps #customer_container .has-error') != null) {
                e.stopPropagation();
                e.preventDefault();
            }
        });
    }

    function handleFieldVisibility()
    {
        var type_person = getTypePerson();

        $.each(agcustomers.fields.customer, function(key, value){
            if (typeof value.insert !== 'undefined' && typeof value.insert[type_person] !== 'undefined' && value.insert[type_person] == 1) {
                $('[name=' + value.name + ']').closest('.form-group').removeClass('hidden');
            } else {
                $('[name=' + value.name + ']').closest('.form-group').addClass('hidden');
            }
        });
    }

    function handleRequiredFields()
    {
        var type_person = getTypePerson();

        $.each(agcustomers.fields.customer, function(key, value){
            var input = $('[name=' + value.name + ']');
            var label = $(input).siblings('label');

            if (typeof value.required !== 'undefined' && typeof value.required[type_person] !== 'undefined' && value.required[type_person] == 1) {
                input.attr('required', true);
                input.closest('.form-group').addClass('required');
                label.text(' ' + getLangData(value.label, agcustomers.id_lang) + ' *');
            } else {
                input.removeAttr('required');
                input.closest('.form-group').removeClass('required');
                label.text(' ' + getLangData(value.label, agcustomers.id_lang));
            }
        });
    }

    function fillDataToForm()
    {
        //tipo de pessoa
        $('#person_type_' + agcustomers.customer_data.person_type).attr('checked', true).closest('span').addClass('checked');

        //demais campos
        $.each(agcustomers.fields.customer, function(key, value){
            $('[name=' + value.name + ']').val(agcustomers.customer_data[value.name]);
        });
    }

    setInterval(function(){
        var btn_onepagecheckout_order = document.querySelector('#onepagecheckoutps #btn_place_order');
        if (btn_onepagecheckout_order != null) {
            btn_onepagecheckout_order.addEventListener('click', function(e) {
                if (document.querySelector('#onepagecheckoutps #customer_container .has-error') != null) {
                    e.stopPropagation();
                    e.preventDefault();
                }
            });
        }
    }, 300);

    setInterval(enableOrDisableSubmitButton, 300);
    setInterval(maskInputs, 500);
    
    //ps17, página de cadastro, checkout em 1 etapa (ps16)
    $(document).on('submit', '#customer-form, #account-creation_form, #new_account_form', function(){
        $('.has-error').removeClass('has-error');
        validateInputs();

        var error = false;
        $.each(agcustomers.fields.customer, function(key, value){
            if ($('[name=' + value.name + ']').closest('.form-group').is('.has-error')) {
                error = true;
                return false;
            }
        });
        return !error;
    });    

    //remove os campos de VAT e company se os campos próprios do módulo forem inseridos
    if (agcustomers_insert_company_fields) {
        $('[name=company]').closest('.form-group').remove();
        $('[name=vat_number]').closest('.form-group').remove();
    }
});



function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}