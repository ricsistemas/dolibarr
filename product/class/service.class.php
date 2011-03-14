<?php
/* Copyright (C) 2002      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *       \file       htdocs/product/class/service.class.php
 *       \ingroup    service
 *       \brief      Fichier de la classe des services predefinis
 *       \version    $Id: service.class.php,v 1.2 2010/07/21 12:35:57 eldy Exp $
 */
require_once(DOL_DOCUMENT_ROOT ."/core/class/commonobject.class.php");


/**
 *       \class      Service
 *       \brief      Classe permettant la gestion des services predefinis
 */
class Service extends CommonObject
{
	var $db;

	var $id;
	var $libelle;
	var $price;
	var $tms;
	var $debut;
	var $fin;

	var $debut_epoch;
	var $fin_epoch;


	function Service($DB, $id=0) {
		$this->db = $DB;
		$this->id = $id;

		return 1;
	}


	/**
	 *      \brief      Charge indicateurs this->nb de tableau de bord
	 *      \return     int         <0 si ko, >0 si ok
	 */
	function load_state_board()
	{
		global $conf, $user;

		$this->nb=array();

		$sql = "SELECT count(p.rowid) as nb";
		$sql.= " FROM ".MAIN_DB_PREFIX."product as p";
		$sql.= " WHERE p.fk_product_type = 1";
		$sql.= " AND p.entity = ".$conf->entity;
		if (!$user->rights->produit->hidden) $sql.=' AND (p.hidden=0 OR p.fk_product_type != 0)';
		if (!$user->rights->service->hidden) $sql.=' AND (p.hidden=0 OR p.fk_product_type != 1)';

		$resql=$this->db->query($sql);
		if ($resql)
		{
			while ($obj=$this->db->fetch_object($resql))
			{
				$this->nb["services"]=$obj->nb;
			}
			return 1;
		}
		else
		{
			dol_print_error($this->db);
			$this->error=$this->db->error();
			return -1;
		}
	}

}
?>
