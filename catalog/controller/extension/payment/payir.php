<?php

class ControllerExtensionPaymentPayir extends Controller
{
    public function index()
    {
        $this->load->language('extension/payment/payir');
        $this->load->model('checkout/order');
        $this->load->library('encryption');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $encryption = new Encryption($this->config->get('config_encryption'));

        //		if ($this->currency->getCode() != 'RLS') {
        //
        //			$this->currency->set('RLS');
        //		}

        $data['button_confirm'] = $this->language->get('button_confirm');

        $data['error_warning'] = FALSE;

        
        if (extension_loaded('curl')) {

            $parameters = [
                'api'          => $this->config->get('payment_payir_api'),
                'amount'       => $this->currency->format($order_info['total'], 'IRR', NULL, FALSE),
                'redirect'     => urlencode($this->url->link('extension/payment/payir/callback', 'order_id=' . $encryption->encrypt($order_info['order_id']), '', 'SSL')),
                'factorNumber' => $order_info['order_id']
            ];
            
            $result = $this->common($this->config->get('payment_payir_send'), $parameters);
            $result = json_decode($result);

           
            
            if (isset($result->status) && $result->status == 1) {

                $data['action'] = $this->config->get('payment_payir_gateway') . $result->transId;


               
            } else {

                $code = isset($result->errorCode) ? $result->errorCode : 'Undefined';
                $message
                      = isset($result->errorMessage) ? $result->errorMessage : $this->language->get('error_undefined');

                $data['error_warning']
                    = $this->language->get('error_request') . '<br/><br/>' . $this->language->get('error_code') . $code . '<br/>' . $this->language->get('error_message') . $message;
            }

        } else {

            $data['error_warning'] = $this->language->get('error_curl');
        }

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/payir')) {

            return $this->load->view($this->config->get('config_template') . '/extension/payment/payir', $data);

        } else {

            return $this->load->view('/extension/payment/payir', $data);
        }
    }

    public function callback()
    {
        ob_start();
        
        $this->load->language('extension/payment/payir');
        $this->load->model('checkout/order');
        $this->load->library('encryption');

        $this->document->setTitle($this->language->get('heading_title'));

        $encryption = new Encryption($this->config->get('config_encryption'));

        $order_id = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : FALSE;
        $order_id = isset($order_id) ? $order_id : $encryption->decrypt($this->request->get['order_id']);

        $order_info = $this->model_checkout_order->getOrder($order_id);

        //		if ($this->currency->getCode() != 'RLS') {
        //
        //			$this->currency->set('RLS');
        //		}

        $data['heading_title'] = $this->language->get('heading_title');

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue']        = $this->url->link('common/home', '', 'SSL');

        $data['error_warning'] = FALSE;

        $data['continue'] = $this->url->link('checkout/cart', '', 'SSL');

        if ($this->request->post['status'] && $this->request->post['transId'] && $this->request->post['factorNumber']) {

            $status        = $this->request->post['status'];
            $trans_id      = $this->request->post['transId'];
            $factor_number = $this->request->post['factorNumber'];
            $message       = $this->request->post['message'];

            if (isset($status) && $status == 1) {

                if ($order_id == $factor_number && $factor_number == $order_info['order_id']) {

                    $parameters = [
                        'api'     => $this->config->get('payment_payir_api'),
                        'transId' => $trans_id
                    ];

                    $result = $this->common($this->config->get('payment_payir_verify'), $parameters);
                    $result = json_decode($result);

                    if (isset($result->status) && $result->status == 1) {

                        //						$amount = @$this->currency->format($order_info['total'], $order_info['currency'], $order_info['value'], false);
                        $amount = $this->currency->format($order_info['total'], 'IRR', NULL, FALSE);


                        if ($amount == $result->amount) {

                            $comment = $this->language->get('text_transaction') . $trans_id;

                            $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_payir_order_status_id'), $comment);

                        } else {

                            $data['error_warning'] = $this->language->get('error_amount');
                        }

                    } else {

                        $code = isset($result->errorCode) ? $result->errorCode : 'Undefined';
                        $message
                              = isset($result->errorMessage) ? $result->errorMessage : $this->language->get('error_undefined');

                        $data['error_warning']
                            = $this->language->get('error_request') . '<br/><br/>' . $this->language->get('error_code') . $code . '<br/>' . $this->language->get('error_message') . $message;
                    }

                } else {

                    $data['error_warning'] = $this->language->get('error_invoice');
                }

            } else {

                $data['error_warning'] = $this->language->get('error_payment');
            }

        } else {

            $data['error_warning'] = $this->language->get('error_data');
        }

        if ($data['error_warning']) {

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', '', 'SSL'),
                'separator' => FALSE
            ];

            $data['breadcrumbs'][] = [
                'text'      => $this->language->get('text_basket'),
                'href'      => $this->url->link('checkout/cart', '', 'SSL'),
                'separator' => ' » '
            ];

            $data['breadcrumbs'][] = [
                'text'      => $this->language->get('text_checkout'),
                'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
                'separator' => ' » '
            ];

            $data['header'] = $this->load->controller('common/header');
            $data['footer'] = $this->load->controller('common/footer');

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/payir_callback')) {

                $this->response->setOutput($this->load->view($this->config->get('config_template') . '/extension/payment/payir_callback', $data));

            } else {

                $this->response->setOutput($this->load->view('extension/payment/payir_callback', $data));
            }

        } else {

            $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
        }
    }

    protected function common($url, $parameters)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}

?>
