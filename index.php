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
require_capability('local/inactivity_report:view', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/inactivity_report/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_inactivity_report'));
$PAGE->set_heading(get_string('pluginname', 'local_inactivity_report'));

use local_inactivity_report\form\reportform;

$mform = new reportform();
$reportlist = [];
$page = optional_param('page', 0, PARAM_INT);
$perpage = (int)get_config('local_inactivity_report', 'maxresults') ?: 8;
$paginationbar = new paging_bar(0, 0, $perpage, $PAGE->url);
$total = 0;
$typecourse = optional_param('type', 0, PARAM_INT);
$lastacces = 0;

if (!isset($_POST['filterdate']) ) {
    $lastacces = optional_param('filterdate', 0, PARAM_INT);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/admin/search.php#linkreports'));
} else if ($data = $mform->get_data()) {
    $typecourse = $data->type;
    $lastacces = $data->filterdate;
}

$sql = "SELECT u.id, u.firstname, u.lastname, u.email, c.fullname AS coursename, ul.timeaccess
        FROM {user} u
        JOIN {user_lastaccess} ul ON ul.userid = u.id
        JOIN {course} c ON c.id = ul.courseid
        WHERE ul.timeaccess < :filterdate AND ul.courseid = :courseid ORDER BY ul.timeaccess ASC";
if ($typecourse && $lastacces) {
    $coursecontext = context_course::instance($typecourse);
    if (!has_capability('moodle/course:view', $coursecontext)) {
        throw new moodle_exception('nopermissions', 'error');
    }
    $params = ['filterdate' => $lastacces, 'courseid' => $typecourse];
    $total = $DB->count_records_sql(
            "SELECT COUNT(*)
            FROM {user} u
            JOIN {user_lastaccess} ul ON ul.userid = u.id
            JOIN {course} c ON c.id = ul.courseid
            WHERE ul.timeaccess < :filterdate AND ul.courseid = :courseid",
            ['filterdate' => $lastacces, 'courseid' => $typecourse]
            );
    $report = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);
    
    foreach ($report as $record) {
    $reportlist[] = [
        'fullname' => $record->firstname." ".$record->lastname,
        'email' => $record->email,
        'coursename' => $record->coursename,
        'lastaccess' => userdate($record->timeaccess, get_string('strftimedatetime', 'langconfig')),
        'dayselapsed' => (int)((time() - $record->timeaccess) / DAYSECS),
        ];
    }
    $paginationbar = new paging_bar($total, $page, $perpage, new moodle_url('/local/inactivity_report/index.php', ['type' => $typecourse, 'filterdate' => (int)$lastacces]));
}
$download = optional_param('download', '', PARAM_TEXT);
if (!empty($download) && $typecourse && $lastacces) {
    $validformats = ['csv', 'excel', 'ods'];
    if (!in_array($download, $validformats)) {
        $download = 'csv';
    }
    $params = ['filterdate' => $lastacces, 'courseid' => $typecourse];
    $report = $DB->get_records_sql($sql, $params);
    $exportdata = [];
    foreach ($report as $record) {
        $exportdata[] = [
            'fullname' => $record->firstname." ".$record->lastname,
            'email' => $record->email,
            'coursename' => $record->coursename,
            'lastaccess' => userdate($record->timeaccess, get_string('strftimedatetime', 'langconfig')),
            'dayselapsed' => (int)((time() - $record->timeaccess) / DAYSECS),
        ];
    }
    $columns = [
        'fullname' => get_string('fullname'),
        'email' => get_string('email'),
        'coursename' => get_string('coursename', 'local_inactivity_report'),
        'lastaccess' => get_string('lastaccess'),
        'dayselapsed' => get_string('dayselapsed', 'local_inactivity_report'),
    ];
    $filename = 'inactivity_report_' . date('Ymd');
    \core\dataformat::download_data($filename, $download, $columns, $exportdata);
    exit;
}
echo $OUTPUT->header();

$templatedata = [
    'backurl' => (new moodle_url('/admin/search.php#linkreports'))->out(false),
    'reportform' => $mform->render(),
    'report' => $reportlist ?? [],
    'haspagination' => $total > $perpage,
    'pagination' => $OUTPUT->render($paginationbar),
    'download_file' => (new moodle_url('/local/inactivity_report/index.php', ['type' => $typecourse, 'filterdate' => (int)$lastacces, 'download' => 'csv']))->out(false),
];
echo $OUTPUT->render_from_template('local_inactivity_report/main', $templatedata);
echo $OUTPUT->footer();
