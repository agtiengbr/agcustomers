<div id="ag-checkout-validation-overlay" style="display:none;">
    <div id="ag-checkout-validation-modal">
    <h2>{l s='Issues found in your registration' d='Modules.Agcustomers.Shop' mod='agcustomers'}</h2>
    <p>{l s='To proceed with the purchase, please correct the fields below:' d='Modules.Agcustomers.Shop' mod='agcustomers'}</p>
        <form id="ag-checkout-validation-form">
            {foreach from=$agcustomer_checkout_errors item=error}
                    <div class="form-group row">
                        <label class="col-md-3 form-control-label">{$error.label}</label>
                        <div class="col-md-6">
                            {if $error.input_name == 'person_type'}
                                {foreach from=$type_persons item=type}
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="person_type" id="person_type_{$type.name}" value="{$type.name}">
                                        <label class="form-check-label" for="person_type_{$type.name}">
                                            {$type.label}
                                        </label>
                                    </div>
                                {/foreach}
                            {elseif $error.input_name == 'birthday'}
                                {assign var=birthday_val value=$error.value}
                                {if $birthday_val == '0000-00-00'}{assign var=birthday_val value=''}{/if}
                                <input type="date" name="birthday" class="form-control" value="{$birthday_val|escape:'htmlall':'UTF-8'}" />
                            {else}
                                <input type="text" name="{$error.input_name}" class="form-control" value="{$error.value|escape:'htmlall':'UTF-8'}" />
                            {/if}
                        </div>
                    </div>
            {/foreach}
            <div class="form-footer clearfix">
                <button type="submit" class="btn btn-primary pull-xs-right">{l s='Save and continue' d='Modules.Agcustomers.Shop' mod='agcustomers'}</button>
            </div>
            <div class="ag-validation-messages"></div>
        </form>
    </div>
</div>