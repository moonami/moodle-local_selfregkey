<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Install script
 *
 * @package   local_selfregkey
 * @copyright 2018 Moonami, LLC
 * @author    Darko Miletic <dmiletic@moonami.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

use local_selfregkey\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Preinstall profile field.
 *
 * @throws coding_exception
 * @throws dml_exception
 */
function xmldb_local_selfregkey_install() {
    helper::install_field();
}
