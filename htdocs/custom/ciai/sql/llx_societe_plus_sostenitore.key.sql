-- <one line to give the program's name and a brief idea of what it does.>
-- Copyright (C) 2016 Claudio Aschieri <c.aschieri@19.coop>
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

ALTER TABLE llx_societe_plus_sostenitore ADD INDEX idx_societe_plus_fk_soc (fk_soc);
ALTER TABLE llx_societe_plus_sostenitore ADD CONSTRAINT fk_societe_plus_sostenitore_fk_soc FOREIGN KEY (fk_soc) REFERENCES llx_societe (rowid);

