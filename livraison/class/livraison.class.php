<?php
/* Copyright (C) 2003      Rodolphe Quiedeville  <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2010 Regis Houssin         <regis@dolibarr.fr>
 * Copyright (C) 2006-2007 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2007      Franky Van Liedekerke <franky.van.liedekerke@telenet.be>
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
 *  \file       htdocs/livraison/class/livraison.class.php
 *  \ingroup    delivery
 *  \brief      Fichier de la classe de gestion des bons de livraison
 *  \version    $Id: livraison.class.php,v 1.22 2010/12/15 07:49:05 hregis Exp $
 */

require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
require_once(DOL_DOCUMENT_ROOT."/expedition/class/expedition.class.php");
require_once(DOL_DOCUMENT_ROOT."/product/stock/class/mouvementstock.class.php");
if ($conf->propal->enabled)   require_once(DOL_DOCUMENT_ROOT."/comm/propal/class/propal.class.php");
if ($conf->commande->enabled) require_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");


/**
 *  \class      Livraison
 *  \brief      Classe de gestion des bons de livraison
 */
class Livraison extends CommonObject
{
	var $db;
	var $error;
	var $element="delivery";
	var $fk_element="fk_livraison";
	var $table_element="livraison";

	var $id;
	var $brouillon;
	var $origin;
	var $origin_id;
	var $socid;
	var $ref_customer;

	var $expedition_id;

	var $date_delivery;    // Date really received
	var $date_creation;
	var $date_valid;


	/**
	 * Initialisation
	 */
	function Livraison($DB)
	{
		$this->db = $DB;
		$this->lines = array();
		$this->products = array();

		// List of short language codes for status
		$this->statuts[-1] = 'StatusSendingCanceled';
		$this->statuts[0]  = 'StatusSendingDraft';
		$this->statuts[1]  = 'StatusSendingValidated';
	}

	/**
	 *    \brief      Create delivery receipt in database
	 *    \param      user        Objet du user qui cree
	 *    \return     int         <0 si erreur, id livraison cree si ok
	 */
	function create($user)
	{
		global $conf;

		dol_syslog("Livraison::create");

		$error = 0;

        $now=dol_now();

		/* On positionne en mode brouillon le bon de livraison */
		$this->brouillon = 1;

		$this->user = $user;

		$this->db->begin();

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."livraison (";
		$sql.= "ref";
		$sql.= ", entity";
		$sql.= ", fk_soc";
		$sql.= ", ref_customer";
		$sql.= ", date_creation";
		$sql.= ", fk_user_author";
		$sql.= ", date_delivery";
		$sql.= ", fk_address";
		$sql.= ") VALUES (";
		$sql.= "'(PROV)'";
		$sql.= ", ".$conf->entity;
		$sql.= ", ".$this->socid;
		$sql.= ", '".$this->ref_customer."'";
		$sql.= ", ".$this->db->idate($now);
		$sql.= ", ".$user->id;
		$sql.= ", ".($this->date_delivery?"'".$this->db->idate($this->date_delivery)."'":"null");
		$sql.= ", ".($this->fk_delivery_address > 0 ? $this->fk_delivery_address : "null");
		$sql.= ")";

		dol_syslog("Livraison::create sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."livraison");

			$numref = "(PROV".$this->id.")";

			$sql = "UPDATE ".MAIN_DB_PREFIX."livraison ";
			$sql.= "SET ref = '".addslashes($numref)."'";
			$sql.= " WHERE rowid = ".$this->id;

			dol_syslog("Livraison::create sql=".$sql, LOG_DEBUG);
			$resql=$this->db->query($sql);
			if ($resql)
			{
				if (! $conf->expedition_bon->enabled)
				{
					$commande = new Commande($this->db);
					$commande->id = $this->commande_id;
					$this->lines = $commande->fetch_lines();
				}


				/*
				 *  Insertion des produits dans la base
				 */
				for ($i = 0 ; $i < sizeof($this->lines) ; $i++)
				{
					$origin_id=$this->lines[$i]->origin_line_id;
					if (! $origin_id) $origin_id=$this->lines[$i]->commande_ligne_id;	// For backward compatibility

					if (! $this->create_line(0, $origin_id, $this->lines[$i]->qty, $this->lines[$i]->fk_product, $this->lines[$i]->description))
					{
						$error++;
					}
				}

				if (! $error && $this->id && $this->origin_id)
				{
					$ret = $this->add_object_linked();
					if (!$ret)
					{
						$error++;
					}

					if (! $conf->expedition_bon->enabled)
					{
						// TODO uniformiser les statuts
						$ret = $this->setStatut(2,$this->origin_id,$this->origin);
						if (! $ret)
						{
							$error++;
						}
					}
				}

				if (! $error)
				{
					$this->db->commit();
					return $this->id;
				}
				else
				{
					$error++;
					$this->error=$this->db->lasterror()." - sql=".$this->db->lastqueryerror;
					dol_syslog("Livraison::create Error -3 ".$this->error, LOG_ERR);
					$this->db->rollback();
					return -3;
				}
			}
			else
			{
				$error++;
				$this->error=$this->db->lasterror()." - sql=".$this->db->lastqueryerror;
				dol_syslog("Livraison::create Error -2 ".$this->error, LOG_ERR);
				$this->db->rollback();
				return -2;
			}
		}
		else
		{
			$error++;
			$this->error=$this->db->lasterror()." - sql=".$this->db->lastqueryerror;
			dol_syslog("Livraison::create Error -1 ".$this->error, LOG_ERR);
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *
	 *
	 */
	function create_line($transaction, $commande_ligne_id, $qty, $fk_product=0, $description)
	{
		$error = 0;
		$idprod = $fk_product;
		$j = 0;

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."livraisondet (fk_livraison, fk_origin_line,";
		$sql.= " fk_product, description, qty)";
		$sql.= " VALUES (".$this->id.",".$commande_ligne_id.",";
		$sql.= " ".($idprod>0?$idprod:"null").",";
		$sql.= " ".($description?"'".addslashes($description)."'":"null").",";
		$sql.= $qty.")";

		dol_syslog("Livraison::create_line sql=".$sql, LOG_DEBUG);
		if (! $this->db->query($sql) )
		{
			$error++;
		}

		if ($error == 0 )
		{
			return 1;
		}
	}

	/**
	 * 	\brief			Read a delivery receipt
	 */
	function fetch($id)
	{
		global $conf;

		$sql = "SELECT l.rowid, l.fk_soc, l.date_creation, l.date_valid, l.ref, l.ref_customer, l.fk_user_author,";
		$sql.=" l.total_ht, l.fk_statut, l.fk_user_valid, l.note, l.note_public";
		$sql.= ", l.date_delivery, l.fk_address, l.model_pdf";
		$sql.= ", el.fk_source as origin_id, el.sourcetype as origin";
		$sql.= " FROM ".MAIN_DB_PREFIX."livraison as l";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."element_element as el ON el.fk_target = l.rowid AND el.targettype = '".$this->element."'";
		$sql.= " WHERE l.rowid = ".$id;

		dol_syslog("Livraison::fetch sql=".$sql, LOG_DEBUG);
		$result = $this->db->query($sql) ;
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);

				$this->id                   = $obj->rowid;
				$this->date_delivery        = $this->db->jdate($obj->date_delivery);
				$this->date_creation        = $this->db->jdate($obj->date_creation);
				$this->date_valid           = $this->db->jdate($obj->date_valid);
				$this->ref                  = $obj->ref;
				$this->ref_customer         = $obj->ref_customer;
				$this->socid                = $obj->fk_soc;
				$this->statut               = $obj->fk_statut;
				$this->user_author_id       = $obj->fk_user_author;
				$this->user_valid_id        = $obj->fk_user_valid;
				$this->fk_delivery_address  = $obj->fk_address;
				$this->note                 = $obj->note;
				$this->note_public          = $obj->note_public;
				$this->modelpdf             = $obj->model_pdf;
				$this->origin               = $obj->origin;		// May be 'shipping'
				$this->origin_id            = $obj->origin_id;	// May be id of shipping

				$this->db->free($result);

				if ($this->statut == 0) $this->brouillon = 1;

				$file = $conf->livraison->dir_output . "/" .get_exdir($livraison->id,2) . "/" . $this->id.".pdf";
				$this->pdf_filename = $file;

				/*
				 * Lignes
				 */
				$result=$this->fetch_lines();
				if ($result < 0)
				{
					return -3;
				}

				return 1;
			}
			else
			{
				$this->error='Delivery with id '.$rowid.' not found sql='.$sql;
				dol_syslog('Livraison::Fetch Error '.$this->error, LOG_ERR);
				return -2;
			}
		}
		else
		{
			dol_syslog('Livraison::Fetch Error '.$this->error, LOG_ERR);
			$this->error=$this->db->error();
			return -1;
		}
	}

	/**
	 *        \brief      Valide l'expedition, et met a jour le stock si stock gere
	 *        \param      user        Objet de l'utilisateur qui valide
	 *        \return     int
	 */
	function valid($user)
	{
		global $conf;

		dol_syslog("livraison.class.php::valid begin");

		$this->db->begin();

		$error = 0;

		if ($user->rights->expedition->livraison->valider)
		{
			if ($conf->global->LIVRAISON_ADDON)
			{
				// Definition du nom de module de numerotation de commande
				$modName = $conf->global->LIVRAISON_ADDON;

				if (is_readable(DOL_DOCUMENT_ROOT .'/includes/modules/livraison/'.$modName.'.php'))
				{
					require_once DOL_DOCUMENT_ROOT .'/includes/modules/livraison/'.$modName.'.php';

					// Recuperation de la nouvelle reference
					$objMod = new $modName($this->db);
					$soc = new Societe($this->db);
					$soc->fetch($this->socid);

					// on verifie si le bon de livraison est en numerotation provisoire
					$livref = substr($this->ref, 1, 4);
					if ($livref == 'PROV')
					{
						$this->ref = $objMod->livraison_get_num($soc,$this);
					}

					// Tester si non deja au statut valide. Si oui, on arrete afin d'eviter
					// de decrementer 2 fois le stock.
					$sql = "SELECT ref";
					$sql.= " FROM ".MAIN_DB_PREFIX."livraison";
					$sql.= " WHERE ref = '".$this->ref."'";
					$sql.= " AND fk_statut <> 0";
					$sql.= " AND entity = ".$conf->entity;

					$resql=$this->db->query($sql);
					if ($resql)
					{
						$num = $this->db->num_rows($resql);
						if ($num > 0)
						{
							return 0;
						}
					}

					$sql = "UPDATE ".MAIN_DB_PREFIX."livraison SET";
					$sql.= " ref='".addslashes($this->ref)."'";
					$sql.= ", fk_statut = 1";
					$sql.= ", date_valid = ".$this->db->idate(mktime());
					$sql.= ", fk_user_valid = ".$user->id;
					$sql.= " WHERE rowid = ".$this->id;
					$sql.= " AND fk_statut = 0";

					$resql=$this->db->query($sql);
					if ($resql)
					{
						// Si module stock gere et que expedition faite depuis un entrepot
						if ($conf->stock->enabled && $this->entrepot_id && $conf->global->STOCK_CALCULATE_ON_RECEIVING == 1)
						{

							//Enregistrement d'un mouvement de stock pour chaque produit de l'expedition

							dol_syslog("livraison.class.php::valid enregistrement des mouvements");

							$sql = "SELECT cd.fk_product, cd.subprice, ld.qty ";
							$sql.= " FROM ".MAIN_DB_PREFIX."commandedet as cd";
							$sql.= ", ".MAIN_DB_PREFIX."livraisondet as ld";
							$sql.= " WHERE ld.fk_livraison = ".$this->id;
							$sql.= " AND cd.rowid = ld.fk_commande_ligne";

							$resql=$this->db->query($sql);
							if ($resql)
							{
								$num = $this->db->num_rows($resql);
								$i=0;
								while($i < $num)
								{
									dol_syslog("livraison.class.php::valid movement $i");

									$obj = $this->db->fetch_object($resql);

									$mouvS = new MouvementStock($this->db);
									$entrepot_id = "1"; // TODO ajouter possibilite de choisir l'entrepot
									$result=$mouvS->livraison($user, $obj->fk_product, $entrepot_id, $obj->qty, $obj->subprice);
									if ($result < 0) { $error++; break; }
									$i++;
								}

							}
							else
							{
								$this->db->rollback();
								$this->error=$this->db->error()." - sql=$sql";
								dol_syslog("livraison.class.php::valid ".$this->error, LOG_ERR);
								return -2;

							}
						}

						// On efface le repertoire de pdf provisoire
						$livraisonref = dol_sanitizeFileName($this->ref);
						if ($conf->expedition->dir_output)
						{
							$dir = $conf->livraison->dir_output . "/" . $livraisonref ;
							$file = $dir . "/" . $livraisonref . ".pdf";
							if (file_exists($file))
							{
								if (!dol_delete_file($file))
								{
									$this->error=$langs->trans("ErrorCanNotDeleteFile",$file);
									return 0;
								}
							}
							if (file_exists($dir))
							{
								if (!dol_delete_dir($dir))
								{
									$this->error=$langs->trans("ErrorCanNotDeleteDir",$dir);
									return 0;
								}
							}
						}

						dol_syslog("livraison.class.php::valid ok");
					}
					else
					{
						$this->db->rollback();
						$this->error=$this->db->error()." - sql=$sql";
						dol_syslog("livraison.class.php::valid ".$this->error, LOG_ERR);
						return -1;
					}
				}
			}
		}
		else
		{
			$this->error="Non autorise";
			dol_syslog("livraison.class.php::valid ".$this->error, LOG_ERR);
			return -1;
		}

		// Appel des triggers
		include_once(DOL_DOCUMENT_ROOT.'/core/class/interfaces.class.php');
		$interface = new Interfaces($this->db);
		$result = $interface->run_triggers('DELIVERY_VALIDATE', $this, $user, $langs, $conf);
		// Fin appel triggers
		if ($result < 0)
		{
			$this->db->rollback();
			$this->error = $interface->errors;
			dol_syslog("livraison.class.php::valid ".$this->error, LOG_ERR);
			return -1;
		}
		else
		{
			$this->db->commit();
			dol_syslog("livraison.class.php::valid commit");
			return 1;
		}
	}

	/**     \brief      Cree le bon de livraison depuis une expedition existante
	 *		\param      user            Utilisateur qui cree
	 *		\param      sending_id      Id de l'expedition qui sert de modele
	 */
	function create_from_sending($user, $sending_id)
	{
		$expedition = new Expedition($this->db);
		$result=$expedition->fetch($sending_id);

		$this->lines = array();

		for ($i = 0 ; $i < sizeof($expedition->lines) ; $i++)
		{
			$line = new LivraisonLigne($this->db);
			$line->origin_line_id    = $expedition->lines[$i]->origin_line_id;
			$line->libelle           = $expedition->lines[$i]->libelle;
			$line->description       = $expedition->lines[$i]->description;
			$line->qty               = $expedition->lines[$i]->qty_shipped;
			$line->fk_product        = $expedition->lines[$i]->fk_product;
			$line->ref               = $expedition->lines[$i]->ref;
			
			$this->lines[$i] = $line;
		}

		$this->origin               = $expedition->element;
		$this->origin_id            = $expedition->id;
		$this->note                 = $expedition->note;
		$this->fk_project           = $expedition->fk_project;
		$this->date_delivery        = $expedition->date_delivery;
		$this->fk_delivery_address  = $expedition->fk_delivery_address;
		$this->socid                = $expedition->socid;
		$this->ref_customer			= $expedition->ref_customer;

		return $this->create($user);
	}


	/**
	 * Ajoute une ligne
	 *
	 */
	function addline( $id, $qty )
	{
		$num = sizeof($this->lines);
		$line = new LivraisonLigne($this->db);

		$line->commande_ligne_id = $id;
		$line->qty = $qty;

		$this->lines[$num] = $line;
	}

	/**
	 *
	 *
	 */
	function deleteline($lineid)
	{
		if ($this->statut == 0)
		{
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."commandedet";
			$sql.= " WHERE rowid = ".$lineid;

			if ($this->db->query($sql) )
			{
				$this->update_price();

				return 1;
			}
			else
			{
				return 0;
			}
		}
	}

	/**
	 * Supprime la fiche
	 *
	 */
	function delete()
	{
		$this->db->begin();

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."livraisondet";
		$sql.= " WHERE fk_livraison = ".$this->id;
		if ( $this->db->query($sql) )
		{
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."element_element";
			$sql.= " WHERE fk_target = ".$this->id;
			$sql.= " AND targettype = '".$this->element."'";
			if ( $this->db->query($sql) )
			{
				$sql = "DELETE FROM ".MAIN_DB_PREFIX."livraison";
				$sql.= " WHERE rowid = ".$this->id;
				if ( $this->db->query($sql) )
				{
					$this->db->commit();

					// On efface le repertoire de pdf provisoire
					$livref = dol_sanitizeFileName($this->ref);
					if ($conf->livraison->dir_output)
					{
						$dir = $conf->livraison->dir_output . "/" . $livref ;
						$file = $conf->livraison->dir_output . "/" . $livref . "/" . $livref . ".pdf";
						if (file_exists($file))
						{
							if (!dol_delete_file($file))
							{
								$this->error=$langs->trans("ErrorCanNotDeleteFile",$file);
								return 0;
							}
						}
						if (file_exists($dir))
						{
							if (!dol_delete_dir($dir))
							{
								$this->error=$langs->trans("ErrorCanNotDeleteDir",$dir);
								return 0;
							}
						}
					}

					return 1;
				}
				else
				{
					$this->error=$this->db->lasterror()." - sql=$sql";
					$this->db->rollback();
					return -3;
				}
			}
			else
			{
				$this->error=$this->db->lasterror()." - sql=$sql";
				$this->db->rollback();
				return -2;
			}
		}
		else
		{
			$this->error=$this->db->lasterror()." - sql=$sql";
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *	\brief      Renvoie nom clicable (avec eventuellement le picto)
	 *	\param		withpicto		0=Pas de picto, 1=Inclut le picto dans le lien, 2=Picto seul
	 *	\return		string			Chaine avec URL
	 */
	function getNomUrl($withpicto=0)
	{
		global $langs;

		$result='';
		$urlOption='';


		$lien = '<a href="'.DOL_URL_ROOT.'/livraison/fiche.php?id='.$this->id.'">';
		$lienfin='</a>';

		$picto='sending';
		$label=$langs->trans("ShowReceiving").': '.$this->ref;

		if ($withpicto) $result.=($lien.img_object($label,$picto).$lienfin);
		if ($withpicto && $withpicto != 2) $result.=' ';
		$result.=$lien.$this->ref.$lienfin;
		return $result;
	}

	/**
	 *
	 *
	 */
	function fetch_lines()
	{
		$this->lines = array();

		$sql = "SELECT ld.rowid, ld.fk_product, ld.description, ld.subprice, ld.total_ht, ld.qty as qty_shipped,";
		$sql.= " cd.qty as qty_asked,";
		$sql.= " p.ref, p.fk_product_type as fk_product_type, p.label as label, p.description as product_desc";
		$sql.= " FROM ".MAIN_DB_PREFIX."commandedet as cd, ".MAIN_DB_PREFIX."livraisondet as ld";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product as p on p.rowid = ld.fk_product";
		$sql.= " WHERE ld.fk_origin_line = cd.rowid";
		$sql.= " AND ld.fk_livraison = ".$this->id;

		dol_syslog("Livraison::fetch_lines sql=".$sql);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$line = new LivraisonLigne($this->db);

				$obj = $this->db->fetch_object($resql);

				$line->description    = $obj->description;
				$line->fk_product     = $obj->fk_product;
				$line->qty_asked      = $obj->qty_asked;
				$line->qty_shipped    = $obj->qty_shipped;

				$line->ref            = $obj->ref;
				$line->libelle        = $obj->label;           // Label produit
				$line->label          = $obj->label;
				$line->product_desc   = $obj->product_desc;    // Description produit
				$line->product_type   = $obj->fk_product_type;

				$line->price          = $obj->price;
				$line->total_ht       = $obj->total_ht;

				$this->lines[$i] = $line;

				$i++;
			}
			$this->db->free($resql);
		}

		return $this->lines;
	}


	/**
	 *    \brief      Retourne le libelle du statut d'une expedition
	 *    \return     string      Libelle
	 */
	function getLibStatut($mode=0)
	{
		return $this->LibStatut($this->statut,$mode);
	}

	/**
	 *		\brief      Renvoi le libelle d'un statut donne
	 *    	\param      statut      Id statut
	 *    	\param      mode        0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 *    	\return     string		Libelle
	 */
	function LibStatut($statut,$mode)
	{
		global $langs;

		if ($mode==0)
		{
			if ($statut==-1) return $langs->trans('StatusSendingCanceled');
			if ($statut==0)  return $langs->trans('StatusSendingDraft');
			if ($statut==1)  return $langs->trans('StatusSendingValidated');
		}
		if ($mode==1)
		{
			if ($statut==-1) return $langs->trans($this->statuts[$statut]);
			if ($statut==0)  return $langs->trans($this->statuts[$statut]);
			if ($statut==1)  return $langs->trans($this->statuts[$statut]);
		}
		if ($mode == 4)
		{
			if ($statut==-1) return img_picto($langs->trans('StatusSendingCanceled'),'statut5').' '.$langs->trans('StatusSendingCanceled');
			if ($statut==0)  return img_picto($langs->trans('StatusSendingDraft'),'statut0').' '.$langs->trans('StatusSendingDraft');
			if ($statut==1)  return img_picto($langs->trans('StatusSendingValidated'),'statut4').' '.$langs->trans('StatusSendingValidated');
		}
	}


	/**
	 *		\brief		Initialise object with default value to be used as example
	 */
	function initAsSpecimen()
	{
		global $user,$langs,$conf;

		// Charge tableau des produits prodids
		$prodids = array();
		$sql = "SELECT rowid";
		$sql.= " FROM ".MAIN_DB_PREFIX."product";
		$sql.= " WHERE tosell = 1";
		$sql.= " AND entity = ".$conf->entity;
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num_prods = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num_prods)
			{
				$i++;
				$row = $this->db->fetch_row($resql);
				$prodids[$i] = $row[0];
			}
		}

		// Initialise parametres
		$this->id=0;
		$this->ref = 'SPECIMEN';
		$this->specimen=1;
		$this->socid = 1;
		$this->date_delivery = time();
		$this->note_public='SPECIMEN';

		$i=0;
		$line=new LivraisonLigne($this->db);
		$line->fk_product     = $prodids[0];
		$line->qty_asked      = 10;
		$line->qty_shipped    = 9;
		$line->ref            = 'REFPROD';
		$line->label          = 'Specimen';
		$line->description    = 'Description';
		$line->price          = 100;
		$line->total_ht       = 100;
		
		$this->lines[$i] = $line;
	}

	/**
	 *   \brief      Renvoie la quantite de produit restante a livrer pour une commande
	 *   \return     array		Product remaining to be delivered
	 */
	function getRemainingDelivered()
	{
		global $langs;

		// Get the linked object
		$this->load_object_linked(-1,-1,$this->id,$this->element);

		// Get the product ref and qty in source
		$sqlSourceLine = "SELECT st.rowid, st.description, st.qty";
		$sqlSourceLine.= ", p.ref, p.label";
		$sqlSourceLine.= " FROM ".MAIN_DB_PREFIX.$this->linked_object[0]['type']."det as st";
		$sqlSourceLine.= " LEFT JOIN ".MAIN_DB_PREFIX."product as p ON st.fk_product = p.rowid";
		$sqlSourceLine.= " WHERE fk_".$this->linked_object[0]['type']." = ".$this->linked_object[0]['linkid'];

		$resultSourceLine = $this->db->query($sqlSourceLine);
		if ($resultSourceLine)
		{
			$num_lines = $this->db->num_rows($resultSourceLine);
			$i = 0;
			$resultArray = array();
			while ($i < $num_lines)
			{
				$objSourceLine = $this->db->fetch_object($resultSourceLine);

				// Recupere les lignes de la source deja livrees
				$sql = "SELECT ld.fk_origin_line, sum(ld.qty) as qty";
				$sql.= " FROM ".MAIN_DB_PREFIX."livraisondet as ld, ".MAIN_DB_PREFIX."livraison as l,";
				$sql.= " ".MAIN_DB_PREFIX.$this->linked_object[0]['type']." as c";
				$sql.= ", ".MAIN_DB_PREFIX.$this->linked_object[0]['type']."det as cd";
				$sql.= " WHERE ld.fk_livraison = l.rowid";
				$sql.= " AND ld.fk_origin_line = cd.rowid";
				$sql.= " AND cd.fk_".$this->linked_object[0]['type']." = c.rowid";
				$sql.= " AND cd.fk_".$this->linked_object[0]['type']." = ".$this->linked_object[0]['linkid'];
				$sql.= " AND ld.fk_origin_line = ".$objSourceLine->rowid;
				$sql.= " GROUP BY ld.fk_origin_line";

				$result = $this->db->query($sql);
				$row = $this->db->fetch_row($result);

				if ($objSourceLine->qty - $row[1] > 0)
				{
					if ($row[0] == $objSourceLine->rowid)
					{
						$array[$i]['qty'] = $objSourceLine->qty - $row[1];
					}
					else
					{
						$array[$i]['qty'] = $objSourceLine->qty;
					}

					$array[$i]['ref'] = $objSourceLine->ref;
					$array[$i]['label'] = $objSourceLine->label?$objSourceLine->label:$objSourceLine->description;
				}
				elseif($objSourceLine->qty - $row[1] < 0)
				{
					$array[$i]['qty'] = $objSourceLine->qty - $row[1]. " Erreur livraison !";
					$array[$i]['ref'] = $objSourceLine->ref;
					$array[$i]['label'] = $objSourceLine->label?$objSourceLine->label:$objSourceLine->description;
				}

					$i++;
			}
			return $array;
		}
		else
		{
			$this->error=$this->db->error()." - sql=$sqlSourceLine";
			dol_syslog("livraison.class.php::getRemainingDelivered ".$this->error, LOG_ERR);
			return -1;
		}
	}

}



/**
 *  \class      LivraisonLigne
 *  \brief      Classe de gestion des lignes de bons de livraison
 */
class LivraisonLigne
{
	var $db;

	// From llx_expeditiondet
	var $qty;
	var $qty_asked;
	var $qty_shipped;
	var $price;
	var $fk_product;
	var $commande_ligne_id;
	var $label;       // Label produit
	var $description;  // Description produit
	var $ref;

	function LivraisonLigne($DB)
	{
		$this->db=$DB;
	}

}

?>
