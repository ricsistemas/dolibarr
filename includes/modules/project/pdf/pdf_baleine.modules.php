<?php
/* Copyright (C) 2010 Regis Houssin  <regis@dolibarr.fr>

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
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/includes/modules/project/pdf/pdf_baleine.modules.php
 *	\ingroup    project
 *	\brief      Fichier de la classe permettant de generer les projets au modele Baleine
 *	\author	    Regis Houssin
 *	\version    $Id: pdf_baleine.modules.php,v 1.33 2010/12/31 00:27:50 eldy Exp $
 */

require_once(DOL_DOCUMENT_ROOT."/includes/modules/project/modules_project.php");
require_once(DOL_DOCUMENT_ROOT."/projet/class/project.class.php");
require_once(DOL_DOCUMENT_ROOT."/projet/class/task.class.php");
require_once(DOL_DOCUMENT_ROOT."/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/lib/pdf.lib.php');


/**
 *	\class      pdf_baleine
 *	\brief      Classe permettant de generer les projets au modele Baleine
 */

class pdf_baleine extends ModelePDFProjects
{
	var $emetteur;	// Objet societe qui emet

	/**
	 *		\brief  Constructor
	 *		\param	db		Database handler
	 */
	function pdf_baleine($db)
	{
		global $conf,$langs,$mysoc;

		$langs->load("main");
		$langs->load("projects");
		$langs->load("companies");

		$this->db = $db;
		$this->name = "baleine";
		$this->description = $langs->trans("DocumentModelBaleine");

		// Dimension page pour format A4
		$this->type = 'pdf';
		$this->page_largeur = 210;
		$this->page_hauteur = 297;
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=10;
		$this->marge_droite=10;
		$this->marge_haute=10;
		$this->marge_basse=10;

		$this->option_logo = 1;                    // Affiche logo FAC_PDF_LOGO
		$this->option_tva = 1;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_codeproduitservice = 1;      // Affiche code produit-service

		// Recupere emmetteur
		$this->emetteur=$mysoc;
		if (! $this->emetteur->pays_code) $this->emetteur->pays_code=substr($langs->defaultlang,-2);    // Par defaut, si n'�tait pas d�fini

		// Defini position des colonnes
		$this->posxref=$this->marge_gauche+1;
		$this->posxlabel=$this->marge_gauche+25;
		$this->posxprogress=$this->marge_gauche+140;
		$this->posxdatestart=$this->marge_gauche+150;
		$this->posxdateend=$this->marge_gauche+170;
	}


	/**
	 *	\brief      Fonction generant le projet sur le disque
	 *	\param	    object   		Object project a generer
	 *	\param		outputlangs		Lang output object
	 *	\return	    int         	1 if OK, <=0 if KO
	 */
	function write_file($object,$outputlangs)
	{
		global $user,$langs,$conf;

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (!class_exists('TCPDF')) $outputlangs->charset_output='ISO-8859-1';

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("projects");

		if ($conf->projet->dir_output)
		{
			$nblignes = sizeof($object->lines);

			$default_font_size = pdf_getPDFFontsize($outputlangs);

			$objectref = dol_sanitizeFileName($object->ref);
			$dir = $conf->projet->dir_output;
			if (! preg_match('/specimen/i',$objectref)) $dir.= "/" . $objectref;
			$file = $dir . "/" . $objectref . ".pdf";

			if (! file_exists($dir))
			{
				if (create_exdir($dir) < 0)
				{
					$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{
				// Protection et encryption du pdf
/*				if ($conf->global->PDF_SECURITY_ENCRYPTION)
				{
					$pdf=new FPDI_Protection('P','mm',$this->format);
					$pdfrights = array('print'); // Ne permet que l'impression du document
					$pdfuserpass = ''; // Mot de passe pour l'utilisateur final
					$pdfownerpass = NULL; // Mot de passe du proprietaire, cree aleatoirement si pas defini
					$pdf->SetProtection($pdfrights,$pdfuserpass,$pdfownerpass);
				}
				else
				{
					$pdf=new FPDI('P','mm',$this->format);
				}
*/
                $pdf=pdf_getInstance($this->format);

                if (class_exists('TCPDF'))
                {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }
                $pdf->SetFont(pdf_getPDFFont($outputlangs));

				// Complete object by loading several other informations
				$task = new Task($this->db);
				$tasksarray = $task->getTasksArray(0,0,$object->id);

				$object->lines=$tasksarray;
				$nblignes=sizeof($object->lines);

				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("Project"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("Project"));
				if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right
				$pdf->SetAutoPageBreak(1,0);

				// New page
				$pdf->AddPage();
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColor(0,0,0);

				$tab_top = 50;
				$tab_height = 200;
				$tab_top_newpage = 40;
                $tab_height_newpage = 210;

				// Affiche notes
				if (! empty($object->note_public))
				{
					$pdf->SetFont('','', $default_font_size - 1);
					$pdf->SetXY ($this->posxref-1, $tab_top-2);
					$pdf->MultiCell(190, 3, $outputlangs->convToOutputCharset($object->note_public), 0, 'L');
					$nexY = $pdf->GetY();
					$height_note=$nexY-($tab_top-2);

					// Rect prend une longueur en 3eme param
					$pdf->SetDrawColor(192,192,192);
					$pdf->Rect($this->marge_gauche, $tab_top-3, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $height_note+1);

					$tab_height = $tab_height - $height_note;
					$tab_top = $nexY+6;
				}
				else
				{
					$height_note=0;
				}

				$iniY = $tab_top + 7;
				$curY = $tab_top + 7;
				$nexY = $tab_top + 7;

				// Boucle sur les lignes
				for ($i = 0 ; $i < $nblignes ; $i++)
				{
					$curY = $nexY;

					// Description of ligne
					$ref=$object->lines[$i]->ref;
					$libelleline=$object->lines[$i]->label;
					$progress=$object->lines[$i]->progress.'%';
					$datestart=dol_print_date($object->lines[$i]->date_start,'day');
					$dateend=dol_print_date($object->lines[$i]->date_end,'day');


					$pdf->SetFont('','', $default_font_size - 1);   // Dans boucle pour gerer multi-page

					$pdf->SetXY($this->posxref, $curY);
					$pdf->MultiCell(60, 3, $outputlangs->convToOutputCharset($ref), 0, 'L');
					$pdf->SetXY($this->posxlabel, $curY);
					$pdf->MultiCell(108, 3, $outputlangs->convToOutputCharset($libelleline), 0, 'L');
					$pdf->SetXY($this->posxprogress, $curY);
					$pdf->MultiCell(16, 3, $progress, 0, 'L');
					$pdf->SetXY($this->posxdatestart, $curY);
					$pdf->MultiCell(20, 3, $datestart, 0, 'L');
					$pdf->SetXY($this->posxdateend, $curY);
					$pdf->MultiCell(20, 3, $dateend, 0, 'L');


					$pdf->SetFont('','', $default_font_size - 1);   // On repositionne la police par defaut
					$nexY = $pdf->GetY();

					$nexY+=2;    // Passe espace entre les lignes

					// Cherche nombre de lignes a venir pour savoir si place suffisante
					if ($i < ($nblignes - 1))	// If it's not last line
					{
						//on recupere la description du produit suivant
						$follow_descproduitservice = $object->lines[$i+1]->label;
						//on compte le nombre de ligne afin de verifier la place disponible (largeur de ligne 52 caracteres)
						$nblineFollowDesc = (dol_nboflines_bis($follow_descproduitservice,52,$outputlangs->charset_output)*4);
					}
					else	// If it's last line
					{
						$nblineFollowDesc = 0;
					}

					if (($nexY+$nblineFollowDesc) > ($tab_top+$tab_height) && $i < ($nblignes - 1))
					{
						$this->_tableau($pdf, $tab_top, $tab_height + 20, $nexY, $outputlangs);

						$this->_pagefoot($pdf, $object, $outputlangs);

						// New page
						$pdf->AddPage();
						$pagenb++;
						$this->_pagehead($pdf, $object, 0, $outputlangs);
						$pdf->SetFont('','', $default_font_size - 1);
						$pdf->MultiCell(0, 3, '');		// Set interline to 3
						$pdf->SetTextColor(0,0,0);

						$tab_top=$tab_top_newpage;
						$tab_height=$tab_height_newpage;

						$nexY = $tab_top + 7;
					}
				}

				// Show square
				$this->_tableau($pdf, $tab_top, $tab_height, $nexY, $outputlangs);
				$bottomlasttab=$tab_top + $tab_height + 1;

				/*
				 * Pied de page
				 */
				$this->_pagefoot($pdf,$object,$outputlangs);
				$pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file,'F');
				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;   // Pas d'erreur
			}
			else
			{
				$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}

		$this->error=$langs->transnoentities("ErrorConstantNotDefined","LIVRAISON_OUTPUTDIR");
		return 0;
	}


	/*
	 *   \brief      Affiche la grille des lignes
	 *   \param      pdf     objet PDF
	 */
	function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs)
	{
		global $conf,$mysoc;

        $default_font_size = pdf_getPDFFontSize($outputlangs);

		$pdf->SetDrawColor(128,128,128);

		// Rect prend une longueur en 3eme param
		$pdf->Rect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height);
		// line prend une position y en 3eme param
		$pdf->line($this->marge_gauche, $tab_top+6, $this->page_largeur-$this->marge_droite, $tab_top+6);

		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('','', $default_font_size);

		$pdf->SetXY ($this->posxref-1, $tab_top+2);
		$pdf->MultiCell(80,2, $outputlangs->transnoentities("Tasks"),'','L');

	}

	/**
	 *   	\brief      Affiche en-tete bon livraison
	 *   	\param      pdf     	objet PDF
	 *   	\param      delivery    object delivery
	 *      \param      showadress  0=non, 1=oui
	 */
	function _pagehead(&$pdf, $object, $showadress=1, $outputlangs)
	{
		global $langs,$conf,$mysoc;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);

		$pdf->SetTextColor(0,0,60);
		$pdf->SetFont('','B', $default_font_size + 3);

		$posy=$this->marge_haute;

		$pdf->SetXY($this->marge_gauche,$posy);

		// Logo
		$logo=$conf->mycompany->dir_output.'/logos/'.$mysoc->logo;
		if ($mysoc->logo)
		{
			if (is_readable($logo))
			{
				$pdf->Image($logo, $this->marge_gauche, $posy, 0, 24);
			}
			else
			{
				$pdf->SetTextColor(200,0,0);
				$pdf->SetFont('','B', $default_font_size - 2);
				$pdf->MultiCell(100, 3, $langs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'L');
				$pdf->MultiCell(100, 3, $langs->transnoentities("ErrorGoToModuleSetup"), 0, 'L');
			}
		}
		else $pdf->MultiCell(100, 4, $outputlangs->transnoentities($this->emetteur->nom), 0, 'L');

		$pdf->SetFont('','B', $default_font_size + 3);
		$pdf->SetXY(100,$posy);
		$pdf->SetTextColor(0,0,60);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("Project")." ".$outputlangs->convToOutputCharset($object->ref), '' , 'R');
		$pdf->SetFont('','', $default_font_size + 2);

		$posy+=6;
		$pdf->SetXY(100,$posy);
		$pdf->SetTextColor(0,0,60);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("DateStart")." : " . dol_print_date($object->date_start,'day',false,$outputlangs,true), '', 'R');
		$posy+=6;
		$pdf->SetXY(100,$posy);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("DateEnd")." : " . dol_print_date($object->date_end,'day',false,$outputlangs,true), '', 'R');

		$pdf->SetTextColor(0,0,60);

		// Add list of linked orders
		// TODO mutualiser
	    $object->load_object_linked();

	    if ($conf->commande->enabled)
		{
			$outputlangs->load('orders');
			foreach($object->linked_object as $key => $val)
			{
				if ($key == 'commande')
				{
					for ($i = 0; $i<sizeof($val);$i++)
					{
						$newobject=new Commande($this->db);
						$result=$newobject->fetch($val[$i]);
						if ($result >= 0)
						{
							$posy+=4;
							$pdf->SetXY(100,$posy);
							$pdf->SetFont('','', $default_font_size - 1);
							$text=$newobject->ref;
							if ($newobject->ref_client) $text.=' ('.$newobject->ref_client.')';
							$pdf->MultiCell(100, 4, $outputlangs->transnoentities("RefOrder")." : ".$outputlangs->transnoentities($text), '', 'R');
						}
					}
				}
			}
		}

	}

	/**
	 *   	\brief      Show footer of page
	 *   	\param      pdf     		PDF factory
	 * 		\param		object			Object invoice
	 *      \param      outputlangs		Object lang for output
	 * 		\remarks	Need this->emetteur object
	 */
	function _pagefoot(&$pdf,$object,$outputlangs)
	{
		return pdf_pagefoot($pdf,$outputlangs,'DELIVERY_FREE_TEXT',$this->emetteur,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object);
	}

}

?>
