<?php
//
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
//
//

/*! \file ezcurlgateway.php
*/

/*!
  \class eZCurlGateway ezcurlgateway.php
  \brief The class eZCurlGateway is a
  base class for payment gateways which
  support payment by executing cURL commands
  to a payment processor over SSL.
*/

include_once( 'kernel/shop/classes/ezpaymentgateway.php' );

class eZCurlGateway extends eZPaymentGateway
{
    // override $useForm as false if you do not need to gather additional
    // user information before you send the curl information (although I can't
    // think of many situations in which this would happen, let me know if you
    // actually use it, otherwise I might just take it out.
    
    const CURL_GATEWAY_SHOW_FORM =  1 ;

    const CURL_GATEWAY_DO_CURL = 2 ;
    
    const CURL_GATEWAY_WAIT = 3;
    
    var $useForm = true;

    /*!
     Constructor.
    */
    function eZCurlGateway()
    {
    }

    function execute( $process, $event )
    {
        $http = eZHTTPTool::instance();

        $processParameters = $process->attribute( 'parameter_list' );
        $processID =  $process->attribute( 'id' );

        // if a form has been posted, we try and validate it.
        if ( $http->hasPostVariable( 'validate' ) )
        {
        	
        
            $errors = $this->validateForm( $process );

            if ( !$errors ) {
                $process->setAttribute( 'event_state', eZCurlGateway::CURL_GATEWAY_DO_CURL );
            }
            else
            {
                return $this->loadForm( $process, $errors);
            }
        }
       

        if ( $process->attribute('event_state') == eZCurlGateway::CURL_GATEWAY_SHOW_FORM )
        {
            // set the event state to do curl if we are not using a form
            if ( !$this->useForm ) {
                $process->setAttribute( 'event_state', eZCurlGateway::CURL_GATEWAY_DO_CURL );
            }
        }
       
       
        eZDebug::writeError($process->attribute( 'event_state' ),"status");   
        switch ( $process->attribute( 'event_state' ) )
        {
            case eZCurlGateway::CURL_GATEWAY_SHOW_FORM:
            {
                return $this->loadForm( $process );
            }
            break;
            case eZCurlGateway::CURL_GATEWAY_DO_CURL:
            {
                return $this->doCURL( $process );
            }
            case eZCurlGateway::CURL_GATEWAY_WAIT:
            {
              return $this->doWait( $process );
            }
            break;
        }
    }

    /*!
    \method abstract
    \brief Needs to be overridden and return
    EZ_WORKFLOW_TYPE_STATUS_FETCH_TEMPLATE_REPEAT if you are using a form.
    */
    function loadForm( $process, $errors = false )
    {
        return eZWorkflowType::STATUS_FETCH_TEMPLATE_REPEAT;
    }

    /*!
    \method abstract
    \brief Should validate post data from the loaded form.
    */
    function validateForm( $process )
    {
        $errors[] = 'You must override this method: ezauthorizegateway::validateForm';
        if ( $errors )
            return $this->loadForm( $process, $errors );
        else
            return false;
    }

    /*!
    \method abstract
    \brief Should run the curl command and process the response. Typically you
    would call the loadForm method on a negative response, passing it an error,
    and on a positive response would complete the order by returning
    EZ_WORKFLOW_TYPE_STATUS_ACCEPTED.
    */
    function doCURL( $process )
    {
        return eZWorkflowType::STATUS_ACCEPTED;
    }
    
    /*!
     \method abstract
     \brief should check for an event and process the workflow. Typically waiting for external validation.
     * 
     */
    function doWait( $process )
    {
        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

?>
