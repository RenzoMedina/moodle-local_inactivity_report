<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     local_inactivity_report
 * @category    admin
 * @copyright   2026 Renzo Medina <medinast30@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_inactivity_report_settings', get_string('pluginname', 'local_inactivity_report'));

    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    if ($ADMIN->fulltree) {
        // TO-DO: Define actual plugin settings page and add it to the tree - {@link https://docs.moodle.org/dev/Admin_settings}.

        // Example setting - a text field. this value is max number of results to show in the report.
        $settings->add(new admin_setting_configtext(
            'local_inactivity_report/maxresults',
            get_string('maxresults', 'local_inactivity_report'),
            get_string('maxresults_desc', 'local_inactivity_report'),
            8,
            PARAM_INT
        ));
    } 
    // Add the settings page to the admin tree under 'reports'.
   $ADMIN->add('reports', new admin_externalpage(
        'local_inactivity_report',
        get_string('pluginname', 'local_inactivity_report'),
        new moodle_url('/local/inactivity_report/index.php'),
        'moodle/site:config'
    ));
    // Add the settings page to the admin tree under 'localplugins'.
    $ADMIN->add('localplugins', $settings);
}
