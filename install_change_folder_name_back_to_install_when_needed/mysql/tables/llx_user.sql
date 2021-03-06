-- ============================================================================
-- Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2006-2009 Laurent Destailleur  <eldy@users.sourceforge.net>
-- Copyright (C) 2007-2009 Regis Houssin        <regis@dolibarr.fr>
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
-- $Id: llx_user.sql,v 1.5 2011/01/10 09:39:42 hregis Exp $
-- ===========================================================================

create table llx_user
(
  rowid             integer AUTO_INCREMENT PRIMARY KEY,
  datec             datetime,
  tms               timestamp,
  login             varchar(24) NOT NULL,
  entity            integer DEFAULT 1 NOT NULL,	-- multi company id
  pass              varchar(32),
  pass_crypted      varchar(128),
  pass_temp         varchar(32),			-- temporary password when asked for forget password
  name              varchar(50),
  firstname         varchar(50),
  office_phone      varchar(20),
  office_fax        varchar(20),
  user_mobile       varchar(20),
  email             varchar(255),
  admin             smallint DEFAULT 0,
  webcal_login      varchar(25),			-- TODO move to an extra table (ex: llx_extra_fields)
  phenix_login      varchar(25),			-- TODO move to an extra table (ex: llx_extra_fields)
  phenix_pass       varchar(128),			-- TODO move to an extra table (ex: llx_extra_fields)
  module_comm       smallint DEFAULT 1,
  module_compta     smallint DEFAULT 1,
  fk_societe        integer,
  fk_socpeople      integer,
  fk_member         integer,
  note              text DEFAULT NULL,
  datelastlogin     datetime,
  datepreviouslogin datetime,
  egroupware_id     integer,
  ldap_sid          varchar(255) DEFAULT NULL,
  openid            varchar(255),
  statut            tinyint DEFAULT 1,
  photo             varchar(255),     -- filename or url of photo
  lang              varchar(6)
)type=innodb;

-- 
-- List of codes for the field entity
--
-- 0 : superadmin or global user
-- 1 : first company user
-- 2 : second company user
-- 3 : etc...
--