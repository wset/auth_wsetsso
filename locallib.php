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

defined('MOODLE_INTERNAL') || die();

// A token is deemed invalid after this many seconds
define('MAX_TOKEN_AGE', 3600);

/**
 * Create a connection token and store in database
 * (for current user)
 */
function auth_wsetsso_createtoken() {
    global $DB, $USER;

    // create sha1 token based on something randomish
    $token = sha1(microtime(true).mt_rand(10000,90000));

    // get the user's IP (they could have multiple logins)
    $ip = getremoteaddr();

    // there should be a current user
    if (!isset($USER->id)) {
        notice('User ID is not defined. Looks like login failed somehow');
        die;
    }
    $userid = $USER->id;

    // Create database record
    $record = new stdClass;
    $record->token = $token;
    $record->ip = $ip;
    $record->userid = $userid;
    $record->timeupdated = time();
    $DB->insert_record('auth_wsetsso_token', $record);

    return $token;
}

/**
 * Check the supplied token is valid
 * @param string $token
 * @return mixed false or $user object
 */
function auth_wsetsso_checktoken($token) {
    global $DB;

    if (!$record = $DB->get_record('auth_wsetsso_token', array('token' => $token))) {
        return false;
    }
    $user = $DB->get_record('user', array('id' => $record->userid), '*', MUST_EXIST);
    
    // The token must be within age limits
    if (time() > ($record->timeupdated + MAX_TOKEN_AGE)) {
        return false;
    }

    // All good so return user object
    return $user;
}

/**
 * Get a list of idnumbers of cohorts that a user is a member of
 * @param int $userid
 * @return array 
 */
function auth_wsetsso_cohorts($userid) {
    global $DB;

    // get cohort records
    $sql = "SELECT ch.* from {cohort} ch
        JOIN {cohort_members} cm ON (cm.cohortid = ch.id)
        WHERE userid = ?";
    $cohorts = $DB->get_records_sql($sql, array($userid)); 

    // Format array
    $cohortids = array();
    if ($cohorts) {
        foreach ($cohorts as $cohort) {
            if (!empty($cohort->idnumber)) {
                $cohortids[] = $cohort->idnumber;
            }
        }
    }

    // If required, add 'user enrolled anywhere' dummy cohort.
    $config = get_config('auth_wsetsso');
    if (!empty($config->enrolcohort)) {
        $courseids = enrol_get_users_courses($userid, true, 'id');
        if ($courseids) {
            $cohortids[] = $config->enrolcohort;
        }
    }
 
    return $cohortids;   
}
