<?php
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2010 Regis Houssin        <regis@dolibarr.fr>
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

/**     \file       htdocs/product/popuprop.php
 *		\ingroup    propal, produit
 *		\brief      Liste des produits/services par popularite
 *		\version    $Id: popuprop.php,v 1.39 2010/04/28 21:29:12 grandoc Exp $
 */

require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT.'/product/class/product.class.php');

// Security check
if ($user->societe_id) $socid=$user->societe_id;
$result=restrictedArea($user,'produit|service');

$sortfield = isset($_GET["sortfield"])?$_GET["sortfield"]:$_POST["sortfield"];
$sortorder = isset($_GET["sortorder"])?$_GET["sortorder"]:$_POST["sortorder"];
$page = $_GET["page"];
if ($page < 0) $page = 0;
if (! $sortfield) $sortfield="c";
if (! $sortorder) $sortorder="DESC";

if ($page == -1) $page = 0;
$limit = $conf->liste_limit;
$offset = $limit * $page ;


$staticproduct=new Product($db);


/*
 * View
 */

llxHeader();

//On n'affiche le lien page suivante que s'il y a une page suivante ...
$sql = "SELECT count(*) as c";
$sql.= " FROM ".MAIN_DB_PREFIX."product";
$sql.= " WHERE entity = ".$conf->entity;
if (isset($_GET['type'])) $sql.= " AND fk_product_type = ".$_GET['type'];

$result=$db->query($sql);
if ($result)
{
    $obj = $db->fetch_object($result);
    $num = $obj->c;
}

$param = '';
$title = $langs->trans("ListProductServiceByPopularity");
if (isset($_GET['type']))
{
	$param = '&amp;type='.$_GET['type'];
	$title = $langs->trans("ListProductByPopularity");
	if ($_GET['type'] == 1) $title = $langs->trans("ListServiceByPopularity");
}

print_barre_liste($title, $page, "popuprop.php",$param,"","","",$num);


print '<table class="noborder" width="100%">';

print "<tr class=\"liste_titre\">";
print_liste_field_titre($langs->trans("Ref"),"popuprop.php", "p.ref","","","",$sortfield,$sortorder);
print_liste_field_titre($langs->trans("Type"),"popuprop.php", "p.type","","","",$sortfield,$sortorder);
print_liste_field_titre($langs->trans("Label"),"popuprop.php", "p.label","","","",$sortfield,$sortorder);
print_liste_field_titre("Nb. de proposition","popuprop.php", "c","","",'align="right"',$sortfield,$sortorder);
print "</tr>\n";

$sql  = "SELECT p.rowid, p.label, p.ref, p.fk_product_type as type, count(*) as c";
$sql.= " FROM ".MAIN_DB_PREFIX."propaldet as pd";
$sql.= ", ".MAIN_DB_PREFIX."product as p";
$sql.= " WHERE p.rowid = pd.fk_product";
$sql.= " AND p.entity = ".$conf->entity;
if (!$user->rights->produit->hidden) $sql.=' AND (p.hidden=0 OR p.fk_product_type != 0)';
if (!$user->rights->service->hidden) $sql.=' AND (p.hidden=0 OR p.fk_product_type != 1)';
if (isset($_GET['type'])) $sql.= " AND fk_product_type = ".$_GET['type'];
$sql.= " GROUP BY (p.rowid)";
$sql.= $db->order($sortfield,$sortorder);
$sql.= $db->plimit($limit ,$offset);

$result=$db->query($sql) ;
if ($result)
{
  $num = $db->num_rows($result);
  $i = 0;

  $var=True;
  while ($i < $num)
    {
      $objp = $db->fetch_object($result);

      	  // Multilangs
	    if ($conf->global->MAIN_MULTILANGS) // si l'option est active
	    {
		    $sql = "SELECT label";
		    $sql.= " FROM ".MAIN_DB_PREFIX."product_lang";
		    $sql.= " WHERE fk_product=".$objp->rowid;
		    $sql.= " AND lang='". $langs->getDefaultLang() ."'";
		    $sql.= " LIMIT 1";

		    $resultp = $db->query($sql);
		    if ($resultp)
		    {
			    $objtp = $db->fetch_object($resultp);
			    if ($objtp->label != '') $objp->label = $objtp->label;
		    }
	    }

      $var=!$var;
      print "<tr $bc[$var]>";
      print '<td><a href="'.DOL_URL_ROOT.'/product/stats/fiche.php?id='.$objp->rowid.'">';
	  if ($objp->type==1) print img_object($langs->trans("ShowService"),"service");
	  else print img_object($langs->trans("ShowProduct"),"product");
      print " ";
      print $objp->ref.'</a></td>';
      print '<td>';
      if ($objp->type==1) print $langs->trans("ShowService");
      else print $langs->trans("ShowProduct");
      print '</td>';
      print '<td>'.$objp->label.'</td>';
      print '<td align="right">'.$objp->c.'</td>';
      print "</tr>\n";
      $i++;
    }
  $db->free();
}
print "</table>";

$db->close();

llxFooter('$Date: 2010/04/28 21:29:12 $ - $Revision: 1.39 $');
?>
