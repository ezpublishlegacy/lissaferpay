<?php 

// Definition of eZCurlGateway class
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


class paymentpageHandler
{
	var $request;
    var $response;
    public $error;
    public $errorString;
    public $errorCode;

  
	
	public function paymentpageHandler()
	{
		$this->error = false;
	
		$ini = eZINI::instance( 'paymentpage.ini' );
		$this->mode = $ini->variable('paymentpage','Mode');
		$this->use3D = $ini->variable('paymentpage','use3D');
		
		$paymentPageSettings = 'PaymentPageSettings_'.$this->mode;
		$this->saferpayUsername = $ini->variable($paymentPageSettings,'UserName');
        $this->saferpayPassword = $ini->variable($paymentPageSettings,'password');
        $this->saferpayAccountID = $ini->variable($paymentPageSettings,'accountID');
        $this->saferpayCurrency = $ini->variable($paymentPageSettings,'currency');
        $this->saferpayExecuteUrl = $ini->variable($paymentPageSettings,'executeURL'); 
        $this->saferpayComleteUrl = $ini->variable($paymentPageSettings,'payComleteURL'); 
        $this->verifyUrl = $ini->variable($paymentPageSettings,'verifyURL'); 

        $this->logger = eZPaymentLogger::CreateForAdd( "var/log/SaferpayPayment.log" );
	}
	
	
 public function send($type, $order_total_amount, $threeD_sessionID)
    {
        	
    		$saferpay = new saferpayHandler();
    	
            $this->logger->writeTimedString($type ,"do send type");
    	 	
    	 	$url_exe = $this->saferpayExecuteUrl; // aus INI
    	 	
    	 	//attribute aus ini bzw. Warencorb mitgeben
    	 	$pswd = $this->saferpayPassword;  			// aus INI
    	 	$accountid = $this->saferpayAccountID;		//aus INI
    	 	$amount = $order_total_amount;			
    	 	$currency = $this->saferpayCurrency;	//aus INI
    	 	
    	 	if($this->use3D=='1')
    	 	{
	    	 	 $pan= $_SESSION['saferpay']['CardNumber'];
	    	 	 $cvc = $_SESSION['saferpay']['SecurityNumber'];
	    	 	 $month = $_SESSION['saferpay']['ExpirationMonth'];
	    	 	 $year = $_SESSION['saferpay']['ExpirationYear'];
    	 	}
    	 	else
    	 	{
	    	 	 $pan = $_REQUEST["CardNumber"];			//Form
	    	 	 $cvc = $_REQUEST["SecurityNumber"];		//Form
	    	 	 $month = $_REQUEST["ExpirationMonth"];		//Form
	    	 	 $year = substr($_REQUEST["ExpirationYear"],-2); //Form
    	 	}
    	 	
    	 	$pan = str_replace(' ','',$pan);
    	 	
    	 	$exp=$month.$year;
    	 	
    	 	
    	 	
    	 	$attributes = "?spPassword=" . $pswd;
    	 	$attributes .= "&ACCOUNTID=" . $accountid;
			$attributes .= "&AMOUNT=" . $amount;
			$attributes .= "&CURRENCY=" . $currency;
			$attributes .= "&PAN=" . $pan;
			$attributes .= "&CVC=" . $cvc;
			$attributes .= "&EXP=" . $exp;
			
            if($type=="payEnrolled")
                {
                    $attributes .= "&MPI_SESSIONID=" . $threeD_sessionID;
                }
		
			$url = $url_exe.$attributes;
			$this->logger->writeTimedString($url,"url first send");
			
			$ch=curl_init($url);
			

   			# Fix for curl version > 7.1 with require CA cert by default.
            # For better security implement a cacert bundle
			$file = eZExtension::baseDirectory() . '/lispaymentpage/ca/cacert.pem';
            if ( $this->mode != "demo" and file_exists( $file ) )
            {
                curl_setopt ($ch, CURLOPT_CAINFO, $file );
            }
            else
            {
                curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            }
            
            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            $res = curl_exec($ch);  
            
    		curl_close( $ch );
    		$xml = substr($res,3);
    		$xmlObject = simplexml_load_string($xml);
    		
    		//Antwort felder
    		$result_response = (int)$xmlObject['RESULT'];     	
    		$exp_response = (int)$xmlObject['EXP'];
    		$pan_response = (string)$xmlObject['PAN'];
    		$transactionID = (string)$xmlObject['ID'];
    		
    		$threeD_session =(int)$xmlObject['MPI_SESSIONID'];
    		$threeD_link =(string)$xmlObject['MPI_PA_LINK'];
    		$threeD_auth =(string)$xmlObject['MPI_PA_REQUIRED'];  //yes oder no
    		
    		$this->logger->writeTimedString($result_response,"RESULT first send");
    		
    		if($result_response == 0 && substr($res,0,3)=="OK:" ) // && substr($pan_response,-4) == substr($pan,-4) ) // Antwort vom 1. send ist in Ordnung
    		{
	
	    	
	    	 	$url_conf = $this->saferpayComleteUrl;
	    	 	//$response mit einbauen
	    	 	$id=(string)$xmlObject['ID'];   //aus result 1. send
	    	 	
	    	
	    	    //$providername = (string)$xmlObject['PROVIDERNAME'];
	    	 	
	    	 	$attributes = "?spPassword=" . $pswd;
	    	 	$attributes .= "&ACCOUNTID=" . $accountid;
	    	 	$attributes .= "&ID=" . $id;
	    	 //	$attributes .= "&PROVIDERNAME=" . $providername;
	    	 	
	    	 	/*
	    		if($type=="payEnrolled")
	            {
	                $attributes .= "&MPI_SESSIONID=" . $threeD_sessionID;
	            }
	    	 	*/
	    	 	
	    	 	$url = $url_conf.$attributes;
	    		
	    	 	$this->logger->writeTimedString($url,"url second send");
	    	 	
    			$cc=curl_init($url);
    			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            	$res = curl_exec($cc);
            	
            	$this->logger->writeTimedString($res,"RESULT second send");
            	
    			if($res == "OK")
    			{
    				if($threeD_sessionID != false)
    				{
    				    $error = "OK | Transaktionskennung: ".$transactionID." | 3D_Secure";
    				}
    				else
    				{
    				    $error = "OK | Transaktionskennung: ".$transactionID;
    				}
    				curl_close( $cc );
            	
    			}else
    			{
    				
    				$error = "ERROR";
    			}
            	
    			return $error;
    			
    			
    		}
    		else //Ergebnis vom 1. send ist NICHT in ordnung
    		{
    			
    			
    				$error = "ERROR_".$result_response;
    				return $error;
    			
    		}
    	
    }
	
    
  public static function unsetSessionParams()
    {
        unset($_SESSION['paymentpage']);
    }
    
    
  public function hasError()
    {
        if ($this->error)
            return true;
        return false;
    }
    
}
?>