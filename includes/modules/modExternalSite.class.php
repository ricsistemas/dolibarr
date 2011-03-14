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

/**     \defgroup   externalsite     Module externalsite
        \brief      Module to include an external web site/tools into Dolibarr menu and into a frame page.
		\version	$Id: modExternalSite.class.php,v 1.2 2010/04/19 16:55:32 grandoc Exp $
*/

/**
        \file       htdocs/includes/modules/modExternalSite.class.php
        \ingroup    externalsite
        \brief      Description and activation file for module ExternalSite
*/

include_once(DOL_DOCUMENT_ROOT ."/includes/modules/DolibarrModules.class.php");


/**     \class      modExternalSite
        \brief      Description and activation class for module ExternalSite
*/

class modExternalSite extends DolibarrModules
{

   /**
    *   \brief      Constructor. Define names, constants, directories, boxes, permissions
    *   \param      DB      Database handler
    */
	function modExternalSite($DB)
	{
		$this->db = $DB;

		// Id for module (must be unique).
		// Use here a free id.
		$this->numero = 100;

		// Family can be 'crm','financial','hr','projects','product','technic','other'
		// It is used to sort modules in module setup page
		$this->family = "other";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description used if translation string 'ModuleXXXDesc' not found (XXX is id value)
		$this->description = "Include any external web site into Dolibarr menus and view it into a Dolibarr frame.";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = 'dolibarr';
		// Key used in llx_const table to save module status enabled/disabled (XXX is id value)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=other)
		$this->special = 1;
		// Name of png file (without png) used for this module
		$this->picto='bookmark';
		// Call to inside lang's file
		$this->langfiles = array("@externalsite");

		// Data directories to create when module is enabled
		$this->dirs = array();

		// Config pages. Put here list of php page names stored in admmin directory used to setup module
		$this->config_page_url = array("externalsite.php@externalsite");

		// Dependencies
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled

		// Constants
		$this->const = array();			// List of parameters

		// Boxes
		$this->boxes = array();			// List of boxes
		$r=0;

		// Add here list of php file(s) stored in includes/boxes that contains class to show a box.
		// Example:
        //$this->boxes[$r][1] = "myboxa.php";
    	//$r++;
        //$this->boxes[$r][1] = "myboxb.php";
    	//$r++;

		// Permissions
		$this->rights_class = 'externalsite';	// Permission key
		$this->rights = array();		// Permission array used by this module

        // Menus
		//------
		$r=0;

		$this->menu[$r]=array('fk_menu'=>0,
													'type'=>'top',
													'titre'=>'ExternalSites',
													'mainmenu'=>'externalsite',
													'leftmenu'=>'1',
													'url'=>'/externalsite/frames.php',
													'langs'=>'other',
													'position'=>100,
													'perms'=>'',
													'enabled'=>'$conf->externalsite->enabled',
													'target'=>'',
													'user'=>0
													);
		$r++;

	}

	/**
     *		\brief      Function called when module is enabled.
     *					The init function add previous constants, boxes and permissions into Dolibarr database.
     *					It also creates data directories.
     */
	function init()
  	{
    	$sql = array();

    	return $this->_init($sql);
  	}

	/**
	 *		\brief		Function called when module is disabled.
 	 *              	Remove from database constants, boxes and permissions from Dolibarr database.
 	 *					Data directories are not deleted.
 	 */
	function remove()
	{
    	$sql = array();

    	return $this->_remove($sql);
  	}

}

?>
