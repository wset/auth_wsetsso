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
 * @package    auth_wsetsso
 * @copyright  2015 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . "/externallib.php");
require_once(dirname(__FILE__) . '/locallib.php');

class auth_wsetsso_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function checktoken_parameters() {
        return new external_function_parameters(
                array(
                    'token' => new external_value(PARAM_ALPHANUM, 'The SSO token (from Moodle login) to be authenticted', VALUE_REQUIRED),
                )
        );
    }

    /**
     * Returns user details if valid
     * @return string welcome message
     */
    public static function checktoken($token) {
        global $USER, $DB;


        //Parameter validation
        $params = self::validate_parameters(self::checktoken_parameters(), array(
            'token' => $token,
        ));

        //Context validation
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        // TODO Capability checking
        //if (!has_capability('moodle/user:viewdetails', $context)) {
        //    throw new moodle_exception('cannotviewprofile');
        //}

        // Validated parameters
        $token = $params['token'];

        // Check
        if ($user = auth_wsetsso_checktoken($token)) {
            $cohorts = auth_wsetsso_cohorts($user->id);
            $result = array(
                'valid' => 1,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'userid' => $user->id,
                'email' => $user->email,
                'cohorts' => $cohorts,
            );
        } else {
            $result = array(
                'valid' => 0,
                'firstname' => '',
                'lastname' => '',
                'userid' => '',
                'email' => '',
                'cohorts' => array(),
            );
        }
        
        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function checktoken_returns() {
        return new external_single_structure(
            array(
                'valid' => new external_value(PARAM_INT, '0=auth fail, 1=pass'),
                'firstname' => new external_value(PARAM_TEXT, 'User first name'),
                'lastname' => new external_value(PARAM_TEXT, 'User last name'),
                'userid' => new external_value(PARAM_INT, 'User (Moodle) internal id number'),
                'email' => new external_value(PARAM_EMAIL, 'User email'),
                'cohorts' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'Cohort idnumber'),
                    'List of cohort ids'
                ),
            )
         );
    }
}

