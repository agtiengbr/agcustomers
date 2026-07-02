{if $type_address === 'invoice'}
    <p>{l s='Your invoice address is incomplete. To place your order, please complete your registration by' d='Modules.Agcustomers.Shop' mod='agcustomers'} <a href="{$link->getPageLink('address', true, null, ['id_address' => $cart_obj->id_address_invoice])|escape:'html':'UTF-8'}">{l s='clicking here' d='Modules.Agcustomers.Shop' mod='agcustomers'}</a>.</p>
{else}
    <p>{l s='Your delivery address is incomplete. To place your order, please complete your registration by' d='Modules.Agcustomers.Shop' mod='agcustomers'} <a href="{$link->getPageLink('address', true, null, ['id_address' => $cart_obj->id_address_delivery])|escape:'html':'UTF-8'}">{l s='clicking here' d='Modules.Agcustomers.Shop' mod='agcustomers'}</a>.</p>
{/if}
