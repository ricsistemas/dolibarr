-- ===================================================================
-- Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2009 Laurent Destailleur  <eldy@users.sourceforge.net>
-- Copyright (C) 2010 Juanjo Menent        <jmenent@2byte.es>
-- Copyright (C) 2010 Regis Houssin        <regis@dolibarr.fr>
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
-- $Id: llx_facturedet_rec.sql,v 1.6 2010/09/24 14:39:03 hregis Exp $
-- ===================================================================

create table llx_facturedet_rec
(
  rowid           integer AUTO_INCREMENT PRIMARY KEY,
  fk_facture      integer NOT NULL,
  fk_parent_line  integer NULL,
  fk_product      integer NULL,
  product_type    integer DEFAULT 0,
  description     text,
  tva_tx          double(6,3) DEFAULT 19.6, -- taux tva
  localtax1_tx    double(6,3) DEFAULT 0,    -- tax local tax 1
  localtax2_tx	  double(6,3) DEFAULT 0,    -- tax local tax 2
  qty             real,              -- quantity
  remise_percent  real DEFAULT 0,    -- pourcentage de remise
  remise          real DEFAULT 0,    -- montant de la remise
  subprice        double(24,8),              -- prix avant remise
  price           double(24,8),               -- prix final
  total_ht        double(24,8),	             	-- Total HT de la ligne toute quantity et incluant remise ligne et globale
  total_tva       double(24,8),	             	-- Total TVA de la ligne toute quantity et incluant remise ligne et globale
  total_localtax1 double(24,8) DEFAULT 0,		-- Total LocalTax1 for total quantity of line
  total_localtax2 double(24,8) DEFAULT 0,		-- total LocalTax2 for total quantity of line
  total_ttc       double(24,8),	             	-- Total TTC de la ligne toute quantity et incluant remise ligne et globale
  special_code    integer UNSIGNED DEFAULT 0,		-- code pour les lignes speciales
  rang            integer    DEFAULT 0				-- ordre d'affichage
)type=innodb;
