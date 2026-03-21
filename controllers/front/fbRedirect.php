<?php

class AgCustomersFbRedirectModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $fb = new Facebook\Facebook([
            'app_id' => Configuration::get('AGCUSTOMERS_FACEBOOK_APP_ID'),
            'app_secret' => Configuration::get('AGCUSTOMERS_FACEBOOK_SECRET_KEY'),
            'default_graph_version' => Configuration::get('AGCUSTOMERS_FACEBOOK_OG_VERSION')
        ]);
     

        $helper = $fb->getRedirectLoginHelper();
        if (isset($_GET['state'])) {
            $helper->getPersistentDataHandler()->set('state', $_GET['state']);
        }
        try {
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (! isset($accessToken)) {
            if ($helper->getErrorReason() === 'user_denied') {
                if (Module::isInstalled('onepagecheckoutps') && Module::isEnabled('onepagecheckoutps') && count($this->context->cart->getProducts()) > 0) {
                    $url_redirect = $this->context->link->getPageLink('order-opc');
                } else {
                    $url_redirect = $this->context->link->getPageLink(Tools::getValue('back'));
                }

                $url_redirect .= '?agcustomers_error=user_denied';
                
                Tools::redirect($url_redirect);
                exit();
            }

            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        $fb->setDefaultAccessToken($accessToken);

           try {
  // Returns a `Facebook\FacebookResponse` object
          $response = $fb->get('/me?fields=id,name,email');
          $email = $response->getDecodedBody()['email'];
          $name = $response->getDecodedBody()['name'];
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        if (!$email) {
            echo json_encode(array(
                'success' => false,
                'error_type' => 'field.email.missing',
                'error_msg' => $this->module->ps17? $this->trans('E-mail is missing.', array(), 'Modules.AgCustomers.Error') : $this->module->l('E-mail is missing', 'fbredirect')
            ));
            exit();
        }

        $c = new Customer();
        $c = $c->getByEmail($email);

        if (!Validate::isLoadedObject($c)) {
            $names = explode(' ', $name);
            $firstname = $names[0];

            unset($names[0]);
            $lastname = implode(' ', $names);

            if (Module::isInstalled('onepagecheckoutps') && Module::isEnabled('onepagecheckoutps') && count($this->context->cart->getProducts()) > 0) {
                $url_redirect = $this->context->link->getPageLink('order-opc');
            } else {
                $url_redirect = $this->context->link->getPageLink(Tools::getValue('back'));
            }

            $url_redirect = $url_redirect . '?create_account=1&email=' . $email . '&firstname=' . $firstname . '&lastname=' . $lastname . '&back=' . urlencode(Tools::getValue('back'));

            if ($this->module->ps17) {
                $this->redirectWithNotifications($url_redirect);
            } else {
                header("Location: $url_redirect");
            }
            
            exit();
        }

        if ($this->module->ps17) {
            $this->context->updateCustomer($c);
        } else {
            $this->updateCustomer($c);
        }

        if (Module::isInstalled('onepagecheckoutps') && Module::isEnabled('onepagecheckoutps') && count($this->context->cart->getProducts()) > 0) {
            $url_redirect = $this->context->link->getPageLink('order-opc');
        } else {
            $url_redirect = $this->context->link->getPageLink(Tools::getValue('back'));
        }
        
        header("Location: $url_redirect");
        
        exit();
    }

    protected function updateCustomer(Customer $customer)
    {
        $this->customer = $customer;
        self::$cookie->id_customer = (int) $customer->id;
        self::$cookie->customer_lastname = $customer->lastname;
        self::$cookie->customer_firstname = $customer->firstname;
        self::$cookie->passwd = $customer->passwd;
        self::$cookie->logged = 1;
        $customer->logged = 1;
        self::$cookie->email = $customer->email;
        self::$cookie->is_guest =  $customer->isGuest();
        $this->context->cart->secure_key = $customer->secure_key;

        if (Configuration::get('PS_CART_FOLLOWING') && (empty(self::$cookie->id_cart) || Cart::getNbProducts(self::$cookie->id_cart) == 0) && $idCart = (int) Cart::lastNoneOrderedCart($this->customer->id)) {
            $this->context->cart = new Cart($idCart);
        } else {
            $idCarrier = (int) $this->context->cart->id_carrier;
            $this->context->cart->id_carrier = 0;
            $this->context->cart->setDeliveryOption(null);
            $this->context->cart->id_address_delivery = (int) Address::getFirstCustomerAddressId((int) ($customer->id));
            $this->context->cart->id_address_invoice = (int) Address::getFirstCustomerAddressId((int) ($customer->id));
        }
        $this->context->cart->id_customer = (int) $customer->id;

        if (isset($idCarrier) && $idCarrier) {
            $deliveryOption = [$this->context->cart->id_address_delivery => $idCarrier.','];
            $this->context->cart->setDeliveryOption($deliveryOption);
        }

        $this->context->cart->save();
        self::$cookie->id_cart = (int) $this->context->cart->id;
        self::$cookie->write();
        $this->context->cart->autosetProductAddress();
    }
}
