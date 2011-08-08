<?php

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__) . '/../../config.php');

/**
 * Simple FN_Admin block config form definition
 *
 * @package    contrib
 * @subpackage block_FN_Admin
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Simple FN_admin block config form class
 *
 * @copyright 2011 MoodleFN
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_fn_admin_edit_form extends block_edit_form {

    protected function specific_definition($mform) {

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block_fn_admin'));

        //Config title for the block.
        $mform->addElement('text', 'config_title', get_string('setblocktitle', 'block_fn_admin'));
        $mform->setType('config_title', PARAM_MULTILANG);
        $mform->setDefault('config_title', get_string('pluginname', 'block_fn_admin'));
        $mform->addHelpButton('config_title', 'config_title', 'block_fn_admin');

        $hideshow = array(0 => get_string('hide'), 1 => get_string('show'));

        // control the visibility of the admin menu items
        $mform->addElement('select', 'config_showadminmenuitems', get_string('showadminmenuitems',
                            'block_fn_admin'), $hideshow);
        $mform->setDefault('config_showadminmenuitems', 1);
        $mform->addHelpButton('config_showadminmenuitems', 'config_showadminmenuitems',
                            'block_fn_admin');

        // control the visibility of the enrol/unenrol link
        $mform->addElement('select', 'config_showenrolunenollink',
                            get_string('showenrolunenollink', 'block_fn_admin'), $hideshow);
        $mform->setDefault('config_showenrolunenollink', 1);
        $mform->addHelpButton('config_showenrolunenollink', 'config_showenrolunenollink',
                            'block_fn_admin');

        // control the visibility of the moodle profile link
        $mform->addElement('select', 'config_showprofilelink',
                            get_string('showprofilelink', 'block_fn_admin'), $hideshow);
        $mform->setDefault('config_showprofilelink', 1);
        $mform->addHelpButton('config_showprofilelink', 'config_showprofilelink', 'block_fn_admin');

         // control the visibility of the moodle grade link
        $mform->addElement('select', 'config_showgradelink',
                            get_string('showgradelink', 'block_fn_admin'), $hideshow);
        $mform->setDefault('config_showgradelink', 1);
        $mform->addHelpButton('config_showgradelink', 'config_showgradelink', 'block_fn_admin');

        // control the visibility of the teacher tool section
        $mform->addElement('select', 'config_showteachertools',
                            get_string('showteachertools', 'block_fn_admin'), $hideshow);
        $mform->setDefault('config_showteachertools', 1);
        $mform->addHelpButton('config_showteachertools', 'config_showteachertools',
                            'block_fn_admin');
    }
}