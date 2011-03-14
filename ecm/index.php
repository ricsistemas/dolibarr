<?php
/* Copyright (C) 2008-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2008-2010 Regis Houssin        <regis@dolibarr.fr>
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
 *	\file       htdocs/ecm/index.php
 *	\ingroup    ecm
 *	\brief      Main page for ECM section area
 *	\version    $Id: index.php,v 1.92 2011/01/10 00:09:31 eldy Exp $
 *	\author		Laurent Destailleur
 */

if (! defined('REQUIRE_JQUERY_LAYOUT'))  define('REQUIRE_JQUERY_LAYOUT','1');

require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formfile.class.php");
require_once(DOL_DOCUMENT_ROOT."/lib/ecm.lib.php");
require_once(DOL_DOCUMENT_ROOT."/lib/files.lib.php");
require_once(DOL_DOCUMENT_ROOT."/lib/treeview.lib.php");
require_once(DOL_DOCUMENT_ROOT."/ecm/class/ecmdirectory.class.php");

// Load traductions files
$langs->load("ecm");
$langs->load("companies");
$langs->load("other");
$langs->load("users");
$langs->load("orders");
$langs->load("propal");
$langs->load("bills");
$langs->load("contracts");

// Security check
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'ecm','');

// Load permissions
$user->getrights('ecm');

// Get parameters
$socid=GETPOST('socid');
$action=GETPOST("action");
$section=GETPOST("section");
if (! $section) $section=0;

$upload_dir = $conf->ecm->dir_output.'/'.$section;

$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if ($page == -1) { $page = 0; }
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortorder) $sortorder="ASC";
if (! $sortfield) $sortfield="label";

$ecmdir = new ECMDirectory($db);
if (GETPOST("section"))
{
	$result=$ecmdir->fetch(GETPOST("section"));
	if (! $result > 0)
	{
		dol_print_error($db,$ecmdir->error);
		exit;
	}
}

$form=new Form($db);
$ecmdirstatic = new ECMDirectory($db);
$userstatic = new User($db);


/*******************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 ********************************************************************/

// Upload file
if (GETPOST("sendit") && ! empty($conf->global->MAIN_UPLOAD_DOC))
{
	$relativepath=$ecmdir->getRelativePath();
	$upload_dir = $conf->ecm->dir_output.'/'.$relativepath;

	if (create_exdir($upload_dir) >= 0)
	{
		$resupload = dol_move_uploaded_file($_FILES['userfile']['tmp_name'], $upload_dir . "/" . $_FILES['userfile']['name'],0, 0, $_FILES['userfile']['error']);
		if (is_numeric($resupload) && $resupload > 0)
		{
			//$mesg = '<div class="ok">'.$langs->trans("FileTransferComplete").'</div>';
			//print_r($_FILES);
			$result=$ecmdir->changeNbOfFiles('+');
		}
		else
		{
			$langs->load("errors");
			if ($resupload < 0)	// Unknown error
			{
				$mesg = '<div class="error">'.$langs->trans("ErrorFileNotUploaded").'</div>';
			}
			else if (preg_match('/ErrorFileIsInfectedWithAVirus/',$resupload))	// Files infected by a virus
			{
				$mesg = '<div class="error">'.$langs->trans("ErrorFileIsInfectedWithAVirus").'</div>';
			}
			else	// Known error
			{
				$mesg = '<div class="error">'.$langs->trans($resupload).'</div>';
			}
		}
	}
	else
	{
		$langs->load("errors");
		$mesg = '<div class="error">'.$langs->trans("ErrorFailToCreateDir",$upload_dir).'</div>';
	}
}

// Add directory
if (GETPOST("action") == 'add' && $user->rights->ecm->setup)
{
	$ecmdir->ref                = 'NOTUSEDYET';
	$ecmdir->label              = GETPOST("label");
	$ecmdir->description        = GETPOST("desc");

	$id = $ecmdir->create($user);
	if ($id > 0)
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		$mesg='<div class="error">Error '.$langs->trans($ecmdir->error).'</div>';
		$_GET["action"] = "create";
	}
}

// Remove file
if (GETPOST('action') == 'confirm_deletefile' && GETPOST('confirm') == 'yes')
{
	$result=$ecmdir->fetch(GETPOST("section"));
	if (! $result > 0)
	{
		dol_print_error($db,$ecmdir->error);
		exit;
	}
	$relativepath=$ecmdir->getRelativePath();
	$upload_dir = $conf->ecm->dir_output.'/'.$relativepath;
	$file = $upload_dir . "/" . GETPOST('urlfile');	// Do not use urldecode here ($_GET and $_REQUEST are already decoded by PHP).

	$result=dol_delete_file($file);

	$mesg = '<div class="ok">'.$langs->trans("FileWasRemoved").'</div>';

	$result=$ecmdir->changeNbOfFiles('-');
	$action='file_manager';
}

// Remove directory
if (GETPOST('action') == 'confirm_deletesection' && GETPOST('confirm') == 'yes')
{
	$result=$ecmdir->delete($user);
	$mesg = '<div class="ok">'.$langs->trans("ECMSectionWasRemoved", $ecmdir->label).'</div>';
}

// Refresh directory view
if (GETPOST("action") == 'refreshmanual')
{
    $diroutputslash=str_replace('\\','/',$conf->ecm->dir_output);
    $diroutputslash.='/';

    // Scan directory tree on disk
    $disktree=dol_dir_list($conf->ecm->dir_output,'directories',1,'','','','',0);

    // Scan directory tree in database
    $sqltree=$ecmdirstatic->get_full_arbo(0);

    $adirwascreated=0;

    // Now we compare both trees to complete missing trees into database
    //var_dump($disktree);
    //var_dump($sqltree);
    foreach($disktree as $dirdesc)
    {
        $dirisindatabase=0;
        foreach($sqltree as $dirsqldesc)
        {
            if ($conf->ecm->dir_output.'/'.$dirsqldesc['fullrelativename'] == $dirdesc['fullname'])
            {
                $dirisindatabase=1;
                break;
            }
        }

        if (! $dirisindatabase)
        {
            $txt="Directory found on disk ".$dirdesc['fullname'].", not found into database so we add it";
            dol_syslog($txt);
            //print $txt."<br>\n";

            // We must first find the fk_parent of directory to create $dirdesc['fullname']
            $fk_parent=-1;
            $relativepathmissing=str_replace($diroutputslash,'',$dirdesc['fullname']);
            $relativepathtosearchparent=$relativepathmissing;
            //dol_syslog("Try to find parent id for directory ".$relativepathtosearchparent);
            if (preg_match('/\//',$relativepathtosearchparent))
            //while (preg_match('/\//',$relativepathtosearchparent))
            {
                $relativepathtosearchparent=preg_replace('/\/[^\/]*$/','',$relativepathtosearchparent);
                $txt="Is relative parent path ".$relativepathtosearchparent." for ".$relativepathmissing." found in sql tree ?";
                dol_syslog($txt);
                //print $txt." -> ";
                $parentdirisindatabase=0;
                foreach($sqltree as $dirsqldesc)
                {
                    if ($dirsqldesc['fullrelativename'] == $relativepathtosearchparent)
                    {
                        $parentdirisindatabase=$dirsqldesc['id'];
                        break;
                    }
                }
                if ($parentdirisindatabase > 0)
                {
                    dol_syslog("Yes with id ".$parentdirisindatabase);
                    //print "Yes with id ".$parentdirisindatabase."<br>\n";
                    $fk_parent=$parentdirisindatabase;
                    //break;  // We found parent, we can stop the while loop
                }
                else
                {
                    dol_syslog("No");
                    //print "No<br>\n";
                }
            }
            else
            {
                $fk_parent=0;   // Parent is root
            }

            if ($fk_parent >= 0)
            {
                $ecmdirtmp=new ECMDirectory($db);
                $ecmdirtmp->ref                = 'NOTUSEDYET';
                $ecmdirtmp->label              = basename($dirdesc['fullname']);
                $ecmdirtmp->description        = '';
                $ecmdirtmp->fk_parent          = $fk_parent;

                $txt="We create directory ".$ecmdirtmp->label." with parent ".$fk_parent;
                dol_syslog($txt);
                //print $txt."<br>\n";
                $id = $ecmdirtmp->create($user);
                if ($id > 0)
                {
                    $newdirsql=array('id'=>$id,
                                     'id_mere'=>$ecmdirtmp->fk_parent,
                                     'label'=>$ecmdirtmp->label,
                                     'description'=>$ecmdirtmp->description,
                                     'fullrelativename'=>$relativepathmissing);
                    $sqltree[]=$newdirsql; // We complete fulltree for following loops
                    //var_dump($sqltree);
                    $adirwascreated=1;
                }
            }
            else {
                $txt="Parent of ".$dirdesc['fullname']." not found";
                dol_syslog($txt);
                //print $txt."<br>\n";
            }
        }
    }

    // If a directory was added, the fulltree array is not correctly completed and sorted, so we clean
    // it to be sure that fulltree array is not used without reloading it.
    if ($adirwascreated) $sqltree=null;
}


/*******************************************************************
 * View
 ********************************************************************/

//print "xx".$_SESSION["dol_screenheight"];
$maxheightwin=(isset($_SESSION["dol_screenheight"]) && $_SESSION["dol_screenheight"] > 500)?($_SESSION["dol_screenheight"]-166):660;

$morehead="<style type=\"text/css\">
html, body {
        width:      100%;
        height:     100%;
        padding:    0;
        margin:     0;
        overflow:   auto; /* when page gets too small */
    }
    #containerlayout {
        background: #999;
        height:     ".$maxheightwin."px;
        margin:     0 auto;
        width:      100%;
        min-width:  700px;
        _width:     700px; /* min-width for IE6 */
    }
    .pane {
        display:    none; /* will appear when layout inits */
    }
</style>
<SCRIPT type=\"text/javascript\">
    jQuery(document).ready(function () {
        jQuery('#containerlayout').layout({
        	name: \"ecmlayout\"
        ,   center__paneSelector:   \"#ecm-layout-center\"
        ,   north__paneSelector:    \"#ecm-layout-north\"
        ,   west__paneSelector:     \"#ecm-layout-west\"
        ,   resizable: true
        ,   north__size:        34
        ,   north__resizable:   false
        ,   north__closable:    false
        ,   west__size:         320
        ,   west__minSize:      280
        ,   west__slidable:     true
        ,   west__resizable:    true
        ,   west__togglerLength_closed: '100%'
        ,   useStateCookie:     true
            });

        jQuery('#ecm-layout-center').layout({
            center__paneSelector:   \".ecm-in-layout-center\"
        ,   south__paneSelector:    \".ecm-in-layout-south\"
        ,   resizable: false
        ,   south__minSize:      32
        ,   south__resizable:   false
        ,   south__closable:    false
            });
    });
</SCRIPT>";

llxHeader($morehead,$langs->trans("ECM"),'','','','','','',0,0);

// Ajout rubriques automatiques
$rowspan=0;
$sectionauto=array();
if ($conf->product->enabled || $conf->service->enabled)     { $rowspan++; $sectionauto[]=array('level'=>1, 'module'=>'product', 'test'=>$conf->product->enabled, 'label'=>$langs->trans("ProductsAndServices"),     'desc'=>$langs->trans("ECMDocsByProducts")); }
if ($conf->societe->enabled)     { $rowspan++; $sectionauto[]=array('level'=>1, 'module'=>'company', 'test'=>$conf->societe->enabled, 'label'=>$langs->trans("ThirdParties"), 'desc'=>$langs->trans("ECMDocsByThirdParties")); }
if ($conf->propal->enabled)      { $rowspan++; $sectionauto[]=array('level'=>1, 'module'=>'propal',  'test'=>$conf->propal->enabled,  'label'=>$langs->trans("Prop"),    'desc'=>$langs->trans("ECMDocsByProposals")); }
if ($conf->contrat->enabled)     { $rowspan++; $sectionauto[]=array('level'=>1, 'module'=>'contract','test'=>$conf->contrat->enabled, 'label'=>$langs->trans("Contracts"),    'desc'=>$langs->trans("ECMDocsByContracts")); }
if ($conf->commande->enabled)    { $rowspan++; $sectionauto[]=array('level'=>1, 'module'=>'order',   'test'=>$conf->commande->enabled,'label'=>$langs->trans("CustomersOrders"),       'desc'=>$langs->trans("ECMDocsByOrders")); }
if ($conf->fournisseur->enabled) { $rowspan++; $sectionauto[]=array('level'=>1, 'module'=>'order_supplier', 'test'=>$conf->fournisseur->enabled, 'label'=>$langs->trans("SuppliersInvoices"),     'desc'=>$langs->trans("ECMDocsByOrders")); }
if ($conf->facture->enabled)     { $rowspan++; $sectionauto[]=array('level'=>1, 'module'=>'invoice', 'test'=>$conf->facture->enabled, 'label'=>$langs->trans("CustomersInvoices"),     'desc'=>$langs->trans("ECMDocsByInvoices")); }
if ($conf->fournisseur->enabled) { $rowspan++; $sectionauto[]=array('level'=>1, 'module'=>'invoice_supplier', 'test'=>$conf->fournisseur->enabled, 'label'=>$langs->trans("SuppliersOrders"),     'desc'=>$langs->trans("ECMDocsByOrders")); }


//***********************
// List
//***********************
print_fiche_titre($langs->trans("ECMArea").' - '.$langs->trans("ECMFileManager"));

print $langs->trans("ECMAreaDesc")."<br>";
print $langs->trans("ECMAreaDesc2")."<br>";
print "<br>\n";

// Confirm remove file
if (GETPOST('action') == 'delete')
{
	$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?section='.$_REQUEST["section"].'&urlfile='.urlencode($_GET["urlfile"]), $langs->trans('DeleteFile'), $langs->trans('ConfirmDeleteFile'), 'confirm_deletefile','','',1);
	if ($ret == 'html') print '<br>';
}

if ($mesg) { print $mesg."<br>"; }

// Toolbar
$head = ecm_prepare_head_fm($fac);
//dol_fiche_head($head, 'file_manager', '', 1);


//$conf->use_javascript_ajax=0;


if ($conf->use_javascript_ajax)
{
?>
<div id="containerlayout"> <!-- begin div id="containerlayout" -->
    <div id="ecm-layout-north" class="pane toolbar">
<?php
}
else
{
    print '<table class="border" width="100%">';

    // Toolbar
    print '<tr><td colspan="2" style="background: #FFFFFF" style="height: 24px !important">';
}

// Show button to create a directory
//if (empty($action) || $action == 'file_manager' || preg_match('/refresh/i',$action))
//{
    if ($user->rights->ecm->setup)
    {
        print '<a href="'.DOL_URL_ROOT.'/ecm/docdir.php?action=create" title="'.dol_escape_htmltag($langs->trans('ECMAddSection')).'">';
        //print $langs->trans('ECMAddSection');
        print '<img width="32" height="32" border="0" src="'.DOL_URL_ROOT.'/theme/common/folder-new.png">';
        print '</a>';
    }
    else
    {
        print '<a href="#" title="'.$langs->trans("NotAllowed").'">';
        //print $langs->trans('ECMAddSection');
        print '<img width="32" height="32" border="0" src="'.DOL_URL_ROOT.'/theme/common/folder-new.png">';
        print '</a>';
    }
//}
// Show button to refresh listing
print '<a href="'.$_SERVER["PHP_SELF"].'?action=refreshmanual'.($section?'&amp;section='.$section:'').'"  title="'.dol_escape_htmltag($langs->trans('Refresh')).'">';
print '<img width="32" height="32" border="0" src="'.DOL_URL_ROOT.'/theme/common/view-refresh.png">';
print '</a>';


if ($conf->use_javascript_ajax)
{
?>
   </div>

    <div id="ecm-layout-west" class="pane">
<?php
}
else
{
    print '</td></tr>';
    print '<tr>';

    print '<td width="40%" valign="top" style="background: #FFFFFF" rowspan="2">';
}


// Left area


if (empty($action) || $action == 'file_manager' || preg_match('/refresh/i',$action) || $action == 'delete')
{
	$userstatic = new User($db);
	$ecmdirstatic = new ECMDirectory($db);

	// Confirmation de la suppression d'une ligne categorie
	if ($_GET['action'] == 'delete_section')
	{
		$ret=$form->form_confirm($_SERVER["PHP_SELF"].'?section='.urlencode($_GET["section"]), $langs->trans('DeleteSection'), $langs->trans('ConfirmDeleteSection',$ecmdir->label), 'confirm_deletesection','','',1);
		if ($ret == 'html') print '<br>';
	}

	print '<table width="100%" class="nobordernopadding">';

	print '<tr class="liste_titre"';
    print '<td class="liste_titre" align="left" colspan="6">';
    print '&nbsp;'.$langs->trans("ECMSections");
	print '</td></tr>';

    $showonrightsize='';

	if (sizeof($sectionauto))
	{
		// Root title line (Automatic section)
		print '<tr>';
		print '<td>';
		print '<table class="nobordernopadding"><tr class="nobordernopadding">';
		print '<td align="left" width="24">';
		print img_picto_common('','treemenu/base.gif');
		print '</td><td align="left">'.$langs->trans("ECMRoot").' ('.$langs->trans("ECMSectionsAuto").')';
		print '</td>';
		print '</tr></table>';
		print '</td>';
		print '<td align="right">&nbsp;</td>';
		print '<td align="right">&nbsp;</td>';
		print '<td align="right">&nbsp;</td>';
		print '<td align="right">&nbsp;</td>';
		print '<td align="center">';
		$htmltooltip=$langs->trans("ECMAreaDesc2");
		print $form->textwithpicto('',$htmltooltip,1,0);
		print '</td>';
		//print '<td align="right">'.$langs->trans("ECMNbOfDocsSmall").' <a href="'.$_SERVER["PHP_SELF"].'?action=refreshauto">'.img_picto($langs->trans("Refresh"),'refresh').'</a></td>';
		print '</tr>';

		$sectionauto=dol_sort_array($sectionauto,'label','ASC',true,false);

		$nbofentries=0;
		$oldvallevel=0;
		foreach ($sectionauto as $key => $val)
		{
			if ($val['test'])
			{
				$var=false;

				print '<tr>';

				// Section
				print '<td align="left">';
				print '<table class="nobordernopadding"><tr class="nobordernopadding"><td>';
				tree_showpad($sectionauto,$key);
				print '</td>';

				print '<td valign="top">';
				if ($val['module'] == $_REQUEST["module"])
				{
					$n=3;
					$ref=img_picto('',DOL_URL_ROOT.'/theme/common/treemenu/minustop'.$n.'.gif','',1);
				}
				else
				{
					$n=3;
					$ref=img_picto('',DOL_URL_ROOT.'/theme/common/treemenu/plustop'.$n.'.gif','',1);
				}
				print '<a href="'.DOL_URL_ROOT.'/ecm/index.php?module='.$val['module'].'">';
				print $ref;
				print '</a>';
				print img_picto('',DOL_URL_ROOT.'/theme/common/treemenu/folder.gif','',1);
				print '</td>';

				print '<td valign="middle">';
				print '<a href="'.DOL_URL_ROOT.'/ecm/index.php?module='.$val['module'].'">';
				print $val['label'];
				print '</a></td></tr></table>';
				print "</td>\n";

				// Nb of doc in dir
				print '<td align="right">&nbsp;</td>';

				// Nb of doc in subdir
				print '<td align="right">&nbsp;</td>';

				// Edit link
				print '<td align="right">&nbsp;</td>';

				// Add link
				print '<td align="right">&nbsp;</td>';

				// Info
				print '<td align="center">';
				$htmltooltip='<b>'.$langs->trans("ECMSection").'</b>: '.$val['label'].'<br>';
				$htmltooltip='<b>'.$langs->trans("Type").'</b>: '.$langs->trans("ECMSectionAuto").'<br>';
				$htmltooltip.='<b>'.$langs->trans("ECMCreationUser").'</b>: '.$langs->trans("ECMTypeAuto").'<br>';
				$htmltooltip.='<b>'.$langs->trans("Description").'</b>: '.$val['desc'];
				print $form->textwithpicto('',$htmltooltip,1,0);
				print '</td>';

				print "</tr>\n";

				// Show sublevel
				if ($val['module'] == $_REQUEST["module"])
				{
					if ($val['module'] == 'xxx')
					{
					}
					else
					{
						$showonrightsize='featurenotyetavailable';
					}
				}



				$oldvallevel=$val['level'];
				$nbofentries++;
			}
		}
	}

	// Root title line (Manual section)
	print '<tr><td>';
	print '<table class="nobordernopadding"><tr class="nobordernopadding">';
	print '<td align="left" width="24px">';
	print img_picto_common('','treemenu/base.gif');
	print '</td><td align="left">'.$langs->trans("ECMRoot").' ('.$langs->trans("ECMSectionsManual").')';
	print '</td>';
	print '</tr></table></td>';
	print '<td align="right">';
	print '</td>';
	print '<td align="right">&nbsp;</td>';
	//print '<td align="right"><a href="'.DOL_URL_ROOT.'/ecm/docdir.php?action=create">'.img_edit_add().'</a></td>';
	print '<td align="right">&nbsp;</td>';
	print '<td align="right">&nbsp;</td>';
	print '<td align="center">';
	$htmltooltip=$langs->trans("ECMAreaDesc2");
	print $form->textwithpicto('',$htmltooltip,1,0);
	print '</td>';
	print '</tr>';



	// Load full tree
	if (empty($sqltree)) $sqltree=$ecmdirstatic->get_full_arbo(0);

	// ----- This section will show a tree from a fulltree array -----
	// $section must also be defined
	// ----------------------------------------------------------------

	// Define fullpathselected ( _x_y_z ) of $section parameter
	$fullpathselected='';
	foreach($sqltree as $key => $val)
	{
		//print $val['id']."-".$section."<br>";
		if ($val['id'] == $section)
		{
			$fullpathselected=$val['fullpath'];
			break;
		}
	}
	//print "fullpathselected=".$fullpathselected."<br>";

	// Update expandedsectionarray in session
	$expandedsectionarray=array();
	if (isset($_SESSION['dol_ecmexpandedsectionarray'])) $expandedsectionarray=explode(',',$_SESSION['dol_ecmexpandedsectionarray']);

	if ($section && $_GET['sectionexpand'] == 'true')
	{
		// We add all sections that are parent of opened section
		$pathtosection=explode('_',$fullpathselected);
		foreach($pathtosection as $idcursor)
		{
			if ($idcursor && ! in_array($idcursor,$expandedsectionarray))	// Not already in array
			{
				$expandedsectionarray[]=$idcursor;
			}
		}
		$_SESSION['dol_ecmexpandedsectionarray']=join(',',$expandedsectionarray);
	}
	if ($section && $_GET['sectionexpand'] == 'false')
	{
		// We removed all expanded sections that are child of the closed section
		$oldexpandedsectionarray=$expandedsectionarray;
		$expandedsectionarray=array();	// Reset
		foreach($oldexpandedsectionarray as $sectioncursor)
		{
			// is_in_subtree(fulltree,sectionparent,sectionchild)
			if ($sectioncursor && ! is_in_subtree($sqltree,$section,$sectioncursor)) $expandedsectionarray[]=$sectioncursor;
		}
		$_SESSION['dol_ecmexpandedsectionarray']=join(',',$expandedsectionarray);
	}
	//print $_SESSION['dol_ecmexpandedsectionarray'].'<br>';

	$nbofentries=0;
	$oldvallevel=0;
	$var=true;
	foreach($sqltree as $key => $val)
	{
		$var=false;

		$ecmdirstatic->id=$val['id'];
		$ecmdirstatic->ref=$val['label'];

		// Refresh cache
		if (preg_match('/refresh/i',$_GET['action']))
		{
			$result=$ecmdirstatic->fetch($val['id']);
			$ecmdirstatic->ref=$ecmdirstatic->label;

			$result=$ecmdirstatic->refreshcachenboffile();
			$val['cachenbofdoc']=$result;
		}

		//$fullpathparent=preg_replace('/(_[^_]+)$/i','',$val['fullpath']);

		// Define showline
		$showline=0;

		// If directory is son of expanded directory, we show line
		if (in_array($val['id_mere'],$expandedsectionarray)) $showline=4;
		// If directory is brother of selected directory, we show line
		elseif ($val['id'] != $section && $val['id_mere'] == $ecmdirstatic->motherof[$section]) $showline=3;
		// If directory is parent of selected directory or is selected directory, we show line
		elseif (preg_match('/'.$val['fullpath'].'_/i',$fullpathselected.'_')) $showline=2;
		// If we are level one we show line
		elseif ($val['level'] < 2) $showline=1;

		if ($showline)
		{
			if (in_array($val['id'],$expandedsectionarray)) $option='indexexpanded';
			else $option='indexnotexpanded';
			//print $option;

			print '<tr>';

			// Show tree graph pictos
			print '<td align="left">';
			print '<table class="nobordernopadding"><tr class="nobordernopadding"><td>';
			$resarray=tree_showpad($sqltree,$key);
			$a=$resarray[0];
			$nbofsubdir=$resarray[1];
			$c=$resarray[2];
			$nboffilesinsubdir=$resarray[3];
			print '</td>';

			// Show picto
			print '<td valign="top">';
			//print $val['fullpath']."(".$showline.")";
			$n='2';
			if ($b == 0 || ! in_array($val['id'],$expandedsectionarray)) $n='3';
			if (! in_array($val['id'],$expandedsectionarray)) $ref=img_picto('',DOL_URL_ROOT.'/theme/common/treemenu/plustop'.$n.'.gif','',1);
			else $ref=img_picto('',DOL_URL_ROOT.'/theme/common/treemenu/minustop'.$n.'.gif','',1);
			if ($option == 'indexexpanded') $lien = '<a href="'.$_SERVER["PHP_SELF"].'?section='.$val['id'].'&amp;sectionexpand=false">';
	    	if ($option == 'indexnotexpanded') $lien = '<a href="'.$_SERVER["PHP_SELF"].'?section='.$val['id'].'&amp;sectionexpand=true">';
	    	//$newref=str_replace('_',' ',$ref);
	    	$newref=$ref;
	    	$lienfin='</a>';
	    	print $lien.$newref.$lienfin;
			if (! in_array($val['id'],$expandedsectionarray)) print img_picto($ecmdirstatic->ref,DOL_URL_ROOT.'/theme/common/treemenu/folder.gif','',1);
			else print img_picto($ecmdirstatic->ref,DOL_URL_ROOT.'/theme/common/treemenu/folder-expanded.gif','',1);
			print '</td>';
			// Show link
			print '<td valign="middle">';
			if ($section == $val['id']) print ' <u>';
			print $ecmdirstatic->getNomUrl(0,'index',32);
			if ($section == $val['id']) print '</u>';
			print '</td>';
			print '<td>&nbsp;</td>';
			print '</tr></table>';
			print "</td>\n";

			// Nb of docs
			print '<td align="right">';
			print $val['cachenbofdoc'];
			print '</td>';
			print '<td align="left">';
			if ($nbofsubdir && $nboffilesinsubdir) print '<font color="#AAAAAA">+'.$nboffilesinsubdir.'</font> ';
			print '</td>';

			// Edit link
			print '<td align="right"><a href="'.DOL_URL_ROOT.'/ecm/docmine.php?section='.$val['id'].'">'.img_view().'</a></td>';

			// Add link
			//print '<td align="right"><a href="'.DOL_URL_ROOT.'/ecm/docdir.php?action=create&amp;catParent='.$val['id'].'">'.img_edit_add().'</a></td>';
			print '<td align="right">&nbsp;</td>';

			// Info
			print '<td align="center">';
			$userstatic->id=$val['fk_user_c'];
			$userstatic->nom=$val['login_c'];
			$htmltooltip='<b>'.$langs->trans("ECMSection").'</b>: '.$val['label'].'<br>';
			$htmltooltip='<b>'.$langs->trans("Type").'</b>: '.$langs->trans("ECMSectionManual").'<br>';
			$htmltooltip.='<b>'.$langs->trans("ECMCreationUser").'</b>: '.$userstatic->getNomUrl(1).'<br>';
			$htmltooltip.='<b>'.$langs->trans("ECMCreationDate").'</b>: '.dol_print_date($val['date_c'],"dayhour").'<br>';
			$htmltooltip.='<b>'.$langs->trans("Description").'</b>: '.$val['description'].'<br>';
			$htmltooltip.='<b>'.$langs->trans("ECMNbOfFilesInDir").'</b>: '.$val['cachenbofdoc'].'<br>';
			if ($nbofsubdir) $htmltooltip.='<b>'.$langs->trans("ECMNbOfFilesInSubDir").'</b>: '.$nboffilesinsubdir;
			else $htmltooltip.='<b>'.$langs->trans("ECMNbOfSubDir").'</b>: '.$nbofsubdir.'<br>';
			print $form->textwithpicto('',$htmltooltip,1,0);
			print "</td>";

			print "</tr>\n";
		}

		$oldvallevel=$val['level'];
		$nbofentries++;
	}

	// If nothing to show
	if ($nbofentries == 0)
	{
		print '<tr>';
		print '<td class="left"><table class="nobordernopadding"><tr class="nobordernopadding"><td>'.img_picto_common('','treemenu/branchbottom.gif').'</td>';
		print '<td>'.img_picto('',DOL_URL_ROOT.'/theme/common/treemenu/minustop3.gif','',1).'</td>';
		print '<td valign="middle">';
		print $langs->trans("ECMNoDirecotyYet");
		print '</td>';
		print '<td>&nbsp;</td>';
		print '</table></td>';
		print '<td colspan="5">&nbsp;</td>';
		print '</tr>';
	}


	// ----- End of section -----
	// --------------------------

	print "</table>";
	// Fin de zone Ajax



}

if ($conf->use_javascript_ajax)
{
?>
    </div>

    <div id="ecm-layout-center" class="pane layout-with-no-border">

        <div class="pane-in ecm-in-layout-center">
<?php
}
else
{
    print '</td><td valign="top" style="background: #FFFFFF">';
}

// Right area
$relativepath=$ecmdir->getRelativePath();
$upload_dir = $conf->ecm->dir_output.'/'.$relativepath;
$filearray=dol_dir_list($upload_dir,"files",0,'','\.meta$',$sortfield,(strtolower($sortorder)=='desc'?SORT_ASC:SORT_DESC),1);

$formfile=new FormFile($db);
$param='&amp;section='.$section;
$textifempty=($section?$langs->trans("NoFileFound"):($showonrightsize=='featurenotyetavailable'?$langs->trans("FeatureNotYetAvailable"):$langs->trans("ECMSelectASection")));
$formfile->list_of_documents($filearray,'','ecm',$param,1,$relativepath,$user->rights->ecm->upload,1,$textifempty,40);

//	print '<table width="100%" class="border">';

//	print '<tr><td> </td></tr></table>';



if ($conf->use_javascript_ajax)
{
?>
        </div>
        <div class="pane-in ecm-in-layout-south layout-padding">
<?php
}
else
{
    print '</td></tr>';

    // Actions attach new file
    print '<tr height="22">';
    //print '<td align="center">';
    //print '</td>';
    print '<td>';
}

if (! empty($section))
{
	$formfile->form_attach_new_file(DOL_URL_ROOT.'/ecm/index.php', 'none', 0, $section,$user->rights->ecm->upload, 48);
}
else print '&nbsp;';


if ($conf->use_javascript_ajax)
{
?>
        </div>
    </div>

<!--    <div id="ecm-layout-east" class="pane"></div> -->

<!--    <div id="ecm-layout-south" class="pane"></div> -->

</div> <!-- end div id="containerlayout" -->


<?php
}
else
{
    print '</td></tr>';

    print '</table>';
}


// End of page
$db->close();

llxFooter('$Date: 2011/01/10 00:09:31 $ - $Revision: 1.92 $');
?>
