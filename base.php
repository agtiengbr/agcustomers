<?php

use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

require_once _PS_MODULE_DIR_ . 'agcliente/lib/AgModule.php';
require_once _PS_MODULE_DIR_ . 'agcustomers/vendor/facebook/graph-sdk/src/Facebook/autoload.php';

class BaseAgCustomers extends AgModule
{
    private const DEFAULT_CEP_PROVIDER = 'agti';

    public $checkoutErrors = [];
    protected $hooks = array(
        'displayHeader',
        'displayBackOfficeHeader',
        'actionAdminControllerSetMedia',
        'displayAdminOrderContentOrder',
        'additionalCustomerFormFields',
        'actionAdminCustomersFormModifier',
        'displayBeforeBodyClosingTag',
        'additionalCustomerAddressFormFields',
        'actionAdminAddressesFormModifier',
        'actionObjectCustomerAddAfter',
        'actionObjectCustomerUpdateAfter',

        'actionCustomerGridDefinitionModifier',
        'actionCustomerGridQueryBuilderModifier',
        'actionAddressGridDefinitionModifier',
        'actionAddressGridQueryBuilderModifier',

        'actionCustomerAccountAdd',
        'actionCustomerAccountUpdate'
    );

    protected $workers = [
        [
            'name' => 'syncgroups',
            'controller' => 'syncgroups',
            'delay' => 43200
        ],
    ];
    protected $main_tab ='AdminParentCustomer';

    protected $tabs = [
        [
            'name' => 'Módulo de Cadastro AGTI',
            'className' => 'AdminAgCustomersLoadOptions',
            'active' => 0
        ],
        [
            'name' => 'Preview order AGTI',
            'className' => 'AdminAgCustomersPreview',
            'active' => 0
        ],
        [
            'name' => 'Configurações dos Cadastros',
            'className' => 'AdminAgCustomersConfig',
            'active' => 1
        ]
    ];

    public function __construct()
    {
        $this->name     = 'agcustomers';
        $this->tab      = 'Others';
        $this->version  = '2.8.13';
        $this->author   = 'AGTI';

        $this->bootstrap = true;
        
        parent::__construct();

        // PS9 translations
        $this->displayName = $this->trans('AGTI Customer Registration PRO', [], 'Modules.Agcustomers.Admin');
        $this->description = $this->trans('Allows your customers to register and log in to your store through Facebook and Google. Allows you to add custom fields to PrestaSHop registration form.', [], 'Modules.Agcustomers.Admin');

        // Localize tab names for all languages
        if (isset($this->tabs[0])) {
            $tab1 = [];$tab2 = [];$tab3 = [];
            foreach (Language::getLanguages(true) as $lang) {
                $locale = $lang['locale'] ?? null;
                $tab1[$lang['iso_code']] = $this->trans('Registration module (AGTI)', [], 'Modules.Agcustomers.Admin', $locale);
                $tab2[$lang['iso_code']] = $this->trans('Order preview (AGTI)', [], 'Modules.Agcustomers.Admin', $locale);
                $tab3[$lang['iso_code']] = $this->trans('Registration settings', [], 'Modules.Agcustomers.Admin', $locale);
            }
            $this->tabs[0]['name'] = $tab1 ?: $this->tabs[0]['name'];
            if (isset($this->tabs[1])) { $this->tabs[1]['name'] = $tab2 ?: $this->tabs[1]['name']; }
            if (isset($this->tabs[2])) { $this->tabs[2]['name'] = $tab3 ?: $this->tabs[2]['name']; }
        }

        $this->loadMappings();
    }

    private function isLicensedModeEnabled()
    {
        return true;
    }

    private function getDefaultCepProvider()
    {
        return self::DEFAULT_CEP_PROVIDER;
    }

    public function normalizeCepProvider($provider)
    {
        if (in_array($provider, ['agti', 'republicavirtual', 'viacep'], true)) {
            return $provider;
        }

        return $this->getDefaultCepProvider();
    }

    public function findAddressByPostcode($postcode, $provider = null)
    {
        $postcode = preg_replace('/\D+/', '', (string) $postcode);
        if ($postcode === '') {
            return false;
        }

        $provider = $this->normalizeCepProvider($provider);
        if ($provider === 'agti') {
            $address = AddressFinder::findAgti($postcode);
        } elseif ($provider === 'republicavirtual') {
            $address = AddressFinder::findRepublicaVirtual($postcode);
        } else {
            $address = AddressFinder::findViaCep($postcode);

            if (!$address) {
                $address = AddressFinder::findRepublicaVirtual($postcode);
            }
        }

        return $this->normalizeFoundAddress($address);
    }

    private function normalizeFoundAddress($address)
    {
        if (!$address || !is_object($address)) {
            return false;
        }

        if (!isset($address->street) && isset($address->address)) {
            $address->street = $address->address;
        }

        if (!isset($address->street)) {
            $address->street = '';
        }

        if (!isset($address->district)) {
            $address->district = '';
        }

        if (!isset($address->city)) {
            $address->city = '';
        }

        if (!isset($address->state)) {
            $address->state = '';
        }

        return $address;
    }

    /**
     * Return a map of id_lang => translated string for all active languages.
     * Used to store multilingual labels inside the options arrays.
     */
    private function trAllLanguages(string $message, string $domain = 'Modules.Agcustomers.Admin'): array
    {
        $labels = [];
        foreach (Language::getLanguages(true) as $lang) {
            $locale = $lang['locale'] ?? null;
            $labels[$lang['id_lang']] = $this->trans($message, [], $domain, $locale);
        }
        return $labels;
    }

    /**
     * Normalize and translate default option labels and type-person labels.
     * If an expected field has a scalar label, replace it with a multilingual map.
     */
    private function localizeOptions(array $options): array
    {
        $domain = 'Modules.Agcustomers.Admin';
        $languages = Language::getLanguages(true);

        // Customer fields label map (by field name)
        $customerLabelMap = [
            'firstname'     => 'First name',
            'lastname'      => 'Last name',
            'birthday'      => 'Date of birth',
            'cpf'           => 'CPF',
            'rg'            => 'RG',
            'company_name'  => 'Business name',
            'cnpj'          => 'CNPJ',
            'ie'            => 'State registration',
        ];

        // Address fields label map (by field name)
        $addressLabelMap = [
            'firstname'   => 'First name',
            'lastname'    => 'Last name',
            'address1'    => 'Address',
            'number'      => 'Number',
            'address2'    => 'Address line 2',
            'other'       => 'Additional information',
            'postcode'    => 'Zip/Postal code',
            'city'        => 'City',
            'id_state'    => 'State',
            'id_country'  => 'Country',
            'phone'       => 'Phone',
            'phone_mobile'=> 'Mobile phone',
        ];

        // Type persons label map (by code)
        $typePersonLabelMap = [
            'pf'  => 'Individual',
            'pj'  => 'Company',
            'nbr' => 'Foreign person',
        ];

        // Translate customer field labels (and refresh arrays if still in English)
        if (isset($options['fields']['customer']) && is_array($options['fields']['customer'])) {
            foreach ($options['fields']['customer'] as &$field) {
                if (isset($field['name']) && isset($customerLabelMap[$field['name']])) {
                    $defaultSrc = $customerLabelMap[$field['name']];
                    if (!isset($field['label']) || !is_array($field['label'])) {
                        $field['label'] = $this->trAllLanguages($defaultSrc, $domain);
                    } else {
                        foreach ($languages as $lang) {
                            $idLang = $lang['id_lang'];
                            $locale = $lang['locale'] ?? null;
                            $current = $field['label'][$idLang] ?? '';
                            if ($current === '' || $current === $defaultSrc) {
                                $field['label'][$idLang] = $this->trans($defaultSrc, [], $domain, $locale);
                            }
                        }
                    }
                }
            }
            unset($field);
        }

        // Translate address field labels: always compute from translations so they remain translatable
        if (isset($options['fields']['address']) && is_array($options['fields']['address'])) {
            foreach ($options['fields']['address'] as &$field) {
                if (isset($field['name']) && isset($addressLabelMap[$field['name']])) {
                    $defaultSrc = $addressLabelMap[$field['name']];
                    // Always overwrite with domain translations to keep it dynamic per language
                    $field['label'] = $this->trAllLanguages($defaultSrc, $domain);
                }
            }
            unset($field);
        }

        // Translate type_person labels (and refresh arrays if still in English)
        if (isset($options['type_person']) && is_array($options['type_person'])) {
            foreach ($options['type_person'] as &$type) {
                if (isset($type['name']) && isset($typePersonLabelMap[$type['name']])) {
                    $defaultSrc = $typePersonLabelMap[$type['name']];
                    if (!isset($type['label']) || !is_array($type['label'])) {
                        $type['label'] = $this->trAllLanguages($defaultSrc, $domain);
                    } else {
                        foreach ($languages as $lang) {
                            $idLang = $lang['id_lang'];
                            $locale = $lang['locale'] ?? null;
                            $current = $type['label'][$idLang] ?? '';
                            if ($current === '' || $current === $defaultSrc) {
                                $type['label'][$idLang] = $this->trans($defaultSrc, [], $domain, $locale);
                            }
                        }
                    }
                }
            }
            unset($type);
        }

        return $options;
    }

    public function install()
    {
        // International-friendly: do NOT auto-create Brazil-specific fields on install.
        // If needed, provide a separate configuration action to add BR columns explicitly.
        if (!Configuration::hasKey('AGTI_CEP_PROVIDER')) {
            Configuration::updateValue('AGTI_CEP_PROVIDER', $this->getDefaultCepProvider());
        }
        // Do not create AGCUSTOMERS_CONFIG automatically.
        return parent::install();
    }

    public function reset()
    {
        $this->resetConfig();
    }

    public function resetConfig()
    {
        // Intentionally no-op: we don't auto-create configuration anymore.
        // Address formats remain untouched.
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function getContent()
    {
        $options = $this->getOptions();
        if (Tools::getIsSet('resetFields')) {
            // Reset to a minimal, international-friendly configuration (no BR extras)
            Configuration::updateValue('AGCUSTOMERS_CONFIG', serialize($this->getMinimalOptions()));

            echo json_encode([
                'success' => true,
            ]);
            exit();
        } elseif (Tools::getIsSet('applyBrazilDefaults')) {
            // Apply the Brazil default field set. If the module is not authenticated,
            // we still create all fields but keep non-CPF ones inactive (hidden/disabled).
            $authenticated = $this->isLicensedModeEnabled();

            $options = $this->getMinimalOptions();

            $brazilCustomer = [
                [
                    'name' => 'firstname',
                    'label' => 'Nome',
                    'is_default_input' => 1,
                    'required' => ['pf' => true, 'pj' => true, 'nbr' => true],
                    'insert'   => ['pf' => true, 'pj' => true, 'nbr' => true]
                ],
                [
                    'name' => 'lastname',
                    'label' => 'Sobrenome',
                    'is_default_input' => 1,
                    'required' => ['pf' => true, 'pj' => true, 'nbr' => true],
                    'insert'   => ['pf' => true, 'pj' => true, 'nbr' => true]
                ],
                [
                    'name' => 'birthday',
                    'label' => 'Data de nascimento',
                    'is_default_input' => 1,
                    'required' => ['pf' => true, 'pj' => true, 'nbr' => true],
                    'insert'   => ['pf' => true, 'pj' => true, 'nbr' => true]
                ],
                [
                    'name' => 'cpf',
                    'label' => 'CPF',
                    'mask' => '000.000.000-00',
                    'active' => true,
                    'required' => ['pf' => true, 'pj' => true, 'nbr' => false],
                    'insert'   => ['pf' => true, 'pj' => true, 'nbr' => false]
                ],
                [
                    'name' => 'rg',
                    'label' => 'RG',
                    'active' => $authenticated ? true : false,
                ],
                [
                    'name' => 'company_name',
                    'label' => 'Razão Social',
                    'active' => $authenticated ? true : false,
                ],
                [
                    'name' => 'cnpj',
                    'label' => 'CNPJ',
                    'active' => $authenticated ? true : false,
                    'mask' => '00.000.000/0000-00',
                    'required' => ['pf' => false, 'pj' => $authenticated ? true : false, 'nbr' => false],
                    'insert'   => ['pf' => false, 'pj' => $authenticated ? true : false, 'nbr' => false]
                ],
                [
                    'name' => 'ie',
                    'label' => 'Inscrição Estadual',
                    'active' => $authenticated ? true : false,
                ],
            ];

            $options['fields']['customer'] = $brazilCustomer;
            // Localize labels for all languages before saving
            $options = $this->localizeOptions($options);

            try {
                // Ensure the DB has the needed BR columns (CPF, CNPJ, etc.)
                $this->createCustomerColumns($brazilCustomer);
            } catch (Exception $e) {
                Logger::addLog('AGCUSTOMERS: falha ao criar colunas padrão Brasil - ' . $e->getMessage(), 3);
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'br_columns_failed']);
                exit();
            }

            Logger::addLog('AGCUSTOMERS: campos padrão Brasil aplicados e colunas verificadas/criadas', 1);

            Configuration::updateValue('AGCUSTOMERS_CONFIG', serialize($options));

            echo json_encode(['success' => true]);
            exit();
        } elseif (Tools::getIsSet('resetOverrides')) {
            $this->uninstallOverrides();
            $this->installOverrides();
            
            echo json_encode([
                'success' => true,
            ]);
            exit();
        }
        $this->context->controller->addJs([
            $this->_path . '/views/js/loadingOverlay.js',
            $this->_path . '/views/js/config.js'
        ]);

        if (Tools::isSubmit('agcustomers-submit')) {
            Configuration::updateValue('AGCUSTOMERS_VALIDATION_MODE', Tools::getValue('AGCUSTOMERS_VALIDATION_MODE'));
            $app_id = Tools::getValue('agcustomers_facebook_app_id');
            $app_secret = Tools::getValue('agcustomers_facebook_app_secret');
            $og_version = Tools::getValue('agcustomers_facebook_og_version');
            $google_client_id = Tools::getValue('agcustomers_google_client_id');
            $google_prompt = Tools::getValue('agcustomers_google_prompt');


            $google_type_btn = Tools::getValue('agcustomers_google_type_btn');
            $google_theme_btn = Tools::getValue('agcustomers_google_theme_btn');
            $google_size_btn = Tools::getValue('agcustomers_google_size_btn');
            $google_text_btn = Tools::getValue('agcustomers_google_text_btn');
            $google_shape_btn = Tools::getValue('agcustomers_google_shape_btn');
            $google_logo_btn = Tools::getValue('agcustomers_google_logo_btn');
            
            Configuration::updateValue('AGCUSTOMERS_GOOGLE_TYPE_BTN', $google_type_btn);
            Configuration::updateValue('AGCUSTOMERS_GOOGLE_THEME_BTN', $google_theme_btn);
            Configuration::updateValue('AGCUSTOMERS_GOOGLE_SIZE_BTN', $google_size_btn);
            Configuration::updateValue('AGCUSTOMERS_GOOGLE_TEXT_BTN', $google_text_btn);
            Configuration::updateValue('AGCUSTOMERS_GOOGLE_SHAPE_BTN', $google_shape_btn);
            Configuration::updateValue('AGCUSTOMERS_GOOGLE_LOGO_BTN', $google_logo_btn);

            Configuration::updateValue('AGCUSTOMERS_GOOGLE_PROMPT', $google_prompt);
            Configuration::updateValue('AGCUSTOMERS_GOOGLE_CLIENT_ID', $google_client_id);
            Configuration::updateValue('AGCUSTOMERS_FACEBOOK_APP_ID', $app_id);
            Configuration::updateValue('AGCUSTOMERS_FACEBOOK_SECRET_KEY', $app_secret);
            Configuration::updateValue('AGCUSTOMERS_FACEBOOK_OG_VERSION', $og_version);

            Configuration::updateValue('AGCUSTOMERS_INSERT_CUSTOMER_FIELDS', Tools::getValue('insert_customer_fields'));
            Configuration::updateValue('AGCUSTOMERS_INSERT_RG_FIELD', Tools::getValue('insert_rg_field'));
            Configuration::updateValue('AGCUSTOMERS_INSERT_COMPANY_FIELDS', Tools::getValue('insert_company_fields'));
            Configuration::updateValue('AGCUSTOMERS_INSERT_NUMBER_FIELD', Tools::getValue('insert_number_field'));
            Configuration::updateValue('AGCUSTOMERS_REQUIRE_NUMBER_FIELD', Tools::getValue('require_number_field'));
            Configuration::updateValue('AGCUSTOMERS_FORCE_B2B_FIELDS', Tools::getValue('force_b2b_fields'));
            $this->getDistrictMapping()->mapsTo(Tools::getValue('agcustomers_district'));

            // Keep customer fields ordered when new fields are inserted
            $config = $_POST['AGCUSTOMERS_CONFIG'];
            $customer = $config['fields']['customer'];
            ksort($customer);

            // Validate the submitted data for duplicate field names
            $names = [];
            $errors = false;
            foreach ($customer as $input) {
                if (isset($names[$input['name']])) {
                    $this->context->controller->errors[] = $this->trans('Field name "%name%" is duplicated.', ['%name%' => $input['name']], 'Modules.Agcustomers.Admin');
                    $errors = true;
                    continue;
                }

                if (preg_match("/^[a-zA-Z_][a-zA-Z0-9_]*$/", $input['name']) !== 1) {
                    $this->context->controller->errors[] = $this->trans('Invalid column name "%name%". Use only letters and numbers!', ['%name%' => $input['name']], 'Modules.Agcustomers.Admin');
                    $errors = true;
                    continue;   
                }


                if ($input['type'] == 'checkbox') {
                    foreach ($input['options'] as $option) {
                        if (preg_match("/^[a-zA-Z_][a-zA-Z0-9_]*$/", $input['name'] . $option['value']) !== 1) {
                            $this->context->controller->errors[] = $this->trans('Invalid column name "%name%". Use only letters and numbers!', ['%name%' => $input['name'] . $option['value']], 'Modules.Agcustomers.Admin');
                            $errors = true;
                            continue;   
                        }
                    }
                }
            }

            if ($errors) {
                goto render_form;
            }
     
            $config['fields']['customer'] = $customer;

            Configuration::updateValue('AGCUSTOMERS_CONFIG', serialize($config));
            $this->createCustomerColumns($customer);
            $this->context->controller->confirmations[] = $this->trans('Settings saved successfully!', [], 'Modules.Agcustomers.Admin');
        }

        render_form:
        $config = $this->getOptions();
        $languages = Language::getLanguages(false);
        $customerGroups = Group::getGroups($this->context->language->id);
        agcliente::prepareConfigHelpTab($this->name);
        
        $this->context->smarty->assign([
            'form_action' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name,
            'facebook_app_id' => Configuration::get('AGCUSTOMERS_FACEBOOK_APP_ID'),
            'google_client_id' => Configuration::get('AGCUSTOMERS_GOOGLE_CLIENT_ID'),
            'google_prompt' => Configuration::get('AGCUSTOMERS_GOOGLE_PROMPT'),
            'google_type_btn' => Configuration::get('AGCUSTOMERS_GOOGLE_TYPE_BTN') == '' ? 'standard': Configuration::get('AGCUSTOMERS_GOOGLE_TYPE_BTN'),
            'google_theme_btn' => Configuration::get('AGCUSTOMERS_GOOGLE_THEME_BTN') == '' ? 'outline': Configuration::get('AGCUSTOMERS_GOOGLE_THEME_BTN'),
            'google_size_btn' => Configuration::get('AGCUSTOMERS_GOOGLE_SIZE_BTN') == '' ? 'medium': Configuration::get('AGCUSTOMERS_GOOGLE_SIZE_BTN'),
            'google_text_btn' => Configuration::get('AGCUSTOMERS_GOOGLE_TEXT_BTN') == '' ? 'signin_with': Configuration::get('AGCUSTOMERS_GOOGLE_TEXT_BTN'),
            'google_shape_btn' => Configuration::get('AGCUSTOMERS_GOOGLE_SHAPE_BTN') == '' ? 'rectangular': Configuration::get('AGCUSTOMERS_GOOGLE_SHAPE_BTN'),
            'google_logo_btn' => Configuration::get('AGCUSTOMERS_GOOGLE_LOGO_BTN') == '' ? 'left': Configuration::get('AGCUSTOMERS_GOOGLE_LOGO_BTN'),
            'facebook_app_secret' => Configuration::get('AGCUSTOMERS_FACEBOOK_SECRET_KEY'),
            'facebook_og_version' => Configuration::get('AGCUSTOMERS_FACEBOOK_OG_VERSION'),
            'module' => $this,
            'authenticated' => (int) $this->isLicensedModeEnabled(),
            'agcustomers_validation_mode' => Configuration::get('AGCUSTOMERS_VALIDATION_MODE'),

            // 'force_b2b_fields' => Configuration::get('AGCUSTOMERS_FORCE_B2B_FIELDS'),

            // 'insert_customer_fields' => Configuration::get('AGCUSTOMERS_INSERT_CUSTOMER_FIELDS'),
            // 'insert_rg_field' => Configuration::get('AGCUSTOMERS_INSERT_RG_FIELD'),
            // 'insert_company_fields' => Configuration::get('AGCUSTOMERS_INSERT_COMPANY_FIELDS'),
            // 'insert_number_field' => Configuration::get('AGCUSTOMERS_INSERT_NUMBER_FIELD'),
            // 'require_number_field' => Configuration::get('AGCUSTOMERS_REQUIRE_NUMBER_FIELD'),

            'link_import_fkcustomers' => $this->context->link->getModuleLink($this->name, 'importFromFkCustomers'),
            'link_import_djtal' => $this->context->link->getModuleLink($this->name, 'importFromDjtal'),

            'config' => $config,
            'languages' => $languages,
            'customerGroups' => $customerGroups,
            'modules_path' => _PS_MODULE_DIR_,
            'current_id_lang' => (int)$this->context->language->id,

            'agti_cep_provider' => $this->getDefaultCepProvider(),
            'ps17' => ($this->ps17 || @$this->ps8)
        ]);

        $redirectLinks = [
            Context::getContext()->link->getModuleLink('agcustomers', 'fbRedirect', ['back' => 'authentication']),
            Context::getContext()->link->getModuleLink('agcustomers', 'fbRedirect', ['back' => 'order']),
            Context::getContext()->link->getModuleLink('agcustomers', 'fbRedirect', ['back' => 'cart']),
            Context::getContext()->link->getModuleLink('agcustomers', 'fbRedirect', ['back' => 'order-opc'])
        ];

        // Choose tutorial file according to current language (pt* -> BR tutorial, otherwise EN)
        $tutorialTpl = _PS_MODULE_DIR_ . $this->name . '/views/templates/admin/tutorial_en.tpl';
        $isPt = isset($this->context->language->iso_code) && preg_match('/^pt/i', $this->context->language->iso_code);
        if ($isPt) {
            $tutorialTpl = _PS_MODULE_DIR_ . $this->name . '/views/templates/admin/tutorial_br.tpl';
        }

        $this->context->smarty->assign([
            'tutorial_file' => $tutorialTpl,
            'img01' => Media::getMediaPath($this->_path . '/views/img/tutorial_01_br.png'),
            'img02' => Media::getMediaPath($this->_path . '/views/img/tutorial_02_br.png'),
            'img03' => Media::getMediaPath($this->_path . '/views/img/tutorial_03_br.png'),
            'img04' => Media::getMediaPath($this->_path . '/views/img/tutorial_04_br.png'),
            'shop_url' => $this->context->shop->getBaseUrl(),

            'redirectLinks' => $redirectLinks
        ]);

        $this->context->controller->addJs([
            $this->_path . '/views/js/config.js',
            $this->_path . '/views/js/riot_compiler.min.js'
        ]);


        $this->context->controller->addCss([
            $this->_path . 'views/css/loadingOverlay.css',
            $this->_path . '/views/css/configuration.css'
        ]);

        $html = $this->display(_PS_MODULE_DIR_ . $this->name, 'views/templates/admin/configuration.tpl');
        return $html . $this->display(_PS_MODULE_DIR_ . $this->name, 'views/templates/admin/ps-tags.tpl');
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addJs([
            $this->_path . '/views/js/loadingOverlay.js',
            $this->_path . '/views/js/jquery.mask.min.js'
        ]);

        if ($this->ps16) {
            //trata do formulário de cadastro
            $this->context->controller->addJs($this->_path . '/views/js/registration.js');
        } else {
            if (in_array($this->context->controller->php_self, ['identity', 'authentication', 'registration', 'address'])) {
                $this->context->controller->addJs($this->_path . '/views/js/registration.ps17.js');
            }

            if (in_array($this->context->controller->php_self, ['order', 'order-opc']) && !Module::isEnabled('agcheckout')) {
                $this->context->controller->addJs($this->_path . '/views/js/registration.ps17.js');
            }
        }

        //verifica se a página atual é o formulário de cadastro
        $reflectedClass = new ReflectionClass($this->context->controller);
        $property = $reflectedClass->getProperty('template');
        $property->setAccessible(true);

        $template = $property->getValue($this->context->controller);        

        $display_facebook_button = false;
        if (($this->ps17 || @$this->ps8) && ($template == 'customer/registration.tpl' || $template == "customer/authentication.tpl" || $this->context->controller->php_self === 'order' || $this->context->controller->php_self === 'identity')) {
            $display_facebook_button = true;
        }

        if ($this->ps16 && ($this->context->controller->php_self === 'authentication' || $this->context->controller->php_self === 'order-opc')) {
            $display_facebook_button = true;
        }

        if (($this->ps17 || @$this->ps8)) {
            $this->context->controller->addCss([
                $this->_path . 'views/css/loadingOverlay.css',
                $this->_path . 'views/css/front.css'
            ]);
        }

        if ($display_facebook_button) {
            $fb_data = [
                'app_id' => Configuration::get('AGCUSTOMERS_FACEBOOK_APP_ID'),
                'app_secret' => Configuration::get('AGCUSTOMERS_FACEBOOK_SECRET_KEY'),
                'default_graph_version' => Configuration::get('AGCUSTOMERS_FACEBOOK_OG_VERSION')
            ];

            if ($fb_data['app_id'] && $fb_data['app_secret'] && $fb_data['default_graph_version']) {
                $fb = new Facebook\Facebook([
                    'app_id' => Configuration::get('AGCUSTOMERS_FACEBOOK_APP_ID'),
                    'app_secret' => Configuration::get('AGCUSTOMERS_FACEBOOK_SECRET_KEY'),
                    'default_graph_version' => Configuration::get('AGCUSTOMERS_FACEBOOK_OG_VERSION')
                ]);

                $helper = $fb->getRedirectLoginHelper();

                $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

                $permissions = ['email']; // Optional permissions

                $loginUrl = $helper->getLoginUrl($this->context->link->getModuleLink($this->name, 'fbRedirect', array('back' => $this->context->controller->php_self)), $permissions);
                
           
                Media::addJsDef([
                    'agcustomers_authentication_url' => $loginUrl,
                    'agcustomers_facebook_text' => $this->trans('Enter with Facebook', [], 'Modules.Agcustomers.Shop')
                ]);

                $this->context->smarty->assign([
                    'agcustomers_authentication_url' => $loginUrl,
                    'agcustomers_facebook_text' => $this->trans('Enter with Facebook', [], 'Modules.Agcustomers.Shop')
                ]);

                //não exibe o botão de login pelo facebook na tela de informações pessoais ou se o usuário estiver na tela de cadastro
                //já tendo sido redirecionado pelo facebook
                if ($this->context->controller->php_self !== 'identity' || !Tools::getIsSet('firstname')) {
                    if (($this->ps17 || @$this->ps8)) {
                        $this->context->controller->addJs([
                            $this->_path . '/views/js/fb_login.js',
                        ]);
                    } elseif ($this->ps16) {
                        $this->context->controller->addJs([
                            $this->_path . '/views/js/fb_login.ps16.js',
                        ]);
                    }
                }
            }
        }

        if(Configuration::get('AGCUSTOMERS_GOOGLE_CLIENT_ID') != ''){
            $this->context->controller->addJs([
                $this->_path . '/views/js/google_login.js'
            ]);
        }
        
        if ($this->ps16) {
            $this->context->controller->addCss(
                $this->_path . '/views/css/front.ps16.css'
            );
        }

        $options = $this->getOptions();
        $customer_data = AgCustomerData::getCustomerData();

        Media::addJsDef([
            'agcustomers_address_autocomplete' => true,
            'agcustomers_district_field' => 'address2',
            'agcustomers_logged' => $this->context->customer->id == NULL ? false:true,
            'agcustomers_google_client_id' => Configuration::get('AGCUSTOMERS_GOOGLE_CLIENT_ID'),
            'agcustomers_google_prompt' => Configuration::get('AGCUSTOMERS_GOOGLE_PROMPT'),
            'agcustomers_address_search_url' => $this->context->link->getModuleLink('agcustomers', 'AddressSearch'),
            'agcustomers_insert_customer_fields' => (bool) Configuration::get('AGCUSTOMERS_INSERT_CUSTOMER_FIELDS'),
            'agcustomers_insert_rg_field' => (bool) Configuration::get('AGCUSTOMERS_INSERT_RG_FIELD'),
            'agcustomers_insert_company_fields' => (bool) Configuration::get('AGCUSTOMERS_INSERT_COMPANY_FIELDS'),
            'agcustomers_insert_number_field' => true,
            'agcustomers_require_number_field' => (bool) Configuration::get('AGCUSTOMERS_REQUIRE_NUMBER_FIELD'),
            'agcustomers_number_translation' => $this->trans('Number', [], 'Modules.Agcustomers.Shop'),
            'agcustomers_cpf' =>    $this->context->customer->cpf,
            'agcustomers_rg' => $this->context->customer->rg,
            'agcustomers_company_name' => $this->context->customer->company_name,
            'agcustomers_cnpj' => $this->context->customer->cnpj,
            'agcustomers_ie' => $this->context->customer->ie,
            'agcustomers_force_b2b_fields' => (bool) Configuration::get('AGCUSTOMERS_FORCE_B2B_FIELDS'),
            'agcustomers_mask_birthday_input' => true,
            'agcustomers_mask_birthday_format' => $this->context->language->date_format_lite,
            'agcustomers_mask_postcode_input' => true,

            'agcustomers_required_phone' => (bool)Module::isInstalled('agpagseguro') && (bool)Module::isEnabled('agpagseguro'),
            'agcustomers_required_birthday'     => (bool)Module::isInstalled('agpagseguro') && (bool)Module::isEnabled('agpagseguro'),
            'agcustomers' => [
                'configs_btn' => [
                    'google_type_btn'  => Configuration::get('AGCUSTOMERS_GOOGLE_TYPE_BTN'),
                    'google_theme_btn' => Configuration::get('AGCUSTOMERS_GOOGLE_THEME_BTN'),
                    'google_size_btn'  => Configuration::get('AGCUSTOMERS_GOOGLE_SIZE_BTN'),
                    'google_text_btn'  => Configuration::get('AGCUSTOMERS_GOOGLE_TEXT_BTN'),
                    'google_shape_btn' => Configuration::get('AGCUSTOMERS_GOOGLE_SHAPE_BTN'),
                    'google_logo_btn'  => Configuration::get('AGCUSTOMERS_GOOGLE_LOGO_BTN')
                ],
                'urls' => [
                    'ajaxRequests' => $this->context->link->getModuleLink('agcustomers', 'ajaxRequests'),
                    'facebook' => $this->context->link->getModuleLink('agcustomers', 'facebook'),
                    'google' => $this->context->link->getModuleLink('agcustomers', 'google'),
                    'create_acount' => $this->context->link->getPageLink('authentication', true)."?create_account=1" ,
                    'acustomers_facebook_image' => $this->context->shop->getBaseURL(true) .'modules/' . $this->name . '/views/img/facebook_logo.png'
                ],
                'translations' => [
                    'type_person' => $this->trans('Customer type', [], 'Modules.Agcustomers.Shop'),
                    'saving' => $this->trans('Saving...', [], 'Modules.Agcustomers.Shop'),
                    'saved_ok' => $this->trans('Data updated successfully! The page will reload.', [], 'Modules.Agcustomers.Shop'),
                    'please_fix' => $this->trans('Please correct the following errors:', [], 'Modules.Agcustomers.Shop'),
                    'unexpected_error' => $this->trans('An unexpected error occurred. Try again.', [], 'Modules.Agcustomers.Shop'),
                    'save_and_continue' => $this->trans('Save and continue', [], 'Modules.Agcustomers.Shop'),
                    'number_label' => $this->trans('Number', [], 'Modules.Agcustomers.Shop'),
                    'cpf' => $this->trans('CPF', [], 'Modules.Agcustomers.Admin'),
                    'rg' => $this->trans('RG', [], 'Modules.Agcustomers.Admin'),
                    'company_name' => $this->trans('Business name', [], 'Modules.Agcustomers.Admin'),
                    'cnpj' => $this->trans('CNPJ', [], 'Modules.Agcustomers.Admin'),
                    'ie' => $this->trans('State registration', [], 'Modules.Agcustomers.Admin'),
                    'no_number' => $this->trans('N/A', [], 'Modules.Agcustomers.Admin'),
                    'already_registered' => $this->trans('already registered.', [], 'Modules.Agcustomers.Shop'),
                    'fill_all_required' => $this->trans('Please make sure you have filled in all required fields.', [], 'Modules.Agcustomers.Shop'),
                    'fix_to_continue' => $this->trans('Fix your registration to continue', [], 'Modules.Agcustomers.Shop'),
                    // Age validation messages for client-side usage
                    'age_min_msg' => $this->trans('You must be at least %min% years old.', [], 'Modules.Agcustomers.Shop'),
                    'age_max_msg' => $this->trans('The maximum allowed age is %max% years.', [], 'Modules.Agcustomers.Shop'),
                    // Back-office config and general texts
                    'new_field' => $this->trans('New field', [], 'Modules.Agcustomers.Admin'),
                    'cannot_remove_default_field' => $this->trans('You cannot remove one of the module default fields.', [], 'Modules.Agcustomers.Admin'),
                    'confirm_delete_field' => $this->trans('Do you really want to delete this customer field? This operation is irreversible and will be processed after saving the form.', [], 'Modules.Agcustomers.Admin'),
                    'confirm_delete_type_person' => $this->trans('This operation is irreversible. Do you really want to delete this person type?', [], 'Modules.Agcustomers.Admin'),
                    'reset_fields_confirm' => $this->trans('Do you really want to reset the fields to the module initial configuration? This operation is irreversible.', [], 'Modules.Agcustomers.Admin'),
                    'reset_overrides_confirm' => $this->trans('Do you really want to reset the module overrides? This operation is irreversible.', [], 'Modules.Agcustomers.Admin'),
                    'brazil_defaults_confirm' => $this->trans('Apply Brazil default settings? This will preconfigure common Brazilian fields.', [], 'Modules.Agcustomers.Admin'),
                    'social_login_info' => $this->trans('Use your account to make identification easier. Only your first name, last name and email address will be stored.', [], 'Modules.Agcustomers.Shop')
                ],
                'is_auth' => (bool) @$this->context->controller->auth,
                'config' => $options['config'],
                'fields' => $options['fields'],
                'type_persons' => $options['type_person'],
                'id_lang' => $this->context->language->id,
                'customer_data' => $customer_data,
                'ps178' => version_compare(_PS_VERSION_, '1.7.8', '>')
            ]
        ]);

        if (Tools::getValue('id_address')) {
            $address = new Address(Tools::getValue('id_address'));
            Media::addJsDef([
                'agcustomers_address_number' => $address->number
            ]);
        }

        $this->context->controller->addJs($this->_path . '/views/js/address_autocomplete.js');

        if (Tools::getValue('agcustomers_error') === 'user_denied') {
            $this->context->controller->errors[] = $this->l('You did\'t authorize the Facebook app', 'base');
        }

         $customer = $this->context->customer;
         //no PS 1.7.8 não podemos exibir mensagens de erro na página do carrinho ou o botão de proceder ao checkout é desativado
        if (version_compare(_PS_VERSION_, '1.7.8', '<')) {
            if (Configuration::get('AGCUSTOMERS_VALIDATION_MODE') === 'text' && Validate::isLoadedObject($customer) && !$this->isValidCustomer($customer->id)) {
                $this->context->controller->errors[] = $this->displayInvalidCustomerWarning();
            }
        } elseif (Configuration::get('AGCUSTOMERS_VALIDATION_MODE') === 'text' && Validate::isLoadedObject($customer) && $this->context->controller instanceof OrderController && !$this->isValidCustomer($customer->id)) {
            $this->context->controller->errors[] = $this->displayInvalidCustomerWarning();
        }

        if (($this->ps17 || @$this->ps8)) {
            //validação dos endereços do carrinho

            //checkout
            if (isset($this->context->controller->php_self) && $this->context->controller->php_self === 'order') {
                $cart = $this->context->cart;
                if (Validate::isLoadedObject($cart) && $cart->id_address_delivery && !$this->isValidAddress($cart->id_address_delivery)) {
                    $this->context->controller->errors[] = $this->displayInvalidAddressWarning($cart, 'delivery');
                }

                if (Validate::isLoadedObject($cart) && $cart->id_address_invoice && $cart->id_address_delivery != $cart->id_address_invoice  && !$this->isValidAddress($cart->id_address_invoice)) {
                    $this->context->controller->errors[] = $this->displayInvalidAddressWarning($cart, 'invoice');
                }
            }
            //página de endereços ou checkout
            if (isset($this->context->controller->php_self) && in_array($this->context->controller->php_self, ['address', 'order'])) {
                $this->context->controller->addJs([$this->_path . 'views/js/address.js']);
            }
        }

        if (Configuration::get('AGCUSTOMERS_VALIDATION_MODE') === 'modal' && $this->context->customer->isLogged() && in_array($this->context->controller->php_self, ['order', 'order-opc'])) {
            $errors = $this->getCustomerValidationErrors();

            if (count($errors) > 0) {
                $this->checkoutErrors = $errors;

                Media::addJsDef([
                    'agcustomer_checkout_errors' => $errors,
                    'agcustomer_checkout_form_action' => $this->context->link->getModuleLink($this->name, 'ajax', [], true)
                ]);

                $this->context->controller->addJs($this->_path . 'views/js/checkout_validation.js');
                $this->context->controller->addCss($this->_path . 'views/css/checkout_validation.css');
            }
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        $return = '<script type="text/javascript">';
        if ($this->context->controller->controller_name === 'AdminOrders' && (Tools::getIsSet('id_order') || Tools::getValue('id_order'))) {
            $this->context->controller->addJs([
                $this->_path . '/views/js/admin_orders.js'
            ]);

            $order = new Order(Tools::getValue('id_order'));
            $customer_data = AgCustomerData::getCustomerData($order->id_customer);

            $address_shipping = new Address($order->id_address_delivery);
            $state_shipping = new State($address_shipping->id_state);
            $address_shipping->state = $state_shipping->name;

            $address_invoice = new Address($order->id_address_invoice);
            $state_invoice = new State($address_shipping->id_state);
            $address_invoice->state = $state_invoice->name;

            $return .= "var agcustomers_address_shipping = " . json_encode($address_shipping) . ";";
            $return .= "var agcustomers_address_invoice = " . json_encode($address_invoice) . ";";
            $return .= "var agcustomers_data = '" . json_encode($customer_data) . "';";
        } elseif ($this->context->controller->controller_name === 'AdminOrders' && !Tools::getValue('id_order')) {
            $this->context->controller->addJs([
                $this->_path . '/views/js/custom_preview_order.js'
            ]);

            $return .= "var agcustomer_token='" . Tools::getAdminTokenLite('AdminAgCustomersPreview') . "';";
        } elseif ($this->context->controller->controller_name === 'AdminCustomers') {
            $customer = $this->context->customer;

            if (Validate::isLoadedObject($customer)) {
                $return .= "var agcustomers_cpf = '{$customer->cpf}';";
                $return .= "var agcustomers_rg = '{$customer->rg}';";
                $return .= "var agcustomers_cnpj = '{$customer->cnpj}';";
                $return .= "var agcustomers_ie = '{$customer->ie}';";
                $return .= "var agcustomers_company_name = '{$customer->company_name}';";
            } else {
                $return .= "var agcustomers_cpf = '';";
                $return .= "var agcustomers_rg = '';";
                $return .= "var agcustomers_cnpj = '';";
                $return .= "var agcustomers_ie = '';";
                $return .= "var agcustomers_company_name = '';";
            }

            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $this->context->controller->addJquery();
                $this->context->controller->addJS([
                    $this->_path . '/views/js/jquery.mask.min.js',
                    $this->_path . '/views/js/admin_customers_view.1.6.js',
                    $this->_path . '/views/js/admin_customers_edit.1.6.js'
                ]);
            } elseif (version_compare(_PS_VERSION_, '1.7.6', '<')) {
                $this->context->controller->addJquery();
                $this->context->controller->addJS([
                    $this->_path . '/views/js/jquery.mask.min.js',
                    $this->_path . '/views/js/admin_customers_view.js',
                    $this->_path . '/views/js/admin_customers_edit.js'
                ]);
            } else {
                Media::addJsDef([
                    'agcustomers_id_customer' => Tools::getValue('id_customer'),
                ]);

                $js = [
                    $this->_path . '/views/js/jquery.mask.min.js',
                    $this->_path . '/views/js/admin_customers_view.1.7.6.js',
                ];

                $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                $isCustomerCreateOrEdit = Tools::getValue('id_customer')
                    || strpos($requestUri, '/customers/new') !== false
                    || (bool) preg_match('#/customers/[0-9]+/edit#', $requestUri);

                if ($isCustomerCreateOrEdit) {
                    $js[] = $this->_path . '/views/js/admin_customers_edit.1.7.6.js';
                }

                $this->context->controller->addJS($js);
            }
        }
        

        $return .= "var agcustomers_url_load_options = '" . $this->context->link->getAdminLink('AdminAgCustomersLoadOptions') . "'";
        $return .= '</script>';

        return $return;
    }

    public function hookActionAdminControllerSetMedia()
    {
        $jsvar = [];

        if ($this->context->controller->controller_name === 'AdminAddresses') {
            $this->context->controller->addJs(
                $this->_path . 'views/js/admin_address.js'
            );

            if (Tools::getValue('submitFormAjax') && Tools::getValue('liteDisplaying')) {
                //iframe de edição do endereço dentro do pedido
                preg_match("/\/sell\/addresses\/order\/([0-9]*)\/(invoice|delivery)/", $_SERVER['REQUEST_URI'], $matches);
                $id_order = $matches[1];
                $objOrder = new Order($id_order);

                if ($matches[2] == 'invoice') {
                    $address = new Address($objOrder->id_address_invoice);
                } else {
                    $address = new Address($objOrder->id_address_delivery);
                }
            } else {
                //página de endereço padrão
                $address = new Address(Tools::getValue('id_address'));
            }
            $jsvar['address']['number'] = (int)$address->number;
        }

        Media::addJsDef(['agcustomers' => $jsvar]);
    }

    public function hookDisplayAdminOrderContentOrder($data)
    {
        if (Configuration::get('AGCUSTOMERS_1_4_5')) {
            $insert_customer_fields = Configuration::get('AGCUSTOMERS_INSERT_CUSTOMER_FIELDS');
            $insert_rg_field = Configuration::get('AGCUSTOMERS_INSERT_RG_FIELD');
            $insert_company_fields = Configuration::get('AGCUSTOMERS_INSERT_COMPANY_FIELDS');
        } else {
            //retrocompatibility
            $insert_additional_fields = Configuration::get('insert_additional_fields');
        }


        if ((@$insert_additional_fields || @$insert_customer_fields || @$insert_rg_field || @$insert_company_fields) === false) {
            return false;
        }

        $customer = $data['customer'];

        $return = '';
        $return .= '<script type="text/javascript">';
        $return .= 'var agcustomer_cpf="' . $customer->cpf . '";';
        $return .= 'var agcustomer_rg="' . $customer->rg . '";';
        $return .= 'var agcustomer_company_name="' . $customer->company_name . '";';
        $return .= 'var agcustomer_cnpj="' . $customer->cnpj . '";';
        $return .= 'var agcustomer_ie="' . $customer->ie . '";';
        $return .= '</script>';

        return $return;
    }

    //adiciona os campos de cpf, rg, nome da empresa e demais no formulário de cadastro
    public function hookAdditionalCustomerFormFields($params)
    {
        if (Module::isInstalled('onepagecheckoutps') && Module::isEnabled('onepagecheckoutps') && @$this->context->controller->php_self == 'order') {
            return;
        }

        $fields = $this->getFields();
        $options = $this->getOptions();

        $return = [];


        $type_persons = $this->getOptions()['type_person'];
        $active_type_persons = $this->getActiveTypePersons();

        // if (count($active_type_persons) > 1) {
            $obj = (new FormField)
                ->setName('person_type')
                ->setType('radio-buttons');

            $label = $this->l('Person Type', 'base');
            $obj->setLabel($label);


            //se houver só um tipo de pessoa, ele não deve ser marcado como origatório, pois a opção de tipo de pessoa não é exibida no front,
            //e campos ocultos de formulários não podem ser obrigatórios.
            if (count($active_type_persons) > 1) {
                $obj->setRequired(true);
            }

                
            foreach ($active_type_persons as $type_person) {
                if (is_array($type_person['label'])) {
                    if (isset($type_person['label'][$this->context->language->id])) {
                        $label = $type_person['label'][$this->context->language->id];
                    } else {
                        $label = array_pop($type_person['label']);
                    }
                } else {
                    $label = $type_person['label'];
                }

                $obj->addAvailableValue($type_person['name'], $label);
            }
            $return[] = $obj;
        // } elseif (count($active_type_persons)) {
        //     $obj = (new FormField)
        //         ->setName('person_type')
        //         ->setType('hidden')
        //         ->setLabel('teste')
        //         ->setValue($active_type_persons[0]['name']);
        //     $return[] = $obj;
        // }

        if (version_compare(_PS_VERSION_, '1.7.7', '>=')) {
            $default_fields = $params['fields'];
        } else {
            $default_fields = [
                'firstname' => [],
                'lastname' => [],
                'birthday' => []                
            ];
        }
        foreach ($fields['customer'] as $field) {
            if (!@$field['insert']) { 
                continue;
            }

            if (isset($default_fields[$field['name']])) {
                $obj = &$default_fields[$field['name']];
            } else {
                if ((isset($field['type']) && $field['type'] == 'text') || !isset($field['type'])) {
                    $obj = (new FormField)
                    ->setName($field['name'])
                    ->setType('text');

                    //se o formulário de criação de conta tiver sido submetido não marca o campo como obrigatório,
                    //porque isso bloqueará o cadastro
                    if (!Tools::isSubmit('submitCreate')) {
                        $obj->setRequired(true);
                    }
                } elseif ($field['type'] == 'select') {
                    $obj = (new FormField)
                        ->setName($field['name'])
                        ->setType('select');

                    if (!Tools::isSubmit('submitCreate')) {
                        $obj->setRequired(true);
                    }

                    foreach ($field['options'] as $option) {
                        $obj->addAvailableValue($option['value'], $option['text']);
                    }
                } elseif ($field['type'] == 'checkbox') {
                    foreach ($field['options'] as $option) {
                        $input_name = $field['name'] . $option['value'];
                        $obj = (new FormField)
                            ->setLabel($option['text'])
                            ->setName($input_name)
                            ->setType('checkbox')
                            ->setValue(Db::getInstance()->getValue((new DbQuery)->from('customer')->select($input_name)->where('id_customer=' . $this->context->customer->id)));

                        if (!@$field['edit_fo'] && ($this->context->customer->{$field['name']} || Tools::getValue($field['name'])) && method_exists($obj, 'setReadOnly')) {
                            $obj->setReadOnly(true);
                        }

                        $return[] = $obj;
                    }
                    continue;
                }
            }

            if (!@$field['edit_fo'] && ($this->context->customer->{$field['name']} || Tools::getValue($field['name'])) && method_exists($obj, 'setReadOnly')) {
                $obj->setReadOnly(true);
            }
            
            
            if (is_array($field['label'])) { 
                if (isset($field['label'][$this->context->language->id])) {
                    $label = $field['label'][$this->context->language->id];
                } else {
                    $label = array_pop($field['label']);
                }
            } else {
                $label = $field['label'];
            }
            

            if (!isset($default_fields[$field['name']])) {
                $obj->setLabel($label);
                $return[] = $obj;
            }
        }

        return $return;
    }

    public function hookActionAdminAddressesFormModifier($params)
    {
        if ($this->context->language->iso_code == 'br' && Configuration::get('AGCUSTOMERS_INSERT_NUMBER_FIELD')) {
            $fields = &$params['fields'];
            $fields[0]['form']['input'][] = array(
                'type' => 'text',
                'name' => 'number',
                'label' => $this->trans('Number', [], 'Modules.Agcustomers.Admin'),
                'required' => (bool) Configuration::get('AGCUSTOMERS_REQUIRE_NUMBER_FIELD'),
                'col' => 1
            );

            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                $params['fields_value']['number'] = $params['object']->number;
            } else {
                $address = new Address(Tools::getValue('id_address'));
                $params['fields_value']['number'] = $address->number;
            }
        }
    }

    public function hookActionObjectCustomerAddAfter($params)
    {
        $customer = $params['object'];

        //cliente cadastrado pelo formulário do BO
        if (@$this->context->controller->controller_name === 'Admin' && !$customer->cpf) {
            $customer->cpf = Tools::getValue('cpf');
            $customer->cnpj = Tools::getValue('cnpj');
            $customer->rg = Tools::getValue('rg');
            $customer->ie = Tools::getValue('ie');
            $customer->company_name = Tools::getValue('company_name');

            $customer->update();
        }
    }

    public function hookActionObjectCustomerUpdateAfter($params)
    {
        $customer = $params['object'];

        //cliente atualizado pelo formulário do BO
        if (@$this->context->controller->controller_name === 'Admin') {
            static $saving;

            if ($saving) {
                return;
            }

            $saving = 1;

            if (Tools::getIsSet('cpf')) {
                $customer->cpf = Tools::getValue('cpf');
            }

            if (Tools::getIsSet('cnpj')) {
                $customer->cnpj = Tools::getValue('cnpj');
            }

            if (Tools::getIsSet('rg')) {
                $customer->rg = Tools::getValue('rg');
            }

            if (Tools::getIsSet('ie')) {
                $customer->ie = Tools::getValue('ie');
            }

            if (Tools::getIsSet('company_name')) {
                $customer->company_name = Tools::getValue('company_name');
            }


            $customer->update();
        }
    }

    public function hookPaymentTop()
    {
        if (
            file_exists(_PS_MODULE_DIR_ . 'onepagecheckoutps/onepagecheckoutps.php') &&
            Module::isInstalled('onepagecheckoutps') &&
            Module::isEnabled('onepagecheckoutps')
        ) {
            return;
        }

        if (!$this->isValidCustomer($this->context->customer->id)) {
            Tools::redirect($this->context->link->getPageLink('identity'));
        }

        if (!$this->isValidAddress($this->context->cart->id_address_delivery)) {
            $this->context->controller->errors[] = $this->trans('Your delivery address is incomplete.', [], 'Modules.Agcustomers.Shop');
            $this->context->controller->redirectWithNotifications($this->context->link->getPageLink('address', true, null, ['id_address' => $this->context->cart->id_address_delivery]));
        }

        if (!$this->isValidAddress($this->context->cart->id_address_invoice)) {
            $this->context->controller->errors[] = $this->trans('Your invoice address is incomplete.', [], 'Modules.Agcustomers.Shop');
            $this->context->controller->redirectWithNotifications($this->context->link->getPageLink('address', true, null, ['id_address' => $this->context->cart->id_address_invoice]));
        }
    }


    public function isValidCustomer($id_customer)
    {
        trigger_error('The method ' . __METHOD__ . ' is deprecated. Use getCustomerValidationErrors() instead.', E_USER_DEPRECATED);
        $sql = new DbQuery;
        $sql->from('customer')->where('id_customer=' . (int)$id_customer);
        $customer_data = Db::getInstance()->getRow($sql);

        $active_type_persons = $this->getActiveTypePersons();
        if (empty($customer_data['person_type'])) {
            return count($active_type_persons) == 0;
        }

        $fields = $this->getFields();
        foreach ($fields['customer'] as $field) {
            if (@$field['required'][$customer_data['person_type']] && !$customer_data[$field['name']]) {
                return false;
            }
        }

        return true;
    }

    public function getCustomerValidationErrors($id_customer = null)
    {
        if (is_null($id_customer)) {
            $id_customer = $this->context->customer->id;
        }
        if (!$id_customer) {
            return [];
        }

        $errors = [];
        $sql = new DbQuery;
        $sql->from('customer')->where('id_customer=' . (int)$id_customer);
        $customer_data = Db::getInstance()->getRow($sql);

        if (!$customer_data) {
            return [];
        }

        $person_type = $customer_data['person_type'];
        $fields = $this->getFields();

        $active_type_persons = $this->getActiveTypePersons();
        if (empty($person_type) && count($active_type_persons) > 0) {
            $errors[] = [
                'input_name' => 'person_type',
                'label' => $this->trans('Person type', [], 'Modules.Agcustomers.Shop'),
                'message' => $this->trans('The field "Person type" is required.', [], 'Modules.Agcustomers.Shop')
            ];
        }

        if ($person_type) {
            foreach ($fields['customer'] as $field) {
                $name = isset($field['name']) ? $field['name'] : null;
                if (!$name) { continue; }

                $isRequired = !empty($field['required'][$person_type]);
                $value = isset($customer_data[$name]) ? $customer_data[$name] : null;

                // Resolve label (supports multilingual array)
                $label = isset($field['label']) ? $field['label'] : $name;
                if (is_array($label)) {
                    if (isset($label[$this->context->language->id])) {
                        $label = $label[$this->context->language->id];
                    } else {
                        $label = array_pop($label);
                    }
                }

                // Special validation for birthday: detect '0000-00-00' and invalid dates
                $isBirthdayInvalid = false;
                if ($name === 'birthday' && $isRequired && !empty($value)) {
                    if ($value === '0000-00-00') {
                        $isBirthdayInvalid = true;
                    } else {
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                            list($y, $m, $d) = explode('-', $value);
                            if (!checkdate((int)$m, (int)$d, (int)$y)) {
                                $isBirthdayInvalid = true;
                            }
                        } else {
                            $isBirthdayInvalid = true;
                        }
                    }
                }

                if ($isRequired && (empty($value) || $isBirthdayInvalid)) {
                    $message = empty($value)
                        ? $this->trans('The field %label% is required.', ['%label%' => $label], 'Modules.Agcustomers.Shop')
                        : $this->trans('The field %label% must be a valid date (YYYY-MM-DD).', ['%label%' => $label], 'Modules.Agcustomers.Shop');

                    $errors[] = [
                        'input_name' => $name,
                        'label' => $label,
                        'message' => $message,
                        'value' => $value,
                    ];
                }
            }
        }

        // Age range validation (if configured) using stored birthday
        try {
            $options = $this->getOptions();
            $custCfg = isset($options['config']['customer']) ? $options['config']['customer'] : [];
            $minAge = (int)($custCfg['min_age'] ?? 0);
            $maxAge = (int)($custCfg['max_age'] ?? 0);

            $birthdayStr = $customer_data['birthday'] ?? '';
            if ($birthdayStr && $birthdayStr !== '0000-00-00' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdayStr)) {
                list($by, $bm, $bd) = array_map('intval', explode('-', $birthdayStr));
                if (checkdate($bm, $bd, $by)) {
                    $today = new \DateTimeImmutable('today');
                    $bday  = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $by, $bm, $bd));
                    $age   = (int)$bday->diff($today)->y;

                    $labelBday = $this->trans('Date of birth', [], 'Modules.Agcustomers.Shop');

                    if ($minAge > 0 && $age < $minAge) {
                        $errors[] = [
                            'input_name' => 'birthday',
                            'label' => $labelBday,
                            'message' => $this->trans('You must be at least %min% years old.', ['%min%' => $minAge], 'Modules.Agcustomers.Shop'),
                            'value' => $birthdayStr,
                        ];
                    }

                    if ($maxAge > 0 && $age > $maxAge) {
                        $errors[] = [
                            'input_name' => 'birthday',
                            'label' => $labelBday,
                            'message' => $this->trans('The maximum allowed age is %max% years.', ['%max%' => $maxAge], 'Modules.Agcustomers.Shop'),
                            'value' => $birthdayStr,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore runtime issues
        }

        return $errors;
    }

    public function isValidAddress($id_address)
    {
        $sql = new DbQuery;
        $sql->from('address')->where('id_address=' . (int)$id_address);
        $address_data = Db::getInstance()->getRow($sql);

        $fields = $this->getFields();
        foreach ($fields['address'] as $field) {
            if (@$field['required'] && !$address_data[$field['name']]) {
                return false;
            }
        }

        return true;
    }

    public function displayInvalidCustomerWarning()
    {
        return $this->display($this->_path, 'invalid_customer_warning.tpl');
    }

    public function displayInvalidAddressWarning(Cart $cart, $type_address)
    {
        $this->context->smarty->assign([
            'cart_obj' => $cart,
            'type_address' => $type_address
        ]);
        
        return $this->display($this->_path, 'invalid_address_warning.tpl');
    }
    

    public function loadMappings()
    {
        $this->district_mapping = new AgColumnMapping();
        $this->district_mapping->setData([
            'table_name' => 'address',
            'configuration_name' => 'agcustomers_district'
        ]);
    }

    public function getDistrictMapping()
    {
        return $this->district_mapping;
    }

    public function isBrazilEnabled()
    {
        $brazil = new Country(Country::getByIso('br'));
        return $brazil->active;
    }


    public function getFields()
    {
        $options = $this->getOptions();
        return $options['fields'];
    }

    public function getOptions()
    {
        $options = Configuration::get('AGCUSTOMERS_CONFIG');
        if (empty($options)) {
            // Do not auto-create defaults in DB; just render minimal options
            // until the admin applies Brazil defaults or saves settings.
            $options = $this->getMinimalOptions();
        } else {
            $options = unserialize($options);
        }
         // define a data de aniversario sempre como unique = false
        $options['fields']['customer'][2]['unique'] = "0";
        // Safety: if address section is missing/empty in DB, populate with defaults
        if (!isset($options['fields']['address']) || !is_array($options['fields']['address']) || count($options['fields']['address']) === 0) {
            $options['fields']['address'] = $this->getDefaultAddressFields();
        }
        $options['config']['cep_provider'] = $this->normalizeCepProvider($options['config']['cep_provider'] ?? null);
        // Ensure labels are multilingual and localized for current languages
        return $this->localizeOptions($options);
    }

    private function columnExists($tableName, $column)
    {
        $sql = 'SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA="' . pSQL(_DB_NAME_) . '" AND TABLE_NAME="' . pSQL($tableName) . '" AND COLUMN_NAME="' . pSQL($column) . '"';
        return (bool)Db::getInstance()->getValue($sql, false);
    }

    /**
     * Minimal, international-friendly options used by Reset Fields.
     * Keeps only first/last name and birthday + default address flags,
     * without BR-specific extras.
     */
    public function getMinimalOptions()
    {
        $options = [
            'config' => [
                'asterisk_require_fields' => 0,
                'cep_provider' => $this->getDefaultCepProvider(),
                'customer' => [
                    'position' => 1,
                    'display_missing_data_error' => 1,
                    'min_age' => 0,
                    'max_age' => 0,
                ],
                'address' => [
                    'position' => 1,
                    'disabled' => 1,
                    'error_msg' => 1,
                ],
            ],
            'fields' => [
                'customer' => [
                    [
                        'name' => 'firstname',
                        'label' => 'First name',
                        'is_default_input' => 1,
                        'required' => ['pf' => true, 'pj' => true, 'nbr' => true],
                        'insert'   => ['pf' => true, 'pj' => true, 'nbr' => true],
                    ],
                    [
                        'name' => 'lastname',
                        'label' => 'Last name',
                        'is_default_input' => 1,
                        'required' => ['pf' => true, 'pj' => true, 'nbr' => true],
                        'insert'   => ['pf' => true, 'pj' => true, 'nbr' => true],
                    ],
                    [
                        'name' => 'birthday',
                        'label' => 'Date of birth',
                        'is_default_input' => 1,
                        'required' => ['pf' => true, 'pj' => true, 'nbr' => true],
                        'insert'   => ['pf' => true, 'pj' => true, 'nbr' => true],
                    ],
                ],
                'address' => $this->getDefaultAddressFields(),
            ],
            'type_person' => [
                ['name' => 'pf', 'label' => 'Individual', 'active' => true],
                ['name' => 'pj', 'label' => 'Company',    'active' => true],
                ['name' => 'nbr','label' => 'Foreign person', 'active' => true],
            ],
        ];

        return $this->localizeOptions($options);
    }

    /**
     * Default address fields used when configuration lacks address definitions.
     */
    private function getDefaultAddressFields(): array
    {
        return [
            [ 'label' => 'First name',       'name' => 'firstname',    'required' => true ],
            [ 'label' => 'Last name',        'name' => 'lastname',     'required' => true ],
            [ 'label' => 'Address',          'name' => 'address1',     'required' => true ],
            [ 'label' => 'Number',           'name' => 'number',       'required' => true ],
            [ 'label' => 'Address line 2',   'name' => 'address2',     'required' => true ],
            [ 'label' => 'Additional information','name' => 'other',   'required' => false ],
            [ 'label' => 'Zip/Postal code',  'name' => 'postcode',     'required' => true ],
            [ 'label' => 'City',             'name' => 'city',         'required' => true ],
            [ 'label' => 'State',            'name' => 'id_state',     'required' => true ],
            [ 'label' => 'Country',          'name' => 'id_country',   'required' => true ],
            [ 'label' => 'Phone',            'name' => 'phone',        'required' => false ],
            [ 'label' => 'Mobile phone',     'name' => 'phone_mobile', 'required' => true ],
        ];
    }

    public function getActiveTypePersons()
    {
        $type_persons = $this->getOptions()['type_person'];
        $active_type_persons = [];

        foreach ($type_persons as $type_person) {
            if ($type_person['active']) {
                $active_type_persons[] = $type_person;
            }
        }

        return $active_type_persons;
    }

    public function getUnauthenticatedOptions()
    {
        return [
            'config' => [
                'asterisk_require_fields' => 0,
                'cep_provider' => $this->getDefaultCepProvider(),
                'customer' => ['position' => 1],
                'address' => [
                    'position' => 1,
                    'disabled'=> 1,
                    'error_msg'=> 0
                ]
            ],
            'fields' => [
                'customer' => [
                    [
                        'name' => 'cpf',
                        'label' => 'CPF',
                        'mask' => '000.000.000-00',
                        'active' => true,
                        'required' => [
                            'pf' => true,
                        ],
                        'insert' => [
                            'pf' => true,
                        ],
                        'edit_fo' => true,
                        'edit_bo' => true,
                        'type' => 'text'
                    ]
                ],
                'address' => [
                    [
                        'label' => 'Nome',
                        'name' => 'firstname',
                        'required' => true
                    ],
                    [
                        'label' => 'Sobrenome',
                        'name' => 'lastname',
                        'required' => true
                    ],
                    [
                        'label' => 'Endereço',
                        'name' => 'address1',
                        'required' => true
                    ],
                    [
                        'label' => 'Número',
                        'name' => 'number',
                        'required' => true
                    ],
                    [
                        'label' => 'address2',
                        'name' => 'address2',
                        'required' => true
                    ],
                    [
                        'label' => 'Complemento',
                        'name' => 'other',
                        'required' => false
                    ],
                    [
                        'label' => 'CEP',
                        'name' => 'postcode',
                        'required' => true
                    ],
                    [
                        'label' => 'Cidade',
                        'name' => 'city',
                        'required' => true
                    ],
                    [
                        'label' => 'Estado',
                        'name' => 'id_state',
                        'required' => true
                    ],
                    [
                        'label' => 'País',
                        'name' => 'id_country',
                        'required' => true
                    ],
                    [
                        'label' => 'Telefone',
                        'name' => 'phone',
                        'required' => false
                    ],
                    [
                        'label' => 'Telefone Celular',
                        'name' => 'phone_mobile',
                        'required' => true
                    ]
                ]
            ],
            'type_person' => [
                [
                    'name' => 'pf',
                    'label' => 'Pessoa Física',
                    'active' => true
                ],
                [
                    'name' => 'pj',
                    'label' => 'Pessoa Jurídica',
                    'active' => false
                ],
                [
                    'name' => 'nbr',
                    'label' => 'Pessoa Estrangeira',
                    'active' => false
                ]
            ]
        ];
    }

    public function getDefaultOptions()
    {
        // Build raw defaults (historically contained PT-BR labels)
        // and immediately convert them to multilingual, localized labels
        // so when the configuration is recreated in the DB it is already
        // PS9-translation friendly for all active languages.
        $options = [
            'config' => [
                'asterisk_require_fields' => 0,
                'cep_provider' => $this->getDefaultCepProvider(),
                'customer' => [
                    'position' => 1,
                    'display_missing_data_error' => 1,
                    'min_age' => 0,
                    'max_age' => 0
                ],
                'address' => [
                'position' => 1,
                'disabled'=> 1,
                'error_msg'=> 1
            ],
            ],
            'fields' => [
                'customer' => [
                    [
                        'name' => 'firstname',
                        'label' => 'Nome',
                        'is_default_input' => 1,
                        'required' => [
                            'pf' => true,
                            'pj' => true,
                            'nbr' => true
                        ],
                        'insert' => [
                            'pf' => true,
                            'pj' => true,
                            'nbr' => true
                        ]
                    ],
                    [
                        'name' => 'lastname',
                        'label' => 'Sobrenome',
                        'is_default_input' => 1,
                        'required' => [
                            'pf' => true,
                            'pj' => true,
                            'nbr' => true
                        ],
                        'insert' => [
                            'pf' => true,
                            'pj' => true,
                            'nbr' => true
                        ]
                    ],
                    [
                        'name' => 'birthday',
                        'label' => 'Data de nascimento',
                        'is_default_input' => 1,
                        'required' => [
                            'pf' => true,
                            'pj' => true,
                            'nbr' => true
                        ],
                        'insert' => [
                            'pf' => true,
                            'pj' => true,
                            'nbr' => true
                        ]
                    ],
                    [
                        'name' => 'cpf',
                        'label' => 'CPF',
                        'mask' => '000.000.000-00',
                        'active' => true,
                        'required' => [
                            'pf' => true,
                            'pj' => true,
                            'nbr' => false
                        ],
                        'insert' => [
                            'pf' => true,
                            'pj' => true,
                            'nbr' => false
                        ]
                    ],
                    [
                        'name' => 'rg',
                        'label' => 'RG',
                        'active' => true,
                    ],
                    [
                        'name' => 'company_name',
                        'label' => 'Razão Social',
                        'active' => true,
                    ],
                    [
                        'name' => 'cnpj',
                        'label' => 'CNPJ',
                        'active' => true,
                        'mask' => '00.000.000/0000-00',
                        'required' => [
                            'pf' => false,
                            'pj' => true,
                            'nbr' => false
                        ],
                        'insert' => [
                            'pf' => false,
                            'pj' => true,
                            'nbr' => false
                        ]
                    ],
                    [
                        'name' => 'ie',
                        'label' => 'Inscrição Estadual',
                        'active' => true,
                    ],
                ],
                'address' => [
                    [
                        'label' => 'Nome',
                        'name' => 'firstname',
                        'required' => true
                    ],
                    [
                        'label' => 'Sobrenome',
                        'name' => 'lastname',
                        'required' => true
                    ],
                    [
                        'label' => 'Endereço',
                        'name' => 'address1',
                        'required' => true
                    ],
                    [
                        'label' => 'Número',
                        'name' => 'number',
                        'required' => true
                    ],
                    [
                        'label' => 'address2',
                        'name' => 'address2',
                        'required' => true
                    ],
                    [
                        'label' => 'Complemento',
                        'name' => 'other',
                        'required' => false
                    ],
                    [
                        'label' => 'CEP',
                        'name' => 'postcode',
                        'required' => true
                    ],
                    [
                        'label' => 'Cidade',
                        'name' => 'city',
                        'required' => true
                    ],
                    [
                        'label' => 'Estado',
                        'name' => 'id_state',
                        'required' => true
                    ],
                    [
                        'label' => 'País',
                        'name' => 'id_country',
                        'required' => true
                    ],
                    [
                        'label' => 'Telefone',
                        'name' => 'phone',
                        'required' => false
                    ],
                    [
                        'label' => 'Telefone Celular',
                        'name' => 'phone_mobile',
                        'required' => true
                    ]
                ]
            ],
            'type_person' => [
                [
                    'name' => 'pf',
                    'label' => 'Pessoa Física',
                    'active' => true
                ],
                [
                    'name' => 'pj',
                    'label' => 'Pessoa Jurídica',
                    'active' => true
                ],
                [
                    'name' => 'nbr',
                    'label' => 'Pessoa Estrangeira',
                    'active' => true
                ]
            ]
        ];

        // Return defaults with labels converted to id_lang=>label maps for all languages
        return $this->localizeOptions($options);
    }

    public function createCustomerColumns($inputs)
    {
        foreach ($inputs as $input) {
            if ($input['type'] !== 'checkbox') {
                $sql = 'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA="' . pSQL(_DB_NAME_) . '" AND TABLE_NAME="' . pSQL(_DB_PREFIX_ . 'customer') . '" AND COLUMN_NAME="' . pSQL($input['name']) . '"';
                $exists = Db::getInstance()->getValue($sql, true, false);
                if ($exists) {
                    continue;
                }

                $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'customer ADD COLUMN ' . $input['name'] . ' varchar(255)';
                if (!Db::getInstance()->execute($sql)) {
                    throw new Exception('AGCUSTOMERS: falha ao criar coluna de cliente ' . $input['name']);
                }

                continue;
            }

            foreach ($input['options'] as $option) {
                $column_name = $input['name'] . $option['value'];

                $sql = 'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA="' . pSQL(_DB_NAME_) . '" AND TABLE_NAME="' . pSQL(_DB_PREFIX_ . 'customer') . '" AND COLUMN_NAME="' . pSQL($column_name) . '"';
                $exists = Db::getInstance()->getValue($sql, true, false);
                if ($exists) {
                    continue;
                }

                $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'customer ADD COLUMN ' . $column_name . ' varchar(255)';
                if (!Db::getInstance()->execute($sql)) {
                    throw new Exception('AGCUSTOMERS: falha ao criar coluna de cliente ' . $column_name);
                }
            }
        }
    }


    public function hookActionCustomerGridDefinitionModifier(array $params)
    {
        /** @var GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $hasCpf = $this->columnExists(_DB_PREFIX_ . 'customer', 'cpf');
        $hasCnpj = $this->columnExists(_DB_PREFIX_ . 'customer', 'cnpj');

        // CPF
        if ($hasCpf) {
            $definition
                ->getColumns()
                ->addAfter(
                    'id_customer',
                    (new DataColumn('cpf'))
                        ->setName($this->trans('CPF', [], 'Admin.Global'))
                        ->setOptions([
                            'field' => 'cpf',
                        ])
            );

            // For search filter
            $definition->getFilters()->add(
                (new Filter('cpf', TextType::class))
                    ->setAssociatedColumn('cpf')
                    ->setTypeOptions([
                        'attr' => [
                            'placeholder' => $this->trans('Search CPF', [], 'Admin.Actions'),
                        ],
                        'required' => false,
                    ])
            );
        }

        if ($hasCnpj) {
            // CNPJ
            $definition
                ->getColumns()
                ->addAfter(
                    'email',
                    (new DataColumn('cnpj'))
                        ->setName($this->trans('CNPJ', [], 'Admin.Global'))
                        ->setOptions([
                            'field' => 'cnpj',
                        ])
            );

            // For search filter
            $definition->getFilters()->add(
                (new Filter('cnpj', TextType::class))
                    ->setAssociatedColumn('cnpj')
                    ->setTypeOptions([
                        'attr' => [
                            'placeholder' => $this->trans('Search CNPJ', [], 'Admin.Actions'),
                        ],
                        'required' => false,
                    ])
            );
        }
    }


    public function hookActionCustomerGridQueryBuilderModifier(array $params)
    {
        if (Tools::getValue('id_customer')) {
            return;
        }
        
        /** @var QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        /** @var CustomerFilters $searchCriteria */
        $searchCriteria = $params['search_criteria'];


        $hasCpf = $this->columnExists(_DB_PREFIX_ . 'customer', 'cpf');
        $hasCnpj = $this->columnExists(_DB_PREFIX_ . 'customer', 'cnpj');

        // CPF
        if ($hasCpf) {
            $searchQueryBuilder->addSelect('c.cpf');

            if ('c.´cpf' === $searchCriteria->getOrderBy()) {
                $searchQueryBuilder->orderBy('c.`cpf`', $searchCriteria->getOrderWay());
            }

            foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
                if ('cpf' === $filterName) {
                    $searchQueryBuilder->andWhere('c.`cpf` LIKE :cpf');
                    $searchQueryBuilder->setParameter('cpf',  "%$filterValue%");
                }
            }
        }

         // CNPJ
         if ($hasCnpj) {
             $searchQueryBuilder->addSelect('c.cnpj');

             if ('c.cnpj' === $searchCriteria->getOrderBy()) {
                 $searchQueryBuilder->orderBy('c.`cnpj`', $searchCriteria->getOrderWay());
             }

             foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
                 if ('cnpj' === $filterName) {
                     $searchQueryBuilder->andWhere('c.`cnpj` LIKE :cnpj');
                     $searchQueryBuilder->setParameter('cnpj',  "%$filterValue%");
                 }
             }
         }
    }



    public function hookActionAddressGridDefinitionModifier(array $params)
    {
        /** @var GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $hasNumber = $this->columnExists(_DB_PREFIX_ . 'address', 'number');

        if ($hasNumber) {
            // Address number
            $definition
                ->getColumns()
                ->addAfter(
                    'address1',
                    (new DataColumn('number'))
                        ->setName($this->trans('Number', [], 'Admin.Global'))
                        ->setOptions([
                            'field' => 'number',
                        ])
            );

            // For search filter
            $definition->getFilters()->add(
                (new Filter('number', TextType::class))
                    ->setAssociatedColumn('number')
                    ->setTypeOptions([
                        'attr' => [
                            'placeholder' => $this->trans('Search number', [], 'Admin.Actions'),
                        ],
                        'required' => false,
                    ])
            );
        }
    }


    public function hookActionAddressGridQueryBuilderModifier(array $params)
    {
        /** @var QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        /** @var CustomerFilters $searchCriteria */
        $searchCriteria = $params['search_criteria'];


        $hasNumber = $this->columnExists(_DB_PREFIX_ . 'address', 'number');

        if ($hasNumber) {
            $searchQueryBuilder->addSelect('a.number');

            if ('a.number' === $searchCriteria->getOrderBy()) {
                $searchQueryBuilder->orderBy('a.`number`', $searchCriteria->getOrderWay());
            }

            foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
                if ('number' === $filterName) {
                    $searchQueryBuilder->andWhere('a.`number` LIKE :number');
                    $searchQueryBuilder->setParameter(':number',  "%$filterValue%");
                }
            }
        }
    }



    public function hookDisplayBeforeBodyClosingTag()
    {
        $output = '';
        if (Configuration::get('AGCUSTOMERS_GOOGLE_CLIENT_ID')) {
            $output .= "<script src='https://accounts.google.com/gsi/client' async defer></script>";
        }

        if (!empty($this->checkoutErrors)) {
            // Passa também as opções de tipo de pessoa para o template
            $type_persons = $this->getActiveTypePersons();
            $this->context->smarty->assign([
                'agcustomer_checkout_errors' => $this->checkoutErrors,
                'type_persons' => $type_persons
            ]);
            $output .= $this->display($this->_path, 'views/templates/front/checkout_validation_modal.tpl');
        }

        return $output;
    }

    public function hookActionCustomerAccountAdd($props){
        $this->updateGroupCustomer($props['newCustomer']);
        return true;
    }


    public function hookActionCustomerAccountUpdate($props){
        $this->updateGroupCustomer($props['customer']);
        return true;
    }

    public function updateGroupCustomer($customer){
        $options = $this->getOptions();

        foreach ($options['type_person'] as $type_person) {
            if($type_person['name'] == $customer->person_type){
                $newGroup = $type_person['group_customer'];
            }

            Db::getInstance()->delete('customer_group', 'id_group = ' . (int) $type_person['group_customer'].' AND id_customer = '.(int) $customer->id);
        }

        $groups=[$newGroup];
        

        $customer->addGroups($groups);
        Db::getInstance()->update("customer", ['id_default_group' => $newGroup], 'id_customer=' . (int)$customer->id);
    }
}
