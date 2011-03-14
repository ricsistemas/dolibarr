<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *      \file       htdocs/adherents/note.php
 *      \ingroup    member
 *      \brief      Fiche de notes sur un adherent
 *		\version    $Id: note.php,v 1.26 2010/09/02 18:51:48 eldy Exp $
*/

require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT.'/lib/member.lib.php');
require_once(DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php');
require_once(DOL_DOCUMENT_ROOT."/adherents/class/adherent_type.class.php");

$action=isset($_GET["action"])?$_GET["action"]:(isset($_POST["action"])?$_POST["action"]:"");
$id=isset($_GET["id"])?$_GET["id"]:(isset($_POST["id"])?$_POST["id"]:"");

$langs->load("companies");
$langs->load("members");
$langs->load("bills");

if (!$user->rights->adherent->lire)
  accessforbidden();

$adh = new Adherent($db);
$result=$adh->fetch($id);
if ($result > 0)
{
    $adht = new AdherentType($db);
    $result=$adht->fetch($adh->typeid);
}


/******************************************************************************/
/*                     Actions                                                */
/******************************************************************************/

if ($_POST["action"] == 'update' && $user->rights->adherent->creer && ! $_POST["cancel"])
{
	$db->begin();

	$res=$adh->update_note($_POST["note"],$user);
	if ($res < 0)
	{
		$mesg='<div class="error">'.$adh->error.'</div>';
		$db->rollback();
	}
	else
	{
		$db->commit();
	}
}



/*
 * View
 */

llxHeader('',$langs->trans("Member"),'EN:Module_Foundations|FR:Module_Adh&eacute;rents|ES:M&oacute;dulo_Miembros');

$html = new Form($db);

if ($id)
{
	$head = member_prepare_head($adh);

	dol_fiche_head($head, 'note', $langs->trans("Member"), 0, 'user');

	if ($msg) print '<div class="error">'.$msg.'</div>';

	print "<form method=\"post\" action=\"note.php\">";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';

    print '<table class="border" width="100%">';

    // Reference
	print '<tr><td width="20%">'.$langs->trans('Ref').'</td>';
	print '<td colspan="3">';
	print $html->showrefnav($adh,'id');
	print '</td>';
	print '</tr>';

    // Nom
    print '<tr><td>'.$langs->trans("Lastname").'</td><td class="valeur" colspan="3">'.$adh->nom.'&nbsp;</td>';
	print '</tr>';

    // Prenom
    print '<tr><td>'.$langs->trans("Firstname").'</td><td class="valeur" colspan="3">'.$adh->prenom.'&nbsp;</td></tr>';

    // Login
    print '<tr><td>'.$langs->trans("Login").'</td><td class="valeur" colspan="3">'.$adh->login.'&nbsp;</td></tr>';

    // Type
    print '<tr><td>'.$langs->trans("Type").'</td><td class="valeur">'.$adht->getNomUrl(1)."</td></tr>\n";

    // Status
    print '<tr><td>'.$langs->trans("Status").'</td><td class="valeur">'.$adh->getLibStatut(4).'</td></tr>';

    // Note
    print '<tr><td valign="top">'.$langs->trans("Note").'</td>';
	print '<td valign="top" colspan="3">';
	if ($action == 'edit' && $user->rights->adherent->creer)
	{
	    print "<input type=\"hidden\" name=\"action\" value=\"update\">";
		print "<input type=\"hidden\" name=\"id\" value=\"".$adh->id."\">";
        require_once(DOL_DOCUMENT_ROOT."/lib/doleditor.class.php");
        $doleditor=new DolEditor('note',$adh->note,280,'dolibarr_notes','',true,true,$conf->fckeditor->enabled && $conf->global->FCKEDITOR_ENABLE_MEMBER,10,80);
        $doleditor->Create();
	}
	else
	{
		print nl2br($adh->note);
	}
	print "</td></tr>";

	if ($action == 'edit')
	{
		print '<tr><td colspan="4" align="center">';
		print '<input type="submit" class="button" name="update" value="'.$langs->trans("Save").'">';
		print '&nbsp; &nbsp;';
		print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
		print '</td></tr>';
	}

    print "</table>";
	print "</form>\n";


    /*
    * Actions
    */
    print '</div>';
    print '<div class="tabsAction">';

    if ($user->rights->adherent->creer && $action != 'edit')
    {
        print "<a class=\"butAction\" href=\"note.php?id=$adh->id&amp;action=edit\">".$langs->trans('Modify')."</a>";
    }

    print "</div>";


}

$db->close();

llxFooter('$Date: 2010/09/02 18:51:48 $ - $Revision: 1.26 $');
?>
