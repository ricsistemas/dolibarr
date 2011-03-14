<?php
/* Copyright (C) 2010      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2010      Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *	\file       htdocs/includes/modules/supplier_invoice/pdf/pdf_canelle.modules.php
 *	\ingroup    fournisseur
 *	\brief      Class file to generate the supplier invoices with the canelle model
 *	\version    $Id: pdf_canelle.modules.php,v 1.25 2011/01/03 09:50:08 eldy Exp $
 */

require_once(DOL_DOCUMENT_ROOT."/includes/modules/supplier_invoice/modules_facturefournisseur.php");
require_once(DOL_DOCUMENT_ROOT."/fourn/class/fournisseur.facture.class.php");
require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once(DOL_DOCUMENT_ROOT."/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/lib/pdf.lib.php');


/**
 *	\class      pdf_canelle
 *	\brief      Class to generate the supplier invoices with the canelle model
 */
class pdf_canelle extends ModelePDFSuppliersInvoices
{

	/**
	 *	\brief      Constructor
	 *	\param	    db		Handler access data base
	 */
	function pdf_canelle($db,$object)
	{
		global $conf,$langs,$mysoc;

		$langs->load("main");
		$langs->load("bills");

		$this->db = $db;
		$this->name = "canelle";
		$this->description = $langs->trans('SuppliersInvoiceModel');

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
		$this->option_tva = 1;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_modereg = 1;                 // Affiche mode reglement
		$this->option_condreg = 1;                 // Affiche conditions reglement
		$this->option_codeproduitservice = 1;      // Affiche code produit-service
		$this->option_multilang = 1;               // Dispo en plusieurs langues

		$this->franchise=!$mysoc->tva_assuj;

        // Get source company
        if (! is_object($object->thirdparty)) $object->fetch_thirdparty();
        $this->emetteur=$object->thirdparty;
        if (! $this->emetteur->pays_code) $this->emetteur->pays_code=substr($langs->defaultlang,-2);    // By default, if was not defined

        // Defini position des colonnes
		$this->posxdesc=$this->marge_gauche+1;
		$this->posxtva=113;
		$this->posxup=126;
		$this->posxqty=145;
		$this->posxdiscount=162;
		$this->postotalht=174;

		$this->tva=array();
        $this->localtax1=array();
        $this->localtax2=array();
		$this->atleastoneratenotnull=0;
		$this->atleastonediscount=0;
	}


	/**
     *      Write the invoice as a document onto disk
     *      @param      object          Object invoice to build (or id if old method)
     *      @param      outputlangs     Lang object for output language
     *      @return     int             1=OK, 0=KO
	 */
	function write_file($object,$outputlangs='')
	{
		global $user,$langs,$conf,$mysoc;

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (!class_exists('TCPDF')) $outputlangs->charset_output='ISO-8859-1';

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("bills");
		$outputlangs->load("products");

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		if ($conf->fournisseur->dir_output.'/facture')
		{
			$object->fetch_thirdparty();

			$deja_regle = $object->getSommePaiement();
            //$amount_credit_notes_included = $object->getSumCreditNotesUsed();
            //$amount_deposits_included = $object->getSumDepositsUsed();

			// Definition de $dir et $file
			if ($object->specimen)
			{
				$dir = $conf->fournisseur->dir_output.'/facture';
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->fournisseur->dir_output . "/facture/" . $objectref;
				$file = $dir . "/" . $objectref . ".pdf";
			}

			if (! file_exists($dir))
			{
				if (create_exdir($dir) < 0)
				{
					$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
					return 0;
				}

			}

			if (file_exists($dir))
			{
				$nblignes = sizeof($object->lines);

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

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("Invoice"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("Order"));
				if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right
				$pdf->SetAutoPageBreak(1,0);

			    // Positionne $this->atleastonediscount si on a au moins une remise
                for ($i = 0 ; $i < $nblignes ; $i++)
                {
                    if ($object->lines[$i]->remise_percent)
                    {
                        $this->atleastonediscount++;
                    }
                }

                // New page
				$pdf->AddPage();
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColor(0,0,0);

				$tab_top = 90;
				$tab_top_newpage = 50;
				$tab_height = 110;
				$tab_height_newpage = 150;

				// Affiche notes
				if (! empty($object->note_public))
				{
					$tab_top = 88;

					$pdf->SetFont('','', $default_font_size - 1);   // Dans boucle pour gerer multi-page
					$pdf->SetXY ($this->posxdesc-1, $tab_top);
					$pdf->MultiCell(190, 3, $outputlangs->convToOutputCharset($object->note_public), 0, 'L');
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

				$iniY = $tab_top + 7;
				$curY = $tab_top + 7;
				$nexY = $tab_top + 7;

				// Boucle sur les lignes
				for ($i = 0 ; $i < $nblignes ; $i++)
				{
					$curY = $nexY;

                    $pdf->SetFont('','', $default_font_size - 1);   // Dans boucle pour gerer multi-page

                    // Description of product line
                    $pdf->SetFont('','', $default_font_size - 1);   // Into loop to work with multipage
                    $curX = $this->posxdesc-1;
                    pdf_writelinedesc($pdf,$object,$i,$outputlangs,$this->posxtva-$curX,3,$curX,$curY,0,0,1);

					$pdf->SetFont('','', $default_font_size - 1);   // On repositionne la police par defaut
					$nexY = $pdf->GetY();

					// VAT rate
                    if (empty($conf->global->MAIN_GENERATE_DOCUMENTS_WITHOUT_VAT))
                    {
    					$vat_rate = pdf_getlinevatrate($object, $i, $outputlangs);
                        $pdf->SetXY ($this->posxtva, $curY);
	       				$pdf->MultiCell($this->posxup-$this->posxtva-1, 3, $vat_rate, 0, 'R');
                    }

					// Unit price before discount
					$pdf->SetXY ($this->posxup, $curY);
					$pdf->MultiCell($this->posxqty-$this->posxup-1, 3, price($object->lines[$i]->pu_ht), 0, 'R', 0);

					// Quantity
					$pdf->SetXY ($this->posxqty, $curY);
					$pdf->MultiCell($this->posxdiscount-$this->posxqty-1, 3, $object->lines[$i]->qty, 0, 'R');

					// Discount on line
					$pdf->SetXY ($this->posxdiscount, $curY);
					if ($object->lines[$i]->remise_percent)
					{
						$pdf->MultiCell($this->postotalht-$this->posxdiscount-1, 3, $object->lines[$i]->remise_percent."%", 0, 'R');
					}

					// Total HT line
                    $total_excl_tax = pdf_getlinetotalexcltax($object, $i, $outputlangs);
                    $pdf->SetXY ($this->postotalht, $curY);
                    $pdf->MultiCell(26, 3, $total_excl_tax, 0, 'R', 0);

					// Collecte des totaux par valeur de tva dans $this->tva["taux"]=total_tva
					$tvaligne=$object->lines[$i]->total_tva;
					if ($object->remise_percent) $tvaligne-=($tvaligne*$object->remise_percent)/100;
					$vatrate=(string) $object->lines[$i]->tva_tx;
					if (($object->lines[$i]->info_bits & 0x01) == 0x01) $vatrate.='*';
					$this->tva[$vatrate] += $tvaligne;

					$nexY+=2;    // Passe espace entre les lignes

					// Test if a new page is required
					if ($pagenb == 1)
					{
						$tab_top_in_current_page=$tab_top;
						$tab_height_in_current_page=$tab_height;
					}
					else
					{
						$tab_top_in_current_page=$tab_top_newpage;
						$tab_height_in_current_page=$tab_height_newpage;
					}
					if (($nexY+$nblineFollowDesc) > ($tab_top_in_current_page+$tab_height_in_current_page) && $i < ($nblignes - 1))
					{
						if ($pagenb == 1)
						{
							$this->_tableau($pdf, $tab_top, $tab_height + 20, $nexY, $outputlangs);
						}
						else
						{
							$this->_tableau($pdf, $tab_top_newpage, $tab_height_newpage, $nexY, $outputlangs);
						}

						$this->_pagefoot($pdf, $object, $outputlangs);

						// New page
						$pdf->AddPage();
						$pagenb++;
						$this->_pagehead($pdf, $object, 0, $outputlangs);
						$pdf->SetFont('','', $default_font_size - 1);
						$pdf->MultiCell(0, 3, '');		// Set interline to 3
						$pdf->SetTextColor(0,0,0);

						$nexY = $tab_top_newpage + 7;
					}

				}

				// Show square
				if ($pagenb == 1)
				{
					$this->_tableau($pdf, $tab_top, $tab_height, $nexY, $outputlangs);
					$bottomlasttab=$tab_top + $tab_height + 1;
				}
				else
				{
					$this->_tableau($pdf, $tab_top_newpage, $tab_height_newpage, $nexY, $outputlangs);
					$bottomlasttab=$tab_top_newpage + $tab_height_newpage + 1;
				}

				// Affiche zone totaux
				$posy=$this->_tableau_tot($pdf, $object, $deja_regle, $bottomlasttab, $outputlangs);

				if ($deja_regle || $amount_credit_notes_included || $amount_deposits_included)
				{
					$posy=$this->_tableau_versements($pdf, $object, $posy, $outputlangs);
				}

				// Pied de page
				$this->_pagefoot($pdf, $object, $outputlangs);
				$pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file,'F');
				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;   // Pas d'erreur
			}
			else
			{
				$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->trans("ErrorConstantNotDefined","SUPPLIER_OUTPUTDIR");
			return 0;
		}
		$this->error=$langs->trans("ErrorUnknown");
		return 0;   // Erreur par defaut
	}

	/**
	 *   \brief      Show total to pay
	 *   \param      pdf         	Object PDF
	 *   \param      object        	Object invoice
	 *   \param      deja_regle  	Amount payed
	 *   \return     y              Next position
	 */
	function _tableau_tot(&$pdf, $object, $deja_regle, $posy, $outputlangs)
	{
        $default_font_size = pdf_getPDFFontSize($outputlangs);

        $tab2_top = $posy;
		$tab2_hl = 4;
		$pdf->SetFont('','', $default_font_size - 1);

		$pdf->SetXY ($this->marge_gauche, $tab2_top + 0);
		// If France, show VAT mention if not applicable
		if ($this->emetteur->pays_code == 'FR' && $this->franchise == 1)
		{
			$pdf->MultiCell(100, $tab2_hl, $outputlangs->transnoentities("VATIsNotUsedForInvoice"), 0, 'L', 0);
		}

		// Tableau total
		$lltot = 200; $col1x = 120; $col2x = 170; $largcol2 = $lltot - $col2x;

		// Total HT
		$pdf->SetFillColor(255,255,255);
		$pdf->SetXY ($col1x, $tab2_top + 0);
		$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("TotalHT"), 0, 'L', 1);
		$pdf->SetXY ($col2x, $tab2_top + 0);
		$pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ht + $object->remise), 0, 'R', 1);

		// Affichage des totaux de TVA par taux (conformement a reglementation)
		$pdf->SetFillColor(248,248,248);

		foreach( $this->tva as $tvakey => $tvaval )
		{
			if ($tvakey)    // On affiche pas taux 0
			{
				$this->atleastoneratenotnull++;

				$index++;
				$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);

				$tvacompl='';

				if (preg_match('/\*/',$tvakey))
				{
					$tvakey=str_replace('*','',$tvakey);
					$tvacompl = " (".$outputlangs->transnoentities("NonPercuRecuperable").")";
				}

				$totalvat =$outputlangs->transnoentities("TotalVAT").' ';
				$totalvat.=vatrate($tvakey,1).$tvacompl;
				$pdf->MultiCell($col2x-$col1x, $tab2_hl, $totalvat, 0, 'L', 1);

				$pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
				$pdf->MultiCell($largcol2, $tab2_hl, price($tvaval), 0, 'R', 1);
			}
		}
		if (! $this->atleastoneratenotnull) // If not vat at all
		{
			$index++;
			$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("TotalVAT"), 0, 'L', 1);
			$pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($largcol2, $tab2_hl, price($object->total_tva), 0, 'R', 1);
		}

		$useborder=0;

		$index++;
		$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
		$pdf->SetTextColor(0,0,60);
		$pdf->SetFillColor(224,224,224);
		$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("TotalTTC"), $useborder, 'L', 1);

		$pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
		$pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ttc), $useborder, 'R', 1);
		$pdf->SetFont('','', $default_font_size - 1);
		$pdf->SetTextColor(0,0,0);

		if ($deja_regle > 0)
		{
			$index++;

			$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("AlreadyPaid"), 0, 'L', 0);

			$pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($largcol2, $tab2_hl, price($deja_regle), 0, 'R', 0);

			$index++;
			$pdf->SetTextColor(0,0,60);
			//$pdf->SetFont('','B', $default_font_size - 1);
			$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("RemainderToPay"), $useborder, 'L', 1);

			$pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ttc - $deja_regle), $useborder, 'R', 1);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetTextColor(0,0,0);
		}

		$index++;
		return ($tab2_top + ($tab2_hl * $index));
	}

	/**
	 *   Show the lines of invoice
	 *   @param      pdf     object PDF
	 */
	function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs)
	{
		global $conf;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

        // Amount in (at tab_top - 1)
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('','',$default_font_size - 2);
		$titre = $outputlangs->transnoentities("AmountInCurrency",$outputlangs->transnoentitiesnoconv("Currency".$conf->monnaie));
		$pdf->SetXY($this->page_largeur - $this->marge_droite - ($pdf->GetStringWidth($titre) + 3), $tab_top -3);
		$pdf->MultiCell(($pdf->GetStringWidth($titre) + 3), 2, $titre);

		$pdf->SetDrawColor(128,128,128);

		// Rect prend une longueur en 3eme param
		$pdf->Rect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height);
		// line prend une position y en 3eme param
		$pdf->line($this->marge_gauche, $tab_top+6, $this->page_largeur-$this->marge_droite, $tab_top+6);

		$pdf->SetFont('','', $default_font_size - 1);

		$pdf->SetXY ($this->posxdesc-1, $tab_top+2);
		$pdf->MultiCell(108,2, $outputlangs->transnoentities("Designation"),'','L');

        if (empty($conf->global->MAIN_GENERATE_DOCUMENTS_WITHOUT_VAT))
        {
    		$pdf->line($this->posxtva-1, $tab_top, $this->posxtva-1, $tab_top + $tab_height);
    		$pdf->SetXY ($this->posxtva-3, $tab_top+2);
    		$pdf->MultiCell($this->posxup-$this->posxtva+3,2, $outputlangs->transnoentities("VAT"),'','C');
        }

		$pdf->line($this->posxup-1, $tab_top, $this->posxup-1, $tab_top + $tab_height);
		$pdf->SetXY ($this->posxup-1, $tab_top+2);
		$pdf->MultiCell($this->posxqty-$this->posxup-1,2, $outputlangs->transnoentities("PriceUHT"),'','C');

		$pdf->line($this->posxqty-1, $tab_top, $this->posxqty-1, $tab_top + $tab_height);
		$pdf->SetXY ($this->posxqty-1, $tab_top+2);
		$pdf->MultiCell($this->posxdiscount-$this->posxqty-1,2, $outputlangs->transnoentities("Qty"),'','C');

		$pdf->line($this->posxdiscount-1, $tab_top, $this->posxdiscount-1, $tab_top + $tab_height);

		if ($this->atleastonediscount)
		{
			$pdf->SetXY ($this->posxdiscount-1, $tab_top+2);
			$pdf->MultiCell($this->postotalht-$this->posxdiscount+1,2, $outputlangs->transnoentities("ReductionShort"),'','C');
		}

		if ($this->atleastonediscount)
		{
			$pdf->line($this->postotalht, $tab_top, $this->postotalht, $tab_top + $tab_height);
		}
		$pdf->SetXY ($this->postotalht-1, $tab_top+2);
		$pdf->MultiCell(30,2, $outputlangs->transnoentities("TotalHTShort"),'','C');

	}

	/**
	 *  Show payments table
	 *  @param      pdf     		Object PDF
	 *  @param      object     		Object invoice
	 *	@param		posy			Position y in PDF
	 *	@param		outputlangs		Object langs for output
	 *	@return 	int				<0 if KO, >0 if OK
	 */
	function _tableau_versements(&$pdf, $object, $posy, $outputlangs)
	{
		$tab3_posx = 120;
		$tab3_top = $posy + 8;
		$tab3_width = 80;
		$tab3_height = 4;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		$pdf->SetFont('','', $default_font_size - 2);
		$pdf->SetXY ($tab3_posx, $tab3_top - 5);
		$pdf->MultiCell(60, 5, $outputlangs->transnoentities("PaymentsAlreadyDone"), 0, 'L', 0);

		$pdf->line($tab3_posx, $tab3_top-1+$tab3_height, $tab3_posx+$tab3_width, $tab3_top-1+$tab3_height);

		$pdf->SetFont('','', $default_font_size - 4);
		$pdf->SetXY ($tab3_posx, $tab3_top );
		$pdf->MultiCell(20, 3, $outputlangs->transnoentities("Payment"), 0, 'L', 0);
		$pdf->SetXY ($tab3_posx+21, $tab3_top );
		$pdf->MultiCell(20, 3, $outputlangs->transnoentities("Amount"), 0, 'L', 0);
		$pdf->SetXY ($tab3_posx+40, $tab3_top );
		$pdf->MultiCell(20, 3, $outputlangs->transnoentities("Type"), 0, 'L', 0);
		$pdf->SetXY ($tab3_posx+58, $tab3_top );
		$pdf->MultiCell(20, 3, $outputlangs->transnoentities("Num"), 0, 'L', 0);

		$y=0;

		$pdf->SetFont('','', $default_font_size - 4);

		// Loop on each payment
		$sql = "SELECT p.datep as date, p.fk_paiement as type, p.num_paiement as num, pf.amount as amount,";
		$sql.= " cp.code";
		$sql.= " FROM ".MAIN_DB_PREFIX."paiementfourn_facturefourn as pf, ".MAIN_DB_PREFIX."paiementfourn as p";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_paiement as cp ON p.fk_paiement = cp.id";
		$sql.= " WHERE pf.fk_paiementfourn = p.rowid and pf.fk_facturefourn = ".$object->id;
		$sql.= " ORDER BY p.datep";
		$resql=$this->db->query($sql);
		if ($resql)
		{
		    $num = $this->db->num_rows($resql);
			$i=0;
			while ($i < $num) {
			    $y+=3;
				$row = $this->db->fetch_object($resql);

				$pdf->SetXY ($tab3_posx, $tab3_top+$y );
				$pdf->MultiCell(20, 3, dol_print_date($this->db->jdate($row->date),'day',false,$outputlangs,true), 0, 'L', 0);
				$pdf->SetXY ($tab3_posx+21, $tab3_top+$y);
				$pdf->MultiCell(20, 3, price($row->amount), 0, 'L', 0);
				$pdf->SetXY ($tab3_posx+40, $tab3_top+$y);
				$oper = $outputlangs->getTradFromKey("PaymentTypeShort" . $row->code);

				$pdf->MultiCell(20, 3, $oper, 0, 'L', 0);
				$pdf->SetXY ($tab3_posx+58, $tab3_top+$y);
				$pdf->MultiCell(30, 3, $row->num, 0, 'L', 0);

				$pdf->line($tab3_posx, $tab3_top+$y+3, $tab3_posx+$tab3_width, $tab3_top+$y+3 );

				$i++;
			}
		}
		else
		{
			$this->error=$this->db->lasterror();
			dol_syslog($this->db,$this->error, LOG_ERR);
			return -1;
		}

	}

	/**
	 *   	\brief      Show header of page
	 *    	\param      pdf     		Object PDF
	 *      \param      object          Object invoice
	 *      \param      showadress      0=no, 1=yes
	 *      \param      outputlangs		Object lang for output
	 */
	function _pagehead(&$pdf, $object, $showadress=1, $outputlangs)
	{
		global $langs,$conf,$mysoc;

		$outputlangs->load("main");
		$outputlangs->load("bills");
		$outputlangs->load("orders");
		$outputlangs->load("companies");

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		// Do not add the BACKGROUND as this is for suppliers
		//pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);

		$pdf->SetTextColor(0,0,60);
		$pdf->SetFont('','B', $default_font_size + 3);

		$posy=$this->marge_haute;

		$pdf->SetXY($this->marge_gauche,$posy);

		// Logo
		/*
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
		else
		{*/
			$text=$this->emetteur->nom;
			$pdf->MultiCell(100, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
		//}

		$pdf->SetFont('','B', $default_font_size + 3);
		$pdf->SetXY(100,$posy);
		$pdf->SetTextColor(0,0,60);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("SupplierInvoice")." ".$outputlangs->convToOutputCharset($object->ref), '' , 'R');
		$pdf->SetFont('','', $default_font_size + 2);

		$posy+=6;
		$pdf->SetXY(100,$posy);
		if ($object->date)
		{
			$pdf->SetTextColor(0,0,60);
			$pdf->MultiCell(100, 4, $outputlangs->transnoentities("Date")." : " . dol_print_date($object->date,"day",false,$outputlangs,true), '', 'R');
		}
		else
		{
			$pdf->SetTextColor(255,0,0);
			$pdf->MultiCell(100, 4, strtolower($outputlangs->transnoentities("OrderToProcess")), '', 'R');
		}

		if ($showadress)
		{
			// Receive email
			$posy=42;
			$hautcadre=40;
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($this->marge_gauche,$posy-5);
			$pdf->MultiCell(66,5, $outputlangs->transnoentities("BillTo").":",0,"L");


			$pdf->SetXY($this->marge_gauche,$posy);
			$pdf->SetFillColor(230,230,230);
			$pdf->MultiCell(82, $hautcadre, "", 0, 'R', 1);



			// Nom emetteur
			$carac_emetteur_name=$outputlangs->convToOutputCharset($mysoc->nom);
			$pdf->SetTextColor(0,0,60);
			$pdf->SetFont('','B', $default_font_size);
			$pdf->SetXY($this->marge_gauche+2,$posy+3);
			$pdf->MultiCell(80, 4, $carac_emetteur_name, 0, 'L');

			// Sender properties
			$carac_emetteur = pdf_build_address($outputlangs,$mysoc);

			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetXY($this->marge_gauche+2,$posy+8);
			$pdf->MultiCell(80, 4, $carac_emetteur, 0, 'L');

			// Client destinataire
			$posy=42;
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY(100,$posy-5);
			$pdf->MultiCell(96, 4, $outputlangs->transnoentities("Supplier").":");
			//
			$client = new Societe($this->db);
			$client->fetch($object->socid);
			$object->client = $client;
			//

			// Cadre client destinataire
			$pdf->rect(100, $posy, 100, $hautcadre);

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

			// Show customer/recipient
			$pdf->SetXY(102,$posy+3);
			$pdf->SetFont('','B', $default_font_size);
			$pdf->MultiCell(96,4, $carac_client_name, 0, 'L');

			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetXY(102,$posy+8);
			$pdf->MultiCell(96,4, $carac_client, 0, 'L');
		}
	}

	/**
	 *   	\brief      Show footer of page
	 *   	\param      pdf     		PDF factory
	 * 		\param		object			Object invoice
	 *      \param      outputlang		Object lang for output
	 * 		\remarks	Need this->emetteur object
	 */
	function _pagefoot(&$pdf, $object, $outputlangs)
	{
		return pdf_pagefoot($pdf,$outputlangs,'SUPPLIER_INVOICE_FREE_TEXT',$this->emetteur,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object);
	}

}

?>
