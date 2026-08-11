$(function(){
    var options;
    var id_language;
    var nativeCustomerFields = ['firstname', 'lastname', 'birthday'];

    function getLangData(array_datas, id_lang)
    {
        if (typeof array_datas !== 'object') {
            return array_datas;
        }

        if (typeof array_datas[id_lang] !== 'undefined') {
            return array_datas[id_lang];
        }

        var languages = Object.keys(array_datas);
        return languages.length ? array_datas[languages[0]] : '';
    }

    function isNativeCustomerField(field)
    {
        return field.is_default_input || nativeCustomerFields.indexOf(field.name) !== -1;
    }

    function addInputs(customer_data)
    {
        var card = $('.customer-personal-informations-card').first();
        var container = card.find('.card-body').first();

        if (!container.length || container.find('.agcustomers-custom-fields').length) {
            return;
        }

        var fields = $('<div/>', { class: 'agcustomers-custom-fields' });

        $.each(options.type_person, function(key, value) {
            if (value.name == customer_data.person_type) {
                fields.append(renderCustomerInfoRow('Person Type', getLangData(value.label, id_language)));
                return false;
            }
        });
        
        $.each(options.fields.customer, function(key, value) {
            if (isNativeCustomerField(value)) {
                return;
            }

            fields.append(renderCustomerInfoRow(getLangData(value.label, id_language), customer_data[value.name] || ''));
        });

        container.append(fields);
    }

    function loadOptions(customerId, success)
    {
        $.getJSON(agcustomers_url_load_options + '&id_customer=' + encodeURIComponent(customerId), function(data){
            if (!data.success || !data.customer_data || !Object.keys(data.customer_data).length) {
                return;
            }

            options = data.options;
            id_language = data.id_language;

            success(data);
        });
    }

    var customerCard = $('.customer-personal-informations-card').first();
    var customerId = $.trim(customerCard.find('.customer-id').first().text());
    if (!customerCard.length || !/^\d+$/.test(customerId)) {
        return;
    }

    loadOptions(customerId, function(data){
        addInputs(data.customer_data);
    });

    function renderCustomerInfoRow(label, text)
    {
        var row, label, form_control_container, form_control;
        
        row = $('<div class="row mb-1"/>');

        label = $('<label/>', {
            class: 'col-4 text-right',
            text: label
        });

        form_control_container = $('<div/>', {
            class: 'col-8',
            text: text
        });

        row.append(label).append(form_control_container);
        form_control_container.append(form_control);

        return row[0].outerHTML;
    }
});
