<?php
/*
 * @copyright Copyright (C) 2010-2013 land in sicht AG All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
*/

//include_once ( 'extension/ezauthorize/classes/ezcurlgateway.php' );


class lisSaferpayGateway extends eZCurlGateway
{





	const EZ_PAYMENT_GATEWAY_TYPE_LISSAFERPAY = "lissaferpay";
	/*!
	 Constructor
	 */
	function lisSaferpayGateway()
	{
		$this->logger = eZPaymentLogger::CreateForAdd( "var/log/eZPaymentGatewayType.log" );
		$ini = eZINI::instance( 'lissaferpay.ini' );
		$this->mode = $ini->variable('lissaferpay','Mode');
		$this->use3D = $ini->variable('lissaferpay','use3D');
		$saferpaySettings = 'lisSaferpaySettings_'.$this->mode;
		$this->saferpayUsername = $ini->variable($saferpaySettings,'UserName');
		$this->saferpayPassword = $ini->variable($saferpaySettings,'password');
		$this->saferpayAccountID = $ini->variable($saferpaySettings,'accountID');
		$this->saferpayCurrency = $ini->variable($saferpaySettings,'currency');
		$this->saferpayExecuteUrl = $ini->variable($saferpaySettings,'executeURL');
		$this->saferpayComleteUrl = $ini->variable($saferpaySettings,'payComleteURL');
		$this->verifyUrl = $ini->variable($saferpaySettings,'verifyURL');

		$this->error[5] = $ini->variable('lissaferpay','error5');
		$this->error[21] = $ini->variable('lissaferpay','error21');
		$this->error[22] = $ini->variable('lissaferpay','error22');
		$this->error[23] = $ini->variable('lissaferpay','error23');
		$this->error[61] = $ini->variable('lissaferpay','error61');
		$this->error[62] = $ini->variable('lissaferpay','error62');
		$this->error[63] = $ini->variable('lissaferpay','error63');
		$this->error[64]= $ini->variable('lissaferpay','error64');
		$this->error[65] = $ini->variable('lissaferpay','error65');
		$this->error[67] = $ini->variable('lissaferpay','error67');
		$this->error[68] = $ini->variable('lissaferpay','error68');
		$this->error[75] = $ini->variable('lissaferpay','error75');
		$this->error[76] = $ini->variable('lissaferpay','error76');
		$this->error[77] = $ini->variable('lissaferpay','error77');
		$this->error[78] = $ini->variable('lissaferpay','error78');
		$this->error[80] = $ini->variable('lissaferpay','error80');
		$this->error[82] = $ini->variable('lissaferpay','error82');
		$this->error[83] = $ini->variable('lissaferpay','error83');
		$this->error[84] = $ini->variable('lissaferpay','error84');
		$this->error[87] = $ini->variable('lissaferpay','error87');
		$this->error[88] = $ini->variable('lissaferpay','error88');
		$this->error[89] = $ini->variable('lissaferpay','error89');
		$this->error[90] = $ini->variable('lissaferpay','error90');
		$this->error[97] = $ini->variable('lissaferpay','error97');
		$this->error[98] = $ini->variable('lissaferpay','error98');
		$this->error[102] = $ini->variable('lissaferpay','error102');
		$this->error[104] = $ini->variable('lissaferpay','error104');
		$this->error[105] = $ini->variable('lissaferpay','error105');
		$this->error[151] = $ini->variable('lissaferpay','error151');
		$this->error[152] = $ini->variable('lissaferpay','error152');
		$this->error[301] = $ini->variable('lissaferpay','error301');

	}

	function loadForm( $process, $errors = 0 )
	{
		$http = eZHTTPTool::instance();

		// get parameters
		$processParams = $process->attribute( 'parameter_list' );

		// load ini
		$ini = eZINI::instance( 'lissaferpay.ini' );

		// regen posted form values
		if ( $http->hasPostVariable( 'validate' ) )
		{
			$tplVars['cardnumber'] = $_SESSION['cardnumber'] = $http->postVariable( 'CardNumber' );
			$tplVars['cardname'] = $_SESSION['cardname'] = $http->postVariable( 'CardName' );
			$tplVars['cardtype'] = $_SESSION['cardtype'] = strtolower( $http->postVariable( 'CardType' ) );
			$tplVars['securitynumber'] = $_SESSION['securitynumber'] = $http->postVariable( 'SecurityNumber' );
			$tplVars['expirationmonth'] = $_SESSION['expirationmonth'] = $http->postVariable( 'ExpirationMonth' );
			$tplVars['expirationyear'] = $_SESSION['expirationyear'] = $http->postVariable( 'ExpirationYear' );
			//$tplVars['agb'] = $_SESSION['agb'] = $http->postVariable('agb');
		}
		else
		{
			 
			// set form values to SESSION-Values
			if(isset($_SESSION['cardnumber']))
			{
				$tplVars['cardnumber'] = $_SESSION['cardnumber'];
			}

			if(isset($_SESSION['cardname']))
			{
				$tplVars['cardname'] = $_SESSION['cardname'];
			}
			if(isset($_SESSION['cardtype']))
			{
				$tplVars['cardtype'] = $_SESSION['cardtype'];
			}

			if(isset($_SESSION['securitynumber']))
			{
				$tplVars['securitynumber'] = $_SESSION['securitynumber'];
			}
			if(isset($_SESSION['expirationmonth']))
			{
				$tplVars['expirationmonth'] = $_SESSION['expirationmonth'];
			}
			if(isset($_SESSION['expirationyear']))
			{
				$tplVars['expirationyear'] = $_SESSION['expirationyear'];
			}
			/*
			 var_dump($_SESSION['agb']);
			 if(isset($_SESSION['agb']))
			 {
			 $tplVars['agb'] = $_SESSION['agb'];
			 }
			 */
		}

		$tplVars['errors'] = $errors;
		$tplVars['order_id'] = $processParams['order_id'];
		$tplVars['Order'] = eZOrder::fetch($processParams['order_id']);

		$process->Template=array
		(
            'templateName' => 'design:workflow/eventtype/result/' . 'lissaferpay_form.tpl',
            'templateVars' => $tplVars
		);

		return eZWorkflowType::STATUS_FETCH_TEMPLATE_REPEAT;
	}


	function load3DForm ($process, $threeD_link)
	    {
	        
	        $processParams = $process->attribute( 'parameter_list' );
	        $tplVars = array(); 
	        
	        $tplVars['threeD_link'] = $threeD_link;
	        
	        
	        $process->Template=array
	        (
	            'templateName' => 'design:workflow/eventtype/result/' . 'lissaferpay_3Dredirect.tpl',
	            'templateVars' => $tplVars
	        );
	        return eZWorkflowType::STATUS_FETCH_TEMPLATE_REPEAT;
	    }
	

	function validateForm( $process )
	{
		$http = eZHTTPTool::instance();
		$errors = false;

		$CardNumber = $http->postVariable( 'CardNumber' );
		$CardNumber = str_replace(' ','',$CardNumber);
		 

		if ( $http->hasPostVariable( 'agb' )  == false )
		{
			$errors[] = 'Bitte akzeptieren Sie die AGBs.';

		}
		if ( trim( $http->postVariable( 'CardName' ) ) == '' )
		{
			$errors[] = 'Bitte geben Sie den Namen auf Ihrer Kreditkarte ein.';
		}
		elseif( strlen( trim( $http->postVariable( 'CardName' ) ) ) > 79 )
		{
			$errors[] = 'Der Name darf nicht länger als 80 Zeichen sein.';
		}
		if ( trim($CardNumber)== '' )
		{
			$errors[] = 'Bitte geben Sie eine gültige Kreditkartennummer ein';
		}
		elseif( strlen($CardNumber) > 49 )
		{
			$errors[] = 'Die Kreditkartennummer darf nicht länger als 50 Zeichen sein';
		}
		if ($this->mode != "demo")
		{
			if (!$this->Luhn($CardNumber))
			{
				$errors[] = 'Ihre Kreditkartennummer ist nicht korrekt.';
			}
		}
		if ( trim( $http->postVariable( 'SecurityNumber' ) ) == '' )
		{
			$errors[] = 'Bitte geben Sie eine gültige Prüfnummer ein';
		}
		elseif( strlen( trim( $http->postVariable( 'SecurityNumber' ) ) ) > 3 )
		{
			$errors[] = 'Die Prüfnummer darf nicht länger als 3 Zeichen sein';
		}
		if ( trim( $http->postVariable( 'ExpirationMonth' ) ) == '' )
		{
			$errors[] = 'Bitte geben Sie das Ablaufdatum (Monat) Ihrer Kreditkarte an.';
		}

		if ( trim( $http->postVariable( 'ExpirationYear' ) ) == '' )
		{
			$errors[] = 'Bitte geben Sie das Ablaufdatum (Jahr) Ihrer Kreditkarte an.';
		}



		return $errors;
	}

	/*
	 * Builds URI and executes the Authorize.Net curl functions.
	 */
	function doCURL( $process )
	{

		$ini = eZINI::instance( 'lissaferpay.ini' );
		 
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
		//
		// note start of order transmission
		/*
		if($this->startStatusCode)
		{
		$order->modifyStatus($this->startStatusCode);
		}
		*/
		// assign variables to Authorize.Net class from post
		$saferpay = new lissaferpayHandler();
		 
		if($this->use3D =='1') //wenn 3D benutzt werden soll
		{
//			var_dump('Use 3D');
//			break;
//			echo 'VerifyEnrollment Anfrage senden';
			//VerifyEnrollment Anfrage senden

			$saferpay = new lissaferpayHandler();
			$ini = eZINI::instance( 'lissaferpay.ini' );
            $backlink = $ini->variable('lissaferpay','backlink3d');
                                
			$url_verify =$this->verifyUrl;


			$pswd = $this->saferpayPassword;            // aus INI
			$accountid = $this->saferpayAccountID;      //aus INI
			 
			$amount = $order_total_amount;
			$currency = $this->saferpayCurrency;    //aus INI

			$pan = $_SESSION['lissaferpay']['CardNumber'] = $_REQUEST["CardNumber"];         //aus Form
			$pan = str_replace(' ','',$pan);
			$cvc = $_SESSION['lissaferpay']['SecurityNumber'] =$_REQUEST["SecurityNumber"];     //aus Form

			$month =  $_SESSION['lissaferpay']['ExpirationMonth'] = $_REQUEST["ExpirationMonth"];      //aus Form
			$year =  $_SESSION['lissaferpay']['ExpirationYear'] = substr($_REQUEST["ExpirationYear"],-2); //aus Form
			$exp=$month.$year;


			$attributes = "?MSGTYPE="."VerifyEnrollmentRequest";
			$attributes .= "&spPassword=" . $pswd;
			$attributes .= "&ACCOUNTID=" . $accountid;
			$attributes .= "&AMOUNT=" . $amount;
			$attributes .= "&CURRENCY=" . $currency;
			$attributes .= "&MPI_PA_BACKLINK=".$backlink;
			$attributes .= "&PAN=" . $pan;
			$attributes .= "&CVC=" . $cvc;
			$attributes .= "&EXP=" . $exp;



			$url_ver = $url_verify.$attributes;
			$ch=curl_init($url_ver);
			 
			# Fix for curl version > 7.1 with require CA cert by default.
			# For better security implement a cacert bundle
			$file = eZExtension::baseDirectory() . '/lissaferpay/ca/cacert.pem';
			if ( $this->mode != "demo" and file_exists( $file ) )
			{
				curl_setopt ($ch, CURLOPT_CAINFO, $file );
			}
			else
			{
				curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			}


			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$res = curl_exec($ch);  //VerifyEnrollment Abfrage absenden
			 

			curl_close( $ch );
			$xml = substr($res,3);
			$xmlObject = simplexml_load_string($xml);

			//Antwort felder
			$result_response = (int)$xmlObject['RESULT'];
			
			$threeD_session =(int)$xmlObject['MPI_SESSIONID'];
			$threeD_link =(string)$xmlObject['MPI_PA_LINK'];     //link zum redirect
			$threeD_auth =(string)$xmlObject['MPI_PA_REQUIRED'];  //yes oder no

			//Antwort
			if($threeD_auth == 'yes')
			{

				//Redirect zu $threeD_link status auf wait -> der richte Send kommt dann in der Do wait funktion 
				$process->setAttribute( 'event_state', eZCurlGateway::CURL_GATEWAY_WAIT ); 
				
				return $this->load3DForm( $process, $threeD_link );
				
			}
			else
			{
				//ohne 3D
				$response = $saferpay->send($type="pay", $order_total_amount, $threeD_sessionID=false );
			}



		}//end wenn 3D benutzt werden soll
		 
		 
		if($this->use3D =='0') //Wenn 3D nicht benutzt werden soll
		{
			//nur normale Bezahlung ohne 3D
			$response = $saferpay->send($type="pay", $order_total_amount, $threeD_sessionID=false );
		}
		 
		 
		$this->saveOrderDOM($order, $response);

		// OrderITem speichern um später zu wissen, mit welchen Gateway gezahlt wurde
		$orderItem = new eZOrderItem( array( 'order_id' => $order_id,
	                                             'description' => "Kreditkartenzahlung",
	                                             'price' =>0,
	                                             'type' => 'lissaferpay' )
		);

		$orderItem->store();
		 

		$num = substr($response, 6); // Fehlernummer

		if($response == "ERROR_".$num) //Antwort von 1. send nicht in Ordung
		{
			$test[] = $this->error[$num];

			return $this->loadForm( $process, $test); //Form nochmal anzeigen
		}
		else
		{

			if ($response == "ERROR") //Antwort vom 2. send nicht in Ordung
			{
				//je nach fehler Formular nochmal zeigen, oder Abbrechen
				$this->logger->writeTimedString($saferpay->error,"ERROR");
				if ($saferpay->errorCode == "1234")
				{
					//$order->modifyStatus($this->failStatusCode);
					return eZWorkflowType::STATUS_REJECTED;
				}
				else
				{
					return $this->loadForm( $process, $saferpay->error );
				}

			}
			else
			{ // Antwort vom 2. send OK

				//$order->modifyStatus($this->successStatusCode);
				lissaferpayHandler::unsetSessionParams();
				return eZWorkflowType::STATUS_ACCEPTED;
				 
			}
		}
	}


	public function doWait($process)
	{
		
		//system hat gewartet und erwartet nun in der Session ein paar werte, damit die Zahlung abgeschlossen werden kann.
		//$this->logger->writeTimedString($PaRes,"executing doWait");
		
		$threeD_sessionID = $_SESSION['lissaferpay']['MPI_SESSIONID'];
		$mpi_liabilityshift = $_SESSION['lissaferpay']['MPI_LIABILITYSHIFT'];
		
	    $this->logger->writeTimedString($threeD_sessionID,"threeD_sessionID");
	    $this->logger->writeTimedString($mpi_liabilityshift,"mpi_liabilityshift");
	    
		// make the order object
		$processParams = $process->attribute( 'parameter_list' );

		// get order id
		$order_id = $processParams['order_id'];

		// get order
		$order = eZOrder::fetch( $processParams['order_id'] );

		$order_total_amount = $this->priceFromat($order->attribute( 'total_inc_vat' ));
		//nachdem man vom redirect zurück kommt, entweder die richtige Zahlung anstossen oder abrechen...

		if($mpi_liabilityshift =="yes")
		{
			
			$saferpay = new lissaferpayHandler();
			$response = $saferpay->send($type="payEnrolled",$order_total_amount , $threeD_sessionID );  //TODO: $threeD_sessionID muss noch irgendwie mitgegeben werden
		
	       
	        
			$this->saveOrderDOM($order, $response);
			// OrderITem speichern um später zu wissen, mit welchen Gateway gezahlt wurde
			$orderItem = new eZOrderItem( array( 'order_id' => $order_id,
	                                             'description' => "Kreditkartenzahlung",
	                                             'price' => 0,
	                                             'type' => 'lissaferpay' )
			);
			$orderItem->store();
		
			
			$num = substr($response, 6); // Fehlernummer
	
	        if($response == "ERROR_".$num) //Antwort von 1. send nicht in Ordung
	        {
	            $test[] = $this->error[$num];
	
	            return $this->loadForm( $process, $test); //Form nochmal anzeigen
	        }
	        else
	        {  
	
	            if ($response == "ERROR") //Antwort vom 2. send nicht in Ordung
	            {
	                //je nach fehler Formular nochmal zeigen, oder Abbrechen
	                $this->logger->writeTimedString($saferpay->error,"ERROR");
	                if ($saferpay->errorCode == "1234")
	                {
	                    //$order->modifyStatus($this->failStatusCode);
	                    return eZWorkflowType::STATUS_REJECTED;
	                }
	                else
	                {
	                    return $this->loadForm( $process, $saferpay->error );
	                }
	
	            }
	            else
	            { // Antwort vom 2. send OK
	
	                //$order->modifyStatus($this->successStatusCode);
	                lissaferpayHandler::unsetSessionParams();
	                return eZWorkflowType::STATUS_ACCEPTED;
	                 
	            }
			
            }
		}
		else
		{
			    $order->modifyStatus($this->failStatusCode);
                lissaferpayHandler::unsetSessionParams();
                return eZWorkflowType::STATUS_REJECTED;
			
		}
	
        
		
	}

	public function paymentInfo($orderID=0)
	{
		$ini = eZINI::instance('lissaferpay' );
		
		if ($orderID)
		{
			$parse = false;
			$order = eZOrder::fetch( $orderID );

			$orderItems = $order->attribute( 'order_items' );

			foreach ( $orderItems as $orderItem )
			{
				if ( $orderItem->attribute( 'type' ) == 'lissaferpay' )
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
				if (!empty($xml->lissaferpay))
				{
					$result['response'] = (string)$xml->lissaferpay->response;  //OK
					 
				}

				return array("result" =>$result);

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
		//infos zur Order Speichern

		 
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
			 

			//altes saferpay Node löschen falls vorhanden
			foreach ($dom->getElementsByTagName("lissaferpay") as $item)
			{
				$node2 = $item;

			}

			if ($node2)
			$order_info->removeChild( $item );

			$tag = (string)lisSaferpayGateway::EZ_PAYMENT_GATEWAY_TYPE_LISSAFERPAY; //lissaferpay


			//schauen, ob schon Wirecard Message vorhanden ist. Wenn ja , Überschrieben sonst neu anlegen.
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
eZPaymentGatewayType::registerGateway( lisSaferpayGateway::EZ_PAYMENT_GATEWAY_TYPE_LISSAFERPAY, "lissaferpaygateway", "saferpay.de" );

?>