{if $type_address === 'invoice'}
    <p>{l s='Your invoice address is incomplete. To place your order, please complete your registration by %s.' mod='agcustomers' sprintf=["<a href='``$link->getPageLink('address', null, null, ['id_address' => $cart_obj->id_address_invoice])``'>{l s='clicking here' mod='agcustomers'}</a>"]}</p>
{else}
    <p>{l s='Your delivery address is incomplete. To place your order, please complete your registration by %s.' mod='agcustomers' sprintf=["<a href='``$link->getPageLink('address', null, null, ['id_address' => $cart_obj->id_address_delivery])``'>{l s='clicking here' mod='agcustomers'}</a>"]}</p>
{/if}