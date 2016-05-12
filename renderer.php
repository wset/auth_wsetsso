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

class auth_wsetsso_renderer extends plugin_renderer_base {

    public function jquerybutton($endpointurl, $token, $skip) {
        global $CFG;
        $url = $endpointurl.'?token='.$token;

        echo '<script>';
        echo "$(document).ready(function(e) {";
        if($skip == 1) {
            $config = get_config('auth_wsetsso');
            echo "    window.addEventListener(\"message\", receivemessage, false);";
            echo "";
            echo "    function receivemessage(event)";
            echo "    {";
            echo "        var origin = event.origin || event.originalEvent.origin;"; // For Chrome, the origin property is in the event.originalEvent object.
            echo "        if (origin == \"".$config->messageoriginurl."\")";
            echo "          window.top.location.href = '{$url}&redirUrl=' + event.data;";
            echo "    }";
        }
        else {
            echo "    window.top.location.href = '{$url}';";

        }
        echo "});";
        echo "</script>";

        $message = get_string('pageshouldredirect');

        echo $this->notification($message, 'redirectmessage');
        echo '<div class="continuebutton"><a href="'.$url.'" target="_top">' . get_string('continue') . '</div>';

    }

}

