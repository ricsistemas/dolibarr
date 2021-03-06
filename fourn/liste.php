<?php
/* Copyright (C) 2001-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2006 Regis Houssin        <regis@dolibarr.fr>
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
 *       \file       htdocs/fourn/liste.php
 *       \ingroup    fournisseur
 *       \brief      Home page of supplier area
 *       \version    $Id: liste.php,v 1.20 2010/07/10 16:42:12 eldy Exp $
 */

require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formother.class.php");

$langs->load("suppliers");
$langs->load("orders");
$langs->load("companies");

$socname = isset($_GET["socname"])?$_GET["socname"]:'';
$search_nom = isset($_GET["search_nom"])?$_GET["search_nom"]:'';
$search_ville = isset($_GET["search_ville"])?$_GET["search_ville"]:'';

$langs->load("suppliers");
$langs->load("orders");
$langs->load("companies");

// Security check
$socid = isset($_GET["socid"])?$_GET["socid"]:'';
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'societe',$socid,'');

$page = isset($_GET["page"])?$_GET["page"]:'';
$sortorder = isset($_GET["sortorder"])?$_GET["sortorder"]:'';
$sortfield = isset($_GET["sortfield"])?$_GET["sortfield"]:'';
if ($page == -1) { $page = 0 ; }
$offset = $conf->liste_limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortorder) $sortorder="ASC";
if (! $sortfield) $sortfield="nom";

// Load categ filters
$search_categ = isset($_GET["search_categ"])?$_GET["search_categ"]:$_POST["search_categ"];


/*
 *	View
 */

$htmlother=new FormOther($db);

llxHeader();

$sql = "SELECT s.rowid as socid, s.nom, s.ville, s.datec, s.datea,  st.libelle as stcomm, s.prefix_comm";
$sql.= " , code_fournisseur, code_compta_fournisseur";
if (!$user->rights->societe->client->voir && !$socid) $sql .= ", sc.fk_soc, sc.fk_user ";
$sql.= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."c_stcomm as st";
if ($search_categ) $sql.= ", ".MAIN_DB_PREFIX."categorie_fournisseur as cf";
if (!$user->rights->societe->client->voir && !$socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
$sql.= " WHERE s.fk_stcomm = st.id AND s.fournisseur=1";
if ($search_categ) $sql.= " AND s.rowid = cf.fk_societe";	// Join for the needed table to filter by categ
if (!$user->rights->societe->client->voir && !$socid) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
if ($socid) $sql .= " AND s.rowid = ".$socid;
if ($socname) {
	$sql .= " AND s.nom like '%".addslashes($socname)."%'";
	$sortfield = "s.nom";
	$sortorder = "ASC";
}
if ($search_nom)
{
	$sql .= " AND s.nom LIKE '%".addslashes($search_nom)."%'";
}
if ($search_ville)
{
	$sql .= " AND s.ville LIKE '%".addslashes($search_ville)."%'";
}
// Insert categ filter
if ($search_categ)
{
	$sql .= " AND cf.fk_categorie = ".addslashes($search_categ);
}
// Count total nb of records
$nbtotalofrecords = 0;
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$result = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($result);
}
$sql.= $db->order($sortfield,$sortorder);
$sql.= $db->plimit($conf->liste_limit+1, $offset);

$resql = $db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);
	$i = 0;

	$param = "&amp;search_nom=".$search_nom."&amp;search_code=".$search_code."&amp;search_ville=".$search_ville;
 	if ($search_categ != '') $param.='&amp;search_categ='.$search_categ;

	print_barre_liste($langs->trans("ListOfSuppliers"), $page, "liste.php", $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords);

	print '<form action="liste.php" method="GET">';
	print '<table class="liste" width="100%">';

	// Filter on categories
	$moreforfilter='';
	if ($conf->categorie->enabled)
	{
		$moreforfilter.=$langs->trans('Categories'). ': ';
		$moreforfilter.=$htmlother->select_categories(1,$search_categ,'search_categ');
		$moreforfilter.=' &nbsp; &nbsp; &nbsp; ';
	}
	if ($moreforfilter)
	{
		print '<tr class="liste_titre">';
		print '<td class="liste_titre" colspan="6">';
		print $moreforfilter;
		print '</td></tr>';
	}

	print '<tr class="liste_titre">';
	print_liste_field_titre($langs->trans("Company"),$_SERVER["PHP_SELF"],"s.nom","",$param,'valign="middle"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Town"),$_SERVER["PHP_SELF"],"s.ville","",$param,'valign="middle"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("SupplierCode"),$_SERVER["PHP_SELF"],"s.code_fournisseur","",$param,'align="left"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("AccountancyCode"),$_SERVER["PHP_SELF"],"s.code_compta","",$param,'align="left"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("DateCreation"),$_SERVER["PHP_SELF"],"datec","",$param,'align="center"',$sortfield,$sortorder);
	print '<td class="liste_titre">&nbsp;</td>';
	print "</tr>\n";

	print '<tr class="liste_titre">';

	print '<td class="liste_titre"><input type="text" class="flat" name="search_nom" value="'.$search_nom.'"></td>';
	print '<td class="liste_titre"><input type="text" class="flat" name="search_ville" value="'.$search_ville.'"></td>';

	print '<td align="left" class="liste_titre">';
	print '<input class="flat" type="text" size="10" name="search_code_fournisseur" value="'.$_GET["search_code_fournisseur"].'">';
	print '</td>';

	print '<td align="left" class="liste_titre">';
	print '<input class="flat" type="text" size="10" name="search_compta" value="'.$_GET["search_compta"].'">';
	print '</td>';

	print '<td class="liste_titre" colspan="2" align="right"><input class="liste_titre" type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" alt="'.$langs->trans("Search").'"></td>';

	print '</tr>';

	$var=True;

	while ($i < min($num,$conf->liste_limit))
	{
		$obj = $db->fetch_object($resql);
		$var=!$var;

		print "<tr $bc[$var]>";
		print '<td><a href="fiche.php?socid='.$obj->socid.'">'.img_object($langs->trans("ShowSupplier"),"company").'</a>';
		print "&nbsp;<a href=\"fiche.php?socid=".$obj->socid."\">".$obj->nom."</a></td>\n";
		print "<td>".$obj->ville."</td>\n";
		print '<td align="left">'.$obj->code_fournisseur.'&nbsp;</td>';
		print '<td align="left">'.$obj->code_compta_fournisseur.'&nbsp;</td>';
		print '<td align="center">'.dol_print_date($db->jdate($obj->datec)).'</td>';
		print "<td>&nbsp;</td>\n";
		print "</tr>\n";
		$i++;
	}
	print "</table>\n";
	print "</form>\n";
	$db->free($resql);
}
else
{
	dol_print_error($db);
}

$db->close();

llxFooter('$Date: 2010/07/10 16:42:12 $ - $Revision: 1.20 $');
?>
