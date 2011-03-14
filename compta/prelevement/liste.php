<?PHP
/* Copyright (C) 2005      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis@dolibarr.fr>
 * Copyright (C) 2010-2011 Juanjo Menent        <jmenent@2byte.es>
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
 *      \file       htdocs/compta/prelevement/liste.php
 *      \ingroup    prelevement
 *      \brief      Page liste des prelevements
 *      \version    $Id: liste.php,v 1.31 2011/01/05 09:57:55 simnandez Exp $
 */
require('../../main.inc.php');
require_once(DOL_DOCUMENT_ROOT."/compta/prelevement/class/bon-prelevement.class.php");
require_once(DOL_DOCUMENT_ROOT."/compta/prelevement/class/ligne-prelevement.class.php");

$langs->load("withdrawals");
$langs->load("companies");
$langs->load("categories");

// Security check
$socid = isset($_GET["socid"])?$_GET["socid"]:'';
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'prelevement','','','bons');

$bon=new BonPrelevement($db,"");
$ligne=new LignePrelevement($db,$user);

$page = $_GET["page"];
$sortorder = (empty($_GET["sortorder"])) ? "DESC" : $_GET["sortorder"];
$sortfield = (empty($_GET["sortfield"])) ? "p.datec" : $_GET["sortfield"];
$offset = $conf->liste_limit * $page ;



/*
 *  View
 */

llxHeader('',$langs->trans("WithdrawalsLines"));

$sql = "SELECT p.rowid, p.ref, p.statut, p.datec";
$sql.= " ,f.rowid as facid, f.facnumber, f.total_ttc";
$sql.= " , s.rowid as socid, s.nom, s.code_client";
$sql.= " , pl.amount, pl.statut as statut_ligne, pl.rowid as rowid_ligne";
$sql.= " FROM ".MAIN_DB_PREFIX."prelevement_bons as p";
$sql.= " , ".MAIN_DB_PREFIX."prelevement_lignes as pl";
$sql.= " , ".MAIN_DB_PREFIX."prelevement_facture as pf";
$sql.= " , ".MAIN_DB_PREFIX."facture as f";
$sql.= " , ".MAIN_DB_PREFIX."societe as s";
$sql.= " WHERE pl.fk_prelevement_bons = p.rowid";
$sql.= " AND pf.fk_prelevement_lignes = pl.rowid";
$sql.= " AND pf.fk_facture = f.rowid";
$sql.= " AND f.fk_soc = s.rowid";
$sql.= " AND f.entity = ".$conf->entity;
if ($socid) $sql.= " AND s.rowid = ".$socid;
if ($_GET["search_ligne"])
{
    $sql.= " AND pl.rowid = '".$_GET["search_ligne"]."'";
}
if ($_GET["search_bon"])
{
    $sql.= " AND p.ref LIKE '%".$_GET["search_bon"]."%'";
}
if ($_GET["search_code"])
{
    $sql.= " AND s.code_client LIKE '%".$_GET["search_code"]."%'";
}
if ($_GET["search_societe"])
{
    $sel = $_GET["search_societe"];
    $sql .= " AND s.nom LIKE '%".$sel."%'";
}
$sql.=$db->order($sortfield,$sortorder);
$sql.=$db->plimit($conf->liste_limit+1, $offset);

$result = $db->query($sql);
if ($result)
{
    $num = $db->num_rows($result);
    $i = 0;

    $urladd = "&amp;statut=".$_GET["statut"];
    $urladd .= "&amp;search_bon=".$_GET["search_bon"];

    print_barre_liste($langs->trans("WithdrawalsLines"), $page, "liste.php", $urladd, $sortfield, $sortorder, '', $num);

    print"\n<!-- debut table -->\n";
    print '<table class="liste" width="100%">';

    print '<tr class="liste_titre">';
    print '<td class="liste_titre">'.$langs->trans("Line").'</td>';
    print_liste_field_titre($langs->trans("WithdrawalReceipt"),$_SERVER["PHP_SELF"],"p.ref");
    print_liste_field_titre($langs->trans("Bill"),$_SERVER["PHP_SELF"],"f.facnumber",'',$urladd);
    print_liste_field_titre($langs->trans("Company"),$_SERVER["PHP_SELF"],"s.nom");
    print_liste_field_titre($langs->trans("CustomerCode"),$_SERVER["PHP_SELF"],"s.code_client",'','','align="center"');
    print_liste_field_titre($langs->trans("Date"),$_SERVER["PHP_SELF"],"p.datec","","",'align="center"');
    print_liste_field_titre($langs->trans("Amount"),$_SERVER["PHP_SELF"],"pl.amount","","",'align="right"');
    print '<td class="liste_titre">&nbsp;</td>';
    print '</tr>';

    print '<form action="liste.php" method="GET">';
    print '<tr class="liste_titre">';
    print '<td class="liste_titre"><input type="text" class="flat" name="search_ligne" value="'. $_GET["search_ligne"].'" size="6"></td>';
    print '<td class="liste_titre"><input type="text" class="flat" name="search_bon" value="'. $_GET["search_bon"].'" size="8"></td>';
    print '<td>&nbsp;</td>';
    print '<td class="liste_titre"><input type="text" class="flat" name="search_societe" value="'. $_GET["search_societe"].'" size="12"></td>';
    print '<td class="liste_titre" align="center"><input type="text" class="flat" name="search_code" value="'. $_GET["search_code"].'" size="8"></td>';
    print '<td class="liste_titre">&nbsp;</td>';
    print '<td class="liste_titre">&nbsp;</td>';
    print '<td class="liste_titre" align="right"><input type="image" class="liste_titre" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" name="button_search" alt="'.$langs->trans("Search").'"></td>';
    print '</tr>';
    print '</form>';

    $var=True;

    while ($i < min($num,$conf->liste_limit))
    {
        $obj = $db->fetch_object($result);

        $var=!$var;

        print "<tr $bc[$var]><td>";

        print $ligne->LibStatut($obj->statut_ligne,2);
        print "&nbsp;";
        
        print '<a href="'.DOL_URL_ROOT.'/compta/prelevement/ligne.php?id='.$obj->rowid_ligne.'">';
        print substr('000000'.$obj->rowid_ligne, -6);
        print '</a></td>';

        print '<td>';
        
        print $bon->LibStatut($obj->statut,2);
        print "&nbsp;";
        
        print '<a href="fiche.php?id='.$obj->rowid.'">'.$obj->ref."</a></td>\n";

        print '<td><a href="'.DOL_URL_ROOT.'/compta/facture.php?facid='.$obj->facid.'">';
        print img_object($langs->trans("ShowBill"),"bill");
          print '&nbsp;<a href="'.DOL_URL_ROOT.'/compta/facture.php?facid='.$obj->facid.'">'.$obj->facnumber."</a></td>\n";
        print '</a></td>';

        print '<td><a href="fiche.php?id='.$obj->rowid.'">'.$obj->nom."</a></td>\n";

        print '<td align="center"><a href="fiche.php?id='.$obj->rowid.'">'.$obj->code_client."</a></td>\n";

        print '<td align="center">'.dol_print_date($db->jdate($obj->datec),'day')."</td>\n";

        print '<td align="right">'.price($obj->amount)."</td>\n";

        print '<td>&nbsp;</td>';

        print "</tr>\n";
        $i++;
    }
    print "</table>";
    $db->free($result);
}
else
{
    dol_print_error($db);
}

$db->close();


llxFooter('$Date: 2011/01/05 09:57:55 $ - $Revision: 1.31 $');
?>
