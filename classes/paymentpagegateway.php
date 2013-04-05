<?php
//
// Definition of PaymentPage class
//
// An abstract class for implementing transparent credit card
// payment in eZ publish using cURL.
//
//
// SOFTWARE NAME: paymentpage extension for eZ Publish
// SOFTWARE RELEASE: 1.1.0
// COPYRIGHT NOTICE: Copyright (C) 1999-2010 eZ Systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
// This program is free software; you can redistribute it and/or
// modify it under the terms of version 2.0 of the GNU General
// Public License as published by the Free Software Foundation.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of version 2.0 of the GNU General
// Public License along with this program; if not, write to the Free
// Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
// MA 02110-1301, USA.


class PaymentPageGateway extends eZCurlGateway
{


	const EZ_PAYMENT_GATEWAY_TYPE_PAYMENTPAGE = "paymentpage";
	/*!
	 Constructor
	 */
	function PaymentPageGateway()
	{
		$this->logger = eZPaymentLogger::CreateForAdd( "var/log/eZPaymentGatewayType.log" );
		$ini = eZINI::instance( 'paymentpage.ini' );
		$this->mode = $ini->variable('paymentpage','Mode');
		$paymentpageSettings = 'PaymentPageSettings_'.$this->mode;
		
		$this->Username = $ini->variable($paymentpageSettings,'UserName');
		$this->password = $ini->variable($paymentpageSettings,'password');
		$this->accountID = $ini->variable($paymentpageSettings,'accountID');
		
		$this->create_url = $ini->variable($paymentpageSettings,'create_url');
		$this->verifyConfirmURL = $ini->variable($paymentpageSettings,'verifyConfirmURL');
		$this->payComleteURL = $ini->variable($paymentpageSettings,'payComleteURL');
		
		$this->currency = $ini->variable($paymentpageSettings,'currency');
		$this->successlink = $ini->variable($paymentpageSettings,'successlink');
		$this->faillink = $ini->variable($paymentpageSettings,'faillink');
		$this->backlink = $ini->variable($paymentpageSettings,'backlink');
		$this->description = $ini->variable($paymentpageSettings,'description');
		$this->configPP = $ini->variable($paymentpageSettings,'configPP');
		$this->paymentMethods = $ini->variable($paymentpageSettings,'paymentMethods');
	}


	function loadForm( $process, $errors = 0 )
	{
		$http = eZHTTPTool::instance();

		// get parameters
		$processParams = $process->attribute( 'parameter_list' );

		// load ini
		$ini = eZINI::instance( 'paymentpage.ini' );
		
		//create link
		//1. create PAYINIT		
		$payment_url = $this->createPayinit($process);
		
		$tplVars['link'] = $payment_url;
		
		$process->Template=array
		(
            'templateName' => 'design:workflow/eventtype/result/' . 'paymentpage_redirect.tpl',
            'templateVars' => $tplVars
		);

		return eZWorkflowType::STATUS_FETCH_TEMPLATE_REPEAT;
	}

	
	function createPayinit( $process )
	{
		
		$ini = eZINI::instance( 'paymentpage.ini' );
			
		// load http
		$http = eZHTTPTool::instance();
		//
		// make the order object
		$processParams = $process->attribute( 'parameter_list' );
		//
		// get order id
		$order_id = $processParams['order_id'];
		//
		// get order
		$order = eZOrder::fetch( $processParams['order_id'] );
		//
		// get total order amount, including tax
		$order_total_amount = $this->priceFromat($order->attribute( 'total_inc_vat' ));
	
		// get user id
		$user_id = $processParams['user_id'];
		
		//$user_data = eZOrder::accountInformation();
		//$paymentpage = new paymentpageHandler();
		
		$url_verify = $this->create_url;
		
		
		$user_data = $order->accountInformation();
	
		$pswd = $this->password;         
		$accountid = $this->accountID;
		$currency = $this->currency;
		$amount = $order_total_amount;

		$attributes  = "?ACCOUNTID=" . $accountid;
		$attributes .= "&AMOUNT=" . $amount;
		$attributes .= "&CURRENCY=" . $currency;
		$attributes .= "&ORDERID=" . $order_id;
		$attributes .= "&SUCCESSLINK=".urlencode($this->successlink);
		$attributes .= "&FAILLINK=" . urlencode($this->faillink);
		$attributes .= "&BACKLINK=" . urlencode($this->backlink);
		$attributes .= "&DESCRIPTION=" . urlencode($this->description);
		//$attributes .= "&PAYMENTMETHODS=" . urlencode($this->paymentMethods);
		//$attributes .= "&PROVIDERID=" . urlencode($this->paymentMethods);
	
		if($this->configPP != '')
		{	
			$attributes .= "&VTCONFIG=" . urlencode($this->configPP);
		}
		
		$attributes .= "&FIRSTNAME=" . urlencode($user_data["first_name"]);
		$attributes .= "&LASTNAME=" . urlencode($user_data["last_name"]);
		$attributes .= "&STREET=" . urlencode($user_data["street"]);
		$attributes .= "&ZIP=" . urlencode($user_data["zip"]);
		$attributes .= "&CITY=" . urlencode($user_data["city"]);
		
		if($user_data["country"] == 'Germany'){
			$country = 'DE';
		}elseif($user_data["country"] == 'Switzerland'){
			$country = 'CH';
		}elseif($user_data["country"] == 'Austria'){
			$country = 'AT';
		}else{
			$country = 'CH';
		}
		
		$attributes .= "&COUNTRY=" . urlencode($country);
		$attributes .= "&EMAIL=" . urlencode($user_data["email"]);
		
		$this->logger->writeTimedString($attributes ,"CreatePayInit");
		
		$url_ver = $url_verify.$attributes;
		
	
		$ch = curl_init( $url_ver );
		
		curl_setopt($ch, CURLOPT_PORT, 443);			// set option for outgoing SSL requests via CURL
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	// ignore SSL-certificate-check - session still SSL-safe
		curl_setopt($ch, CURLOPT_HEADER, 0);		// no header in output
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		
		$payment_url = curl_exec($ch);

		curl_close( $ch );
		$process->setAttribute( 'event_state', eZCurlGateway::CURL_GATEWAY_WAIT );
		
		
		
		return $payment_url;
		
	}

	/*
	 * Builds URI and executes the Authorize.Net curl functions.
	 */
	



	public function doWait($process)
	{
		
		$data = $_SESSION['paymentpage']['data'];
		$signature = $_SESSION['paymentpage']['signature'];
		
		$response='';
		// make the order object
		$processParams = $process->attribute( 'parameter_list' );

		// get order id
		$order_id = $processParams['order_id'];
		// get order
		$order = eZOrder::fetch( $processParams['order_id'] );

		$order_total_amount = $this->priceFromat($order->attribute( 'total_inc_vat' ));

		$verifyConfirmUrl = $this->verifyConfirmURL;
		$accountid = $this->accountID;
		
		$this->logger->writeTimedString( $_SESSION['paymentpage']['return'] ,"return");
		
		if( $_SESSION['paymentpage']['return'] == 'OK')
		{
			$attributes='';
		
			
			$attributes  = "?ACCOUNTID=" . $accountid;
			$attributes .= "&DATA=" . $data;
			$attributes .= "&SIGNATURE=" . $signature;
			
			$url = $verifyConfirmUrl.$attributes;
			
	
			$ch = curl_init( $url );
			
		
			curl_setopt($ch, CURLOPT_PORT, 443);				// set option for outgoing SSL requests via CURL
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	// ignore SSL-certificate-check - session still SSL-safe
			curl_setopt($ch, CURLOPT_HEADER, 0);				// no header in output
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			
			$result_ok = curl_exec($ch);
			
			curl_close( $ch );
			
			$this->logger->writeTimedString($result_ok ,"reult_ok");
			
			if(substr($result_ok,0,3)=="OK:"){
				
				$vpc = array();
				parse_str( substr( $result_ok, 3), $vpc );
			
				$saferpay_paycomplete_gateway = $this->payComleteURL;
			
				$vt_id = $vpc["ID"];
				$vt_token = $vpc["TOKEN"];
				
				$paycomplete_url = $saferpay_paycomplete_gateway . "?ACCOUNTID=" . $accountid;
				$paycomplete_url .= "&ID=" . urlencode($vt_id) . "&TOKEN=" . urlencode($vt_token);
				
				//testaccount
				//if( substr(	$accountid, 0, 6) == "99867-" ) {
					$paycomplete_url .= "&spPassword=". $this->password;
				//}
			
				
				$cs = curl_init($paycomplete_url);
			
				curl_setopt($cs, CURLOPT_PORT, 443);			// set option for outgoing SSL requests via CURL
				curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, false);	// ignore SSL-certificate-check - session still SSL-safe
				curl_setopt($cs, CURLOPT_HEADER, 0);			// no header in output
				curl_setopt ($cs, CURLOPT_RETURNTRANSFER, true);	// receive returned characters
				
				$answer = curl_exec($cs);
				
				$this->logger->writeTimedString($answer ,"answer");
				
				curl_close($cs);
	
				if( substr($answer,0,3) == "OK:" ) {
					
					$response = substr($answer,0,2);
					$this->saveOrderDOM($order, $response);
					
					$orderItem = new eZOrderItem( array( 'order_id' => $order_id,
														'description' => "CreditCard",
														'price' => 0,
														'type' => 'paymentpage' ));
					$orderItem->store();
					
					//$order->modifyStatus($this->failStatusCode);
	                 unset($_SESSION['paymentpage']);
	                return eZWorkflowType::STATUS_ACCEPTED;
					
					
				}else{
					
					$response = 'ERROR2';
					$this->saveOrderDOM($order, $response);
					//$order->modifyStatus($this->failStatusCode);
					unset($_SESSION['paymentpage']);
	                return eZWorkflowType::STATUS_REJECTED;
					
				}
				
				
			}else{
				
				$response = 'ERROR1';
				$this->logger->writeTimedString($response ,"response");
				$this->saveOrderDOM($order, $response);
				unset($_SESSION['paymentpage']);
				return eZWorkflowType::STATUS_REJECTED;
				
			}
			
		}else{
			$response = 'ERROR';
			$this->logger->writeTimedString($response ,"response");
			$this->saveOrderDOM($order, $response);
			unset($_SESSION['paymentpage']);
			return eZWorkflowType::STATUS_REJECTED;
			
			
		}
	}

	public function paymentInfo($orderID=0)
	{
		
		$ini = eZINI::instance('paymentpage' );
		
		if ($orderID)
		{
			$parse = false;
			$order = eZOrder::fetch( $orderID );

			$orderItems = $order->attribute( 'order_items' );

			foreach ( $orderItems as $orderItem )
			{				
				if ( $orderItem->attribute( 'type' ) == 'paymentpage' )
				{
					$parse = true;
					break;
				}
			}

			if ($parse)
			{
				
				$saferpayResponse = $order->attribute("data_text_2"); //OK
				$xml = @simplexml_load_string ($saferpayResponse);

				$result = array();
				if (!empty($xml->paymentpage))
				{
					$result['response'] = (string)$xml->paymentpage->response;  //OK
				}

				return array("result" => $result);

			}
		}
		return array("result" => false);
	}

	/*
	TODO:
	This function need fixes it uses hardcoded values from a shop account handler

	Workaround:
	set INI value eZAuthorizeSettings->GetOrderCustomerInformation = false
	*/
	function getOrderInfo( $order )
	{
		// get order information out of eZXML
		$xml = new eZXML();
		$xmlDoc = $order->attribute( 'data_text_1' );

		if( $xmlDoc != null )
		{
			$dom = $xml->domTree( $xmlDoc );

			$order_first_name = $dom->elementsByName( "first-name" );
			$this->order_first_name = $order_first_name[0]->textContent();

			$order_last_name = $dom->elementsByName( "last-name" );
			$this->order_last_name = $order_last_name[0]->textContent();

			$order_email = $dom->elementsByName( "email" );
			$this->order_email = $order_email[0]->textContent();

			$order_street1 = $dom->elementsByName( "street1" );
			$this->order_street1 = $order_street1[0]->textContent();
			$this->order_company = $order_street1;

			$order_street2 = $dom->elementsByName( "street2" );
			$this->order_street2 = $order_street2[0]->textContent();

			$order_zip = $dom->elementsByName( "zip" );
			$this->order_zip = $order_zip[0]->textContent();

			$order_place = $dom->elementsByName( "place" );
			$this->order_place = $order_place[0]->textContent();

			$order_state = $dom->elementsByName( "state" );
			$this->order_state = $order_state[0]->textContent();

			$order_country = $dom->elementsByName( "country" );
			$this->order_country = $order_country[0]->textContent();

			$order_comment = $dom->elementsByName( "comment" );
			$this->order_comment = $order_comment[0]->textContent();
			return true;
		}
		return false;
	}

	/**
	 * formatiert Preis um immer in kleinster Einheit zu sein
	 */
	function priceFromat($price) {
		$formatted = "";
		$formatted = sprintf("%.2f",$price);
		$formatted = str_replace(".","",$formatted);
		return $formatted;
	}
	/**
	 * Validates a number according to Luhn check algorithm
	 *
	 * This function checks given number according Luhn check
	 * algorithm. It is published on several places, also here:
	 *
	 * @link http://www.webopedia.com/TERM/L/Luhn_formula.html
	 * @link http://www.merriampark.com/anatomycc.htm
	 * @link http://hysteria.sk/prielom/prielom-12.html#3 (Slovak language)
	 * @link http://www.speech.cs.cmu.edu/~sburke/pub/luhn_lib.html (Perl lib)
	 *
	 * @param  string  $number to check
	 * @return bool    TRUE if number is valid, FALSE otherwise
	 * @access public
	 * @static
	 * @author Ondrej Jombik <nepto@pobox.sk>
	 */
	function Luhn($number)
	{
		$len_number = strlen($number);
		$sum = 0;
		for ($k = $len_number % 2; $k < $len_number; $k += 2) {
			if ((intval($number{$k}) * 2) > 9) {
				$sum += (intval($number{$k}) * 2) - 9;
			} else {
				$sum += intval($number{$k}) * 2;
			}
		}
		for ($k = ($len_number % 2) ^ 1; $k < $len_number; $k += 2) {
			$sum += intval($number{$k});
		}
		return ($sum % 10) ? false : true;
	}

	protected function saveOrderDOM($order, $response)
	{
		 
		if ( $order instanceof eZOrder )
		{
			$order_info = false;
			$data_text = $order->attribute("data_text_2");
			//var_dump($order);
			$dom = new DOMDocument('1.0');
			$dom->loadXML( $data_text );
			 
			 
			foreach ($dom->getElementsByTagName("order_info") as $item)
			{
				$order_info = $item;

			}
			if (!$order_info) {
				$order_info = $dom->createElement("order_info");
			}
			 

			foreach ($dom->getElementsByTagName("paymentpage") as $item)
			{
				$node2 = $item;

			}

			if ($node2)
			$order_info->removeChild( $item );

			$tag = (string)PaymentPageGateway::EZ_PAYMENT_GATEWAY_TYPE_PAYMENTPAGE; //paymentpage


			foreach ($dom->getElementsByTagName($tag) as $item)
			{
				$Node = $item;
			}

			if (!$Node) {
				$Node = $dom->createElement($tag);
			}
			 

			$info = $dom->createElement('response', $response);
			$Node->appendChild($info);
			 
			 
			$order_info->appendChild($Node);
			$dom->appendChild($order_info);


			 
			$order->setAttribute( 'data_text_2', $dom->saveXML());
			$order->store();
			 
		}
	}


	public function hasError()
	{
		 
		if ($this->error)
		return true;
		return false;
	}

}
eZPaymentGatewayType::registerGateway( PaymentPageGateway::EZ_PAYMENT_GATEWAY_TYPE_PAYMENTPAGE, "paymentpagegateway", "paymentPage.de" );

?>