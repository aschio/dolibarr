-- CIAI GESTIONALE
-- -- Copyright (C) 2016 Claudio Aschieri <c.aschieri@19.coop>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

CREATE TABLE llx_societe_plus (
	rowid INTEGER AUTO_INCREMENT PRIMARY KEY,
	fk_soc INTEGER NOT NULL,
	traduzione VARCHAR(1) DEFAULT 0 NOT NULL,		-- traduzione richiesta si/no
	sesso VARCHAR(1) DEFAULT 0 NOT NULL					-- sesso 0/1: femmina/maschio
)ENGINE=innodb;


