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
 * Event listener
 *
 * @package   local_selfregkey
 * @copyright 2018 Moonami, LLC
 * @author    Darko Miletic <dmiletic@moonami.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

use local_selfregkey\helper;
use core\event\user_loggedin;
use core\event\user_created;

defined('MOODLE_INTERNAL') || die();

/**
 * @param  user_created $event
 * @throws dml_exception
 */
function local_selfregkey_user_created(user_created $event) {
    global $DB, $CFG;

    $enabled = get_config(helper::COMPONENT, 'enabled');
    if (!$enabled) {
        return;
    }

    require_once($CFG->dirroot.'/user/profile/lib.php');

    $userobject = $DB->get_record('user', ['id' => $event->userid, 'deleted' => 0]);
    $fields = profile_user_record($event->userid);
    if ($userobject and ($userobject->auth === 'email')) {

        complete_user_login($userobject);

        /** @var enrol_self_plugin $enrol */
        $enrol = enrol_get_plugin('self');
        $enrolplugins = $DB->get_records('enrol', ['enrol' => 'self', 'password' => $fields->{helper::SHORTNAME}]);
        foreach ($enrolplugins as $enrolplugin) {
            if ($enrol->can_self_enrol($enrolplugin) === true) {
                $data = new stdClass();
                $data->enrolpassword = $enrolplugin->password;
                $enrol->enrol_self($enrolplugin, $data);
            }
        }

        $enrolplugins = $DB->get_records_sql("
                SELECT e.*, g.enrolmentkey
                  FROM {groups} g
                  JOIN {enrol}  e ON e.courseid = g.courseid AND e.enrol = :enrol AND e.customint1 = :enabled
                 WHERE g.enrolmentkey = :enrolkey",
            ['enrol' => 'self', 'enabled' => 1, 'enrolkey' => $fields->{helper::SHORTNAME}]
        );
        foreach ($enrolplugins as $enrolplugin) {
            if ($enrol->can_self_enrol($enrolplugin) === true) {

                $data = new stdClass();
                // A $data should keep the group enrolment key according to implementation of,
                // Method $enrol_self_plugin->enrol_self.
                $data->enrolpassword = $enrolplugin->enrolmentkey;
                $enrol->enrol_self($enrolplugin, $data);
            }
        }

        require_logout();
    }
}

/**
 * In case plugin is enabled we silently update required custom field with dummy value.
 *
 * @param  user_loggedin $event
 * @throws dml_exception
 */
function local_selfregkey_user_loggedin(user_loggedin $event) {
    if (get_config(helper::COMPONENT, 'enabled')) {
        helper::update_nonselfuser($event->userid);
    }
}
