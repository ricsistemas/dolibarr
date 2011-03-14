<?php
/* Copyright (C) 2010 Regis Houssin  <regis@dolibarr.fr>
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
 *	\file       htdocs/societe/canvas/individual/dao_thirdparty_individual.class.php
 *	\ingroup    thirdparty
 *	\brief      Fichier de la classe des particuliers
 *	\version    $Id: dao_thirdparty_individual.class.php,v 1.4 2010/10/27 21:51:42 eldy Exp $
 */

/**
 *	\class      DaoThirdPartyIndividual
 *	\brief      Classe permettant la gestion des particuliers, cette classe surcharge la classe societe
 */
class DaoThirdPartyIndividual extends Societe
{
	//! Numero d'erreur Plage 1280-1535
	var $errno = 0;

	/**
	 *    Constructeur de la classe
	 *    @param      DB          Handler acces base de donnees
	 */
	function DaoThirdPartyIndividual($DB)
	{
		$this->db = $DB;
	}

	/**
	 *    Lecture des donnees dans la base
	 *    @param	id          Element id
	 *    @param	action		Type of action
	 */
	function fetch($id='', $action='')
	{
		$result = parent::fetch($id);

		return $result;
	}

	/**
     *    Create third party in database
     *    @param      user        Object of user that ask creation
     *    @return     int         >= 0 if OK, < 0 if KO
     */
    function create($user='')
    {
    	$result = parent::create($user);

		return $result;
    }

	/**
     *      Update parameters of third party
     *      @param      id              			id societe
     *      @param      user            			Utilisateur qui demande la mise a jour
     *      @param      call_trigger    			0=non, 1=oui
     *		@param		allowmodcodeclient			Inclut modif code client et code compta
     *		@param		allowmodcodefournisseur		Inclut modif code fournisseur et code compta fournisseur
     *      @return     int             			<0 si ko, >=0 si ok
     */
    function update($id, $user='', $call_trigger=1, $allowmodcodeclient=0, $allowmodcodefournisseur=0)
    {
    	$result = parent::update($id, $user, $call_trigger, $allowmodcodeclient, $allowmodcodefournisseur);

    	return $result;
    }

	/**
     *    Delete third party in database
     *    @param      id      id de la societe a supprimer
     */
    function delete($id)
    {
    	$result = parent::delete($id);

    	return $result;
    }

	/**
	 * 	Fetch datas list
	 */
	function LoadListDatas($limit, $offset, $sortfield, $sortorder)
	{
		global $conf, $langs;

		$this->list_datas = array();
	}

}

?>