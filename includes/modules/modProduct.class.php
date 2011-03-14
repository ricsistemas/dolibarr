<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Sebastien Di Cintio  <sdicintio@ressource-toi.org>
 * Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
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
 *	\defgroup   produit     Module products
 *	\brief      Module pour gerer le suivi de produits predefinis
 *	\version	$Id: modProduct.class.php,v 1.9 2010/11/02 11:42:54 eldy Exp $
 */

/**
 *	\file       htdocs/includes/modules/modProduct.class.php
 *	\ingroup    produit
 *	\brief      Fichier de description et activation du module Produit
 */

include_once(DOL_DOCUMENT_ROOT ."/includes/modules/DolibarrModules.class.php");


/**
 *	\class      modProduct
 *	\brief      Classe de description et activation du module Produit
 */
class modProduct extends DolibarrModules
{
	/**
	 *   \brief      Constructeur. Definit les noms, constantes et boites
	 *   \param      DB      handler d'acces base
	 */
	function modProduct($DB)
	{
		global $conf;

		$this->db = $DB;
		$this->numero = 50;

		$this->family = "products";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		$this->description = "Gestion des produits";

		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = 'dolibarr';

		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->special = 0;
		$this->picto='product';

		// Data directories to create when module is enabled
		$this->dirs = array("/product/temp");

		// Dependencies
		$this->depends = array();
		$this->requiredby = array("modStock","modBarcode");

		// Config pages
		$this->config_page_url = array("produit.php@product");
		$this->langfiles = array("products","companies","stocks","bills");

		// Constants
		$this->const = array();
		$r=0;

		$this->const[$r][0] = "MAIN_SEARCHFORM_PRODUITSERVICE";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = "1";
		$this->const[$r][3] = "Affichage formulaire de recherche des Produits et Services dans la barre de gauche";
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 'allentities';
		$r++;

		// Boxes
		$this->boxes = array();
		$this->boxes[0][1] = "box_produits.php";

		// Permissions
		$this->rights = array();
		$this->rights_class = 'produit';
		$r=0;

		$this->rights[$r][0] = 31; // id de la permission
		$this->rights[$r][1] = 'Lire les produits'; // libelle de la permission
		$this->rights[$r][2] = 'r'; // type de la permission (deprecie a ce jour)
		$this->rights[$r][3] = 1; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'lire';
		$r++;

		$this->rights[$r][0] = 32; // id de la permission
		$this->rights[$r][1] = 'Creer/modifier les produits'; // libelle de la permission
		$this->rights[$r][2] = 'w'; // type de la permission (deprecie a ce jour)
		$this->rights[$r][3] = 0; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'creer';
		$r++;

		$this->rights[$r][0] = 34; // id de la permission
		$this->rights[$r][1] = 'Supprimer les produits'; // libelle de la permission
		$this->rights[$r][2] = 'd'; // type de la permission (deprecie a ce jour)
		$this->rights[$r][3] = 0; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'supprimer';
        $r++;

        $this->rights[$r][0] = 36; // id de la permission
		$this->rights[$r][1] = 'Voir/gérer les produits cachés'; // libelle de la permission
		$this->rights[$r][2] = 'r'; // type de la permission (deprecated)
		$this->rights[$r][3] = 1; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'hidden';
        $r++;

		$this->rights[$r][0] = 38;	// Must be same permission than in service module
		$this->rights[$r][1] = 'Exporter les produits';
		$this->rights[$r][2] = 'r';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'export';
        $r++;


		// Exports
		//--------
		$r=0;

		$r++;
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]="Products";	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_permission[$r]=array(array("produit","export"));
		$this->export_fields_array[$r]=array('p.rowid'=>"Id",'p.ref'=>"Ref",'p.label'=>"Label",'p.description'=>"Description",'p.accountancy_code_sell'=>"ProductAccountancySellCode",'p.accountancy_code_buy'=>"ProductAccountancyBuyCode",'p.note'=>"Note",'p.length'=>"Length",'p.surface'=>"Surface",'p.volume'=>"Volume",'p.weight'=>"Weight",'p.customcode'=>'CustomCode','p.price_base_type'=>"PriceBase",'p.price'=>"UnitPriceHT",'p.price_ttc'=>"UnitPriceTTC",'p.tva_tx'=>'VATRate','p.tosell'=>"OnSell",'p.tobuy'=>"OnBuy",'p.datec'=>'DateCreation','p.tms'=>'DateModification');
		if (! empty($conf->stock->enabled)) $this->export_fields_array[$r]=array_merge ($this->export_fields_array[$r],array('p.stock'=>'Stock','p.pmp'=>'PMPValue'));
		if (! empty($conf->barcode->enabled)) $this->export_fields_array[$r]=array_merge ($this->export_fields_array[$r],array('p.barcode'=>'Barcode'));
		$this->export_entities_array[$r]=array('p.rowid'=>"product",'p.ref'=>"product",'p.label'=>"product",'p.description'=>"product",'p.accountancy_code_sell'=>'product','p.accountancy_code_sell'=>'product','p.note'=>"product",'p.length'=>"product",'p.surface'=>"product",'p.volume'=>"product",'p.weight'=>"product",'p.customcode'=>'product','p.price_base_type'=>"product",'p.price'=>"product",'p.price_ttc'=>"product",'p.tva_tx'=>"product",'p.tosell'=>"product",'p.tobuy'=>"product",'p.datec'=>"product",'p.tms'=>"product");
		if (! empty($conf->stock->enabled)) $this->export_entities_array[$r]=array_merge ($this->export_entities_array[$r],array('p.stock'=>'product','p.pmp'=>'product'));
		if (! empty($conf->barcode->enabled)) $this->export_entities_array[$r]=array_merge ($this->export_entities_array[$r],array('p.barcode'=>'product'));
		$this->export_alias_array[$r]=array('p.rowid'=>"id",'p.ref'=>"ref",'p.label'=>"label",'p.description'=>"description",'p.accountancy_code_sell'=>'accountancy_code_sell','p.accountancy_code_buy'=>'accountancy_code_buy','p.note'=>"note",'p.length'=>"length",'p.surface'=>"area",'p.volume'=>"volume",'p.weight'=>"weight",'p.customcode'=>'customcode','p.price_base_type'=>'pricebase','p.price'=>"priceht",'p.price_ttc'=>"pricettc",'p.tva_tx'=>'vat','p.tosell'=>"onsell",'p.tobuy'=>"onbuy",'p.datec'=>'datecreation','p.tms'=>'datemodification');
		if (! empty($conf->stock->enabled)) $this->export_alias_array[$r]=array_merge ($this->export_alias_array[$r],array('p.stock'=>'stock','p.pmp'=>'pmp'));
		if (! empty($conf->barcode->enabled)) $this->export_alias_array[$r]=array_merge ($this->export_alias_array[$r],array('p.barcode'=>'barcode'));

		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'product as p';
		$this->export_sql_end[$r] .=' WHERE p.fk_product_type = 0 AND p.entity = '.$conf->entity;


		// Imports
		//--------
		$r=0;

		$r++;
		$this->import_code[$r]=$this->rights_class.'_'.$r;
		$this->import_label[$r]="Products";	// Translation key
		$this->import_icon[$r]=$this->picto;
		$this->import_tables_array[$r]=array('p'=>MAIN_DB_PREFIX.'product');
		$this->import_tables_creator_array[$r]=array('p'=>'fk_user_author');	// Fields to store import user id
		$this->import_fields_array[$r]=array('p.ref'=>"Ref*",'p.label'=>"Label*",'p.description'=>"Description",'p.note'=>"Note",'p.price'=>"SellingPriceHT",'p.price_ttc'=>"SellingPriceTTC",'p.tva_tx'=>'VAT','p.tosell'=>"OnSell*",'p.tobuy'=>"OnBuy*",'p.fk_product_type'=>"Type*",'p.finished'=>'Nature','p.duration'=>"Duration",'p.weight'=>"Weight",'p.volume'=>"Volume",'p.datec'=>'DateCreation*');
		$this->import_entities_array[$r]=array();	// We define here only fields that use another picto
		$this->import_regex_array[$r]=array('p.ref'=>'[^ ]','p.tosell'=>'^[0|1]','p.tobuy'=>'^[0|1]','p.fk_product_type'=>'^[0|1]','p.datec'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$');
		$this->import_examplevalues_array[$r]=array('p.ref'=>"PR123456",'p.label'=>"My product",'p.description'=>"This is a description example for record",'p.note'=>"Some note",'p.price'=>"100",'p.price_ttc'=>"110",'p.tva_tx'=>'10','p.tosell'=>"0 or 1",'p.tobuy'=>"0 or 1",'p.fk_product_type'=>"0 for product/1 for service",'p.finished'=>'','p.duration'=>"1y",'p.datec'=>'2008-12-31');
	}


	/**
	 *   \brief      Fonction appelee lors de l'activation du module. Insere en base les constantes, boites, permissions du module.
	 *               Definit egalement les repertoires de donnees a creer pour ce module.
	 */
	function init()
	{
		// Permissions
		$this->remove();

		$sql = array();

		return $this->_init($sql);
	}

	/**
	 *    \brief      Fonction appelee lors de la desactivation d'un module.
	 *                Supprime de la base les constantes, boites et permissions du module.
	 */
	function remove()
	{
		$sql = array();

		return $this->_remove($sql);
	}
}
?>
