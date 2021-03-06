<?php
class ControllerExtensionModuleFinancePluginCalculator extends Controller {
	public function index() {

		$this->load->language('extension/module/financePlugin_calculator');
		$this->load->model('extension/payment/financePlugin');
		$this->load->model('catalog/product');

		$product_selection = $this->config->get('payment_financePlugin_productselection');
		$product_threshold = $this->config->get('payment_financePlugin_price_threshold');

		if (!isset($this->request->get['product_id']) || !$this->config->get('payment_financePlugin_status') || !$this->config->get('module_financePlugin_calculator_status')) {
			return false;
		}

		$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);

		$price = 0;
		if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
			$base_price = !empty($product_info['special']) ? $product_info['special'] : $product_info['price'];
			$price = $this->tax->calculate($base_price, $product_info['tax_class_id'], $this->config->get('config_tax'));
		}
		$price_text = $this->currency->format($price, $this->session->data['currency']);
		$localised_price = filter_var($price_text, FILTER_SANITIZE_NUMBER_INT);

		if ($product_selection == 'threshold' && $product_threshold > $localised_price) {
			return false;
		}

		$api_key = $this->config->get('payment_financePlugin_api_key');
		
		$widget_footnote = (empty($this->config->get('payment_financePlugin_footnote')))
			? ''
			: 'data-footnote="'.$this->config->get('payment_financePlugin_footnote').'"';
		
		$widget_btn_txt = (empty($this->config->get('payment_financePlugin_btn_txt')))
			? ''
			: 'data-button-text="'.$this->config->get('payment_financePlugin_btn_txt').'"';

		$widget_mode = (empty($this->config->get('payment_financePlugin_widget_mode')))
			? ''
			: 'data-mode="'.$this->config->get('payment_financePlugin_widget_mode').'"';

		$key_parts = explode('.', $api_key);
		$js_key = strtolower(array_shift($key_parts));

		$this->model_extension_payment_financePlugin->instantiateSDK($api_key);
		$plans = $this->model_extension_payment_financePlugin->getProductPlans($this->request->get['product_id']);

		if (!$plans) {
			return false;
		}

		$plans_ids = array_map(function ($plan) {
			return $plan->id;
		}, $plans);
		$plans_ids = array_unique($plans_ids);
		$plans_list = implode(',', $plans_ids);

		$data = array(
			'api_key'                  => $js_key,
			'product_price'            => $localised_price,
			'plan_list'                => $plans_list,
			'widget_footnote'          => $widget_footnote,
			'widget_btn_txt'           => $widget_btn_txt,
			'widget_mode'              => $widget_mode,
			'environment'              => $this->config->get('payment_financePlugin_environment')
		);

		return $this->load->view('extension/module/financePlugin_calculator', $data);
	}
}
