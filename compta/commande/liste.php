<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis@dolibarr.fr>
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
 *
 * $Id: liste.php,v 1.38 2010/11/20 13:08:48 eldy Exp $
 */

/**
 *       \file       htdocs/compta/commande/liste.php
 *       \ingroup    commande
 *       \brief      Page liste des commandes
 *       \version    $Revision: 1.38 $
 */

require("../../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formfile.class.php");
require_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");

$langs->load('companies');

// Security check
$orderid = GETPOST("orderid",'int');
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'commande',$orderid,'');

// Assign and check variable
$year=GETPOST('year','int');
$month=GETPOST('month','int');
$status=GETPOST('status','int');
$onbill=GETPOST('afacturer','int');
$page=GETPOST('page','int');
$sf_ref=GETPOST('sf_ref','',2);

$sortorder=GETPOST('sortorder','alpha');
$sortfield=GETPOST('sortfield','alpha');
$page = GETPOST("page",'int');
if ($page == -1) { $page = 0; }
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield) $sortfield="c.rowid";
if (! $sortorder) $sortorder="DESC";

$limit = $conf->liste_limit;

$html = new Form($db);
$formfile = new FormFile($db);


/*
 * View
 */

$now=gmmktime();

llxHeader();

$sql = "SELECT s.nom, s.rowid as socid,";
$sql.= " c.rowid, c.ref, c.total_ht, c.date_commande as date_commande,";
$sql.= " c.fk_statut, c.facture";
$sql.= " FROM ".MAIN_DB_PREFIX."societe as s";
$sql.= ", ".MAIN_DB_PREFIX."commande as c";
if (!$user->rights->societe->client->voir && !$socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
$sql.= " WHERE c.fk_soc = s.rowid";
$sql.= " AND s.entity = ".$conf->entity;
if (!$user->rights->societe->client->voir && !$socid) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
if ($socid) $sql.= " AND s.rowid = ".$socid;
if ($month > 0)
{
    $sql.= " AND date_format(c.date_commande, '%Y-%m') = '".$year."-".$month."'";
}
if ($year > 0)
{
    $sql.= " AND date_format(c.date_commande, '%Y') = '".$year."'";
}
if ($status)
{
    $sql.= " AND fk_statut = ".$status;
}
if ($onbill == 1)
{
    $sql.= " AND fk_statut >=1	AND c.facture = 0";
}
if (dol_strlen($sf_ref) > 0)
{
    $sql.= " AND c.ref like '%".$sf_ref . "%'";
}
$sql.= " ORDER BY $sortfield $sortorder";
$sql.= $db->plimit($limit + 1,$offset);

$resql = $db->query($sql);

if ($resql)
{
    if ($socid)
    {
        $soc = new Societe($db);
        $soc->fetch($socid);
        $title = $langs->trans("ListOfOrders") . " - ".$soc->nom;
    }
    else
    {
        $title = $langs->trans("ListOfOrders");
    }
    // Si page des commandes a facturer
    $link=DOL_URL_ROOT."/compta/commande/fiche.php";
    $title.=" - ".$langs->trans("StatusOrderToBill");
    $param="&amp;socid=".$socid."&amp;year=".$year."&amp;month=".$month;

    $num = $db->num_rows($resql);
    print_barre_liste($title, $page, "liste.php",$param,$sortfield,$sortorder,'',$num);

    $i = 0;
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans("Ref"),"liste.php","c.ref","",$param,'',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans("Company"),"liste.php","s.nom","",$param,'',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans("Date"),"liste.php","c.date_commande","",$param, 'align="center"',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans("Status"),"liste.php","c.fk_statut","",$param,'align="right"',$sortfield,$sortorder);
    print "</tr>\n";
    $var=True;

    $generic_commande = new Commande($db);

    while ($i < min($num,$limit))
    {
        $objp = $db->fetch_object($resql);

        $var=!$var;
        print "<tr $bc[$var]>";

        print '<td width="20%" nowrap="nowrap">';

        $generic_commande->id=$objp->rowid;
        $generic_commande->ref=$objp->ref;

        print '<table class="nobordernopadding"><tr class="nocellnopadd">';
        print '<td class="nobordernopadding" nowrap="nowrap">';
        print $generic_commande->getNomUrl(1);
        print '</td>';

        print '<td width="20" class="nobordernopadding" nowrap="nowrap">';
        if (($db->jdate($objp->date_commande) < ($now - $conf->commande->client->warning_delay)) && $objp->statutid == 1 ) print img_picto($langs->trans("Late"),"warning");
        print '</td>';

        print '<td width="16" align="right" class="nobordernopadding">';
        $filename=dol_sanitizeFileName($objp->ref);
        $filedir=$conf->commande->dir_output . '/' . dol_sanitizeFileName($objp->ref);
        $urlsource=$_SERVER['PHP_SELF'].'?id='.$objp->rowid;
        $formfile->show_documents('commande',$filename,$filedir,$urlsource,'','','',1,'',1);
        print '</td></tr></table>';

        print '</td>';

        print "<td><a href=\"".DOL_URL_ROOT."/comm/fiche.php?socid=".$objp->socid."\">".img_object($langs->trans("ShowCompany"),"company")." ".$objp->nom."</a>";
        print "</td>";

        print "<td align=\"center\">";
        $y = dol_print_date($db->jdate($objp->date_commande),"%Y");
        $m = dol_print_date($db->jdate($objp->date_commande),"%m");
        $mt = dol_print_date($db->jdate($objp->date_commande),"%b");
        $d = dol_print_date($db->jdate($objp->date_commande),"%d");
        print $d."\n";
        print " <a href=\"liste.php?year=$y&amp;month=$m\">";
        print $mt."</a>\n";
        print " <a href=\"liste.php?year=$y\">";
        print $y."</a></td>\n";

		print '<td align="right">'.$generic_commande->LibStatut($objp->fk_statut,$objp->facture,5).'</td>';
        print "</tr>\n";

        $total = $total + $objp->price;
        $subtotal = $subtotal + $objp->price;

        $i++;
    }

    print "</table>";
    $db->free($resql);
}
else
{
    print dol_print_error($db);
}


$db->close();

llxFooter('$Date: 2010/11/20 13:08:48 $ - $Revision: 1.38 $');
?>
