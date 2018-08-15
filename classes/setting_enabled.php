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
 * Custom checkbox setting
 *
 * @package   local_selfregkey
 * @copyright 2018 Moonami, LLC
 * @author    Darko Miletic <dmiletic@moonami.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

namespace local_selfregkey;

use admin_setting_configcheckbox;
use lang_string;

defined('MOODLE_INTERNAL') || die();

/**
 * Class setting_enabled
 *
 * @package local_selfregkey
 * @copyright 2018 Moonami, LLC
 * @author    Darko Miletic <dmiletic@moonami.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class setting_enabled extends admin_setting_configcheckbox {

    public function __construct() {
        parent::__construct(
            helper::COMPONENT.'/enabled',
            new lang_string('enabled', 'core_admin'),
            new lang_string('enabled', 'core_admin'),
            '0'
        );
    }

    /**
     * @param  mixed $data
     * @return string
     */
    public function write_setting($data) {

        helper::set_field(((string)$data === $this->yes));

        return parent::write_setting($data);
    }

}
