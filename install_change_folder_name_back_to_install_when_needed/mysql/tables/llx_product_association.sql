-- ============================================================================
-- Copyright (C) 2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2010 Laurent Destailleur  <eldy@users.sourceforge.net>
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
-- $Id: llx_product_association.sql,v 1.1 2010/07/10 22:55:55 eldy Exp $
-- ============================================================================

create table llx_product_association
(
  rowid                 integer AUTO_INCREMENT PRIMARY KEY,
  fk_product_pere       integer NOT NULL DEFAULT 0, -- id du produit maitre
  fk_product_fils       integer NOT NULL DEFAULT 0, -- id du sous-produit
  qty                   double NULL
)type=innodb;

