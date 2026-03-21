$(function(){
    var agcustomers;
    var options;
    var container_customer = $(' #customer_form');
    var panel = container_customer.find('#fieldset_0 .form-wrapper');

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
            type_person = options.type_person[0].name;
        }

        return type_person;
    }

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

    function loadOptions(email, success)
    {
        $.getJSON(agcustomers_url_load_options + '&email=' + email, function(data){
            options = data.options;
            success(data);
        });
    }

    function addInputs()
    {
        //tipo de pessoa
        var row, label, form_control_container, form_control;

        if (options.type_person.length > 1) {
            row = $('<div class="form-group"/>');

            var t = (window.agcustomers && window.agcustomers.translations) || {};
            label = $('<label/>', {
                class: 'control-label col-lg-3',
                text: t.type_person || 'Person type'
            });

            form_control_container = $('<div/>', {
                class: 'col-lg-9'
            });

            $.each(options.type_person, function(key, value){
                if (value.active == 0) {
                    return;
                }
                var radio = $('<div class="radio t"/>');
                var label = $('<label>' + getLangData(value.label, id_language) + '</label>');
                var input_radio = $('<input type="radio" name="person_type" id="person_type_' + value.name + '" value="' + value.name + '">');

                label.prepend(input_radio);
                radio.append(label);

                $(form_control_container).append(radio);
            });
            row.append(label).append(form_control_container);
            row.insertBefore($('[name=days]').parent().parent().parent().parent());
        }

        $.each(options.fields.customer, function(key, value){
            var row, label, form_control_container, form_control;
            row = $('<div class="form-group hidden"/>');

            label = $('<label/>', {
                class: 'control-label col-lg-3',
                text: getLangData(value.label, id_language)
            });

            form_control_container = $('<div/>', {
                class: 'col-lg-9'
            });

            form_control = $('<input/>', {
                type: 'text',
                class: 'form-control',
                name: value.name,
            });

            row.append(label).append(form_control_container);
            form_control_container.append(form_control);

            $(row).insertBefore($('[name=days]').parent().parent().parent().parent());
        });
    }

    function fillValues(customer_data)
    {
        $('[name=person_type][value=' + customer_data.person_type + ']').attr('checked', true);

        $.each(options.fields.customer, function(key, value){
            $('[name=' + value.name + ']').val(customer_data[value.name]);
        });
    }

    function addEvents()
    {
        $('[name=person_type]').click(function(){
            handleFieldVisibility();
            handleRequiredFields();
        });

        $.each(options.fields.customer, function(key, value){
            $('[name=' + value.name + ']').on('change', function(){
                validateInput($(this));
            });
        });

        $('#customer_form').submit(function(){
            $('.has-error').removeClass('has-error');
            validateInputs();

            var errors = $('.has-error');
            return errors.length == 0;
        });
    }

    function handleFieldVisibility()
    {
        var type_person = getTypePerson();

        $.each(options.fields.customer, function(key, value){
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

        $.each(options.fields.customer, function(key, value){
            var input = $('[name=' + value.name + ']');
            var label = $(input).siblings('label');

            if (typeof value.required !== 'undefined' && typeof value.required[type_person] !== 'undefined' && value.required[type_person] == 1) {
                input.attr('required', true);
                input.closest('.form-group').addClass('required');
                label.text(' ' + getLangData(value.label, id_language) + ' *');
            } else {
                input.removeAttr('required');
                input.closest('.form-group').removeClass('required');
                label.text(' ' + getLangData(value.label, id_language));
            }
        });
    }

    function validateInputs()
    {
        $.each(options.fields.customer, function(key, value){
            validateInput($('[name=' + value.name + ']'));
        });
    }

    function validateInput(input)
    {
        $(input).closest('.form-group').removeClass('has-error').removeClass('form-error');
        var value = $(input).val();

        if (value == '' || typeof value === 'undefined') {
            if (input.is(':required')) {
                input.closest('.form-group')
                    .addClass('has-error').addClass('form-error')
                    .removeClass('has-success').removeClass('form-success');

                input.removeClass('valid');
            }

            return;
        }
        
        if (
            ($(input).attr('name') == 'cnpj' && !validateCnpj(value))
            || ($(input).attr('name') == 'cpf' && !validateCpf(value))
        ) {
            input.closest('.form-group')
                .addClass('has-error').addClass('form-error')
                .removeClass('has-success').removeClass('form-success');

            input.removeClass('valid');
        }
    }

    loadOptions($('#email').val(), function(data){
        addInputs();

        $.each(data.options.fields.customer, function(key, value){
			if (typeof value.mask !== 'undefined' && value.mask != '')
			$('[name=' + value.name + ']').mask(value.mask);
        });
        
        fillValues(data.customer_data);
        handleRequiredFields();
        
        handleFieldVisibility();
        addEvents();
    });
});