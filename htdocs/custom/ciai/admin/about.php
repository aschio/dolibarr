<?php
/* Copyright (C) 2016 Claudio Aschieri <c.aschieri@19.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/about.php
 * 	\ingroup	ciai
 * 	\brief		This file is an example about page
 * 				
 */
// Dolibarr environment
$res = @include "../../main.inc.php"; // From htdocs directory
if (! $res) {
	$res = @include "../../../main.inc.php"; // From "custom" directory
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/ciai.lib.php';

// Use the .inc variant because we don't have autoloading support
require_once '../lib/php-markdown/Markdown.inc.php';

use Markdown;

//require_once "../class/ciai.class.php";
// Translations
$langs->load("ciai@ciai");

// Access control
if (! $user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

/*
 * View
 */
$page_name = "CiaiAbout";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
	. $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = ciaiAdminPrepareHead();
dol_fiche_head(
	$head,
	'about',
	$langs->trans("Module190980Name"),
	0,
	'ciai@ciai'
);

// About page goes here
echo '<br/><img src="' . dol_buildpath('/ciai/img/diciannove.png', 1) . '"/>';

echo '<br/>';

$buffer = file_get_contents(dol_buildpath('/ciai/README.md', 0));
echo Markdown($buffer);

echo '<br>',
'<a href="' . dol_buildpath('/ciai/COPYING', 1) . '">',
'<img src="' . dol_buildpath('/ciai/img/gplv3.png', 1) . '"/>',
'</a>';


// Page end
dol_fiche_end();
llxFooter();
