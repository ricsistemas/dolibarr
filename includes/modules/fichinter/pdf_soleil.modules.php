<?php
/* Copyright (C) 2003      Rodolphe Quiedeville        <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur         <eldy@users.sourceforge.net>
 * Copyright (C) 2008      Raphael Bertrand (Resultic) <raphael.bertrand@resultic.fr>
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
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/includes/modules/fichinter/pdf_soleil.modules.php
 *	\ingroup    ficheinter
 *	\brief      Fichier de la classe permettant de generer les fiches d'intervention au modele Soleil
 *	\version    $Id: pdf_soleil.modules.php,v 1.98 2010/12/26 20:22:59 kujiu Exp $
 */
require_once(DOL_DOCUMENT_ROOT."/includes/modules/fichinter/modules_fichinter.php");
require_once(DOL_DOCUMENT_ROOT."/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/lib/pdf.lib.php');


/**
 *	\class      pdf_soleil
 *	\brief      Class to build interventions documents with model Soleil
 */
class pdf_soleil extends ModelePDFFicheinter
{

	/**
	 *	\brief      Constructeur
	 *	\param	    db		Handler acces base de donnee
	 */
	function pdf_soleil($db=0)
	{
		global $conf,$langs,$mysoc;

		$this->db = $db;
		$this->name = 'soleil';
		$this->description = $langs->trans("DocumentModelStandard");

		// Dimension page pour format A4
		$this->type = 'pdf';
		$this->page_largeur = 210;
		$this->page_hauteur = 297;
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=10;
		$this->marge_droite=10;
		$this->marge_haute=10;
		$this->marge_basse=10;

		$this->option_logo = 1;                    // Affiche logo
		$this->option_tva = 0;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_modereg = 0;                 // Affiche mode reglement
		$this->option_condreg = 0;                 // Affiche conditions reglement
		$this->option_codeproduitservice = 0;      // Affiche code produit-service
		$this->option_multilang = 0;               // Dispo en plusieurs langues
		$this->option_draft_watermark = 1;		   //Support add of a watermark on drafts

		// Recupere emmetteur
		$this->emetteur=$mysoc;
		if (! $this->emetteur->code_pays) $this->emetteur->code_pays=substr($langs->defaultlang,-2);    // By default, if not defined

		// Defini position des colonnes
		$this->posxdesc=$this->marge_gauche+1;
	}

	/**
	 *	\brief      Fonction generant la fiche d'intervention sur le disque
	 *	\param	    fichinter		Object fichinter
	 *	\param		outputlangs		Lang output object
	 *	\return	    int     		1=ok, 0=ko
	 */
	function write_file($fichinter,$outputlangs)
	{
		global $user,$langs,$conf,$mysoc;
		$default_font_size = pdf_getPDFFontSize($outputlangs);

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (!class_exists('TCPDF')) $outputlangs->charset_output='ISO-8859-1';

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("interventions");

		if ($conf->ficheinter->dir_output)
		{
			// If $fichinter is id instead of object
			if (! is_object($fichinter))
			{
				$id = $fichinter;
				$fichinter = new Fichinter($this->db);
				$result=$fichinter->fetch($id);
				if ($result < 0)
				{
					dol_print_error($db,$fichinter->error);
				}
			}

            $fichinter->fetch_thirdparty();

			$fichref = dol_sanitizeFileName($fichinter->ref);
			$dir = $conf->ficheinter->dir_output;
			if (! preg_match('/specimen/i',$fichref)) $dir.= "/" . $fichref;
			$file = $dir . "/" . $fichref . ".pdf";

			if (! file_exists($dir))
			{
				if (create_exdir($dir) < 0)
				{
					$this->error=$outputlangs->trans("ErrorCanNotCreateDir",$dir);
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

				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right
				$pdf->SetAutoPageBreak(1,0);

				// New page
				$pdf->AddPage();
				$pagenb++;
				$pdf->SetTextColor(0,0,0);
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3

				// Pagehead

				//Affiche le filigrane brouillon - Print Draft Watermark
				if($fichinter->statut==0 && (! empty($conf->global->FICHINTER_DRAFT_WATERMARK)) )
				{
                    pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur,'mm',$conf->global->FICHINTER_DRAFT_WATERMARK);
				}

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
						$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'L');
						$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToModuleSetup"), 0, 'L');
					}
				}

				// Nom emetteur
				$posy=40;
				$hautcadre=40;
				$pdf->SetTextColor(0,0,0);
				$pdf->SetFont('','', $default_font_size - 2);

				$pdf->SetXY($this->marge_gauche,$posy);
				$pdf->SetFillColor(230,230,230);
				$pdf->MultiCell(82, $hautcadre, "", 0, 'R', 1);


				$pdf->SetXY($this->marge_gauche+2,$posy+3);

				// Sender name
				$pdf->SetTextColor(0,0,60);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell(80, 4, $outputlangs->convToOutputCharset($this->emetteur->nom), 0, 'L');

				// Sender properties
				$carac_emetteur = pdf_build_address($outputlangs,$this->emetteur);

				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($this->marge_gauche+2,$posy+9);
				$pdf->MultiCell(80, 4, $carac_emetteur, 0, 'L');

				$object=$fichinter;

				// Recipient name
				if (! empty($usecontact))
				{
					// On peut utiliser le nom de la societe du contact
					if ($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT) $socname = $object->contact->socname;
					else $socname = $object->client->nom;
					$carac_client_name=$outputlangs->convToOutputCharset($socname);
				}
				else
				{
					$carac_client_name=$outputlangs->convToOutputCharset($object->client->nom);
				}

				$carac_client=pdf_build_address($outputlangs,$this->emetteur,$object->client,$object->contact,$usecontact,'target');

				// Client destinataire
				$pdf->SetTextColor(0,0,0);
				$pdf->SetFont('','B', $default_font_size);
				$fichinter->fetch_thirdparty();
				$pdf->SetXY(102,42);
				$pdf->MultiCell(86,4, $carac_client_name, 0, 'L');
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY(102,$pdf->GetY());
				$pdf->MultiCell(66,4, $carac_client, 0, 'L');
				$pdf->rect(100, 40, 100, 40);


				$pdf->SetTextColor(0,0,100);
				$pdf->SetFont('','B', $default_font_size + 2);
				$pdf->SetXY(10,86);
				$pdf->MultiCell(120, 4, $outputlangs->transnoentities("InterventionCard")." : ".$outputlangs->convToOutputCharset($fichinter->ref), 0, 'L');

				$pdf->SetFillColor(220,220,220);
				$pdf->SetTextColor(0,0,0);
				$pdf->SetFont('','', $default_font_size);


				$tab_top = 100;
				$tab_top_newpage = 50;
				$tab_height = 110;
				$tab_height_newpage = 150;

				// Affiche notes
				if (! empty($fichinter->note_public))
				{
					$tab_top = 98;

					$pdf->SetFont('','', $default_font_size - 1);   // Dans boucle pour gerer multi-page
					$pdf->SetXY ($this->posxdesc-1, $tab_top);
					$pdf->MultiCell(190, 3, $outputlangs->convToOutputCharset($fichinter->note_public), 0, 'L');
					$nexY = $pdf->GetY();
					$height_note=$nexY-$tab_top;

					// Rect prend une longueur en 3eme param
					$pdf->SetDrawColor(192,192,192);
					$pdf->Rect($this->marge_gauche, $tab_top-1, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $height_note+1);

					$tab_height = $tab_height - $height_note;
					$tab_top = $nexY+6;
				}
				else
				{
					$height_note=0;
				}

				$pdf->SetXY (10, $tab_top);
				$pdf->MultiCell(190,8,$outputlangs->transnoentities("Description"),0,'L',0);
				$pdf->line(10, $tab_top + 8, 200, $tab_top + 8 );

				$pdf->SetFont('','', $default_font_size - 1);

				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetXY (10, $tab_top + 8 );
				$desc=dol_htmlentitiesbr($fichinter->description,1);
				//print $outputlangs->convToOutputCharset($desc); exit;
				$pdf->writeHTMLCell(180, 3, 10, $tab_top + 8, $outputlangs->convToOutputCharset($desc), 0, 1);
				$nexY = $pdf->GetY();

				$pdf->line(10, $nexY, 200, $nexY);

				$pdf->MultiCell(0, 3, '');		// Set interline to 3. Then writeMultiCell must use 3 also.

				//dol_syslog("desc=".dol_htmlentitiesbr($fichinter->description));
				$nblignes = sizeof($fichinter->lines);

				$curY = $pdf->GetY();
				$nexY = $pdf->GetY();

				// Loop on each lines
				for ($i = 0 ; $i < $nblignes ; $i++)
				{
					$fichinterligne = $fichinter->lines[$i];

					$valide = $fichinterligne->id ? $fichinterligne->fetch($fichinterligne->id) : 0;
					if ($valide>0)
					{
						$curY = $nexY+3;

						$pdf->SetXY (10, $curY);
						$pdf->writeHTMLCell(0, 3, $this->marge_gauche, $curY,
						dol_htmlentitiesbr($outputlangs->transnoentities("Date")." : ".dol_print_date($fichinterligne->datei,'dayhour',false,$outputlangs,true)." - ".$outputlangs->transnoentities("Duration")." : ".ConvertSecondToTime($fichinterligne->duration),1,$outputlangs->charset_output), 0, 1, 0);
						$nexY = $pdf->GetY();

						$pdf->SetXY (10, $curY + 3);
						$desc = dol_htmlentitiesbr($fichinterligne->desc,1);
						$pdf->writeHTMLCell(0, 3, $this->marge_gauche, $curY + 3, $desc, 0, 1, 0);
						$nexY+=dol_nboflines_bis($fichinterligne->desc,52,$outputlangs->charset_output)*3;
					}
				}
				//$pdf->line(10, $tab_top+$tab_height+3, 200, $tab_top+$tab_height+3);

				// Rectangle for title and all lines
				$pdf->Rect(10, $tab_top, 190, $tab_height+3);
				$pdf->SetXY (10, $pdf->GetY() + 20);
				$pdf->MultiCell(60, 5, '', 0, 'J', 0);

				$pdf->SetXY(20,220);
				$pdf->MultiCell(66,5, $outputlangs->transnoentities("NameAndSignatureOfInternalContact"),0,'L',0);

				$pdf->SetXY(20,225);
				$pdf->MultiCell(80,30, '', 1);

				$pdf->SetXY(110,220);
				$pdf->MultiCell(80,5, $outputlangs->transnoentities("NameAndSignatureOfExternalContact"),0,'L',0);

				$pdf->SetXY(110,225);
				$pdf->MultiCell(80,30, '', 1);

				$pdf->SetFont('','', $default_font_size - 1);   // On repositionne la police par defaut

				$this->_pagefoot($pdf,$fichinter,$outputlangs);
				$pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file,'F');
				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;
			}
			else
			{
				$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->trans("ErrorConstantNotDefined","FICHEINTER_OUTPUTDIR");
			return 0;
		}
		$this->error=$langs->trans("ErrorUnknown");
		return 0;   // Erreur par defaut
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
		return pdf_pagefoot($pdf,$outputlangs,'FICHINTER_FREE_TEXT',$this->emetteur,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object);
	}

}

?>
