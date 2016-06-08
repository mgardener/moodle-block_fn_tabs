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
 * @package    format_ned_tabs
 * @copyright  Michael Gardener <mgardener@cissq.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/course/edit_form.php');

class course_ned_tabs_edit_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $OUTPUT;
        $mform = &$this->_form;

        $course = $this->_customdata['course'];

        if (!empty($course->id)) {
            $coursecontext = context_course::instance($course->id);
            $context = $coursecontext;
        }

        $mform->addElement('hidden', 'id', $this->_customdata['course']->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'defaulttabwhenset', time());
        $mform->setType('defaulttabwhenset', PARAM_INT);

        $mform->addElement('header', 'fncoursetabs', 'Tabs');

        $showhideoptions = array(
            '1' => get_string('show', 'format_ned_tabs'),
            '0' => get_string('hide', 'format_ned_tabs')
        );
        $mform->addElement('select', 'showtabs', get_string('tabs', 'format_ned_tabs'),
            $showhideoptions);
        $mform->setDefault('showtabs', 1);

        $mform->addElement('select', 'completiontracking', get_string('completiontracking', 'format_ned_tabs'),
            $showhideoptions);

        // For mainheading for the course.
        $label = get_string('mainheading', 'format_ned_tabs');
        $mform->addElement('text', 'mainheading', $label, 'maxlength="24" size="25"');
        $mform->setDefault('mainheading', get_string('defaultmainheading', 'format_ned_tabs'));
        $mform->setType('mainheading', PARAM_TEXT);

        // For topic heading for example Week Section.
        $label = get_string('topicheading', 'format_ned_tabs');
        $mform->addElement('text', 'topicheading', $label, 'maxlength="24" size="25"');
        $mform->setDefault('topicheading', get_string('defaulttopicheading', 'format_ned_tabs'));
        $mform->setType('topicheading', PARAM_TEXT);

        $tabcontentoptions = array(
            'usesectionnumbers' => get_string('usesectionnumbers', 'format_ned_tabs'),
            'usesectiontitles' => get_string('usesectiontitles', 'format_ned_tabs'),
            'usecustomtext' => get_string('usecustomtext', 'format_ned_tabs')
        );
        $mform->addElement('select', 'tabcontent', get_string('tabcontent', 'format_ned_tabs'), $tabcontentoptions);

        $tabwidthoptions = array(
            'equalspacing' => get_string('equalspacing', 'format_ned_tabs'),
            'textwidth' => get_string('textwidth', 'format_ned_tabs'),
        );
        $mform->addElement('select', 'tabwidth', get_string('tabwidth', 'format_ned_tabs'), $tabwidthoptions);

        // For changing the number of tab to show before next link.
        $numberoftabs = array();
        for ($i = 12; $i <= 20; $i++) {
            $numberoftabs[$i] = $i;
        }

        $mform->addElement('select', 'maxtabs', get_string('setnumberoftabs', 'format_ned_tabs'), $numberoftabs);
        $mform->setDefault('maxtabs', $numberoftabs[12]);

        // Work to be done for default tab.
        $radioarray = array();
        $attributes = array();
        $radioarray[] = $mform->createElement('radio', 'defaulttab', '',
            get_string('default_tab_text', 'format_ned_tabs'), 'option1',
            array('checked' => true, 'class' => 'padding_before_radio', 'style' => 'padding-left:10px;')
        );
        // Add second option if the course completion is enabled.
        $completion = new completion_info($course);
        if ($completion->is_enabled()) {
            $radioarray[] = $mform->createElement('radio', 'defaulttab', '',
                get_string('default_tab_notattempted_text', 'format_ned_tabs'), 'option2');
        }

        $radioarray[] = $mform->createElement('radio', 'defaulttab', '',
            get_string('default_tab_specifyweek_text', 'format_ned_tabs'), 'option3');
        $mform->addGroup($radioarray, 'radioar', get_string('label_deafulttab_text', 'format_ned_tabs'), array('<br />'), false);
        $mform->setDefault('defaulttab', 'option1');

        $timenow = time();
        $weekdate = $course->startdate;
        $weekdate += 7200;
        $weekofseconds = 604800;
        $course->enddate = $course->startdate + ($weekofseconds * $course->numsections);

        // Calculate the current week based on today's date and the starting date of the course.
        $currentweek = ($timenow > $course->startdate) ? (int) ((($timenow - $course->startdate) / $weekofseconds) + 1) : 0;

        $currentweek = min($currentweek, $course->numsections);
        $topiclist = array();
        if ($currentweek > 0) {
            for ($i = 1; $i <= $currentweek; $i++) {
                $topiclist[$i] = $i;
            }
        } else {
            $topiclist[1] = 1;
        }

        $mform->addElement('select', 'topictoshow', '', $topiclist, array('class' => 'ddl_padding'));
        $mform->setDefault('topictoshow', $topiclist[1]);

        $mform->addElement('header', 'fncoursecolours', 'Colors');

        $colorschemaoptions = $DB->get_records_menu('format_ned_tabs_color');

        $saveasarray = array();
        $saveasarray[] = &$mform->createElement('select', 'colorschema', '', $colorschemaoptions);
        $saveasarray[] = &$mform->createElement('button', 'managecolorschemas',
            get_string('managecolorschemas', 'format_ned_tabs')
        );
        $mform->addGroup($saveasarray, 'saveasarr', get_string('loadcolorschema', 'format_ned_tabs'), array(' '), false);

        $mform->addElement('header', 'sections', get_string('sections', 'format_ned_tabs'));

        $choices['0'] = get_string("hide");
        $choices['1'] = get_string("show");
        $label = get_string('showsection0', 'format_ned_tabs');
        $mform->addElement('select', 'showsection0', $label, $choices);
        $mform->setDefault('showsection0', $choices['0']);
        unset($choices);

        $choices['0'] = get_string("no");
        $choices['1'] = get_string("yes");
        $label = get_string('showonlysection0', 'format_ned_tabs');
        $mform->addElement('select', 'showonlysection0', $label, $choices);
        $mform->setDefault('showonlysection0', $choices['0']);
        unset($choices);

        $activitytrackingbackgroundoptions = array(
            '1' => get_string('show', 'format_ned_tabs'),
            '0' => get_string('hide', 'format_ned_tabs')
        );
        $mform->addElement('select', 'activitytrackingbackground',
            get_string('activitytrackingbackground', 'format_ned_tabs'), $activitytrackingbackgroundoptions
        );

        $locationoftrackingiconsoptions = array(
            'moodleicons' => get_string('moodleicons', 'format_ned_tabs'),
            'nediconsleft' => get_string('nediconsleft', 'format_ned_tabs'),
            'nediconsright' => get_string('nediconsright', 'format_ned_tabs'),
        );
        $mform->addElement('select', 'locationoftrackingicons',
            get_string('locationoftrackingicons', 'format_ned_tabs'), $locationoftrackingiconsoptions
        );

        $this->add_action_buttons();
    }

}