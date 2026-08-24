$(function(){
    var pattern_only_number = /[^0-9]+/g;

    var form = $('.address-form form, #checkout-addresses-step form');
    var optional_divs = $(form.find('.form-control-comment'));
    var optional_div;

    //traduz o campo de número
    $('[name=number]').closest('.form-group').find('label').text(agcustomers_number_translation);
    $('[name=number]').attr({
        type: 'text',
        inputmode: 'numeric',
        pattern: '[0-9]*'
    });

    var numberInvalidMessage = typeof agcustomers_number_invalid_message !== 'undefined'
        ? agcustomers_number_invalid_message
        : 'Enter only numbers or leave this field blank if the address has no number.';

    $('[name=number]').on('input', function(){
        this.setCustomValidity('');
    }).on('invalid', function(){
        if (this.validity.patternMismatch) {
            this.setCustomValidity(numberInvalidMessage);
        }
    });


    //busca pelo div de "obrigatório" que de fato possua o texto "Campo Obrigatório"
    optional_div = $(
        optional_divs.filter(function(){
            return $(this).text().trim() != '';
        })[0]
    );

    function markRequiredFields()
    {
        $.each(agcustomers.fields.address, function(key, value){
            if (value.required == "1") {
                form
                    .find('[name=' + value.name + ']')
                    .prop('required', true)
                    .closest('.form-group')
                    .find('.form-control-comment')
                    .remove();
            } else {
                var input = form.find('[name=' + value.name + ']');

                input.prop('required', false)
                    .closest('.form-group')
                    .find('.form-control-comment')
                    .remove();

                input.closest('.form-group').append(optional_div.clone());
            }
        });
    }

    form.on('change', 'input,select,textarea', function(key, value) {
        markRequiredFields();
    });
    
    markRequiredFields();
    validateInputs();

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

    
    $("[name='phone']").keyup(delay(function(e) {
        var parents = $(this).parents();
        
        var field_name = $(this).attr("name");
        var phone_number = $(this).val();

        validatePhoneNumber(field_name, parents, phone_number);
    }, 50));

    $("[name='phone_mobile']").keyup(delay(function(e) {
        var parents = $(this).parents();

        var field_name = $(this).attr("name");
        var smartphone_number = $(this).val();

        validatePhoneNumber(field_name, parents, smartphone_number);
    }, 50));

    function validatePhoneNumber(name, parents, number) {
        var component = parents.get(1);

        $(`#invalid_${name}`).remove();
        $(component).removeClass('has-error form-error');
        
        if (number && number.length > 0) {
            number = number.replace(pattern_only_number, '');
            
            if((name.includes('mobile') && number.length != 11) || (number.length < 10 || number.length > 11)) {
                $(component).toggleClass('has-error form-error');

                error_msg = $(component).find('label').text().trim() + " inválido.";
                $(`[name="${name}"]`).after(`<label id="invalid_${name}" class="agcustomers-error" style="width: 100%; text-align: start;">${error_msg}</label>`);
            }
        }
    }

    $(document).on('submit', '.js-address-form form', function(){
        if($("[name='phone']") && $("[name='phone_mobile']")) {
            validatePhoneNumber($("[name='phone']").attr("name"), $("[name='phone']").parents(), $("[name='phone']").val());
            validatePhoneNumber($("[name='phone_mobile']").attr("name"), $("[name='phone_mobile']").parents(), $("[name='phone_mobile']").val());
        }

        return true;
        // $('form').find('div').each(function(){
        //     if ($(this).closest('.form-group').is('.has-error')) {
        //         $('.js-address-form .btn-primary').removeClass('disabled');
        //         error = true;
        //         //por algum motivo retornar essa função no PS 1.7.8 faz com que o formulário de endereços não possa mais ser submetido
        //         if (!agcustomers.ps178) {
        //             return;
        //         }
        //     }
        // });

        // return agcustomers.ps178 || !error;
    }); 

    function delay(callback, ms) {
        var timer = 0;
        return function() {
          var context = this, args = arguments;
          clearTimeout(timer);
          timer = setTimeout(function () {
            callback.apply(context, args);
          }, ms || 0);
        };
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
});