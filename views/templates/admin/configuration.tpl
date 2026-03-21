<form name='agcustomers' class="form-horizontal" method="post" action="{$form_action|escape:'htmlall':'utf-8'}" data-authenticated="{$authenticated|intval}">
    <ul class="nav nav-tabs" role="tablist">
        <li class='active'>
            <a data-toggle="tab" href="#tabCustomers">
                <i class="icon-cogs"></i> {l s='Customer Form' d='Modules.Agcustomers.Admin'}
            </a>
        </li>

         <li>
            <a data-toggle="tab" href="#tabAddresses">
                <i class="icon-cogs"></i> {l s='Address Form' d='Modules.Agcustomers.Admin'}
            </a>
        </li>

        <li>
            <a data-toggle="tab" href="#tabFacebook">
                <i class="icon-cogs"></i> {l s='Facebook Configuration' d='Modules.Agcustomers.Admin'}
            </a>
        </li>

        <li>
            <a data-toggle="tab" href="#tabGoogle">
                <i class="icon-cogs"></i> {l s='Google Configuration' d='Modules.Agcustomers.Admin'}
            </a>
        </li>

        <li>
            <a data-toggle="tab" href="#tabImport">
                <i class="icon-download"></i> {l s='Data Import' d='Modules.Agcustomers.Admin'}
            </a>
        </li>

        <li>
            <a data-toggle="tab" href="#tabMaintenance">
                <i class="icon-cogs"></i> {l s='Maintenance' d='Modules.Agcustomers.Admin'}
            </a>
        </li>

        <li>
            <a data-toggle="tab" href="#tabHelp">
                <i class="icon-question-circle"></i> {l s='Help' d='Modules.Agcustomers.Admin'}
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class='tab-pane active in' id="tabCustomers">
            <div class='panel'>
                <div class='panel-heading'>{l s='Customer Form' d='Modules.Agcustomers.Admin'}</div>
                <div class='row'>
                    <div class='col-md-2'>
                        <ul class="nav nav-tabs" role="tablist">
                            <li class='active'>
                                <a data-toggle="tab" href="#tabCustomersConfig">
                                    {l s='General settings' d='Modules.Agcustomers.Admin'}
                                </a>
                            </li>

                            <li>
                                <a data-toggle="tab" href="#tabCustomersTypePerson">
                                    {l s='Customer type' d='Modules.Agcustomers.Admin'}
                                </a>
                            </li>

                            {foreach from=$config.fields.customer item=field key=key}
                                {if $authenticated || $field.active|default:true}
                                <li>
                                    <a data-toggle="tab" href="#tabCustomers{$field.name}">
                                        {if is_array($field['label'])}
                                            {$field['label'][$current_id_lang]|default:$field['label'][$languages[0]['id_lang']]|default:$field['label']}
                                        {else}
                                            {$field['label']}
                                        {/if}
                                    </a>
                                </li>
                                {/if}
                            {/foreach}

                            <li>
                                <a data-toggle="tab" href="#tabCustomersNewField">
                                    <i class='icon-plus'></i> {l s='Add Field' d='Modules.Agcustomers.Admin'}
                                </a>
                            </li>
                        </ul>
                    </div>

                   <div class='col-md-10'>
                        <div class="tab-content">
                            <div class='tab-pane active in' id="tabCustomersConfig" >
                                <div class='panel'>
                                    <div class='panel-heading'>{l s='General settings' d='Modules.Agcustomers.Admin'}</div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Registration validation mode' d='Modules.Agcustomers.Admin'}</label>
                                        <div class="col-lg-9">
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="AGCUSTOMERS_VALIDATION_MODE" value="text" {if $agcustomers_validation_mode === 'text'}checked="checked"{/if}>
                                                    {l s='Text message (Simple warning at the top of the page)' d='Modules.Agcustomers.Admin'}
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="AGCUSTOMERS_VALIDATION_MODE" value="modal" {if $agcustomers_validation_mode === 'modal'}checked="checked"{/if}>
                                                    {l s='Modal (Blocks checkout and forces correction)' d='Modules.Agcustomers.Admin'}
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="AGCUSTOMERS_VALIDATION_MODE" value="none" {if $agcustomers_validation_mode === 'none' || $agcustomers_validation_mode === false}checked="checked"{/if}>
                                                    {l s='None (Disabled)' d='Modules.Agcustomers.Admin'}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    {include
                                        file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_text.tpl"
                                        label="{l s='Minimum age (years)' d='Modules.Agcustomers.Admin'}"
                                        value={$config['config']['customer']['min_age']|default:0}
                                        name="AGCUSTOMERS_CONFIG[config][customer][min_age]"
                                        col=2
                                        help="{l s='Leave 0 to disable minimum age check.' d='Modules.Agcustomers.Admin'}"
                                    }

                                    {include
                                        file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_text.tpl"
                                        label="{l s='Maximum age (years)' d='Modules.Agcustomers.Admin'}"
                                        value={$config['config']['customer']['max_age']|default:0}
                                        name="AGCUSTOMERS_CONFIG[config][customer][max_age]"
                                        col=2
                                        help="{l s='Leave 0 to disable maximum age check.' d='Modules.Agcustomers.Admin'}"
                                    }

                                    {include
                                        file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_switch.tpl"
                                        label="{l s='Place fields' d='Modules.Agcustomers.Admin'}"
                                        value={$config['config']['customer']['position']|default:1}
                                        name="AGCUSTOMERS_CONFIG[config][customer][position]"
                                    }

                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Setup actions' d='Modules.Agcustomers.Admin'}</label>
                                        <div class="col-lg-9">
                                            <div class="btn-group" role="group">
                                                <a class='btn btn-default apply_brazil_defaults'>{l s='Brazil default settings' d='Modules.Agcustomers.Admin'}</a>
                                                <a class='btn btn-danger reset_fields'>{l s='Reset fields' d='Modules.Agcustomers.Admin'}</a>
                                            </div>
                                            <div class="alert alert-warning" style="margin-top:10px;">
                                                {l s="Do not use these actions unless you know exactly what you're doing, as they may cause loss of information in your customers' records." d='Modules.Agcustomers.Admin'}
                                            </div>
                                            <span class="help-block">{l s='Brazil: creates CPF and other common Brazilian fields. Reset: removes module-created fields and restores a minimal setup.' d='Modules.Agcustomers.Admin'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class='tab-pane' id="tabCustomersTypePerson">
                                <div class='panel'>
                                    <div class='panel-heading'>{l s='Customer type' d='Modules.Agcustomers.Admin'}</div>
                                    <div class='alert alert-info'>{l s='We recommend not editing the "Name" once a person type is in use, as it may cause loss of information in your customers\' records.' d='Modules.Agcustomers.Admin'}</div>
                                    
                                    <div>
                                        {foreach from=$config.type_person item=type_person key=key}
                                            <div class='type-person'>
                                                 {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_text.tpl" label="{l s='Name' d='Modules.Agcustomers.Admin'}" value={$type_person.name|default} name="AGCUSTOMERS_CONFIG[type_person][{$key}][name]" class='name' help="{l s='Identifier used internally. Use only letters, numbers and underscore. Avoid changing when already in use.' d='Modules.Agcustomers.Admin'}"}

                                                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_text.tpl" label="{l s='Label' d='Modules.Agcustomers.Admin'}" value=$type_person['label'] name="AGCUSTOMERS_CONFIG[type_person][{$key}][label]" class='name' default_lang=$current_id_lang multilang=true id="AGCUSTOMERS_CONFIG_type_person_{$key}_label" help="{l s='Text shown to users. Use the language selector to provide translations.' d='Modules.Agcustomers.Admin'}"}
                                               
                                                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_switch.tpl" label="{l s='Enable' d='Modules.Agcustomers.Admin'}" value={$type_person.active} name="AGCUSTOMERS_CONFIG[type_person]["|cat:{$key}|cat:"][active]"}

                                                <div class="form-group client_group">
                                                    <label class="control-label col-lg-3">{l s='Customer group' d='Modules.Agcustomers.Admin'}</label>
                                                    <div class="col-lg-9">
                                                        <select name="AGCUSTOMERS_CONFIG[type_person][{$key}][group_customer]">
                                                            {foreach from=$customerGroups item=customerGroup key=key}
                                                                <option {if $type_person.group_customer==$customerGroup['id_group']} selected{/if} value="{$customerGroup['id_group']}">{$customerGroup['name']}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>
                                                  


                                                <div class='form-group text-center' {if $key <=2}title="{l s='You cannot delete default person types' d='Modules.Agcustomers.Admin'}" {/if}>
                                                    <button class='btn btn-danger' {if $key <=2}disabled="disabled"{/if}>{l s='Delete' d='Modules.Agcustomers.Admin'}</button>
                                                </div>

                                                <hr/>
                                            </div>
                                        {/foreach}

                                        <div class='text-center'>
                                            <button class="btn btn-default new_type_person" title="{l s='Add person type' d='Modules.Agcustomers.Admin'}"><i class="icon-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {foreach from=$config.fields.customer item=field key=key}
                                {if $authenticated || $field.active|default:true}
                                <div class='tab-pane customer_input' id="tabCustomers{$field.name}" data-idx="{$key}">
                                    <input class="is_default_input" name="AGCUSTOMERS_CONFIG[fields][customer][{$key}][is_default_input]" type="hidden" value="{$field.is_default_input|default:0}" />

                                    <div class='panel'>
                                        {assign var=custLabel value=""}
                                        {if is_array($field.label)}
                                            {assign var=custLabel value=$field.label[$current_id_lang]|default:$field.label[$languages[0]['id_lang']]}
                                        {else}
                                            {assign var=custLabel value=$field.label}
                                        {/if}
                                        <div class='panel-heading'>
                                            {if $custLabel}
                                                {$custLabel}
                                            {else}
                                                {l s='Field settings' d='Modules.Agcustomers.Admin'}
                                            {/if}
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-lg-3">{l s='Field type' d='Modules.Agcustomers.Admin'}</label>
                                            <div class="col-lg-3">
                                                <select class="input-type" name="AGCUSTOMERS_CONFIG[fields][customer][{$key}][type]">
                                                    <option value="text" {if $field['type'] == 'text'}selected{/if}>{l s='Text' d='Modules.Agcustomers.Admin'}</option>
                                                    <option value="select" {if $field['type'] == 'select'}selected{/if}>{l s='Dropdown list' d='Modules.Agcustomers.Admin'}</option>
                                                    <option value="checkbox" {if $field['type'] == 'checkbox'}selected{/if}>{l s='Checkboxes' d='Modules.Agcustomers.Admin'}</option>
                                                </select>
                                            </div>
                                        </div>

                                        
                                        <div class="form-group select-options {if $field['type'] != 'select' && $field['type'] != 'checkbox'}hidden{/if}">
                                            <label class="control-label col-lg-3">{l s='Available options' d='Modules.Agcustomers.Admin'}</label>
                                            <div class="col-lg-8">
                                                <table class="table col-lg-12">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>{l s='Option value' d='Modules.Agcustomers.Admin'}</th>
                                                            <th>{l s='Displayed text' d='Modules.Agcustomers.Admin'}</th>
                                                            <th class="fixed-width-xs"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {foreach from=$field['options'] key=i item=$option}
                                                            <tr>
                                                                <input type="hidden" name="AGCUSTOMERS_CONFIG[fields][customer][{$key}][options][{$i}][value]" value="{$option.value}"/>
                                                                <input type="hidden" name="AGCUSTOMERS_CONFIG[fields][customer][{$key}][options][{$i}][text]" value="{$option.text}"/>
                                                                <td>{$option.value}</td>
                                                                <td>{$option.text}</td>
                                                                <td class="icon-trash-td"><i class="icon-trash"></i></td>
                                                            </tr>
                                                        {/foreach}
                                                        <tr>
                                                            <td colspan="3" class="text-center add-option">
                                                                <div class="btn btn-primary">{l s='Add option' d='Modules.Agcustomers.Admin'}</div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <small class="info-message">{l s='Note: To save an option, confirm using the icon' d='Modules.Agcustomers.Admin'} <i class="icon-check"></i></small>
                                            </div>
                                        </div>
                                         
                                        {if $key >= 6}
                                            <div class='alert alert-info'>{l s='We recommend not editing the "Name" once the field is in use, as it may cause loss of information in your customers\' records.' d='Modules.Agcustomers.Admin'}</div>
                                        {/if}

                                        {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_text.tpl" label="{l s='Name' d='Modules.Agcustomers.Admin'}" value=$field.name|default name='AGCUSTOMERS_CONFIG[fields][customer]['|cat:$key|cat:'][name]' class='name' col=4 help="{l s='Technical identifier (column name). Must start with a letter, and contain only letters, numbers, and underscore. Must be unique on customer table.' d='Modules.Agcustomers.Admin'}"}

                                        {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_text.tpl" label="{l s='Label' d='Modules.Agcustomers.Admin'}" value=$field['label'] name="AGCUSTOMERS_CONFIG[fields][customer][{$key}][label]" default_lang=$current_id_lang multilang=true id="AGCUSTOMERS_CONFIG_fields_{$key}_label" help="{l s='Text displayed next to the field on forms. Use the language selector to translate the label for each language.' d='Modules.Agcustomers.Admin'}"}


                                        <div class="form-group">
                                            <label class="control-label col-lg-3">{l s='Display' d='Modules.Agcustomers.Admin'}</label>

                                            <div class="col-lg-9">
                                                {foreach from=$config.type_person item=type_person}
                                                    <div class="checkbox"> <label for="AGCUSTOMERS_CONFIG[fields][customer][{$key}][insert][{$type_person['name']}]"> <input type="checkbox" value="1" name="AGCUSTOMERS_CONFIG[fields][customer][{$key}][insert][{$type_person['name']}]" {if $field['insert'][$type_person['name']]|default}checked{/if}>

                                                        {if is_array($type_person['label'])}
                                                            {$type_person['label'][$current_id_lang]|default:$type_person['label'][$languages[0]['id_lang']]|default:$type_person['label']}
                                                        {else}
                                                            {$type_person['label']}
                                                        {/if}
                                                    </label> </div>
                                                {/foreach}    
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="control-label col-lg-3">{l s='Required' d='Modules.Agcustomers.Admin'}</label>

                                            <div class="col-lg-9">
                                                {foreach from=$config.type_person item=type_person}
                                                    <div class="checkbox"> <label for="AGCUSTOMERS_CONFIG[fields][customer][{$key}][required][{$type_person['name']}]"> <input type="checkbox" value="1" name="AGCUSTOMERS_CONFIG[fields][customer][{$key}][required][{$type_person['name']}]" {if $field['required'][$type_person['name']]|default}checked{/if}>

                                                        {if is_array($type_person['label'])}
                                                            {$type_person['label'][$current_id_lang]|default:$type_person['label'][$languages[0]['id_lang']]|default:$type_person['label']}
                                                        {else}
                                                            {$type_person['label']}
                                                        {/if}
                                                    </label> </div>
                                                {/foreach}    
                                            </div>
                                        </div>

                                        {if $ps17}
                                            {if $field.name != 'birthday'}
                                                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_switch.tpl" label="{l s='Prevent duplication' d='Modules.Agcustomers.Admin'}" value={$field.unique|default:false} name="AGCUSTOMERS_CONFIG[fields][customer]["|cat:{$key}|cat:"][unique]" hint="{l s='If enabled, prevents duplicate values for different customers.' d='Modules.Agcustomers.Admin'}"}
                                            {/if}
                                        {/if}

                                        {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_switch.tpl" label="{l s='Allow customer to edit' d='Modules.Agcustomers.Admin'}" value={$config['fields']['customer'][$key]['edit_fo']} name="AGCUSTOMERS_CONFIG[fields][customer]["|cat:{$key}|cat:"][edit_fo]"}
                                        {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_switch.tpl" label="{l s='Allow employees to edit' d='Modules.Agcustomers.Admin'}" value={$config['fields']['customer'][$key]['edit_bo']} name="AGCUSTOMERS_CONFIG[fields][customer]["|cat:{$key}|cat:"][edit_bo]"}

                                        {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_text.tpl" label="{l s='Mask' d='Modules.Agcustomers.Admin'}" value=$field.mask|default name='AGCUSTOMERS_CONFIG[fields][customer]['|cat:$key|cat:'][mask]' class='mask' col=4 help="{l s='Optional input mask. Use 0 for digits (e.g. 000.000.000-00). Leave empty to disable.' d='Modules.Agcustomers.Admin'}"}                                        
                                    </div>
                                </div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                </div>
                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/panel_footer.tpl" submit="agcustomers-submit"}
            </div>
        </div>

        <div class='tab-pane' id="tabAddresses">
            <div class='panel'>
                <div class='panel-heading'>{l s='General settings' d='Modules.Agcustomers.Admin'}</div>
                {include
                    file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_switch.tpl"
                    label="{l s='Place fields' d='Modules.Agcustomers.Admin'}"
                    value={$config['config']['address']['position']|default:1}
                    name="AGCUSTOMERS_CONFIG[config][address][position]"
                }

                {include
                    file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_switch.tpl"
                    label="{l s='display error messages when required fields are empty' d='Modules.Agcustomers.Admin'}"
                    value={$config['config']['address']['error_msg']|default:1}
                    name="AGCUSTOMERS_CONFIG[config][address][error_msg]"
                }

                 {include
                    file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_switch.tpl"
                    label="{l s='lock the address when it is auto-filled from the zip code' d='Modules.Agcustomers.Admin'}"
                    value={$config['config']['address']['disabled']|default:1}
                    name="AGCUSTOMERS_CONFIG[config][address][disabled]"
                }
                
                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/panel_footer.tpl" submit="agcustomers-submit"}
            </div>

            <div class='panel'>
                <div class='panel-heading'>{l s='Required fields' d='Modules.Agcustomers.Admin'}</div>
                {foreach from=$config.fields.address key=$key item=field}
                    <input name="AGCUSTOMERS_CONFIG[fields][address][{$key}][name]" type="hidden" value="{$field.name|default}" />

                    {assign var=addrLabel value=""}
                    {if is_array($field.label)}
                        {assign var=addrLabel value=$field.label[$current_id_lang]|default:$field.label[$languages[0].id_lang]}
                    {else}
                        {assign var=addrLabel value=$field.label}
                    {/if}

                    {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_switch.tpl" label=$addrLabel value={$field.required} name="AGCUSTOMERS_CONFIG[fields][address]["|cat:$key|cat:"][required]"}
                {/foreach}

                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/panel_footer.tpl" submit="agcustomers-submit"}
            </div>
        </div>

        <div class='tab-pane' id="tabGoogle">
            <div class='panel'>
                <div class='panel-heading'>{l s='Google Configuration' d='Modules.Agcustomers.Admin'}</div>
                {include
                    file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_switch.tpl"
                    label={l s='Display floating window for login with google' d='Modules.Agcustomers.Admin'}
                    value={$google_prompt}
                    
                    name="agcustomers_google_prompt"
                }

                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_text.tpl" label="{l s='Client Id' d='Modules.Agcustomers.Admin'}" value={$google_client_id} name='agcustomers_google_client_id'}
                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/panel_footer.tpl" submit="agcustomers-submit"}
            </div>

            <div class='panel'>
                <div class="panel-heading">
                    {l s='Customizing the google login button' d='Modules.Agcustomers.Admin'}
			    </div>
                <div class="panel-body">

                <div class="form-group">				
                    <label class="control-label col-lg-3">
                        {l s='Type' d='Modules.Agcustomers.Admin'}
                    </label>

                   

                    <select name="agcustomers_google_type_btn" class=" fixed-width-xl" id="agcustomers_google_type_btn">
                        <option{if $google_type_btn==standard} selected{/if} value="standard">{l s='standard' d='Modules.Agcustomers.Admin'}</option>
                        <option{if $google_type_btn==icon} selected{/if} value="icon" >{l s='icon' d='Modules.Agcustomers.Admin'}</option>
                    </select>
                </div>

                <div class="form-group">				
                    <label class="control-label col-lg-3">
                        {l s='Theme' d='Modules.Agcustomers.Admin'}
                    </label>

                    <select name="agcustomers_google_theme_btn" class=" fixed-width-xl" id="agcustomers_google_theme_btn">
                        <option{if $google_theme_btn==outline} selected{/if} value="outline">{l s='White' d='Modules.Agcustomers.Admin'}</option>
                        <option{if $google_theme_btn==filled_blue} selected{/if} value="filled_blue">{l s='Blue' d='Modules.Agcustomers.Admin'}</option>
                        <option{if $google_theme_btn==filled_black} selected{/if} value="filled_black">{l s='Black' d='Modules.Agcustomers.Admin'}</option>
                    </select>
                </div>

                 <div class="form-group">				
                    <label class="control-label col-lg-3">
                        {l s='Size' d='Modules.Agcustomers.Admin'}
                    </label>

                    <select name="agcustomers_google_size_btn" class=" fixed-width-xl" id="agcustomers_google_size_btn">
                        <option{if $google_size_btn==large} selected{/if} value="large">{l s='large' d='Modules.Agcustomers.Admin'}</option>
                        <option{if $google_size_btn==medium} selected{/if} value="medium">{l s='medium' d='Modules.Agcustomers.Admin'}</option>
                        <option{if $google_size_btn==small} selected{/if} value="small">{l s='small' d='Modules.Agcustomers.Admin'}</option>
                    </select>
                </div>

                <div class="form-group">				
                    <label class="control-label col-lg-3">
                        {l s='text to be displayed' d='Modules.Agcustomers.Admin'}
                    </label>

                    <select name="agcustomers_google_text_btn" class=" fixed-width-xl" id="agcustomers_google_text_btn">
                        <option{if $google_text_btn==signin_with} selected{/if} value="signin_with">{l s='signin with Google' d='Modules.Agcustomers.Admin'}</option>
                        <option{if $google_text_btn==signup_with} selected{/if} value="signup_with">{l s='signup with Google' d='Modules.Agcustomers.Admin'}</option>
                        <option{if $google_text_btn==continue_with} selected{/if} value="continue_with">{l s='continue with Google' d='Modules.Agcustomers.Admin'}</option>
                    </select>
                </div>

                 <div class="form-group">				
                    <label class="control-label col-lg-3">
                        {l s='shape' d='Modules.Agcustomers.Admin'}
                    </label>

                    <select name="agcustomers_google_shape_btn" class=" fixed-width-xl" id="agcustomers_google_shape_btn">
                        <option{if $google_shape_btn==pill} selected{/if} value="pill">{l s='pill' d='Modules.Agcustomers.Admin'}</option>
                        <option{if $google_shape_btn==circle} selected{/if} value="circle">{l s='circle' d='Modules.Agcustomers.Admin'}</option>
                        <option{if $google_shape_btn==square} selected{/if} value="square">{l s='square' d='Modules.Agcustomers.Admin'}</option>
                    </select>
                </div>

                  <div class="form-group">				
                    <label class="control-label col-lg-3">
                        {l s='logo alignment' d='Modules.Agcustomers.Admin'}
                    </label>

                    <select name="agcustomers_google_logo_btn" class=" fixed-width-xl" id="agcustomers_google_logo_btn">
                        <option{if $google_logo_btn==left} selected{/if} value="left">{l s='left' d='Modules.Agcustomers.Admin'}</option>
                        <option{if $google_logo_btn==center} selected{/if} value="center">{l s='center' d='Modules.Agcustomers.Admin'}</option>
                    </select>
                </div>
                </div>
                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/panel_footer.tpl" submit="agcustomers-submit"}
            </div>
        </div>

        <div class='tab-pane' id="tabFacebook">
            <div class='panel'>
                <div class='panel-heading'>{l s='Facebook Configuration' d='Modules.Agcustomers.Admin'}</div>
                <div class='alert alert-info'>
                    {l s='URLs to insert in %s field' d='Modules.Agcustomers.Admin' sprintf=['Valid OAuth URIs']}
                    <ul>
                        {foreach $redirectLinks as $url}
                            <li>{$url}</li>
                        {/foreach}
                    </ul>
                </div>

                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_text.tpl" label="{l s='APP ID' d='Modules.Agcustomers.Admin'}" value={$facebook_app_id} name='agcustomers_facebook_app_id'}
                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_text.tpl" label="{l s='Secret Key' d='Modules.Agcustomers.Admin'}" value={$facebook_app_secret} name='agcustomers_facebook_app_secret'}
                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/input_text.tpl" label="{l s='API Version' d='Modules.Agcustomers.Admin'}" value={$facebook_og_version} name='agcustomers_facebook_og_version'}

                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/panel_footer.tpl" submit="agcustomers-submit"}
            </div>
        </div>

        <div class='tab-pane' id="tabImport">
            <div class='panel'>
                <div class='panel-heading'>{l s='Data Import' d='Modules.Agcustomers.Admin'}</div>
                <div class='alert alert-info'>{l s='The import will start in a new window. Please keep it open until it finishes.' d='Modules.Agcustomers.Admin'}</div> 

                <p>
                    <a target='_blank' href='{$link_import_fkcustomers}' class='btn btn-default' onclick="!confirm('{l s='Did your store really have the chosen module? This operation is irreversible and data loss may occur if used incorrectly.' d='Modules.Agcustomers.Admin' js=1}') && event.preventDefault();">{l s='Import from FkCustomers module' d='Modules.Agcustomers.Admin'}</a>
                    <a target='_blank' href='{$link_import_djtal}'       class='btn btn-default' onclick="!confirm('{l s='Did your store really have the chosen module? This operation is irreversible and data loss may occur if used incorrectly.' d='Modules.Agcustomers.Admin' js=1}') && event.preventDefault();">{l s='Import from djtal Brazilian Registration module' d='Modules.Agcustomers.Admin'}</a>
                </p>

                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/panel_footer.tpl" submit="agcustomers-submit"}
            </div>
        </div>

        <div class='tab-pane' id="tabMaintenance">
            <div class='panel'>
                <div class='panel-heading'>{l s='Maintenance' d='Modules.Agcustomers.Admin'}</div>
                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/tab_maintenance.tpl"}
            </div>
        </div>

        <div class='tab-pane' id="tabHelp">
            <div class='panel'>
                <div class='panel-heading'>{l s='Help' d='Modules.Agcustomers.Admin'}</div>
                {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/tab_help.tpl"}
            </div>
        </div>
    </div>
</form>
