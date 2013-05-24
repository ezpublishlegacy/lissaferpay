{* 
  Credit Card Form for eZ Authorize
  (could also be default form for other eZCurlGateway based class)
 
  You MUST have a post variable called 'validate' 
  if you wish eZCurlGateway to catch the form once it's been posted. 

  By default, it is the submit button.
*}
<div class="maincontentbody">
        <div class="content-view-full">
            <div class="class-article">

<h2>Payment Information</h2>
<ul>
<li>Please enter your credit card information</li>
</ul>

{if ne($errors, 0)}
<b>There were errors on the form: </b><br />
<ul>
{foreach $errors as $errmsg}
<li>{$errmsg}</li>
{/foreach}
</ul>
{/if}

<form name="lisWireCardForm" action="checkout" method="post">

<input type=hidden name="orderid" value="{$order_id}">
<input type=hidden name="amount" value="{$amount}">
<input type=hidden name="currency" value="US">
<input type="hidden" name="lang" value="en">

<table border="0" cellpadding="4" cellspacing="4">
<tr>
<td>Name on Card: </td>
<td><input type="text" size="32" name="CardName" value="{$cardname}" /></td>
</tr>
<tr>
<td>Card Type: </td>
<td>
<select name="CardType">
  <option value="visa">Visa</option>
  <option value="mastercard" {if eq($cardtype, 'mastercard')}selected{/if}>MasterCard</option>
  <option value="amercianexpress" {if eq($cardtype, 'americanexpress')}selected{/if}>American Express</option>
  <option value="discover" {if eq($cardtype, 'discover')}selected{/if}>Discover</option>
</select>
</tr>
<tr>
<td>Card Number: </td>
<td><input type="text" size="32" name="CardNumber" value="{$cardnumber}" /></td>
</tr>
<tr>
<td>Security Number: </td>
<td><input type="text" size="5" name="SecurityNumber" value="" /></td>
</tr>
<tr>
<td>Expiration Date: </td>
<td >
  <select name="ExpirationMonth">
    <option value=""></option>
    {* Dynamic Loop *}
    <option value="01" {if eq($expirationmonth, '01')}selected{/if}>01</option>
    <option value="02" {if eq($expirationmonth, '02')}selected{/if}>02</option>
    <option value="03" {if eq($expirationmonth, '03')}selected{/if}>03</option>
    <option value="04" {if eq($expirationmonth, '04')}selected{/if}>04</option>
    <option value="05" {if eq($expirationmonth, '05')}selected{/if}>05</option>
    <option value="06" {if eq($expirationmonth, '06')}selected{/if}>06</option>
    <option value="07" {if eq($expirationmonth, '07')}selected{/if}>07</option>
    <option value="08" {if eq($expirationmonth, '08')}selected{/if}>08</option>
    <option value="09" {if eq($expirationmonth, '09')}selected{/if}>09</option>
    <option value="10" {if eq($expirationmonth, '10')}selected{/if}>10</option>
    <option value="11" {if eq($expirationmonth, '11')}selected{/if}>11</option>
    <option value="12" {if eq($expirationmonth, '12')}selected{/if}>12</option>
  </select>
  <select name="ExpirationYear">
    <option value=""></option>
    {* Dynamic Loop *}
    <option value="2008" {if eq($expirationyear, '2008')}selected{/if}>2008</option>
    <option value="2009" {if eq($expirationyear, '2009')}selected{/if}>2009</option>
    <option value="2010" {if eq($expirationyear, '2010')}selected{/if}>2010</option>
    <option value="2011" {if eq($expirationyear, '2011')}selected{/if}>2011</option>
    <option value="2012" {if eq($expirationyear, '2012')}selected{/if}>2012</option>
    <option value="2013" {if eq($expirationyear, '2013')}selected{/if}>2013</option>
    <option value="2014" {if eq($expirationyear, '2014')}selected{/if}>2014</option>
    <option value="2015" {if eq($expirationyear, '2015')}selected{/if}>2015</option>
    <option value="2016" {if eq($expirationyear, '2016')}selected{/if}>2016</option>
  </select>
</td>
</tr>
<tr>
<td colspan="2"><input class="defaultbutton" type="submit" name="validate" value="Submit" />&nbsp;&nbsp;<input class="defaultbutton" type="submit" name="CancelButton" value="{'Cancel'|i18n('design/standard/workflow')}" /></td>
</tr>
</table>

</form> 

</div>
</div>
</div>
