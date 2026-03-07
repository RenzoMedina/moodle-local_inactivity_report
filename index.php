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
 * Version metadata for the local_inactivity_report plugin.
 *
 * @package   local_inactivity_report
 * @copyright 2026 Renzo Medina <medinast30@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/inactivity_report/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_inactivity_report'));
$PAGE->set_heading(get_string('pluginname', 'local_inactivity_report'));

use local_inactivity_report\form\reportform;

$mform = new reportform();
$report = [];
$reportlist = [];
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/admin/search.php#linkreports'));
} else if ($data = $mform->get_data()) {
    $typecourse = $data->type;
    $lastacces = $data->filterdate;
    
    $report = $DB->get_records_sql(
        "SELECT u.id, u.firstname, u.lastname, u.email, c.fullname AS coursename, FROM_UNIXTIME(ul.timeaccess) AS lastaccess
        FROM {user} u
        JOIN {user_lastaccess} ul ON ul.userid = u.id
        JOIN {course} c ON c.id = ul.courseid
        WHERE ul.timeaccess < :filterdate AND ul.courseid = :courseid",
        ['filterdate' => $lastacces, 'courseid' => $typecourse]
    );
}

foreach ($report as $record) {
    $reportlist[] = [
        'fullname' => $record->firstname." ".$record->lastname,
        'email' => $record->email,
        'coursename' => $record->coursename,
        'lastaccess' => userdate(strtotime($record->lastaccess), get_string('strftimedatetime', 'langconfig')),
        'dayselapsed' => (int)((time() - $record->timeaccess) / DAYSECS),
    ];
}
echo $OUTPUT->header();

$templatedata = [
    'backurl' => (new moodle_url('/admin/search.php#linkreports'))->out(false),
    'reportform' => $mform->render(),
    'report' => $reportlist ?? [],
];
echo $OUTPUT->render_from_template('local_inactivity_report/main', $templatedata);
echo $OUTPUT->footer();
