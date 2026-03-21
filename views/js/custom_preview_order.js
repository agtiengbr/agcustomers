document.addEventListener('DOMContentLoaded', function(){
    const divs = document.querySelectorAll('.preview-toggle');

    divs.forEach(el => el.addEventListener('click', event => {
        const order_url = $(event.currentTarget).data('preview-data-url').split('/');
        
        if(order_url[5] > 0) {
            $.ajax({
                url: 'ajax-tab.php',
                dataType: 'json',
                data :{
                    ajax: true,
                    controller: 'AdminAgCustomersPreview',
                    action: 'PreviewOrder',
                    token: agcustomer_token,
                    id_order: order_url[5]
                },
                success : function(resp){
                    // $(".order-preview-content .col-5").html('');

                    new_preview_address = `
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col-1">
                                        <p class="mb-0 agcustomers">
                                            <i class="material-icons pr-1">local_shipping</i>
                                        </p>
                                        </div>
                                        <div class="col">
                                        <p class="mb-0 agcustomers">
                                            <strong>Transportadora:</strong>
                                                ${resp['shipping_data_address'].carrier_name}
                                        </p>
                                        <p class="mb-0 agcustomers">
                                            <strong>Número de rastreamento:</strong>
                                                            ${resp['shipping_data_address'].tracking_number}
                                                        </p>

                                        <p class="mb-2"><strong>Detalhes do envio:</strong></p>
                                        <p class="mb-0 agcustomers">${resp['shipping_data_address'].firstname} ${resp['shipping_data_address'].lastname}</p>
                                                            <p class="mb-0 agcustomers">1</p>
                                            <p class="mb-0 agcustomers">${resp['shipping_data_address'].address1}, ${resp['shipping_data_address'].number ? resp['shipping_data_address'].number : ((window.agcustomers && window.agcustomers.translations && window.agcustomers.translations.no_number) || 'N/A')}</p>
                                        <p class="mb-0 agcustomers">${resp['shipping_data_address'].address2}</p>
                                        <p class="mb-0 agcustomers">
                                            ${resp['shipping_data_address'].city}
                                            ${resp['shipping_data_address'].state}
                                            ${resp['shipping_data_address'].postcode}
                                        </p>
                                        <p class="mb-0 agcustomers">${resp['shipping_data_address'].country}</p>
                                        <p></p>
                                    </div>
                                </div>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col-1">
                                <p class="mb-0 agcustomers">
                                    <i class="material-icons pr-1">receipt</i>
                                </p>
                                </div>
                                <div class="col">
                                <p class="mb-1">
                                    <strong>Detalhes da fatura:</strong>
                                </p>

                                <p class="mb-0 agcustomers">${resp['invoice_data_address'].firstname} ${resp['invoice_data_address'].lastname}</p>
                                <p class="mb-0 agcustomers">1</p>
                                    <p class="mb-0 agcustomers">${resp['invoice_data_address'].address1}, ${resp['invoice_data_address'].number ? resp['invoice_data_address'].number : ((window.agcustomers && window.agcustomers.translations && window.agcustomers.translations.no_number) || 'N/A')}</p>
                                <p class="mb-0 agcustomers">${resp['invoice_data_address'].address2}</p>
                                <p class="mb-0 agcustomers">
                                    ${resp['invoice_data_address'].city}
                                    ${resp['invoice_data_address'].state}
                                    ${resp['invoice_data_address'].postcode}
                                </p>
                                <p class="mb-0 agcustomers">${resp['invoice_data_address'].country}</p>
                                <p>${resp['invoice_data_address'].email}</p>
                                </div>
                            </div>
                        </div>
                    </div>`;

                    $(".order-preview-content .col-5").append(new_preview_address);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.log(XMLHttpRequest);
                    console.log(textStatus);
                    console.log(errorThrown);
                }
            });
        }
    }));
})