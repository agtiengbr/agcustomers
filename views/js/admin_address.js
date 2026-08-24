document.addEventListener('DOMContentLoaded', function(){
	var input_number = document.querySelector('#number');
	var input_address2 = document.querySelector('#address2');
	var is_form_address = $("#customer_address_address1").length;
	

	function repositionFields()
	{
		if (input_number != null) {
			input_address2.closest('.form-group').parentNode.insertBefore(input_number.closest('.form-group'), input_address2.closest('.form-group'));
		}	
	}

	function insertFields()
	{
		if (input_number == null && is_form_address) {
			var address = '';
			var row, label, form_control_container, form_control;
			row = $('<div class="form-group row type-text"/>');

			var t = (window.agcustomers && window.agcustomers.translations) || {};
			label = $('<label/>', {
				class: 'form-control-label',
				text: t.number_label || 'Number'
			});

            form_control_container = $('<div/>', {
                class: 'col-sm'
            });

            form_control = $('<input/>', {
                type: 'text',
                class: 'form-control',
                name: 'number_address_BO',
                value: agcustomers['address']['number'],
                inputmode: 'numeric',
                pattern: '[0-9]*'
            });

            row.append(label).append(form_control_container);
            form_control_container.append(form_control);

			if($("#customer_address_address1").length > 0) {
				address = "#customer_address_address1";
			} else {
				address = '[name=address1]';
			}

            $(row).insertAfter($(address).closest('.form-group'));
		}	
	}

	insertFields();
	repositionFields();
	$('[name=number_address_BO]').attr({
		type: 'text',
		inputmode: 'numeric',
		pattern: '[0-9]*'
	});
	
});