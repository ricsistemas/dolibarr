<?php
/* Copyright (C) 2006-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *		\file       htdocs/societe/checkvat/checkVatPopup.php
 *		\ingroup    societe
 *		\brief      Popup screen to validate VAT
 *		\version    $Id: checkVatPopup.php,v 1.15.2.2 2011/01/31 23:15:21 eldy Exp $
 */

require ("../../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT."/includes/nusoap/lib/nusoap.php");

$langs->load("companies");

//http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl
$WS_DOL_URL='http://ec.europa.eu/taxation_customs/vies/services/checkVatService';
//$WS_DOL_URL_WSDL=$WS_DOL_URL.'?wsdl';
$WS_DOL_URL_WSDL='http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
$WS_METHOD ='checkVat';


top_htmlhead("", $langs->trans("VATIntraCheckableOnEUSite"));
print '<body style="margin: 10px">';
print '<div>';
print '<div>';

print_fiche_titre($langs->trans("VATIntraCheckableOnEUSite"),'','setup');


if (! $_REQUEST["vatNumber"])
{
	print '<br>';
	print '<font class="error">'.$langs->transnoentities("ErrorFieldRequired",$langs->trans("VATIntraShort")).'</font><br>';
}
else
{
	$countryCode=substr($_REQUEST["vatNumber"],0,2);
	$vatNumber=substr($_REQUEST["vatNumber"],2);
	print '<b>'.$langs->trans("Country").'</b>: '.$countryCode.'<br>';
	print '<b>'.$langs->trans("VATIntraShort").'</b>: '.$vatNumber.'<br>';
	print '<br>';

	// Set the parameters to send to the WebService
	$parameters = array("countryCode" => $countryCode,
						"vatNumber" => $vatNumber);

	// Set the WebService URL
	dol_syslog("Create nusoap_client for URL=".$WS_DOL_URL." WSDL=".$WS_DOL_URL_WSDL);
	$soapclient = new nusoap_client($WS_DOL_URL_WSDL,true);

	// Check for an error
	$err = $soapclient->getError();
	if ($err)
	{
		dol_syslog("Constructor error ".$WS_DOL_URL, LOG_ERR);
	}

	// Call the WebService and store its result in $result.
	dol_syslog("Call method ".$WS_METHOD);
	$result = $soapclient->call($WS_METHOD,$parameters);

	//var_dump($parameters);
	//var_dump($soapclient);
	//print "x".is_array($result)."i";
	//var_dump($result);
	//print $soapclient->request.'<br>';
	//print $soapclient->response.'<br>';

	$messagetoshow='';
	print '<b>'.$langs->trans("Response").'</b>:<br>';

	// Service indisponible
	if (! is_array($result) || preg_match('/SERVICE_UNAVAILABLE/i',$result['faultstring']))
	{
		print '<font class="error">'.$langs->trans("ErrorServiceUnavailableTryLater").'</font><br>';
		$messagetoshow=$soapclient->response;
	}
	elseif (preg_match('/TIMEOUT/i',$result['faultstring']))
	{
		print '<font class="error">'.$langs->trans("ErrorServiceUnavailableTryLater").'</font><br>';
		$messagetoshow=$soapclient->response;
	}
	elseif (preg_match('/SERVER_BUSY/i',$result['faultstring']))
	{
		print '<font class="error">'.$langs->trans("ErrorServiceUnavailableTryLater").'</font><br>';
		$messagetoshow=$soapclient->response;
	}
	elseif ($result['faultstring'])
	{
		print '<font class="error">'.$langs->trans("Error").'</font><br>';
		$messagetoshow=$result['faultstring'];
	}
	// Syntaxe ko
	elseif (preg_match('/INVALID_INPUT/i',$result['faultstring'])
	|| ($result['requestDate'] && ! $result['valid']))
	{
		if ($result['requestDate']) print $langs->trans("Date").': '.$result['requestDate'].'<br>';
		print $langs->trans("VATIntraSyntaxIsValid").': <font class="error">'.$langs->trans("No").'</font> (Might be a non europeen VAT)<br>';
		print $langs->trans("VATIntraValueIsValid").': <font class="error">'.$langs->trans("No").'</font> (Might be a non europeen VAT)<br>';
		//$messagetoshow=$soapclient->response;
	}
	else
	{
		// Syntaxe ok
		if ($result['requestDate']) print $langs->trans("Date").': '.$result['requestDate'].'<br>';
		print $langs->trans("VATIntraSyntaxIsValid").': <font class="ok">'.$langs->trans("Yes").'</font><br>';
		print $langs->trans("VATIntraValueIsValid").': ';
		if (preg_match('/MS_UNAVAILABLE/i',$result['faultstring']))
		{
			print '<font class="error">'.$langs->trans("ErrorVATCheckMS_UNAVAILABLE",$countryCode).'</font><br>';
		}
		else
		{
			if (! empty($result['valid']) && ($result['valid']==1 || $result['valid']=='true'))
			{
				print '<font class="ok">'.$langs->trans("Yes").'</font>';
				print '<br>';
				print $langs->trans("Name").': '.$result['name'].'<br>';
				print $langs->trans("Address").': '.$result['address'].'<br>';
			}
			else
			{
				print '<font class="error">'.$langs->trans("No").'</font>';
				print '<br>'."\n";
			}
		}
	}

	// Show log data into page
	print "\n";
	print '<!-- ';
	var_dump($result);
	print '-->';
}

print '<br>';
print $langs->trans("VATIntraManualCheck",$langs->trans("VATIntraCheckURL"),$langs->trans("VATIntraCheckURL")).'<br>';
print '<br>';
print '<center><input type="button" class="button" value="'.$langs->trans("CloseWindow").'" onclick="javascript: window.close()"></center>';

if ($messagetoshow)
{
	print '<br><br>';
	print "\n".'Error returned:<br>';
	print nl2br($messagetoshow);
}


llxFooter('$Date: 2011/01/31 23:15:21 $ - $Revision: 1.15.2.2 $',0);
?>
