<?php
defined ('_JEXEC') or die('Restricted access');

/**
 * @version 1.0
 * @package  VirtueMart
 * @subpackage PaymentPlugins  - Veritrans
 * @author Rizda Dwi Prasetya (based on payment plugins developed by Valérie Isaksen)
 * @link http://www.veritrans.co.id
 * @copyright Copyright (c) 2015 Veritrans Merchant Integration Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 * Notification URL:
 * http://[YourWebsite.com]/index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component
 *
 * Finish/Unfinish/Error Url;
 * Set by code
 */

if (!class_exists ('vmPSPlugin')) {
	require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}
require_once(JPATH_SITE . '/plugins/vmpayment/veritrans/lib/veritrans-php/Veritrans.php'); 

class plgVmPaymentVeritrans extends vmPSPlugin {
	public static $_this = FALSE;
	function __construct (& $subject, $config) {
		
		//if (self::$_this)
		//   return self::$_this;
		parent::__construct($subject, $config);

		$this->_loggable = TRUE;
		$this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey = 'id'; //virtuemart_kap_id';
		$this->_tableId = 'id'; //'virtuemart_kap_id';
		$varsToPush = $this->getVarsToPush();
		//$this->setEncryptedFields(array('params'));
		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);

	}


	public function getVmPluginCreateTableSQL () {
	
		return $this->createTableSQL ('Payment Veritrans Table');
	}
	
	/**
	 * Fields to create the payment table
	 *
	 * @return string SQL Fileds
	 */
	function getTableSQLFields () {
		// error_log('getTableSQLFields'); // debug purpose
		$SQLfields = array(
            'id'                          => 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
            'virtuemart_order_id'         => 'int(1) UNSIGNED',
            'order_number'                => 'char(64)',
            'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
            'payment_name'                => 'varchar(5000)',
            'payment_type'                => 'varchar(5000)',
            'payment_order_total'         => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\'',
            'payment_currency'            => 'char(3)'
				
		);
	
		return $SQLfields;
	}
	
       /**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 *
	 */
	function plgVmOnStoreInstallPaymentPluginTable ($jplugin_id) {
		// error_log('plgVmOnStoreInstallPaymentPluginTable'); // debug purpose
		return $this->onStoreInstallPluginTable ($jplugin_id);
	}

	function plgVmConfirmedOrder($cart, $order) {
		// error_log('plgVmConfirmedOrder'); // debug purpose
		if (!($this->_currentMethod = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}

		$interface = $this->_loadVeritransInterface($this);  // DONE function
		$interface->setOrder($order);
		$interface->setCart($cart);
		$this->getPaymentCurrency($this->_currentMethod);
		$interface->setTotal($order['details']['BT']->order_total);

		$this->logInfo('plgVmConfirmedOrder order number: ' . $order['details']['BT']->order_number, 'message');
		$subscribe_id = NULL;
		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		if (!class_exists('VirtueMartModelCurrency')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'currency.php');
		}


		$email_currency = $this->getEmailCurrency($this->_currentMethod);

		// TODO save to DB!
		// Prepare data that should be stored in the database
		// $dbValues['order_number'] = $order['details']['BT']->order_number;
		// $dbValues['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
		// $dbValues['payment_name'] = $this->renderPluginName($this->_currentMethod);
		// $dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
		// $dbValues['klikandpay_custom'] = $this->getContext();
		// $dbValues['cost_per_transaction'] = $this->_currentMethod->cost_per_transaction;
		// $dbValues['cost_percent_total'] = $this->_currentMethod->cost_percent_total;
		// $dbValues['payment_currency'] = $this->_currentMethod->payment_currency;
		// $dbValues['email_currency'] = $email_currency;
		// $dbValues['payment_order_total'] = $post_variables["MONTANT"];
		// $dbValues['tax_id'] = $this->_currentMethod->tax_id;
		// $this->storePSPluginInternalData($dbValues);

		// Set our server key
		Veritrans_Config::$serverKey = $this->_currentMethod->serverkey;
		Veritrans_Config::$isProduction = ($this->_currentMethod->shop_mode == 'test') ? FALSE : TRUE;
		Veritrans_Config::$is3ds = $this->_currentMethod->is3ds ? TRUE : FALSE;

		if($this->_currentMethod->credit_card)
			$payements_type[] = 'credit_card';
		if($this->_currentMethod->mandiri_clickpay)
			$payements_type[] = 'mandiri_clickpay';
		if($this->_currentMethod->cimb_clicks)
			$payements_type[] = 'cimb_clicks';
		if($this->_currentMethod->bank_transfer)
			$payements_type[] = 'bank_transfer';
		if($this->_currentMethod->bri_epay)
			$payements_type[] = 'bri_epay';
		if($this->_currentMethod->telkomsel_cash)
			$payements_type[] = 'telkomsel_cash';
		if($this->_currentMethod->xl_tunai)
			$payements_type[] = 'xl_tunai';
		if($this->_currentMethod->echannel)
			$payements_type[] = 'echannel';
		if($this->_currentMethod->bbm_money)
			$payements_type[] = 'bbm_money';
		if($this->_currentMethod->cstore)
			$payements_type[] = 'cstore';
		if($this->_currentMethod->credit_card)
			$payements_type[] = 'indosat_dompetku';

		$conversion_rate = floatval($this->_currentMethod->conversion_rate);
		if(!isset($conversion_rate) OR $conversion_rate='' OR $conversion_rate='1'){
			$conversion_rate = 1;
		}
		$gross_amount= 0;
	  	$items_details = array();

	  	//push item to item details array
		foreach ($order['items'] as $line_item_wrapper) {
			$item = array();
			$line_item_price = $line_item_wrapper->product_final_price;
			$item['id'] = $line_item_wrapper->virtuemart_order_item_id;
			$item['quantity'] = $line_item_wrapper->product_quantity;
			$item['price'] = ceil($line_item_price * $conversion_rate);
			$item['name'] = $line_item_wrapper->order_item_name;
			$items_details[] = $item;

			$gross_amount += $item['price'] * $item['quantity'];
		}

		//push shipment & shipment tax to item details
		$item = array();
		$item['id'] = 'sp';
		$item['quantity'] = 1;
		$item['price'] = ceil(($order['details']['BT']->order_shipment + $order['details']['BT']->order_shipment_tax) * $conversion_rate);
		$item['name'] = "Shipment & Shipment tax";
		$items_details[] = $item;

		$gross_amount += $item['price'] * $item['quantity'];

		//push discount to item details
		$item = array();
		$item['id'] = 'dc';
		$item['quantity'] = 1;
		$item['price'] = -(ceil($order['details']['BT']->coupon_discount) * $conversion_rate);
		$item['name'] = "Coupon Discount";
		$items_details[] = $item;

		$gross_amount += $item['price'] * $item['quantity'];

		// Billing name
		$fname = $order['details']['BT']->first_name;
		if (isset($order['details']['BT']->middle_name) and $order['details']['BT']->middle_name) {
			$fname .= $order['details']['BT']->middle_name;
		}
		$lname = $order['details']['BT']->last_name;
		$address = $order['details']['BT']->address_1;
		if (isset($order['details']['BT']->address_2) and $order['details']['BT']->address_2) {
			$address .= $order['details']['BT']->address_2;
		}

		// check if both phone field filled, append both
		$appender = '';
		if(isset($order['details']['BT']->phone_1) && isset($order['details']['BT']->phone_2)){
			$appender = ', ';
		}
		// Fill transaction data
		// /index.php?option=com_virtuemart&view=vmplg&task=pluginUserPaymentCancel
		
		$finish_url = JURI::root().'index.php?option=com_virtuemart&view=vmplg&task=pluginresponsereceived&';
		$back_url = JURI::root().'index.php?option=com_virtuemart&view=vmplg&task=pluginUserPaymentCancel&';
		// error_log($back_url);  // debug purpose
		$transaction = array(
		    "vtweb" => array (
		        'finish_redirect_url' => $finish_url,
		        'unfinish_redirect_url' => $back_url,
		        'error_redirect_url' => $back_url,
		        'enabled_payments' => $payements_type,
		        ),
		    'transaction_details' => array(
		        'order_id' => $order['details']['BT']->virtuemart_order_id,
		        // 'gross_amount' => $interface->getTotal(), // no decimal allowed for creditcard
		        'gross_amount' => $gross_amount, // no decimal allowed for creditcard
		        ),
		    'item_details' => $items_details,
		    'customer_details' => array(
			    'first_name' => $fname,
			    'last_name' => $lname,
			    'email' => $order['details']['BT']->email,
			    'phone' => $order['details']['BT']->phone_1,
			    'billing_address' => array(
			        'first_name' => $fname,
			        'last_name' => $lname,
			        'address' => $address,
			        'city' => $order['details']['BT']->city,
			        'postal_code' => $order['details']['BT']->zip,
			        // 'country_code' => null,
			        'phone' => $order['details']['BT']->phone_1.$appender.$order['details']['BT']->phone_2,
			    	),
				),
		    );

		// Add shipment details if exists
		if(array_key_exists('ST', $order['details'])){
			// check if both phone field filled, append both
			$appender = '';
			if(isset($order['details']['ST']->phone_1) && isset($order['details']['ST']->phone_2)){
				$appender = ', ';
			}
			// Shipping name
			$sfname = $order['details']['ST']->first_name;
			if (isset($order['details']['ST']->middle_name) and $order['details']['ST']->middle_name) {
				$sfname .= $order['details']['ST']->middle_name;
			}
			$slname = $order['details']['ST']->last_name;
			$saddress = $order['details']['ST']->address_1;
			if (isset($order['details']['ST']->address_2) and $order['details']['ST']->address_2) {
				$saddress .= $order['details']['ST']->address_2;
			}

			$shipping_address = array(
		        'first_name' => $sfname,
		        'last_name' => $slname,
		        'address' => $saddress,
		        'city' => $order['details']['ST']->city,
		        'postal_code' => $order['details']['ST']->zip,
		        // 'country_code' => null,
		        'phone' => $order['details']['ST']->phone_1.$appender.$order['details']['ST']->phone_2,
		    	);
			$transaction['customer_details']['shipping_address'] = $shipping_address;
		}

		// error_log('$transaction = '.print_r($transaction,true)); // debug purpose
		
		$vtweb_url = Veritrans_Vtweb::getRedirectionUrl($transaction);

		$html = $this->getConfirmedHtml($vtweb_url, $interface, $subscribe_id);
		// 	2 = don't delete the cart, don't send email and don't redirect
		$cart->_confirmDone = FALSE;
		$cart->_dataValidated = FALSE;
		$cart->setCartIntoSession();
		vRequest::setVar('display_title', false);
		vRequest::setVar('html', $html);

		// $this->emptyCart(); 
		header("Location: ".$vtweb_url);
		return;
	}


	function plgVmOnPaymentNotification() {

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		$raw_notification = json_decode(file_get_contents('php://input'), true);
		// error_log('xx raw_notification :'.print_r($order_history,true)); // debug purpose
		if (empty($raw_notification)) {
			$this->debugLog('Notification URL accessed with no POST data submitted.', 'plgVmOnPaymentNotification', 'debug', false);
			return FALSE;
		}

		$virtuemart_order_id = $raw_notification['order_id'] ;
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);
		$virtuemart_paymentmethod_id = $order['details']['BT']->virtuemart_paymentmethod_id;
		$this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id);
		$interface = $this->_loadVeritransInterface($this);

		Veritrans_Config::$serverKey = $this->_currentMethod->serverkey;
		Veritrans_Config::$isProduction = ($this->_currentMethod->shop_mode == 'test') ? FALSE : TRUE;

		$response = new Veritrans_Notification();

		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);

		$order_history = $this->updateOrderStatus($interface, $response, $order);

		// error_log('xx response :'.print_r($response,true)); // debug purpose
		// error_log('xx order history :'.print_r($order_history,true)); // debug purpose
		if($order_history == FALSE){
			// error_log("settled"); //debug purpose
			return TRUE;
		}

		return TRUE;
	}


	function updateOrderStatus($interface, $response, $order) {
		$transaction_status = $response->transaction_status;
    	$fraud = $response->fraud_status;
    	$payment_type = $response->payment_type;
    	$payment_status = $this->_currentMethod->status_waiting;

	    $payment_status = $this->_currentMethod->status_canceled;
	    $comments = 'payment could not be processed';

	    if ($transaction_status == 'capture') {
	      if ($fraud == 'challenge') {
	        $payment_status = $this->_currentMethod->status_waiting;
	        $comments = 'Payment status: Challenge, please resolve in Veritrans MAP';
	      }
	      elseif ($fraud == 'accept') {
	        $payment_status = $this->_currentMethod->status_success;
	        $comments = 'Payment accepted via Veritrans';
	      }
	    }
	    elseif ($transaction_status == 'settlement') {
	      if($payment_type != 'credit_card'){
	        $payment_status = $this->_currentMethod->status_success;
	        $comments = 'Payment accepted via Veritrans';
	      } else{
	        return FALSE;
	      }
	    }
	    elseif ($transaction_status == 'pending') {
	        $payment_status = $this->_currentMethod->status_waiting;
	    }
	    elseif ($transaction_status == 'cancel') {    
	        $payment_status = $this->_currentMethod->status_canceled;
	    }
	    elseif ($transaction_status == 'deny') {
	        $payment_status = $this->_currentMethod->status_canceled;
	    }else{
	        $payment_status = $this->_currentMethod->status_canceled;
	    }

		$order_history['comments'] = $comments;
		$order_history['order_status'] = $payment_status;
		$order_history['customer_notified'] = true;

		## TODO DB stuffs
		## here

		$modelOrder = VmModel::getModel('orders');
		$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order_history, TRUE);
		return $order_history;
	}


	function getConfirmedHtml($vtweb_url, $interface, $subscribe_id = NULL) {
		// error_log('getConfirmedHtml'); // debug purpose
		if (vmconfig::get('css')) {
			$msg = vmText::_('Please wait while redirecting to VT-Web Veritrans', true);
		} else {
			$msg='';
		}
		vmJsApi::addJScript('vm.paymentFormAutoSubmit', '
  			jQuery(document).ready(function($){
   				jQuery("body").addClass("vmLoading");
  				var msg="'.$msg.'";
   				jQuery("body").append("<div class=\"vmLoadingDiv\"><div class=\"vmLoadingDivMsg\">"+msg+"</div></div>");
    			jQuery("#vmPaymentForm").submit();
    			window.location.href = "'.$vtweb_url.'";
			})
		');

		$html = '';
		$html .= '<form action="' . $vtweb_url . '" method="post" name="vm_veritrans_form" id="vmPaymentForm" accept-charset="UTF-8">';
		$html .= '<input type="hidden" name="charset" value="utf-8">';
		$html .= '<input type="submit"  value="' . vmText::_('Please wait while redirecting to VT-Web Veritrans') . '" />';
		$html .= '</form>';
		return $html;
	}


	function plgVmOnPaymentResponseReceived(&$html) {

		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		if (!class_exists('shopFunctionsF')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}
		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}

		VmConfig::loadJLang('com_virtuemart_orders', TRUE);

		$virtuemart_order_id = vRequest::getString('order_id', '');
		if (!$virtuemart_order_id) {
			return NULL;
		}
				
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);
		$virtuemart_paymentmethod_id = $order['details']['BT']->virtuemart_paymentmethod_id;
		$this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id);


		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}

		$html = $this->getResponseHTML($order);
		$cart = VirtueMartCart::getCart();
		$cart->emptyCart();
		vRequest::setVar('display_title', false);
		vRequest::setVar('html', $html);

		return TRUE;

	}

	function getResponseHTML($order) {

		$payment_name = $this->renderPluginName($this->_currentMethod);
		VmConfig::loadJLang('com_virtuemart_orders', TRUE);

		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		$cart = VirtueMartCart::getCart();
		$amountInCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $order['details']['BT']->order_currency);
		$currencyDisplay = CurrencyDisplay::getInstance($cart->pricesCurrency);

		$html = $this->renderByLayout('response', array(
			'order_number' =>$order['details']['BT']->order_number,
			'order_pass' =>$order['details']['BT']->order_pass,
			'payment_name' => 'Veritrans',
			'displayTotalInPaymentCurrency' => $amountInCurrency['display']
		));

		return $html;
	}

	function plgVmDeclarePluginParamsPaymentVM3( &$data) {
		return $this->declarePluginParams('payment', $data);
	}

	/*********************/
	/* Private functions */
	/*********************/
	private function _loadVeritransInterface() {	
		if (!class_exists('VeritransHelperVeritrans'))
			require(JPATH_SITE . '/plugins/vmpayment/veritrans/veritrans/helpers/veritrans.php');
		$veritransInterface = new VeritransHelperVeritrans($this->_currentMethod, $this);
		return $veritransInterface;
	}

	function plgVmOnShowOrderBEPayment($virtuemart_order_id, $payment_method_id) {

		if (!$this->selectedThisByMethodId($payment_method_id)) {
			return NULL; // Another method was selected, do nothing
		}
		return NULL;
	}


	/**
	 * This event is fired after the payment method has been selected. It can be used to store
	 * additional payment info in the cart.
	 *
	 * @author Max Milbers
	 * @author Valérie isaksen
	 *
	 * @param VirtueMartCart $cart: the actual cart
	 * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
	 *
	 */
	public function plgVmOnSelectCheckPayment(VirtueMartCart $cart, &$msg) {
		if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}

		$interface = $this->_loadVeritransInterface($this);
		return $interface->onSelectCheck($cart);
	}

	/**
	 * plgVmDisplayListFEPayment
	 * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
	 *
	 * @param object $cart Cart object
	 * @param integer $selected ID of the method selected
	 * @return boolean True on succes, false on failures, null when this plugin was not selected.
	 * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 *
	 * @author Valerie Isaksen
	 */
	public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {
		// error_log('display list FE VT'); // debug purpose
		return $this->displayListFE($cart, $selected, $htmlIn);
	}


	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the method-specific data.
	 *
	 * @param integer $order_id The order ID
	 * @return mixed Null for methods that aren't active, text (HTML) otherwise
	 * @author Max Milbers
	 * @author Valerie Isaksen
	 */
	// public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {

	// 	$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
	// }

	/**
	 * This event is fired during the checkout process. It can be used to validate the
	 * method data as entered by the user.
	 *
	 * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
	 * @author Max Milbers
	 */
	public function plgVmOnCheckoutCheckDataPayment(VirtueMartCart $cart) {
		if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}

		$interface = $this->_loadVeritransInterface($this);
		$interface->setCart($cart);
		$interface->setTotal($cart->cartPrices['billTotal']);
		return $interface->onCheckoutCheckDataPayment($cart);
	}


	/** ================================================================================================
	================================================================================================= */

	function getCosts(VirtueMartCart $cart, $method, $cart_prices) {
		return 0;
    }

    /**
     * Check if the payment conditions are fulfilled for this payment method
     * @author: Valerie Isaksen
     *
     * @param $cart_prices: cart prices
     * @param $payment
     * @return true: if the conditions are fulfilled, false otherwise
     *
     */
    protected function checkConditions($cart, $method, $cart_prices) {
		return true;
    }
    /**
     * We must reimplement this triggers for joomla 1.7
     */


    /*
     * plgVmonSelectedCalculatePricePayment
     * Calculate the price (value, tax_id) of the selected method
     * It is called by the calculator
     * This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
     * @author Valerie Isaksen
     * @cart: VirtueMartCart the current cart
     * @cart_prices: array the new cart prices
     * @return null if the method was not selected, false if the shiiping rate is not valid any more, true otherwise
     *
     *
     */

    public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
	return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
    }

    /**
     * plgVmOnCheckAutomaticSelectedPayment
     * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
     * The plugin must check first if it is the correct type
     * @author Valerie Isaksen
     * @param VirtueMartCart cart: the cart object
     * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
     *
     */
    function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array()) {
	return $this->onCheckAutomaticSelected($cart, $cart_prices);
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the method-specific data.
     *
     * @param integer $order_id The order ID
     * @return mixed Null for methods that aren't active, text (HTML) otherwise
     * @author Max Milbers
     * @author Valerie Isaksen
     */
    protected function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {
	  $this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
    }

    /**
     * This event is fired during the checkout process. It can be used to validate the
     * method data as entered by the user.
     *
     * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
     * @author Max Milbers

      public function plgVmOnCheckoutCheckDataPayment($psType, VirtueMartCart $cart) {
      return null;
      }
     */

    /**
     * This method is fired when showing when priting an Order
     * It displays the the payment method-specific data.
     *
     * @param integer $_virtuemart_order_id The order ID
     * @param integer $method_id  method used for this order
     * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    function plgVmonShowOrderPrintPayment($order_number, $method_id) {
	return $this->onShowOrderPrint($order_number, $method_id);
    }

    /**
     * Save updated order data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk

      public function plgVmOnUpdateOrderPayment(  $_formData) {
      return null;
      }
     */
    /**
     * Save updated orderline data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk

      public function plgVmOnUpdateOrderLine(  $_formData) {
      return null;
      }
     */
    /**
     * plgVmOnEditOrderLineBE
     * This method is fired when editing the order line details in the backend.
     * It can be used to add line specific package codes
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk

      public function plgVmOnEditOrderLineBE(  $_orderId, $_lineId) {
      return null;
      }
     */

    /**
     * This method is fired when showing the order details in the frontend, for every orderline.
     * It can be used to display line specific package codes, e.g. with a link to external tracking and
     * tracing systems
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk

      public function plgVmOnShowOrderLineFE(  $_orderId, $_lineId) {
      return null;
      }
     */
    function plgVmDeclarePluginParamsPayment($name, $id, &$data) {
	return $this->declarePluginParams('payment', $name, $id, $data);
    }

    function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {
	return $this->setOnTablePluginParams($name, $id, $table);
    }
}
//no close php tag