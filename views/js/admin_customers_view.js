$(function(){
    var options;
    var id_language;

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


    function addInputs(customer_data)
    {
        var container = $('.icon-edit').closest('.panel').find('.form-horizontal');

        var html = '';

        $.each(options.type_person, function(key, value) {
            if (value.name == customer_data.person_type) {
                html += renderCustomerInfoRow('Person Type', getLangData(value.label, id_language));
                return false;
            }
        });
        
        

        $.each(options.fields.customer, function(key, value) {
            html += renderCustomerInfoRow(getLangData(value.label, id_language), customer_data[value.name]);
        });

        $(container).append($(html));
    }

    function loadOptions(email, success)
    {
        $.getJSON(agcustomers_url_load_options + '&email=' + email, function(data){
            options = data.options;
            id_language = data.id_language;
            success(data);
        });
    }

    function renderCustomerInfoRow(label, text)
    {
        var row, label, form_control_container, form_control;
        
        row = $('<div class="row"/>');

        label = $('<label/>', {
            class: 'control-label col-lg-3',
            text: label
        });

        form_control_container = $('<div/>', {
            class: 'col-lg-9'
        });

        if (text != null) {
            var p = $('<p class="form-control-static">' + text + '</p>');
            p.appendTo(form_control_container);
        }

        row.append(label).append(form_control_container);

        return row[0].outerHTML;
    }

    var link = $('.panel-heading .panel-heading-action .icon-edit').closest('.panel-heading').find('a:not(.btn)');
    if (link.length == 0) {
        return;
    }
    
    var mail = link.attr('href').split('mailto:')[1];

    loadOptions(mail, function(data){
        addInputs(data.customer_data);
    });

});