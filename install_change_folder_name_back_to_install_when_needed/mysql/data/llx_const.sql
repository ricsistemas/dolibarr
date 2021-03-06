-- Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2003      Jean-Louis Bergamo   <jlb@j1b.org>
-- Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
-- Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
-- Copyright (C) 2004      Guillaume Delecourt  <guillaume.delecourt@opensides.be>
-- Copyright (C) 2005-2010 Regis Houssin        <regis@dolibarr.fr>
-- Copyright (C) 2007 	   Patrick Raguin       <patrick.raguin@gmail.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
--
-- $Id: llx_const.sql,v 1.16 2010/12/18 03:53:30 eldy Exp $
--

--
-- Ne pas placer de commentaire en fin de ligne, ce fichier est parsé lors
-- de l'install et tous les sigles '--' sont supprimés.
--

--
-- Setup constants
--

-- Visible in misc page
insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_NOT_INSTALLED','1','chaine','Setup is running',1,0);
insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_FEATURES_LEVEL','0','chaine','Level of features to show (0=stable only, 1=stable+experimental, 2=stable+experimental+development',1,0);

-- Hidden and common to entities
insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_POPUP_CALENDAR','eldy','chaine','Popup calendar module',0,0);

insert into llx_const (name, value, type, note, visible, entity) values ('SYSLOG_FILE','DOL_DATA_ROOT/dolibarr.log','chaine','Directory where to write log file',0,0);
insert into llx_const (name, value, type, note, visible, entity) values ('SYSLOG_LEVEL','7','chaine','Level of debug info to show',0,0);

insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_MAIL_SMTP_SERVER','','chaine','Host or ip address for SMTP server',0,0);
insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_MAIL_SMTP_PORT','','chaine','Port for SMTP server',0,0);

insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_UPLOAD_DOC','2048','chaine','Max size for file upload (0 means no upload allowed)',0,0);

-- TODO deja initialise dans le module ?
insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_SEARCHFORM_SOCIETE','1','yesno','Show form for quick company search',0,0);
insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_SEARCHFORM_CONTACT','1','yesno','Show form for quick contact search',0,0);
insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_SEARCHFORM_PRODUITSERVICE' ,'1','yesno','Show form for quick product search',0,0);
insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_SEARCHFORM_ADHERENT','1','yesno','Show form for quick member search',0,0);

insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_CONFIRM_AJAX','1','chaine','Use Ajax popup to make confirmations',0,0);

-- Hidden but specific to one entity 
insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_MONNAIE','EUR','chaine','Monnaie',0,1);
insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_MAIL_EMAIL_FROM','dolibarr-robot@domain.com','chaine','EMail emetteur pour les emails automatiques Dolibarr',0,1);

--
-- IHM
--

insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_SIZE_LISTE_LIMIT','25','chaine','Longueur maximum des listes',0,0);
insert into llx_const (name, value, type, note, visible, entity) values ('MAIN_SHOW_WORKBOARD','1','yesno','Affichage tableau de bord de travail Dolibarr',0,0);

insert into llx_const (name, value, type, note, visible) values ('MAIN_MENU_BARRETOP','eldy_backoffice.php','chaine','Module de gestion de la barre de menu pour utilisateurs internes',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_MENUFRONT_BARRETOP','eldy_frontoffice.php','chaine','Module de gestion de la barre de menu pour utilisateurs externes',0);
--insert into llx_const (name, value, type, note, visible) values ('MAIN_MENU_BARRELEFT','eldy_backoffice.php','chaine','Module de gestion de la barre de menu gauche pour utilisateurs internes',0);
--insert into llx_const (name, value, type, note, visible) values ('MAIN_MENUFRONT_BARRELEFT','eldy_frontoffice.php','chaine','Module de gestion de la barre de menu gauche pour utilisateurs externes',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_MENU_SMARTPHONE','iphone_backoffice.php','chaine','Module de gestion de la barre de menu smartphone pour utilisateurs internes',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_MENUFRONT_SMARTPHONE','iphone_frontoffice.php','chaine','Module de gestion de la barre de menu smartphone pour utilisateurs externes',0);

insert into llx_const (name, value, type, note, visible) values ('MAIN_THEME','eldy','chaine','Default theme',0);

--
-- Delai tolerance
--
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_ACTIONS_TODO','7','chaine','Tolérance de retard avant alerte (en jours) sur actions planifiées non réalisées',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_ORDERS_TO_PROCESS','2','chaine','Tolérance de retard avant alerte (en jours) sur commandes clients non traitées',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_SUPPLIER_ORDERS_TO_PROCESS','7','chaine','Tolérance de retard avant alerte (en jours) sur commandes fournisseurs non traitées',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_PROPALS_TO_CLOSE','31','chaine','Tolérance de retard avant alerte (en jours) sur propales à cloturer',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_PROPALS_TO_BILL','7','chaine','Tolérance de retard avant alerte (en jours) sur propales non facturées',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_CUSTOMER_BILLS_UNPAYED','31','chaine','Tolérance de retard avant alerte (en jours) sur factures client impayées',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_SUPPLIER_BILLS_TO_PAY','2','chaine','Tolérance de retard avant alerte (en jours) sur factures fournisseur impayées',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_NOT_ACTIVATED_SERVICES','0','chaine','Tolérance de retard avant alerte (en jours) sur services à activer',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_RUNNING_SERVICES','0','chaine','Tolérance de retard avant alerte (en jours) sur services expirés',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_MEMBERS','31','chaine','Tolérance de retard avant alerte (en jours) sur cotisations adhérent en retard',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_TRANSACTIONS_TO_CONCILIATE','62','chaine','Tolérance de retard avant alerte (en jours) sur rapprochements bancaires à faire',0);


--
-- Tiers
--
insert into llx_const (name, value, type, note, visible, entity) values('SOCIETE_NOLIST_COURRIER','1','yesno','Liste les fichiers du repertoire courrier',0,0);
insert into llx_const (name, value, type, note, visible) values('SOCIETE_CODECLIENT_ADDON','mod_codeclient_leopard','yesno','Module to control third parties codes',0);
insert into llx_const (name, value, type, note, visible) values('SOCIETE_CODECOMPTA_ADDON','mod_codecompta_panicum','yesno','Module to control third parties codes',0);


--
-- Mail Mailing
--
insert into llx_const (name, value, type, note, visible) values ('MAILING_EMAIL_FROM','dolibarr@domain.com','chaine','EMail emmetteur pour les envois d emailings',0);


--
-- FCKEditor
--
insert into llx_const (name, value, type, note, visible) values ('FCKEDITOR_ENABLE_USER',       1,'yesno','Activation fckeditor sur notes utilisateurs',0);
insert into llx_const (name, value, type, note, visible) values ('FCKEDITOR_ENABLE_SOCIETE',    1,'yesno','Activation fckeditor sur notes societe',0);
insert into llx_const (name, value, type, note, visible) values ('FCKEDITOR_ENABLE_PRODUCTDESC',1,'yesno','Activation fckeditor sur notes produits',0);
insert into llx_const (name, value, type, note, visible) values ('FCKEDITOR_ENABLE_MEMBER',     1,'yesno','Activation fckeditor sur notes adherent',0);
insert into llx_const (name, value, type, note, visible) values ('FCKEDITOR_ENABLE_MAILING',    1,'yesno','Activation fckeditor sur emailing',0);

--
-- OsCommerce 1
--
insert into llx_const (name, value, type, note, visible) values ('OSC_DB_HOST','localhost','chaine', 'Host for OSC database for OSCommerce module 1', 0);


--
-- Modeles de numerotation et generation document
--
insert into llx_const (name, value, type, note, visible) values ('DON_ADDON_MODEL',     'html_cerfafr','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('PROPALE_ADDON',       'mod_propale_marbre','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('PROPALE_ADDON_PDF',   'azur','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('COMMANDE_ADDON',      'mod_commande_marbre','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('COMMANDE_ADDON_PDF',  'einstein','chaine', '',0);
insert into llx_const (name, value, type, note, visible) values ('COMMANDE_SUPPLIER_ADDON',      'mod_commande_fournisseur_muguet','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('COMMANDE_SUPPLIER_ADDON_PDF',  'muscadet','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('INVOICE_SUPPLIER_ADDON_PDF',  'canelle','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('EXPEDITION_ADDON',    'enlevement','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('EXPEDITION_ADDON_PDF','rouget','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('FICHEINTER_ADDON',    'pacific','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('FICHEINTER_ADDON_PDF','soleil','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('FACTURE_ADDON',       'terre','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('FACTURE_ADDON_PDF',   'crabe','chaine','',0);


--
-- Duree de validite des propales
--
insert into llx_const (name, value, type, note, visible) VALUES ('PROPALE_VALIDITY_DURATION',      '15', 'chaine', 'Durée de validitée des propales',0);


--
-- Action sur agenda
--
insert into llx_const (name, value, type, note, visible) values ('MAIN_AGENDA_ACTIONAUTO_COMPANY_CREATE','1','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_AGENDA_ACTIONAUTO_CONTRACT_VALIDATE','1','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_AGENDA_ACTIONAUTO_PROPAL_VALIDATE','1','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_AGENDA_ACTIONAUTO_PROPAL_SENTBYMAIL','1','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_AGENDA_ACTIONAUTO_ORDER_VALIDATE','1','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_AGENDA_ACTIONAUTO_ORDER_SENTBYMAIL','1','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_AGENDA_ACTIONAUTO_BILL_VALIDATE','1','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_AGENDA_ACTIONAUTO_BILL_PAYED','1','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_AGENDA_ACTIONAUTO_BILL_CANCEL','1','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_AGENDA_ACTIONAUTO_BILL_SENTBYMAIL','1','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_AGENDA_ACTIONAUTO_ORDER_SUPPLIER_VALIDATE','1','chaine','',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_AGENDA_ACTIONAUTO_BILL_SUPPLIER_VALIDATE','1','chaine','',0);
