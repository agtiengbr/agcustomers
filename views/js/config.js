$(function(){
	$('#tabCustomersTypePerson .new_type_person').click(function(){
		var new_type_person = $('#tabCustomersTypePerson .type-person:first-child').clone();

		//substitui os identificadores dos inputs clonados
		var tmp_html = new_type_person.html();
		tmp_html = tmp_html.replace(/\[0\]/g, "[" + $('#tabCustomersTypePerson .type-person').length + "]");
		new_type_person.html(tmp_html);

		$(new_type_person).find('input').val('');
		new_type_person.insertBefore($(this).closest('.text-center'));


		var input_hidden = $(new_type_person).find(' input');
		var value = $('#tabCustomersTypePerson [hidden=hidden]').length;
		input_hidden[0].value = value;

		var btn_danger = $(new_type_person).find('.btn-danger').removeAttr('disabled');
		return false;
	});

	$(document).on('click', '.btn-danger', function(){
		var t = (window.agcustomers && window.agcustomers.translations) || {};
		var msg = t.confirm_delete_type_person || 'This operation is irreversible. Do you really want to delete this person type?';
		if (!confirm(msg)) {
			return false;
		}

		$(this).closest('.type-person').remove();
		return false;
	})

	//botão de inserir campo no cadastro do cliente
	$('#tabCustomers .nav li .icon-plus').closest('a').click(function(){
		if ($(this).is('.disabled')) {
			return false;
		}

		var tabs_links = $('#tabCustomers ul.nav li');
		$(tabs_links).filter(function(){
			if ($(this).is('.active')) {
				return true;
			}
		}).removeClass('active');

		//clona o link
	var t = (window.agcustomers && window.agcustomers.translations) || {};
	var new_tab = $(tabs_links[2]).clone();
	new_tab.find('a').attr('href', '#tabCustomers' + tabs_links.length).text(t.new_field || 'New field').closest('li').addClass('active');
		new_tab.insertBefore($('#tabCustomers ul.nav li:last-child'));
		new_tab.find('i').removeClass('disabled').removeAttr('disabled').removeAttr('title');		

		//clona o painel
		var panel = $($(tabs_links[2]).find('a').attr('href'));
		panel.siblings('.active').removeClass('active');
		
		var new_tab_panel = panel.clone();
		new_tab_panel.attr('id', 'tabCustomers' + tabs_links.length);
	new_tab_panel.find('.panel-heading').text(t.new_field || 'New field');
		new_tab_panel.addClass('active');

		var tmp_html = new_tab_panel.html();
		tmp_html = tmp_html.replace(/\[0\]/g, "[" + $('#tabCustomers .customer_input').length + "]");
		new_tab_panel.html(tmp_html);
		new_tab_panel.insertBefore(panel);

		new_tab_panel.find('.mask').removeAttr('hidden');
		new_tab_panel.find('input[type=text]')
			.val('')
			.removeAttr('disabled');
		new_tab_panel.find('.mask').attr('hidden', 'hidden');

		new_tab_panel.find('.is_default_input').val(0);

		new_tab_panel.find('.form-group.name').show();
		new_tab_panel.find('.form-group.mask').show();

		new_tab_panel.attr('data-idx', $('#tabCustomers .customer_input').length - 1)
		return false;
	});

	function renderRemoveFieldIcon()
	{
		var i = $('<i/>', {class: 'icon icon-times'});
		return i;
	}

	//impede a edição do nome dos campos padrão do cadastro do cliente
	$('.customer_input .name').each(function(i){
		if (i < 6) {
			$(this).hide();
		}
	});

	//impede a edição da máscara dos campos padrão do cadastro do cliente
	$('.customer_input .mask').each(function(i){
		if (i < 6) {
			$(this).hide();
		}
	});

	//insere o campo para remover campos do cadastro do cliente
	$('#tabCustomers ul.nav li:gt(1):not(:last-child)').each(function(i){
	var t = (window.agcustomers && window.agcustomers.translations) || {};
	var icon = renderRemoveFieldIcon();
		if (i < 6) {
			icon
				.addClass('disabled')
				.attr('disabled', true)
		.attr('title', t.cannot_remove_default_field || 'You cannot remove one of the module default fields.');
		}

		$(this).append(icon);		
	});

	$('#tabCustomers').on('click', 'li i.icon-times', function(){
		if ($(this).is('.disabled')) {
			return;
		}

		var t = (window.agcustomers && window.agcustomers.translations) || {};
		var msg = t.confirm_delete_field || 'Do you really want to delete this customer field? This operation is irreversible and will be processed after saving the form.';
		if (confirm(msg)) {
			var link = $(this).siblings('a');
			$(link.attr('href')).remove();
			$(this).closest('li').remove();
		}

		return false;
	});

	$('#tabCustomers').on('click', '.select-options .icon-trash', function(){
		$(this).closest('tr').remove();
	});

	$('#tabCustomers').on('click', '.select-options .confirm-option', function(){
		let inputs = $(this).closest('tr').find('input'); 
		let value = inputs[2].value;
		let text = inputs[3].value;

		inputs[0].value = value;
		inputs[1].value = text;

		let tds = $(this).closest('tr').find('td');
		$(tds[0]).text(value);
		$(tds[1]).text(text);
		$(this).remove();
	});

	$('#tabCustomers').on('input', '.select-options .value-input', function(){
		const valueInput = $(this).val();
		const textInput = $(this).closest('tr').find('.text-input').val();

		if (valueInput && textInput) {
			$('.confirm-option').removeClass('disabled');
		} else {
			$('.confirm-option').addClass('disabled');
		}
	});

	$('#tabCustomers').on('input', '.select-options .text-input', function(){
		const textInput = $(this).val();
		const valueInput = $(this).closest('tr').find('.value-input').val();

		if (valueInput && textInput) {
			$('.confirm-option').removeClass('disabled');
		} else {
			$('.confirm-option').addClass('disabled');
		}
	});

	$('#tabCustomers').on('click', '.select-options .add-option', function(){
		let i = $(this).closest('.tab-pane').attr('data-idx');
		let j = $(this).closest('tbody').find('tr').length - 1;

		let row = `
			<tr>
				<input type="hidden" name="AGCUSTOMERS_CONFIG[fields][customer][${i}][options][${j}][value]" value="{$option.value}"/>
				<input type="hidden" name="AGCUSTOMERS_CONFIG[fields][customer][${i}][options][${j}][text]" value="{$option.text}"/>

				<td><input class="form-control value-input"/></td>
				<td><input class="form-control text-input"/></td>
				<td class="select-options-icons"><i class="icon-check confirm-option disabled"></i><i class="icon-trash"></i></td>
			</tr>
		`;

		$(row).insertBefore($(this).closest('tr'));
	});

	$('.reset_fields').click(function(){
		var t = (window.agcustomers && window.agcustomers.translations) || {};
		var msg = t.reset_fields_confirm || 'Do you really want to reset the fields to the module initial configuration? This operation is irreversible.';
		if (!confirm(msg)) {
			return false;
		}

		var overlay = loadingOverlay().activate();
		$.getJSON(location.href + '&resetFields', function(data){
			if (data.success) {
				window.location.reload();
			} else {
				loadingOverlay().cancel(overlay);
				var t = (window.agcustomers && window.agcustomers.translations) || {};
				$.growl.error({title: '', message: (t.unexpected_error || 'An unexpected error occurred. Try again.')});
			}
		});

		return false;
	});

	$('.reset_overrides').click(function(){
		var t = (window.agcustomers && window.agcustomers.translations) || {};
		var msg = t.reset_overrides_confirm || 'Do you really want to reset the module overrides? This operation is irreversible.';
		if (!confirm(msg)) {
			return false;
		}

		var overlay = loadingOverlay().activate();
		$.getJSON(location.href + '&resetOverrides', function(data){
			if (data.success) {
				window.location.reload();
			} else {
				loadingOverlay().cancel(overlay);
				var t = (window.agcustomers && window.agcustomers.translations) || {};
				$.growl.error({title: '', message: (t.unexpected_error || 'An unexpected error occurred. Try again.')});
			}
		});

		return false;
	});

	$('.apply_brazil_defaults').click(function(){
		var t = (window.agcustomers && window.agcustomers.translations) || {};
		var msg = t.brazil_defaults_confirm || 'Apply Brazil default settings? This will preconfigure common Brazilian fields.';
		if (!confirm(msg)) {
			return false;
		}

		var overlay = loadingOverlay().activate();
		$.getJSON(location.href + '&applyBrazilDefaults', function(data){
			if (data.success) {
				window.location.reload();
			} else {
				loadingOverlay().cancel(overlay);
				var t = (window.agcustomers && window.agcustomers.translations) || {};
				$.growl.error({title: '', message: (t.unexpected_error || 'An unexpected error occurred. Try again.')});
			}
		});

		return false;
	});


	$('#tabCustomers').on('change', '.input-type', function(){
		let value = $(this).find(':selected').val();
		if (value == 'select' || value == 'checkbox') {
			$(this).closest('.panel').find('.select-options').removeClass('hidden');
		} else {
			$(this).closest('.panel').find('.select-options').addClass('hidden');
		}
	});

});
