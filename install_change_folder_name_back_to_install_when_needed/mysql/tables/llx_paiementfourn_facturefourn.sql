-- ============================================================================
-- Copyright (C) 2003 Rodolphe Quiedeville  <rodolphe@quiedeville.org>
-- Copyright (C) 2005 Marc Barilley / Oc�bo <marc@ocebo.com>
-- Copyright (C) 2005 Laurent Destailleur   <eldy@users.sourceforge.net>
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
-- $Id: llx_paiementfourn_facturefourn.sql,v 1.3 2011/01/16 01:02:10 eldy Exp $
-- ===========================================================================

create table llx_paiementfourn_facturefourn
(
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  fk_paiementfourn INTEGER DEFAULT NULL,
  fk_facturefourn  INTEGER DEFAULT NULL,
  amount DOUBLE DEFAULT '0'
)type=innodb;