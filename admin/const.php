<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 */

/**
 *	\file       htdocs/admin/const.php
 *	\ingroup    setup
 *	\brief      Admin page to define miscellaneous constants
 *	\version    $Id: const.php,v 1.66 2010/12/19 00:00:28 eldy Exp $
 */

require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/admin.lib.php");

$langs->load("admin");

if (!$user->admin)
accessforbidden();
//var_dump($_POST);

$typeconst=array('yesno','texte','chaine');


/*
 * Actions
 */

if ($_POST["action"] == 'add')
{
	$error=0;

	if (empty($_POST["constname"]))
	{
		$mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("Name")).'</div>';
		$error++;
	}
	if (empty($_POST["constvalue"]))
	{
		$mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("Value")).'</div>';
		$error++;
	}

	if (! $error)
	{
		if (dolibarr_set_const($db, $_POST["constname"],$_POST["constvalue"],$typeconst[$_POST["consttype"]],1,isset($_POST["constnote"])?$_POST["constnote"]:'',$_POST["entity"]) < 0)
		{
			dolibarr_print_error($db);
		}
	}
}

if (($_POST["const"] && isset($_POST["update"]) && $_POST["update"] == $langs->trans("Modify")))
{
	foreach($_POST["const"] as $const)
	{
		if ($const["check"])
		{
			if (dolibarr_set_const($db, $const["name"],$const["value"],$const["type"],1,$const["note"],$const["entity"]) < 0)
			{
				dolibarr_print_error($db);
			}
		}
	}
}

// Delete several lines at once
if ($_POST["const"] && $_POST["delete"] && $_POST["delete"] == $langs->trans("Delete"))
{
	foreach($_POST["const"] as $const)
	{
		if ($const["check"])	// Is checkbox checked
		{
			if (dolibarr_del_const($db, $const["rowid"], -1) < 0)
			{
				dolibarr_print_error($db);
			}
		}
	}
}

// Delete line from delete picto
if ($_GET["action"] == 'delete')
{
	if (dolibarr_del_const($db, $_GET["rowid"],$_GET["entity"]) < 0)
	{
		dolibarr_print_error($db);
	}
}


/*
 * View
 */

llxHeader('',$langs->trans("OtherSetup"));

// Add logic to show/hide buttons
if ($conf->use_javascript_ajax)
{
?>
<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	jQuery("#updateconst").hide();
	jQuery("#delconst").hide();
	jQuery(".checkboxfordelete").click(function() {
		jQuery("#delconst").show();
	});
	jQuery(".inputforupdate").keypress(function() {
		jQuery("#updateconst").show();
		tmp=jQuery(this).attr("id");
		jQuery("#check_"+tmp).attr("checked",true);
	});
});
</script>
<?php
}

print_fiche_titre($langs->trans("OtherSetup"),'','setup');

print $langs->trans("ConstDesc")."<br>\n";
print "<br>\n";

if ($mesg) print $mesg;

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '<td>'.$langs->trans("Comment").'</td>';
if ($conf->multicompany->enabled && !$user->entity) print '<td>'.$langs->trans("Entity").'</td>';
print '<td align="center">'.$langs->trans("Action").'</td>';
print "</tr>\n";


$form = new Form($db);


// Line to add new record
$var=false;
print "\n";
print '<form action="const.php" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="add">';

print '<tr '.$bc[$var].' class=value><td><input type="text" class="flat" size="24" name="constname" value=""></td>'."\n";
print '<td>';
print '<input type="text" class="flat" size="30" name="constvalue" value="">';
print '</td><td>';
print '<input type="text" class="flat" size="40" name="constnote" value="">';
print '</td>';
// Limit to superadmin
if ($conf->multicompany->enabled && !$user->entity)
{
	print '<td>';
	print '<input type="text" class="flat" size="1" name="entity" value="'.$conf->entity.'">';
	print '</td>';
}
else
{
	print '<input type="hidden" name="entity" value="'.$conf->entity.'">';
}
print '<td align="center">';
print '<input type="submit" class="button" value="'.$langs->trans("Add").'" name="Button">';
print "</td>\n";
print '</tr>';

print '</form>';
print "\n";

print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';

# Affiche lignes des constantes
$sql = "SELECT";
$sql.= " rowid";
$sql.= ", ".$db->decrypt('name')." as name";
$sql.= ", ".$db->decrypt('value')." as value";
$sql.= ", type";
$sql.= ", note";
$sql.= ", entity";
$sql.= " FROM ".MAIN_DB_PREFIX."const";
$sql.= " WHERE entity IN (".$user->entity.",".$conf->entity.")";
if ($user->entity || empty($conf->multicompany->enabled)) $sql.= " AND visible = 1";
$sql.= " ORDER BY entity, name ASC";

dol_syslog("Const::listConstant sql=".$sql);
$result = $db->query($sql);
if ($result)
{
	$num = $db->num_rows($result);
	$i = 0;
	$var=false;

	while ($i < $num)
	{
		$obj = $db->fetch_object($result);
		$var=!$var;

		print "\n";
		print '<input type="hidden" name="const['.$i.'][rowid]" value="'.$obj->rowid.'">';
		print '<input type="hidden" name="const['.$i.'][name]" value="'.$obj->name.'">';
		print '<input type="hidden" name="const['.$i.'][type]" value="'.$obj->type.'">';

		print "<tr $bc[$var] class=value><td>$obj->name</td>\n";

		// Value
		print '<td>';
		print '<input type="text" id="input'.$i.'" class="flat inputforupdate" size="30" name="const['.$i.'][value]" value="'.htmlspecialchars($obj->value).'"';
		print '>';
		print '</td><td>';

		// Note
		print '<input type="text" id="input'.$i.'"class="flat inputforupdate" size="40" name="const['.$i.'][note]" value="'.htmlspecialchars($obj->note,1).'"';
		print '>';
		print '</td>';

		// Entity limit to superadmin
		if ($conf->multicompany->enabled && !$user->entity)
		{
			print '<td>';
			print '<input type="text" class="flat" size="1" name="const['.$i.'][entity]" value="'.$obj->entity.'">';
			print '</td>';
		}
		else
		{
			print '<input type="hidden" name="const['.$i.'][entity]" value="'.$obj->entity.'">';
		}

		print '<td align="center">';
		if ($conf->use_javascript_ajax)
		{
			print '<input type="checkbox" class="flat checkboxfordelete" id="check_input'.$i.'" name="const['.$i.'][check]" value="1">';
			print ' &nbsp; ';
		}
		else
		{
			print '<a href="const.php?rowid='.$obj->rowid.'&entity='.$obj->entity.'&action=delete">'.img_delete().'</a>';
		}

		print "</td></tr>\n";

		print "\n";
		$i++;
	}
}


print '</table>';

if ($conf->use_javascript_ajax)
{
	print '<br>';
	print '<div id="updateconst" align="right">';
	print '<input type="submit" name="update" class="button" value="'.$langs->trans("Modify").'">';
	print '</div>';
	print '<div id="delconst" align="right">';
	print '<input type="submit" name="delete" class="button" value="'.$langs->trans("Delete").'">';
	print '</div>';
}

print "</form>\n";


$db->close();

llxFooter('$Date: 2010/12/19 00:00:28 $ - $Revision: 1.66 $');
?>
