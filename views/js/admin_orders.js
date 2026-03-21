document.addEventListener('DOMContentLoaded', function(){
	var well = $('#content >.row >.col-lg-12 >.row >.col-lg-5 dl.well');

	var agcustomers_parsed_data = JSON.parse(agcustomers_data);

	var t = (window.agcustomers && window.agcustomers.translations) || {};
	if (typeof agcustomers_parsed_data.ie !== 'undefined' && agcustomers_parsed_data.ie  != '' && agcustomers_parsed_data.ie != null) {
		var ieLabel = t.ie || 'IE';
		var ie = '<dt>' + ieLabel + '</dt><dd>' + agcustomers_parsed_data.ie + '</dd>';
		well.prepend(ie);
	}

	if (typeof agcustomers_parsed_data.cnpj !== 'undefined' && agcustomers_parsed_data.cnpj  != '' && agcustomers_parsed_data.cnpj != null) {
		var cnpjLabel = t.cnpj || 'CNPJ';
		var cnpj = '<dt>' + cnpjLabel + '</dt><dd>' + agcustomers_parsed_data.cnpj + '</dd>';
		well.prepend(cnpj);
	}

	if (typeof agcustomers_parsed_data.company_name !== 'undefined' && agcustomers_parsed_data.company_name  != '' && agcustomers_parsed_data.company_name != null) {
		var companyLabel = t.company_name || 'Business name';
		var company_name = '<dt>' + companyLabel + '</dt><dd>' + agcustomers_parsed_data.company_name + '</dd>';
		well.prepend(company_name);
	}

	//Refazendo os blocos dos endereços
	createNewBlock('#addressShipping', {'address': agcustomers_address_shipping, 'customer': agcustomers_parsed_data});

	createNewBlock('#addressInvoice', {'address': agcustomers_address_invoice, 'customer': agcustomers_parsed_data});


	function createNewBlock(block, data) {
		document.querySelectorAll(`${block}.mb-0, ${block} p`).forEach(n => n.remove());

		var noNumber = t.no_number || 'N/A';
		new_block = `
			<p class="mb-0 agcustomer">${data['customer'].firstname} ${data['customer'].lastname}</p>
			<p class="mb-0 agcustomer">${data['address'].address1}, ${data['address'].number ? data['address'].number : noNumber}</p>
			<p class="mb-0 agcustomer">${data['address'].other}</p>
			<p class="mb-0 agcustomer">${data['address'].address2}</p>
			<p class="mb-0 agcustomer">${data['address'].city}, ${data['address'].state}, ${data['address'].postcode}</p>
			<p class="mb-0 agcustomer">${data['address'].country}</p>
			<p class="mb-0 agcustomer">${data['address'].phone_mobile}</p>
		`;

		$(block).append(new_block);
	}

	let email_div = $('#customerEmail');
	let order_div = $('#validatedOrders');

    // Validate and create CPF div if exists
	if (agcustomers_parsed_data.cpf && agcustomers_parsed_data.cpf !== 'undefined') {
		let cpf_div = `
			<p class="mb-1">
				<strong>${t.cpf || 'CPF'}:</strong>
			</p>
			<p>
				${agcustomers_parsed_data.cpf}
			</p>
		`;
		$(cpf_div).appendTo(email_div);
	}

	// Validate and create RG div if exists
	if (agcustomers_parsed_data.rg && agcustomers_parsed_data.rg !== 'undefined') {
		let rg_div = `
			<p class="mb-1">
				<strong>${t.rg || 'RG'}:</strong>
			</p>
			<p>
				${agcustomers_parsed_data.rg}
			</p>
		`;
		$(rg_div).appendTo(email_div);
	}

	// Validate and create CNPJ div if exists
	if (agcustomers_parsed_data.cnpj && agcustomers_parsed_data.cnpj !== 'undefined') {
		let cnpj_div = `
			<p class="mb-1">
				<strong>${t.cnpj || 'CNPJ'}:</strong>
			</p>
			<p>
				${agcustomers_parsed_data.cnpj}
			</p>
		`;
		$(cnpj_div).appendTo(order_div);
	}

	// Validate and create IE div if exists
	if (agcustomers_parsed_data.ie && agcustomers_parsed_data.ie !== 'undefined') {
		let ie_div = `
			<p class="mb-1">
				<strong>${t.ie || 'IE'}:</strong>
			</p>
			<p>
				${agcustomers_parsed_data.ie}
			</p>
		`;
		$(ie_div).appendTo(order_div);
	}

	// Validate and create Company Name div if exists
	if (agcustomers_parsed_data.company_name && agcustomers_parsed_data.company_name !== 'undefined') {
		let company_div = `
			<p class="mb-1">
				<strong>${t.company_name || 'Business name'}:</strong>
			</p>
			<p>
				${agcustomers_parsed_data.company_name}
			</p>
		`;
		$(company_div).appendTo(order_div);
	}
});
