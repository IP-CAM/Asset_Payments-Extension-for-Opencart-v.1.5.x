<?php
class ControllerPaymentAssetPayments extends Controller {
	protected function index() {
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		
		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$this->data['action'] = 'https://assetpayments.us/checkout/pay';
		
		//****Adding cart details****//
		$order_total = 0;  //Calculating order total to get shipping cost
		foreach ($this->cart->getProducts() as $product) {
			$request['Products'][] = array(
				"ProductId" => $product['model'],
				"ProductName" => $product['name'],
				"ProductPrice" => $product['price'],
				"ProductItemsNum" => $product['quantity'],
				"ImageUrl" => (isset($product['image']))?'http://'.$_SERVER['HTTP_HOST'] .'/image/'. $product['image']:'',
			);
			$order_total += $product['price'] * $product['quantity'];
		}
		
		//****Adding shipping method****//
		$shipping_cost = $order_info['total'] - $order_total; //Calculating shipping cost
		$request['Products'][] = array(
				"ProductId" => '12345',
				"ProductName" => $order_info['shipping_method'],
				"ProductPrice" => $shipping_cost,
				"ImageUrl" => 'https://assetpayments.com/dist/css/images/delivery.png',
				"ProductItemsNum" => 1,
			);
		
		//****Country ISO fix****//
		$country = $order_info['shipping_iso_code_3'];
		if ($country == ''){
			$country = 'UKR';
		}
		
		$send_data = Array(
			'TemplateId' => $this->config->get('assetpayments_type'),
            'MerchantInternalOrderId' => $this->session->data['order_id'],
            'StatusURL' => $this->url->link('payment/assetpayments/callback', '', true),
            'ReturnURL' => $this->url->link('checkout/success', '', true),
			'FirstName' => $order_info['payment_firstname']. ' ' . $order_info['payment_lastname'],
            'LastName' => $order_info['payment_lastname'],
            'Email' => $order_info['email'],
            'Phone' => $order_info['telephone'],           
            'Address' => $order_info['payment_address_1'] . ' ' . $order_info['payment_address_2'] . ' ' . $order_info['payment_city'].' '.$order_info['payment_country'] . ' ' . $order_info['payment_postcode'],           
            'Country' => $order_info['payment_country'],           
            'Zip' => $order_info['payment_postcode'],           
            'City' => $order_info['payment_city'],           
            'Region' => $order_info['payment_zone'],           
            'CountryISO' => $country, 
			'Amount' => $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false),
            'Currency' => $order_info['currency_code'],
			'CustomMerchantInfo' => 'OpenCart: ' .''. VERSION,
            'AssetPaymentsKey' => $this->config->get('assetpayments_merchant'),
			'Products' => $request['Products']
          );
		
		$this->data['xml'] = base64_encode(json_encode($send_data));
				
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/assetpayments.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/assetpayments.tpl';
		} else {
			$this->template = 'default/template/payment/assetpayments.tpl';
		}	
		
		$this->render();
	}

	public function callback() {
		
		$json = json_decode(file_get_contents('php://input'), true);

		$key = $this->config->get('assetpayments_merchant');
		$secret = $this->config->get('assetpayments_signature');
		$transactionId = $json['Payment']['TransactionId'];
		$signature = $json['Payment']['Signature'];
		$order_id = $json['Order']['OrderId'];
		$status = $json['Payment']['StatusCode'];

		$requestSign =$key.':'.$transactionId.':'.strtoupper($secret);
		$sign = hash_hmac('md5',$requestSign,$secret);
		
		if ($status == 1 && $sign == $signature) {
			$this->load->model('checkout/order');
			$this->model_checkout_order->confirm($order_id, $this->config->get('assetpayments_order_status_id'), 'AssetPayments TransactionID: ' .$transactionId);
			$this->model_checkout_order->update($order_id, $this->config->get('assetpayments_order_status_id'),'AssetPayments TransactionID: ' .$transactionId,TRUE);
			
		} 
		if ($status == 2 && $sign == $signature) {
			$this->load->model('checkout/order');
			$this->model_checkout_order->confirm($order_id, $this->config->get('assetpayments_failorder_status_id'), 'Payment FAILED TransactionID: ' .$transactionId);
			$this->model_checkout_order->update($order_id, $this->config->get('assetpayments_failorder_status_id'),'Payment FAILED TransactionID: ' .$transactionId,TRUE);
		}
	}
}
?>