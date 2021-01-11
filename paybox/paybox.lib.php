<?php
/* Copyright (C) 2008-2009 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2007 Regis Houssin        <regis@dolibarr.fr>
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
 * or see http://www.gnu.org/
 */

/**
 *	\file			htdocs/paybox/paybox.lib.php
 *  \brief			Library for common paybox functions
 *  \version		$Id: paybox.lib.php,v 1.13 2010/07/27 22:38:29 eldy Exp $
 */


function llxHeaderPaybox($title, $head = "")
{
	global $user, $conf, $langs;

	header("Content-type: text/html; charset=".$conf->file->character_set_client);

	print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
	//print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd>';
	print "\n";
	print "<html>\n";
	print "<head>\n";
	print '<meta name="robots" content="noindex,nofollow">'."\n";
	print '<meta name="keywords" content="dolibarr,payment,online">'."\n";
	print '<meta name="description" content="Welcome on Dolibarr online payment form">'."\n";
	print "<title>".$title."</title>\n";
	if ($head) print $head."\n";
	if ($conf->global->PAYBOX_CSS_URL) print '<link rel="stylesheet" type="text/css" href="'.$conf->global->PAYBOX_CSS_URL.'?lang='.$langs->defaultlang.'">'."\n";
	else
	{
		print '<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.$conf->css.'?lang='.$langs->defaultlang.'">'."\n";
		print '<style type="text/css">';
		print '.CTableRow1      { margin: 1px; padding: 3px; font: 12px verdana,arial; background: #e6E6eE; color: #000000; -moz-border-radius-topleft:6px; -moz-border-radius-topright:6px; -moz-border-radius-bottomleft:6px; -moz-border-radius-bottomright:6px;}';
		print '.CTableRow2      { margin: 1px; padding: 3px; font: 12px verdana,arial; background: #FFFFFF; color: #000000; -moz-border-radius-topleft:6px; -moz-border-radius-topright:6px; -moz-border-radius-bottomleft:6px; -moz-border-radius-bottomright:6px;}';
		print '</style>';
	}
	print "</head>\n";
	print '<body style="margin: 20px;">'."\n";
}

function llxFooterPayBox()
{
	print "</body>\n";
	print "</html>\n";
}

/**
 *		\brief  	calculates HMAC based on array of parameters
 *		\return 	int				1 if OK, -1 if ERROR
 */
function calc_hmac($ARRAY, $IBS_HASH)
{
	$IBS_HMAC="0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF";	# HMAC Secret Key (by default)	
	if ($conf->global->PAYBOX_IBS_HMAC) $IBS_HMAC=$conf->global->PAYBOX_IBS_HMAC;
	if (empty($IBS_HMAC))
	{
		dol_print_error('',"Paybox setup param PAYBOX_IBS_HMAC not defined");
		return -1;
	}

	$params = array();
	foreach ($ARRAY as $name => $value) {
		$params[] = $name.'='.$value;
	}
	$query = implode('&', $params);

	// Prepare key
	$key = pack('H*', $IBS_HMAC);

	// Sign values
	$sign = hash_hmac($IBS_HASH, $query, $key);
	if ($sign === false) {
		$errorMsg = 'Paybox unable to create hmac signature. Maybe a wrong configuration.';
		dol_print_error('',$errorMsg);
	}

	return strtoupper($sign);

}

/**
 *		\brief  	Create a redirect form to paybox form
 *		\return 	int				1 if OK, -1 if ERROR
 */
function print_paybox_redirect($PRICE,$CURRENCY,$EMAIL,$urlok,$urlko,$TAG)
{
	global $conf, $langs, $db;

	dol_syslog("Paypal.lib::print_paybox_redirect", LOG_DEBUG);

	// Clean parameters
	$PBX_IDENTIFIANT="2";	# Identifiant pour v2 test
	if ($conf->global->PAYBOX_PBX_IDENTIFIANT) $PBX_IDENTIFIANT=$conf->global->PAYBOX_PBX_IDENTIFIANT;
	$IBS_SITE="1999888";    # Site test
	if ($conf->global->PAYBOX_IBS_SITE) $IBS_SITE=$conf->global->PAYBOX_IBS_SITE;
	$IBS_RANG="99";         # Rang test
	if ($conf->global->PAYBOX_IBS_RANG) $IBS_RANG=$conf->global->PAYBOX_IBS_RANG;
	$IBS_DEVISE="840";	# Currency (Dollar US by default)
	$IBS_HASH="256";	# HMAC hash type (256 by default)	
	if ($conf->global->PAYBOX_IBS_HASH) $IBS_HASH=$conf->global->PAYBOX_IBS_HASH;

	if ($CURRENCY == 'USD') $IBS_DEVISE="840";

	$URLPAYBOX="";
	if ($conf->global->PAYBOX_CGI_URL_V2) $URLPAYBOX=$conf->global->PAYBOX_CGI_URL_V2;
	if ($conf->global->PAYBOX_CGI_URL_HMAC) $URLPAYBOX=$conf->global->PAYBOX_CGI_URL_HMAC;


	if (empty($IBS_DEVISE))
	{
		dol_print_error('',"Paybox setup param PAYBOX_IBS_DEVISE not defined");
		return -1;
	}
	if ($CURRENCY == 'EUR') $IBS_DEVISE="978";	if (empty($URLPAYBOX))
	{
		dol_print_error('',"Paybox setup param PAYBOX_CGI_URL_V1 and PAYBOX_CGI_URL_V2 undefined");
		return -1;
	}
	if (empty($IBS_SITE))
	{
		dol_print_error('',"Paybox setup param PAYBOX_IBS_SITE not defined");
		return -1;
	}
	if (empty($IBS_RANG))
	{
		dol_print_error('',"Paybox setup param PAYBOX_IBS_RANG not defined");
		return -1;
	}
	if (empty($IBS_HASH))
	{
		dol_print_error('',"Paybox setup param PAYBOX_IBS_HASH not defined");
		return -1;
	}
	// Definition des parametres vente produit pour paybox
    $IBS_CMD=$TAG;
    $IBS_TOTAL=$PRICE*100;     	# En centimes
    $IBS_MODE=1;            	# Mode formulaire
    $IBS_PORTEUR=$EMAIL;
	$IBS_RETOUR="montant:M;ref:R;auto:A;trans:T";   # Format des parametres du get de validation en reponse (url a definir sous paybox)
    //$IBS_TXT="<center><b>".$langsiso->trans("YouWillBeRedirectedOnPayBox")."</b><br><i>".$langsiso->trans("PleaseBePatient")."...</i><br></center>";
    $IBS_TXT=' ';	// Use a space
    $IBS_BOUTPI=$langs->trans("Wait");
    //$IBS_BOUTPI='';
    $IBS_EFFECTUE=$urlok;
    $IBS_ANNULE=$urlko;
    $IBS_REFUSE=$urlko;
    $IBS_BKGD="#FFFFFF";
    $IBS_WAIT="2000";
	$IBS_LANG="GBR"; 	// By default GBR=english (FRA, GBR, ESP, ITA et DEU...)
	if (preg_match('/^FR/i',$langs->defaultlang)) $IBS_LANG="FRA";
	if (preg_match('/^ES/i',$langs->defaultlang)) $IBS_LANG="ESP";
	if (preg_match('/^IT/i',$langs->defaultlang)) $IBS_LANG="ITA";
	if (preg_match('/^DE/i',$langs->defaultlang)) $IBS_LANG="DEU";
	if (preg_match('/^NL/i',$langs->defaultlang)) $IBS_LANG="NLD";
	if (preg_match('/^SE/i',$langs->defaultlang)) $IBS_LANG="SWE";
	$IBS_OUTPUT='E';
	$PBX_SOURCE='HTML';
	$PBX_TYPEPAIEMENT='CARTE';
	/*building an array to simplify everything*/
	$PBX_ARRAY = array();
	if ($conf->global->PAYBOX_CGI_URL_V2)array_push($PBX_ARRAY, "IBS_MODE"=>$IBS_MODE);	 		
	array_push($PBX_ARRAY, "PBX_SITE"=>$IBS_SITE); 		
	array_push($PBX_ARRAY, "PBX_RANG"=>$IBS_RANG);	 		
	array_push($PBX_ARRAY, "PBX_TOTAL"=>$IBS_TOTAL);		
	array_push($PBX_ARRAY, "PBX_DEVISE"=>$IBS_DEVISE);		
	array_push($PBX_ARRAY, "PBX_CMD	"=>$IBS_CMD);	 		
	array_push($PBX_ARRAY, "PBX_PORTEUR"=>$IBS_PORTEUR);		
	array_push($PBX_ARRAY, "PBX_RETOUR"=>$IBS_RETOUR);		
	array_push($PBX_ARRAY, "PBX_EFFECTUE"=>$IBS_EFFECTUE);	
	array_push($PBX_ARRAY, "PBX_ANNULE"=>$IBS_ANNULE);		
	array_push($PBX_ARRAY, "PBX_REFUSE"=>$IBS_REFUSE);	
	array_push($PBX_ARRAY, "PBX_WAIT"=>$IBS_WAIT);		
	array_push($PBX_ARRAY, "PBX_LANG"=>$IBS_LANG);		
	array_push($PBX_ARRAY, "PBX_OUTPUT"=>$IBS_OUTPUT);		
	array_push($PBX_ARRAY, "PBX_IDENTIFIANT"=>$PBX_IDENTIFIANT);	
	array_push($PBX_ARRAY, "PBX_SOURCE"=>$PBX_SOURCE);	 	
	array_push($PBX_ARRAY, "PBX_TYPEPAIEMENT"=>$PBX_TYPEPAIEMENT);
	array_push($PBX_ARRAY, "PBX_HASH"=>$IBX_HASH);
	ksort($PBX_ARRAY);
    	dol_syslog("Soumission Paybox", LOG_DEBUG);
	foreach($PBX_ARRAY as $var => $val){
		dol_syslog($var.": ".$val, LOG_DEBUG);
	} 


    header("Content-type: text/html; charset=".$conf->file->character_set_client);

    print '<html>'."\n";
    print '<head>'."\n";
    print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".$conf->file->character_set_client."\">\n";
    print '</head>'."\n";
    print '<body>'."\n";
    print "\n";

    // Formulaire pour module Paybox
    print '<form action="'.$URLPAYBOX.'" NAME="Submit" method="POST">'."\n";

    // For Paybox V1 (IBS_xxx)
    /*
    print '<!-- Param for Paybox v1 -->'."\n";
    print '<input type="hidden" name="IBS_MODE" value="'.$IBS_MODE.'">'."\n";
    print '<input type="hidden" name="IBS_SITE" value="'.$IBS_SITE.'">'."\n";
    print '<input type="hidden" name="IBS_RANG" value="'.$IBS_RANG.'">'."\n";
    print '<input type="hidden" name="IBS_TOTAL" value="'.$IBS_TOTAL.'">'."\n";
    print '<input type="hidden" name="IBS_DEVISE" value="'.$IBS_DEVISE.'">'."\n";
    print '<input type="hidden" name="IBS_CMD" value="'.$IBS_CMD.'">'."\n";
    print '<input type="hidden" name="IBS_PORTEUR" value="'.$IBS_PORTEUR.'">'."\n";
    print '<input type="hidden" name="IBS_RETOUR" value="'.$IBS_RETOUR.'">'."\n";
    print '<input type="hidden" name="IBS_EFFECTUE" value="'.$IBS_EFFECTUE.'">'."\n";
    print '<input type="hidden" name="IBS_ANNULE" value="'.$IBS_ANNULE.'">'."\n";
    print '<input type="hidden" name="IBS_REFUSE" value="'.$IBS_REFUSE.'">'."\n";
    print '<input type="hidden" name="IBS_TXT" value="'.$IBS_TXT.'">'."\n";
    print '<input type="hidden" name="IBS_BKGD" value="'.$IBS_BKGD.'">'."\n";
    print '<input type="hidden" name="IBS_WAIT" value="'.$IBS_WAIT.'">'."\n";
    print '<input type="hidden" name="IBS_LANG" value="'.$IBS_LANG.'">'."\n";
    print '<input type="hidden" name="IBS_OUTPUT" value="'.$IBS_OUTPUT.'">'."\n";
	*/

    // For Paybox V2 (PBX_xxx)
    print '<!-- Param for Paybox v2 -->'."\n";
	foreach($PBX_ARRAY as $var => $val){
		print '<input type="hidden" name="'.$var.'" value="'.$val.'">'."\n";
	} 
	//insert HMAC signature
	if ($conf->global->PAYBOX_IBS_HMAC)print '<input type="hidden" name="PBX_HMAC" value="'.calc_hmac($PBX_ARRAY, $IBS_HASH).'">'."\n";
    print '</form>'."\n";

    // Formulaire pour module Paybox v2 (PBX_xxx)


    print "\n";
    print '<script type="text/javascript" language="javascript">'."\n";
    print '	document.Submit.submit();'."\n";
    print '</script>'."\n";
    print "\n";
    print '</body></html>'."\n";
    print "\n";

	return;
}


/**
 * Show footer of company in HTML pages
 *
 * @param   $fromcompany
 * @param   $langs
 */
function html_print_footer($fromcompany,$langs)
{
	global $conf;

	// Juridical status
	$ligne1="";
	if ($fromcompany->forme_juridique_code)
	{
		$ligne1.=($ligne1?" - ":"").$langs->convToOutputCharset(getFormeJuridiqueLabel($fromcompany->forme_juridique_code));
	}
	// Capital
	if ($fromcompany->capital)
	{
		$ligne1.=($ligne1?" - ":"").$langs->transnoentities("CapitalOf",$fromcompany->capital)." ".$langs->transnoentities("Currency".$conf->monnaie);
	}
	// Prof Id 1
	if ($fromcompany->idprof1 && ($fromcompany->pays_code != 'FR' || ! $fromcompany->idprof2))
	{
		$field=$langs->transcountrynoentities("ProfId1",$fromcompany->pays_code);
		if (preg_match('/\((.*)\)/i',$field,$reg)) $field=$reg[1];
		$ligne1.=($ligne1?" - ":"").$field.": ".$langs->convToOutputCharset($fromcompany->idprof1);
	}
	// Prof Id 2
	if ($fromcompany->idprof2)
	{
		$field=$langs->transcountrynoentities("ProfId2",$fromcompany->pays_code);
		if (preg_match('/\((.*)\)/i',$field,$reg)) $field=$reg[1];
		$ligne1.=($ligne1?" - ":"").$field.": ".$langs->convToOutputCharset($fromcompany->idprof2);
	}

	// Second line of company infos
	$ligne2="";
	// Prof Id 3
	if ($fromcompany->idprof3)
	{
		$field=$langs->transcountrynoentities("ProfId3",$fromcompany->pays_code);
		if (preg_match('/\((.*)\)/i',$field,$reg)) $field=$reg[1];
		$ligne2.=($ligne2?" - ":"").$field.": ".$langs->convToOutputCharset($fromcompany->idprof3);
	}
	// Prof Id 4
	if ($fromcompany->idprof4)
	{
		$field=$langs->transcountrynoentities("ProfId4",$fromcompany->pays_code);
		if (preg_match('/\((.*)\)/i',$field,$reg)) $field=$reg[1];
		$ligne2.=($ligne2?" - ":"").$field.": ".$langs->convToOutputCharset($fromcompany->idprof4);
	}
	// IntraCommunautary VAT
	if ($fromcompany->tva_intra != '')
	{
		$ligne2.=($ligne2?" - ":"").$langs->transnoentities("VATIntraShort").": ".$langs->convToOutputCharset($fromcompany->tva_intra);
	}

	print '<br><br><hr>'."\n";
	print '<center><font style="font-size: 10px;">'."\n";
	print $fromcompany->nom.'<br>';
	print $ligne1.'<br>';
	print $ligne2;
	print '</font></center>'."\n";
}

?>
