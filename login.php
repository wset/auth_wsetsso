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

require(dirname(__FILE__) . '/../../config.php');
require(dirname(__FILE__) . '/locallib.php');

require_login();


$context = context_system::instance();
$PAGE->set_context($context);
$url = new moodle_url('/auth/wsetsso/login.php');
$PAGE->set_url($url);
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('logintitle', 'auth_wsetsso'));
$PAGE->set_heading(get_string('loginheading', 'auth_wsetsso'));
$PAGE->requires->jquery();

$output = $PAGE->get_renderer('auth_wsetsso');


// Config for auth_wsetsso
$config = get_config('auth_wsetsso');

echo $OUTPUT->header();

// if we got here then can generate a new token
$token = auth_wsetsso_createtoken();

//echo $output->loginmessage();
echo $output->jquerybutton($config->endpointurl, $token);
echo $OUTPUT->footer();




