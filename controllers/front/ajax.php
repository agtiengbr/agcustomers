<?php

class AgcustomersAjaxModuleFrontController extends ModuleFrontController
{
    /**
     * Send a JSON response and terminate execution (compatible with PS 8/9).
     */
    private function sendJson(array $data, int $statusCode = 200)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            if (function_exists('http_response_code')) {
                http_response_code($statusCode);
            }
        }
        echo json_encode($data);
        exit;
    }

    public function postProcess()
    {
        if (!$this->context->customer->isLogged()) {
            $this->sendJson(['success' => false, 'errors' => [['message' => 'Cliente não está logado.']]], 401);
        }

        // Update customer object from POST data
        $customer = $this->context->customer;
        $fields = $this->module->getFields();
        
        foreach ($fields['customer'] as $field) {
            $fieldName = $field['name'];
            if (Tools::getIsSet($fieldName)) {
                $customer->{$fieldName} = Tools::getValue($fieldName);
            }
        }

        // Save the customer
        if (!$customer->update()) {
            $this->sendJson(['success' => false, 'errors' => [['message' => 'Ocorreu um erro ao salvar os dados.']]], 500);
        }

        // Re-validate
        $errors = $this->module->getCustomerValidationErrors($customer->id);

        if (count($errors) === 0) {
            $this->sendJson(['success' => true]);
        } else {
            $this->sendJson(['success' => false, 'errors' => $errors], 400);
        }
    }
}
