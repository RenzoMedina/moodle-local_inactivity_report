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
 * Form definition for the inactivity report.
 *
 * @package   local_inactivity_report
 * @copyright 2026 Renzo Medina <medinast30@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_inactivity_report\form;
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Form definition for the inactivity report.
 *
 * @package   local_inactivity_report
 * @copyright 2026 Renzo Medina <medinast30@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reportform extends \moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;
        $courses = get_courses();
        $courseoptions = [];
        foreach ($courses as $course) {
            if ($course->id == SITEID) {
                continue; 
            }
            $courseoptions[$course->id] = format_string($course->fullname);
        }
        $mform->addElement('select',  'type',  get_string('selectedcourse', 'local_inactivity_report'), $courseoptions);
        $mform->addElement('date_selector', 'filterdate', get_string('lastaccessbefore', 'local_inactivity_report'));
        $mform->addElement('submit', 'submitbutton', get_string('generate', 'local_inactivity_report'));
    }
}


