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
 * wsetsso authentication login - not a real login module
 * container for sso malarky
 *
 * @package auth_wsetsso
 * @author Howard Miller
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

/**
 * Plugin for no authentication - disabled user.
 */
class auth_plugin_wsetsso extends auth_plugin_base {

    /**
     * Constructor.
     */
    function auth_plugin_wsetsso() {
        $this->authtype = 'wsetsso';
    }

    /**
     * Hook into login/index.php to force redirect after login
     */
    function loginpage_hook() {
        global $CFG;
        global $SESSION;
        global $PAGE;

        // Param 'wsetsso' must be '1' so we know this is an SSO login
        $wsetsso = optional_param('wsetsso', 0, PARAM_INT);
        $ssotheme = get_config('auth_wsetsso')->ssotheme;
        if ($wsetsso) {
            $url = $CFG->httpswwwroot . '/auth/wsetsso/login.php';
            $SESSION->wantsurl = $url;
            $SESSION->wsetsso = true;

            // Change session theme for SSO.
            if(!empty($ssotheme)) {
                try {
                    $themeconfig = theme_config::load($ssotheme);
                    // Makes sure the theme can be loaded without errors.
                    if ($themeconfig->name === $ssotheme) {
                        $SESSION->theme = $ssotheme;
                    }
                    unset($themeconfig);
                    unset($ssotheme);
                } catch (Exception $e) {
                    debugging('Failed to set the theme from the URL.', DEBUG_DEVELOPER, $e->getTrace());
                }
            }

            // add class to login page (maybe)
            $PAGE->add_body_class('wsetsso');
            if (isloggedin() and !isguestuser()) {
                // if user is already logged in redirect straight to token generation.
                redirect($url);
            }
        } else if (isset($SESSION->theme) && $SESSION->theme === $ssotheme) {
            // if we're not using the SSO unset the sso session theme.
            unset($SESSION->theme);
        }
    }

    /**
     * Hook for overriding behaviour of logout page.
     * This method is called from login/logout.php page for all enabled auth plugins.
     *
     * @global object
     * @global string
     */
    function logoutpage_hook() {
        global $USER;     // use $USER->auth to find the plugin used for login
        global $redirect; // can be used to override redirect after logout
        global $SESSION;

        // Redirect only if we arrived via SSO
        if (!empty($SESSION->wsetsso)) {
            $config = get_config('auth_wsetsso');
            if (!empty($config->logouturl)) {
                $redirect = $config->logouturl;
            }
        }
    }

    /**
     * Do not allow any login.
     * We should never be here (as no logins should have this type)
     */
    function user_login($username, $password) {
        return false;
    }

    /**
     * No password updates.
     */
    function user_update_password($user, $newpassword) {
        return false;
    }

    function prevent_local_passwords() {
        // just in case, we do not want to loose the passwords
        return false;
    }

    /**
     * No external data sync.
     *
     * @return bool
     */
    function is_internal() {
        //we do not know if it was internal or external originally
        return true;
    }

    /**
     * No changing of password.
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * No password resetting.
     */
    function can_reset_password() {
        return false;
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    function can_be_manually_set() {
        return true;
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $config An object containing all the data for this page.
     * @param string $error
     * @param array $user_fields
     * @return void
     */
    function config_form($config, $err, $user_fields) {
        include 'config.html';
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     *
     * @param stdClass $config
     * @return void
     */
    function process_config($config) {
        // Set to defaults if undefined.
        if (!isset($config->endpointurl)) {
            $config->endpointurl = 'http://my.moodle.com/auth/wsetsso/wsetcms/index.php';
        }
        if (!isset($config->logouturl)) {
            $config->logouturl = '';
        }
        if (!isset($config->enrolcohort)) {
            $config->enrolcohort = 'moodle_enrols';
        }
        if (!isset($config->ssotheme)) {
            $config->ssotheme = '';
        }

        // Save settings.
        set_config('endpointurl', $config->endpointurl, 'auth_wsetsso');
        set_config('logouturl', $config->logouturl, 'auth_wsetsso');
        set_config('enrolcohort', $config->enrolcohort, 'auth_wsetsso');
        set_config('ssotheme', $config->ssotheme, 'auth_wsetsso');
        return true;
    }
}


