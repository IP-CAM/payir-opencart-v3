<?php 

class ModelExtensionPaymentPayir extends Model
{
	public function getMethod($address)
	{
		$this->load->language('extension/payment/payir');

		if ($this->config->get('payment_payir_status')) {

			$status = true;

		} else {

			$status = false;
		}

		$method_data = array ();

		if ($status) {

			$method_data = array (
        		'code' => 'payir',
        		'title' => $this->language->get('text_title'),
				'terms' => '',
				'sort_order' => $this->config->get('payment_payir_sort_order')
			);
		}

		return $method_data;
	}
}