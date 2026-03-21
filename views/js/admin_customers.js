$(function(){
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

        form_control = $('<p/>', {
            class: 'form-control-static',
            text: text
        });

        row.append(label).append(form_control_container);
        form_control_container.append(form_control);

        return row[0].outerHTML;
    }

    function renderCustomerInfo()
    {
        var t = (window.agcustomers && window.agcustomers.translations) || {};
        var labels = {
            cpf: t.cpf || 'CPF',
            rg: t.rg || 'RG',
            company_name: t.company_name || 'Business name',
            cnpj: t.cnpj || 'CNPJ',
            ie: t.ie || 'State registration'
        };

        return renderCustomerInfoRow(labels.cpf, agcustomers_cpf) + 
        renderCustomerInfoRow(labels.rg, agcustomers_rg) + 
        renderCustomerInfoRow(labels.company_name, agcustomers_company_name) + 
        renderCustomerInfoRow(labels.cnpj, agcustomers_cnpj) + 
        renderCustomerInfoRow(labels.ie, agcustomers_ie);
    }

    var container_customer = $('#container-customer');
    var general_info_panel = container_customer.find('.panel')[0];

    $(general_info_panel).find('.form-horizontal').append($(renderCustomerInfo()));
});