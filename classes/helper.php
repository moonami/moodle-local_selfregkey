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

namespace local_selfregkey;

use admin_setting_configcheckbox;
use dml_exception;
use coding_exception;
use Exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/profile/definelib.php');

/**
 * Class helper
 *
 * @package   local_selfregkey
 * @copyright 2018 Moonami, LLC
 * @author    Darko Miletic <dmiletic@moonami.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
abstract class helper {

    const COMPONENT = 'local_selfregkey';
    const TABLE     = 'user_info_field';
    const SHORTNAME = 'localselfregkey';

    /**
     * In case there are no categories we add the default one.
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function initcategory() {
        global $DB;
        $resultid = null;
        $categories = $DB->get_records('user_info_category');
        if (empty($categories)) {
            $defaultcategory = new stdClass();
            $defaultcategory->name = get_string('profiledefaultcategory', 'admin');
            $defaultcategory->sortorder = 1;
            $resultid = $DB->insert_record('user_info_category', $defaultcategory);
        } else {
            reset($categories);
            $resultid = key($categories);
        }
        return $resultid;
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function install_field() {
        global $DB;
        try {
            $enabled = get_config(self::COMPONENT, 'enabled');

            // Create custom profile field.
            $DB->insert_record(
                self::TABLE,
                [
                    'shortname'         => self::SHORTNAME,
                    'name'              => get_string('signup_field_title', self::COMPONENT),
                    'categoryid'        => self::initcategory(),
                    'signup'            => $enabled,
                    'visible'           => PROFILE_VISIBLE_PRIVATE,
                    'required'          => $enabled,
                    'datatype'          => PARAM_TEXT,
                    'description'       => '',
                    'descriptionformat' => FORMAT_HTML,
                ]
            );
            profile_reorder_fields();
            profile_reorder_categories();
        } catch (Exception $e) {
            mtrace($e->getMessage());
        }
    }

    /**
     * @param  bool $enabled
     * @throws dml_exception
     */
    public static function set_field($enabled) {
        global $DB, $USER;
        $rid = $DB->get_field(self::TABLE, 'id', ['shortname' => self::SHORTNAME]);
        if ($rid) {
            if ($enabled) {
                self::update_nonselfuser($USER->id);
            }
            $DB->update_record(
                self::TABLE,
                (object)[
                    'id' => $rid,
                    'required' => $enabled,
                    'signup'   => $enabled,
                    'visible'  => PROFILE_VISIBLE_PRIVATE
                ]
            );
        }
    }

    /**
     * @param  int $userid
     * @throws dml_exception
     */
    public static function update_nonselfuser($userid) {
        global $DB;
        if ($userid) {
            $userobject = $DB->get_record('user', ['id' => $userid, 'deleted' => 0], 'id, auth');
            if ($userobject and ($userobject->auth !== 'email')) {
                profile_save_data((object)['id' => $userid, 'profile_field_'.self::SHORTNAME => '#']);
            }
        }
    }

    /**
     * @throws dml_exception
     */
    public static function delete_field() {
        global $DB;
        try {
            $rid = $DB->get_field(
                self::TABLE,
                'id',
                ['shortname' => self::SHORTNAME],
                IGNORE_MULTIPLE
            );
            if ($rid) {
                profile_delete_field($rid);
            }
        } catch (Exception $e) {
            mtrace($e->getMessage());
        }
    }

}
