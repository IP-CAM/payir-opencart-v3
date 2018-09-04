<?php 

class ControllerExtensionPaymentPayir extends Controller
{
	private $error = array ();

	public function index()
	{
		
		$this->load->language('extension/payment/payir');
		$this->load->model('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

			$this->model_setting_setting->editSetting('payment_payir', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension',  'user_token='.$this->session->data['user_token'] . '&type=payment', true));
		}
		
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_authorization'] = $this->language->get('text_authorization');
		$data['text_sale'] = $this->language->get('text_sale');
        $data['text_edit'] = $this->language->get( 'text_edit' );

		$data['entry_api'] = $this->language->get('entry_api');
		$data['entry_send'] = $this->language->get('entry_send');
		$data['entry_verify'] = $this->language->get('entry_verify');
		$data['entry_gateway'] = $this->language->get('entry_gateway');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

        $data['tab_general'] = $this->language->get('tab_general');
      	$data['tab_additional'] = $this->language->get('tab_additional');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array (

			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token='. $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array (

			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token='. $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array (

			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/payir','user_token='.  $this->session->data['user_token'], true)
		);

		if (!isset($this->request->get['module_id'])) {

			$data['action'] = $this->url->link('extension/payment/payir', 'user_token='. $this->session->data['user_token'], true);

		} else {

			$data['action'] = $this->url->link('extension/payment/payir', 'user_token='. $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token='. $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->error['warning'])) {

			$data['error_warning'] = $this->error['warning'];

		} else {

			$data['error_warning'] = false;
		}

		if (isset($this->error['api'])) {

			$data['error_api'] = $this->error['api'];

		} else {

			$data['error_api'] = false;
		}

		if (isset($this->error['send'])) {

			$data['error_send'] = $this->error['send'];

		} else {

			$data['error_send'] = false;
		}

		if (isset($this->error['verify'])) {

			$data['error_verify'] = $this->error['verify'];

		} else {

			$data['error_verify'] = false;
		}

		if (isset($this->error['gateway'])) {

			$data['error_gateway'] = $this->error['gateway'];

		} else {

			$data['error_gateway'] = false;
		}

		if (isset($this->request->post['payment_payir_api'])) {

			$data['payment_payir_api'] = $this->request->post['payment_payir_api'];

		} else {

			$data['payment_payir_api'] = $this->config->get('payment_payir_api');
		}

		if (isset($this->request->post['payment_payir_send'])) {

			$data['payment_payir_send'] = $this->request->post['payment_payir_send'];

		} else {

			$data['payment_payir_send'] = $this->config->get('payment_payir_send');

			if(isset($data['payment_payir_send'])){

				$data['payment_payir_send'] = $data['payment_payir_send'];

			} else {

				$data['payment_payir_send'] = 'https://pay.ir/payment/send';
			}
		}
		
		if (isset($this->request->post['payment_payir_verify'])) {

			$data['payment_payir_verify'] = $this->request->post['payment_payir_verify'];

		} else {

			$data['payment_payir_verify'] = $this->config->get('payment_payir_verify');

			if(isset($data['payment_payir_verify'])){

				$data['payment_payir_verify'] = $data['payment_payir_verify'];

			} else {

				$data['payment_payir_verify'] = 'https://pay.ir/payment/verify';
			}
		}

		if (isset($this->request->post['payment_payir_gateway'])) {

			$data['payment_payir_gateway'] = $this->request->post['payment_payir_gateway'];

		} else {

			$data['payment_payir_gateway'] = $this->config->get('payment_payir_gateway');

			if(isset($data['payment_payir_gateway'])){

				$data['payment_payir_gateway'] = $data['payment_payir_gateway'];

			} else {

				$data['payment_payir_gateway'] = 'https://pay.ir/payment/gateway/';
			}
		}

		if (isset($this->request->post['payment_payir_order_status_id'])) {

			$data['payment_payir_order_status_id'] = $this->request->post['payment_payir_order_status_id'];

		} else {

			$data['payment_payir_order_status_id'] = $this->config->get('payment_payir_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['payment_payir_status'])) {

			$data['payment_payir_status'] = $this->request->post['payment_payir_status'];

		} else {

			$data['payment_payir_status'] = $this->config->get('payment_payir_status');
		}

		if (isset($this->request->post['payment_payir_sort_order'])) {

			$data['payment_payir_sort_order'] = $this->request->post['payment_payir_sort_order'];

		} else {

			$data['payment_payir_sort_order'] = $this->config->get('payment_payir_sort_order');
		}
		
		
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/payir', $data));
	}

	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'extension/payment/payir')) {

			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_payir_api']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['api'] = $this->language->get('error_api');
		}

		if (!$this->request->post['payment_payir_send']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['send'] = $this->language->get('error_send');
		}

		if (!$this->request->post['payment_payir_verify']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['verify'] = $this->language->get('error_verify');
		}

		if (!$this->request->post['payment_payir_gateway']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['gateway'] = $this->language->get('error_gateway');
		}

		return !$this->error;
	}
}