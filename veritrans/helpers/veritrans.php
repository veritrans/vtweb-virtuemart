<?php

defined('_JEXEC') or die('Restricted access');


class  VeritransHelperVeritrans {

	const RESPONSE_SUCCESS = "00";
	const RESPONSE_AWAITING = "AWAITING";
	const RESPONSE_AWAITINGCHEQUE = "AWAITINGCHEQUE";


	function __construct($method, $plugin) {
		$this->_method = $method;
		$this->plugin = $plugin;
	}

	/**
	 * @param $order
	 */
	public function setOrder($order) {
		$this->order = $order;
	}

	/**
	 * @param $cart
	 */
	public function setCart($cart) {
		$this->cart = $cart;
	}


	/**
	 * @param $total
	 */
	public function setTotal($total) {
		if (!class_exists('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		// $this->total = vmPSPlugin::getAmountValueInCurrency($total, $this->_method->payment_currency);
		$this->total = $total;

		$cd = CurrencyDisplay::getInstance($this->cart->pricesCurrency);
	}

	/**
	 * @return mixed
	 */
	public function getTotal() {
		return $this->total;
	}


	function checkConditions($cart) {
		return true;
	}

	function onCheckoutCheckDataPayment(VirtueMartCart $cart) {
		return true;
	}

	function onSelectCheck(VirtueMartCart $cart) {
		return true;
	}

	function getOrderDetails($order) {
		$orderDetails = '';
		foreach ($order['items'] as $item) {
			$product_sku = str_replace(array('%', ':', '|'), '-', $item->order_item_sku);
			$product_name = str_replace(array('%', ':', '|'), '-', $item->order_item_name);
			$price = $item->product_final_price;
			$qty = $item->product_quantity;
			$orderDetails .= "REF:" . $product_sku . "%Q:" . $qty . "%PRIX:" . $price . "%PROD:" . $product_name . "|";
		}
		return $orderDetails;
	}


	function getLanguage() {

		$langVeritrans = array(
			'fr' => 'fr',
			'en' => 'en',
			'es' => 'es',
			'it' => 'ir',
			'de' => 'de',
			'nl' => 'du',
		);
		$lang = JFactory::getLanguage();
		$tag = strtolower(substr($lang->get('tag'), 0, 2));
		if (array_key_exists($tag, $langVeritrans)) {
			return $langVeritrans[$tag];
		} else {
			return $langVeritrans['en'];
		}
	}


	// function getVeritransServerUrl($id = NULL) {


	// 	if ($this->_method->shop_mode == 'test') {
	// 		$url = 'https://www.klikandpay.com/paiementtest/check.pl';
	// 	} else {
	// 		$url = 'https://www.klikandpay.com/paiement/check.pl';
	// 	}
	// 	return $url;

	// }


	/**
	 * @param $veritrans_data
	 * @return mixed
	 */
	function getOrderNumber($order_number) {
		return $order_number;
	}

	/**
	 * @return array
	 */
	function getExtraPluginNameInfo() {

		return false;
	}

	/**
	 * KP returns AWAITINGCHEQUE OR AWAITING as response for payments other than CC
	 * @param $veritrans_data
	 * @param $order
	 * @return mixed
	 */
	function getOrderHistory($veritrans_data, $order, $payments) {
		$amountInCurrency = vmPSPlugin::getAmountInCurrency($veritrans_data['MONTANTXKP'], $veritrans_data['DEVISEXKP']);
		$order_history['comments'] = vmText::sprintf('VMPAYMENT_VERITRANS_PAYMENT_STATUS_CONFIRMED', $amountInCurrency['display'], $order['details']['BT']->order_number);
		$order_history['customer_notified'] = true;
		if ($veritrans_data['RESPONSE'] == self::RESPONSE_AWAITINGCHEQUE or $veritrans_data['RESPONSE'] == self::RESPONSE_AWAITING) {
			$order_history['order_status'] = $this->_method->status_waiting;
		} else {
			$order_history['order_status'] = $this->_method->status_success;
		}
		return $order_history;
	}

	function isResponseValid($veritrans_data, $order, $payments) {
		if ($veritrans_data['RESPONSE'] == self::RESPONSE_SUCCESS OR $veritrans_data['RESPONSE'] == self::RESPONSE_AWAITINGCHEQUE or $veritrans_data['RESPONSE'] == self::RESPONSE_AWAITING) {
			return true;
		} else {
			return false;
		}
	}

	function isResponseSuccess($response) {
		return ($response == self::RESPONSE_SUCCESS);
	}


}
