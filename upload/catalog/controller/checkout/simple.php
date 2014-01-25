<?php  
class ControllerCheckoutSimple extends Controller { 
	public function index() {
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->redirect($this->url->link('checkout/cart'));
		}

		// Validate minimum quantity requirments.			
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		

			if ($product['minimum'] > $product_total) {
				$this->redirect($this->url->link('checkout/cart'));
			}				
		}

		$this->language->load('checkout/checkout');

		$this->document->setTitle($this->language->get('heading_title')); 
		//$this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		//$this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_cart'),
			'href'      => $this->url->link('checkout/cart'),
			'separator' => $this->language->get('text_separator')
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		);

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_checkout_option'] = $this->language->get('text_checkout_option');
		$this->data['text_checkout_account'] = $this->language->get('text_checkout_account');
		$this->data['text_checkout_payment_address'] = $this->language->get('text_checkout_payment_address');
		$this->data['text_checkout_shipping_address'] = $this->language->get('text_checkout_shipping_address');
		$this->data['text_checkout_shipping_method'] = $this->language->get('text_checkout_shipping_method');
		$this->data['text_checkout_payment_method'] = $this->language->get('text_checkout_payment_method');		
		$this->data['text_checkout_confirm'] = $this->language->get('text_checkout_confirm');
		$this->data['text_modify'] = $this->language->get('text_modify');

		$this->data['logged'] = $this->customer->isLogged();
		$this->data['shipping_required'] = $this->cart->hasShipping();	

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/simple.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/checkout/simple.tpl';
		} else {
			$this->template = 'default/template/checkout/checkout.tpl';
		}

		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'	
		);

		if (!isset($this->data['firstname'])) $this->data['firstname'] = '';
		if (!isset($this->data['telephone'])) $this->data['telephone'] = '';
		if (!isset($this->data['payment_address'])) $this->data['payment_address'] = '';
		if (!isset($this->data['error']))     $this->data['error'] = array();

		$this->response->setOutput($this->render());
	}
	
	public function confirm() {
		$this->data['error'] = array();

		if (isset($this->request->post['firstname'])) {
			$len = utf8_strlen($this->request->post['firstname']);
			if ($len < 1|| $len > 32) {
				$this->data['error']['firstname'] = 'Имя должно быть от 1 до 32 символов!';
			}
		}

		if (isset($this->request->post['telephone'])) {
			$len = utf8_strlen($this->request->post['telephone']);
			if ($len < 3|| $len > 32) {
				$this->data['error']['telephone'] = 'Номер телефона должен быть от 3 до 32 символов!';
			}
		}
		
		if (isset($this->request->post['payment_address'])) {
			$len = utf8_strlen($this->request->post['payment_address']);
			if ($len < 3 || $len > 128) {
				$this->data['error']['payment_address'] = 'Адрес должен быть от 3 до 128 символов!';
			}
		}

		if (isset($this->request->post['comment'])) {
			$len = utf8_strlen($this->request->post['comment']);
			if ($len > 500) {
				$this->data['error']['comment'] = 'Комментарий должен быть до 500 символов!';
			}
		}

		$this->data['firstname'] = $this->request->post['firstname'];
		$this->data['telephone'] = $this->request->post['telephone'];
		$this->data['payment_address'] = $this->request->post['payment_address'];
		$this->data['comment']   = $this->request->post['comment'];

		if (!empty($this->data['error'])) {
			return $this->index();
		}

		// Validate cart has products and has stock.	
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->redirect($this->url->link('checkout/cart'));			
		}

		// Validate minimum quantity requirments.		
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$this->redirect($this->url->link('checkout/cart'));

				break;
			}
		}
		
		$total_data = array();
		$total = 0;
		$taxes = $this->cart->getTaxes();

		$this->load->model('setting/extension');

		$sort_order = array(); 

		$results = $this->model_setting_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($this->config->get($result['code'] . '_status')) {
				$this->load->model('total/' . $result['code']);

				$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
			}
		}

		$sort_order = array(); 

		foreach ($total_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $total_data);

		$this->language->load('checkout/checkout');

		$data = array();

		$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$data['store_id'] = $this->config->get('config_store_id');
		$data['store_name'] = $this->config->get('config_name');

		if ($data['store_id']) {
			$data['store_url'] = $this->config->get('config_url');		
		} else {
			$data['store_url'] = HTTP_SERVER;	
		}

		$data['customer_id'] = 0;
		$data['customer_group_id'] = 0;
		$data['firstname'] = $this->data['firstname'];
		$data['lastname'] = '';
		$data['email'] = '';
		$data['telephone'] = $this->data['telephone'];
		$data['fax'] = '';

		$data['payment_firstname'] = '';
		$data['payment_lastname'] = '';
		$data['payment_company'] = '';
		$data['payment_company_id'] = '';
		$data['payment_tax_id'] = '';
		$data['payment_address_1'] = $this->data['payment_address'];
		$data['payment_address_2'] = '';
		$data['payment_city'] = '';
		$data['payment_postcode'] = '';
		$data['payment_zone'] = 'Брянская область';
		$data['payment_zone_id'] = '2730';
		$data['payment_country'] = 'Российская Федерация';
		$data['payment_country_id'] = '176';
		$data['payment_address_format'] = '';
		$data['payment_method'] = 'Оплата при доставке';
		$data['payment_code'] = 'cod';
		$data['shipping_firstname'] = '';
		$data['shipping_lastname'] = '';	
		$data['shipping_company'] = '';	
		$data['shipping_address_1'] = '';
		$data['shipping_address_2'] = '';
		$data['shipping_city'] = '';
		$data['shipping_postcode'] = '';
		$data['shipping_zone'] = 'Брянская область';
		$data['shipping_zone_id'] = '2730';
		$data['shipping_country'] = 'Российская Федерация';
		$data['shipping_country_id'] = '176';
		$data['shipping_address_format'] = '';
		$data['shipping_method'] = 'Фиксированная стоимость доставки';
		$data['shipping_code'] = 'flat.flat';

		$product_data = array();

		foreach ($this->cart->getProducts() as $product) {
			$option_data = array();

			foreach ($product['option'] as $option) {
				if ($option['type'] != 'file') {
					$value = $option['option_value'];	
				} else {
					$value = $this->encryption->decrypt($option['option_value']);
				}	

				$option_data[] = array(
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'option_id'               => $option['option_id'],
					'option_value_id'         => $option['option_value_id'],								   
					'name'                    => $option['name'],
					'value'                   => $value,
					'type'                    => $option['type']
				);					
			}

			$product_data[] = array(
				'product_id' => $product['product_id'],
				'name'       => $product['name'],
				'model'      => $product['model'],
				'option'     => $option_data,
				'download'   => $product['download'],
				'quantity'   => $product['quantity'],
				'subtract'   => $product['subtract'],
				'price'      => $product['price'],
				'total'      => $product['total'],
				'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
				'reward'     => $product['reward']
			); 
		}

		// Gift Voucher
		$voucher_data = array();  

		$data['products'] = $product_data;
		$data['vouchers'] = $voucher_data;
		$data['totals'] = $total_data;
		$data['comment'] = $this->data['comment'];
		$data['total'] = $total;
		$data['affiliate_id'] = 0;
		$data['commission'] = 0;

		$data['language_id'] = $this->config->get('config_language_id');
		$data['currency_id'] = $this->currency->getId();
		$data['currency_code'] = $this->currency->getCode();
		$data['currency_value'] = $this->currency->getValue($this->currency->getCode());
		$data['ip'] = $this->request->server['REMOTE_ADDR'];

		if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
			$data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];	
		} elseif(!empty($this->request->server['HTTP_CLIENT_IP'])) {
			$data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];	
		} else {
			$data['forwarded_ip'] = '';
		}

		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];	
		} else {
			$data['user_agent'] = '';
		}

		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];	
		} else {
			$data['accept_language'] = '';
		}

		$this->load->model('checkout/order');

		$this->session->data['order_id'] = $this->model_checkout_order->addOrder($data);

		$this->redirect('/index.php?route=checkout/success');	
	}
}
?>