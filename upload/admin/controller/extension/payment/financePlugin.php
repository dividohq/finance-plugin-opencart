<?php
class ControllerExtensionPaymentFinancePlugin extends Controller
{
	private $error = array();

	public function index()
	{
		$this->load->language('extension/payment/financePlugin');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('extension/payment/financePlugin');

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
			$new_settings = $this->request->post;
			
			try {
				$new_settings['payment_financePlugin_environment'] = $this->model_extension_payment_financePlugin->getEnvironmentFromSDK($new_settings['payment_financePlugin_api_key']);
			} catch (Exception $e) {
				$this->log->write($e->getMessage());
				$new_settings['payment_financePlugin_environment'] = '';
			}
			
			$this->model_setting_setting->editSetting('payment_financePlugin', $new_settings);

			$this->session->data['success'] = $this->language->get('plugin_edit_success_msg');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['entry_plans_options'] = array(
			'all' => $this->language->get('show_all_plans_option'),
			'selected' => $this->language->get('select_specific_plans_option'),
		);

		$data['entry_products_options'] = array(
			'all' => $this->language->get('finance_all_products_option'),
			'selected' => $this->language->get('finance_specific_products_option'),
			'threshold' => $this->language->get('finance_threshold_products_option'),
		);

		$data['button_save'] = $this->language->get('save_label');
		$data['button_cancel'] = $this->language->get('cancel_label');

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('home_label'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('extensions_label'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/financePlugin', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/financePlugin', 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL');

		if (isset($this->request->post['payment_financePlugin_api_key'])) {
			$data['payment_financePlugin_api_key'] = $this->request->post['payment_financePlugin_api_key'];
		} else {
			$data['payment_financePlugin_api_key'] = $this->config->get('payment_financePlugin_api_key');
		}

		if (isset($this->request->post['payment_financePlugin_shared_secret'])) {
			$data['payment_financePlugin_shared_secret'] = $this->request->post['payment_financePlugin_shared_secret'];
		} else {
			$data['payment_financePlugin_shared_secret'] = $this->config->get('payment_financePlugin_shared_secret');
		}

		if (isset($this->request->post['payment_financePlugin_order_status_id'])) {
			$data['payment_financePlugin_order_status_id'] = $this->request->post['payment_financePlugin_order_status_id'];
		} elseif ($this->config->get('payment_financePlugin_order_status_id')) {
			$data['payment_financePlugin_order_status_id'] = $this->config->get('payment_financePlugin_order_status_id');
		} else {
			$data['payment_financePlugin_order_status_id'] = 2;
		}

		if (isset($this->request->post['payment_financePlugin_status'])) {
			$data['payment_financePlugin_status'] = $this->request->post['payment_financePlugin_status'];
		} else {
			$data['payment_financePlugin_status'] = $this->config->get('payment_financePlugin_status');
		}

		if (isset($this->request->post['payment_financePlugin_sort_order'])) {
			$data['payment_financePlugin_sort_order'] = $this->request->post['payment_financePlugin_sort_order'];
		} else {
			$data['payment_financePlugin_sort_order'] = $this->config->get('payment_financePlugin_sort_order');
		}

		if (isset($this->request->post['payment_financePlugin_title'])) {
			$data['payment_financePlugin_title'] = $this->request->post['payment_financePlugin_title'];
		} else {
			$data['payment_financePlugin_title'] = $this->config->get('payment_financePlugin_title');
		}

		if (isset($this->request->post['payment_financePlugin_widget_mode'])) {
			$data['payment_financePlugin_widget_mode'] = $this->request->post['payment_financePlugin_widget_mode'];
		} else {
			$data['payment_financePlugin_widget_mode'] = $this->config->get('payment_financePlugin_widget_mode');
		}

		if (isset($this->request->post['payment_financePlugin_btn_txt'])) {
			$data['payment_financePlugin_btn_txt'] = $this->request->post['payment_financePlugin_btn_txt'];
		} else {
			$data['payment_financePlugin_btn_txt'] = $this->config->get('payment_financePlugin_btn_txt');
		}

		if (isset($this->request->post['payment_financePlugin_footnote'])) {
			$data['payment_financePlugin_footnote'] = $this->request->post['payment_financePlugin_footnote'];
		} else {
			$data['payment_financePlugin_footnote'] = $this->config->get('payment_financePlugin_footnote');
		}

		if (isset($this->request->post['payment_financePlugin_productselection'])) {
			$data['payment_financePlugin_productselection'] = $this->request->post['payment_financePlugin_productselection'];
		} else {
			$data['payment_financePlugin_productselection'] = $this->config->get('payment_financePlugin_productselection');
		}

		if (isset($this->request->post['payment_financePlugin_price_threshold'])) {
			$data['payment_financePlugin_price_threshold'] = $this->request->post['payment_financePlugin_price_threshold'];
		} else {
			$data['payment_financePlugin_price_threshold'] = $this->config->get('payment_financePlugin_price_threshold');
		}

		if (isset($this->request->post['payment_financePlugin_cart_threshold'])) {
			$data['payment_financePlugin_cart_threshold'] = $this->request->post['payment_financePlugin_cart_threshold'];
		} else {
			$data['payment_financePlugin_cart_threshold'] = $this->config->get('payment_financePlugin_cart_threshold');
		}

		if (isset($this->request->post['payment_financePlugin_planselection'])) {
			$data['payment_financePlugin_planselection'] = $this->request->post['payment_financePlugin_planselection'];
		} else {
			$data['payment_financePlugin_planselection'] = $this->config->get('payment_financePlugin_planselection');
		}

		if (isset($this->request->post['payment_financePlugin_plans_selected'])) {
			$data['payment_financePlugin_plans_selected'] = $this->request->post['payment_financePlugin_plans_selected'];
		} elseif ($this->config->get('payment_financePlugin_plans_selected')) {
			$data['payment_financePlugin_plans_selected'] = $this->config->get('payment_financePlugin_plans_selected');
		} else {
			$data['payment_financePlugin_plans_selected'] = array();
		}

		if (isset($this->request->post['payment_financePlugin_categories'])) {
			$data['payment_financePlugin_categories'] = $this->request->post['payment_financePlugin_categories'];
		} elseif ($this->config->get('payment_financePlugin_categories')) {
			$data['payment_financePlugin_categories'] = $this->config->get('payment_financePlugin_categories');
		} else {
			$data['payment_financePlugin_categories'] = array();
		}

		$data['categories'] = array();

		$this->load->model('catalog/category');

		foreach ($data['payment_financePlugin_categories'] as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}

		try {
			$data['financePlugin_plans'] = $this->model_extension_payment_financePlugin->getAllPlans();
		} catch (Exception $e) {
			$this->log->write($e->getMessage());
			$data['financePlugin_plans'] = array();
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['environment'] = $this->config->get('payment_financePlugin_environment');

		$this->response->setOutput($this->load->view('extension/payment/financePlugin', $data));
	}


	public function order()
	{
		if (!$this->config->get('payment_financePlugin_status')) {
			return null;
		}

		$this->load->model('extension/payment/financePlugin');
		$this->load->language('extension/payment/financePlugin_order');

		$order_id = $this->request->get['order_id'];
		$lookup = $this->model_extension_payment_financePlugin->getLookupByOrderId($order_id);

		$lastStatus = $this->model_extension_payment_financePlugin->getLastStatus($order_id);
		$data['order_status'] = (!($lastStatus)) ? 'READY' : $lastStatus;

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if(isset($this->request->post['activate'])) {
				$response = $this->model_extension_payment_financePlugin->activateOrder($order_id);
				if(isset($response->error)) {
					$data['notification'] = $this->language->get('activation_error_msg');
				} else {
					$data['notification'] = $this->language->get('activation_success_msg');
					$data['order_status'] = 'AWAITING-ACTIVATION';
				}
			}elseif(isset($this->request->post['refund'])) {
				$response = $this->model_extension_payment_financePlugin->refundOrder($order_id);
				if(isset($response->error)) {
					$data['notification'] = $this->language->get('refund_error_msg');
				} else {
					$data['notification'] = $this->language->get('refund_success_msg');
					$data['order_status'] = 'REFUNDED';
				}
			}elseif(isset($this->request->post['cancel'])) {
				$response = $this->model_extension_payment_financePlugin->cancelOrder($order_id);
				if(isset($response->error)) {
					$data['notification'] = $this->language->get('cancellation_error_msg');
				} else {
					$data['notification'] = $this->language->get('cancellation_success_msg');
					$data['order_status'] = 'CANCELLED';
				}
			}

		}

		$proposal_id = null;
		$application_id = null;
		$deposit_amount = null;
		if ($lookup->num_rows == 1) {
			$lookup_data = $lookup->row;
			$proposal_id = $lookup_data['proposal_id'];
			$application_id = $lookup_data['application_id'];
			$deposit_amount = $lookup_data['deposit_amount'];
		}

		$data['proposal_id'] = $proposal_id;
		$data['application_id'] = $application_id;
		$data['deposit_amount'] = $deposit_amount;

		return $this->load->view('extension/payment/financePlugin_order', $data);
	}

	public function install()
	{
		$this->load->model('extension/payment/financePlugin');
		$this->model_extension_payment_financePlugin->install();
	}

	public function uninstall()
	{
		$this->load->model('extension/payment/financePlugin');
		$this->model_extension_payment_financePlugin->uninstall();
	}

	protected function validate()
	{
		if (!$this->user->hasPermission('modify', 'extension/payment/financePlugin')) {
			$this->error['warning'] = $this->language->get('calculator_permission_error_msg');
		}

		return !$this->error;
	}
}
