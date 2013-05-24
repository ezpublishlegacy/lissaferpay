<?php 
/*
 * @copyright Copyright (C) 2010-2013 land in sicht AG All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
*/
class lissaferpayHandler
{
	var $request;
    var $response;
    public $error;
    public $errorString;
    public $errorCode;

  
	
	public function lissaferpayHandler()
	{
		$this->error = false;
	
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

        $this->logger = eZPaymentLogger::CreateForAdd( "var/log/lisSaferpayPayment.log" );
	}
	
	
 public function send($type, $order_total_amount, $threeD_sessionID)
    {
        	
    		$saferpay = new lissaferpayHandler();
    		$ini = eZINI::instance( 'lissaferpay.ini' );
    
            $this->logger->writeTimedString($type ,"do send type");
    	 	//echo "SEND-FUNKTION..EXECUTE<br>";
    	 	$url_exe = $this->saferpayExecuteUrl; // aus INI
    	 	
    	 	//attribute aus ini bzw. Warencorb mitgeben
    	 	$pswd = $this->saferpayPassword;  			// aus INI
    	 	$accountid = $this->saferpayAccountID;		//aus INI
    	 	$amount = $order_total_amount;			
    	 	$currency = $this->saferpayCurrency;	//aus INI
    	 	
    	 	if($this->use3D=='1')
    	 	{
	    	 	 $pan= $_SESSION['lissaferpay']['CardNumber'];
	    	 	 $cvc = $_SESSION['lissaferpay']['SecurityNumber'];
	    	 	 $month = $_SESSION['lissaferpay']['ExpirationMonth'];
	    	 	 $year = $_SESSION['lissaferpay']['ExpirationYear'];
    	 	}
    	 	else
    	 	{
	    	 	 $pan = $_REQUEST["CardNumber"];			//aus Form
	    	 	 $cvc = $_REQUEST["SecurityNumber"];		//aus Form
	    	 	 $month = $_REQUEST["ExpirationMonth"];		//aus Form
	    	 	 $year = substr($_REQUEST["ExpirationYear"],-2); //aus Form
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
    		
    		if($result_response == 0 && substr($res,0,3)=="OK:" ) // Antwort vom 1. send ist in Ordnung
    		{
	
	    	 	//echo "SEND-FUNKTION..CONFIRM <br>";
	    	 	$url_conf = $this->saferpayComleteUrl;
	    	 	//$response mit einbauen
	    	 	$id=(string)$xmlObject['ID'];   //aus result 1. send
	    	 	

	    	 	
	    	 	$attributes = "?spPassword=" . $pswd;
	    	 	$attributes .= "&ACCOUNTID=" . $accountid;
	    	 	$attributes .= "&ID=" . $id;
	     	
	    	 	//die nächsten 3 zeilen waren auskommentiert
	    		if($type=="payEnrolled")
	            {
	                $attributes .= "&MPI_SESSIONID=" . $threeD_sessionID;
	            }
	    	 	
	    	 	
	    	 	$url = $url_conf.$attributes;
	    		
	    	 	$this->logger->writeTimedString($url,"url second send");
	    	 	
    			$cc=curl_init($url);
    			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            	$res = curl_exec($cc);
            	
//            	$info1 = curl_getinfo($cc);

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
        unset($_SESSION['lissaferpay']);
    }
    
    
  public function hasError()
    {
        if ($this->error)
            return true;
        return false;
    }
    
}
?>