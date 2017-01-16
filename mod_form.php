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
 * The main collaborativefolders configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_collaborativefolders
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_collaborativefolders\folder_generator;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/repository/sciebo/lib.php');
require_once($CFG->libdir.'/webdavlib.php');
require_once($CFG->dirroot.'/lib/setuplib.php');

/**
 * Module instance settings form
 *
 * @package    mod_collaborativefolders
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_collaborativefolders_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $PAGE, $COURSE;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('collaborativefoldersname', 'collaborativefolders'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'collaborativefoldersname', 'collaborativefolders');
        $mform->addElement('text', 'foldername', get_string('fieldsetgroups', 'collaborativefolders'), array('size' => '64'));
        $mform->setType('foldername', PARAM_RAW_TRIMMED);
        $mform->addRule('foldername', null, 'required', null, 'client');
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }
        $renderer = $PAGE->get_renderer('mod_collaborativefolders');
        $mform->addElement('header', 'groupmodus', get_string('createforall', 'collaborativefolders'));
        $mform->addElement('checkbox', 'Groupmode', 'Enable Groupmode');
        $arrayofgroups = $this->get_relevant_fields();
        foreach ($arrayofgroups as $id => $group){
            $mform->addElement('advcheckbox', $group['id'] , $group['name'], ' Number of participants: ' . $group['numberofparticipants'], array(), array(0, 1));
        }

        // TODO do we need Grades for colaborative Folders?
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
    public function get_all_groups() {
        global $DB;
        // TODO for Performance reasons only get neccessary record
        return $DB->get_records('groups');
    }

    public function get_relevant_fields() {
        $allgroups = $this->get_all_groups();
        $relevantinformation = array();
        foreach ($allgroups as $key => $group) {
            $relevantinformation[$key]['name'] = $group->name;
            $relevantinformation[$key]['id'] = $group->id;
            $numberofparticipants = count(groups_get_members($group->id));
            $relevantinformation[$key]['numberofparticipants'] = $numberofparticipants;
        }
        return $relevantinformation;

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $foldergenerator = new folder_generator();
        /*if ($foldergenerator->check_for_404_error($data['foldername']) == false) {
            $errors['timeviewto'] = get_string('viewtodatevalidation', 'data');
        }*/
    }
}
