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
 * Text replacer form
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions {@link https://gwizit.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     G Wiz IT Solutions
 */

namespace local_textplus\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for text replacer tool
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class replacer_form extends \moodleform {

    /**
     * Form definition
     */
    public function definition() {
        $mform = $this->_form;
        $formdata = $this->_customdata['formdata'];
        $step = isset($this->_customdata['step']) ? $this->_customdata['step'] : 1;

        // Add step indicator.
        $mform->addElement('html', $this->render_step_indicator($step));

        if ($step == 1) {
            $this->definition_step1($formdata);
        } else if ($step == 2) {
            $this->definition_step2($formdata);
        } else if ($step == 3) {
            $this->definition_step3($formdata);
        }

        // Session key.
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUMEXT);

        // Current step.
        $mform->addElement('hidden', 'step', $step);
        $mform->setType('step', PARAM_INT);
    }

    /**
     * Step 1: Search criteria
     */
    protected function definition_step1($formdata) {
        $mform = $this->_form;

        // Section header.
        $mform->addElement('header', 'step1header', get_string('step1_header', 'local_textplus'));
        $mform->setExpanded('step1header', true);

        // Search term.
        $mform->addElement('text', 'searchterm', get_string('searchterm', 'local_textplus'),
            ['size' => '50']);
        $mform->setType('searchterm', PARAM_TEXT);
        $mform->addRule('searchterm', null, 'required', null, 'client');
        $mform->addHelpButton('searchterm', 'searchterm', 'local_textplus');
        $mform->setDefault('searchterm', $formdata->searchterm);

        // Case sensitive search option.
        $mform->addElement('advcheckbox', 'casesensitive',
            get_string('casesensitive', 'local_textplus'));
        $mform->addHelpButton('casesensitive', 'casesensitive', 'local_textplus');
        $mform->setDefault('casesensitive', isset($formdata->casesensitive) ? $formdata->casesensitive : 0);

        // Action button.
        $this->add_action_buttons(false, get_string('findbtn', 'local_textplus'));
    }

    /**
     * Step 2: Content selection
     */
    protected function definition_step2($formdata) {
        $mform = $this->_form;

        // Section header.
        $mform->addElement('header', 'step2header', get_string('step2_header', 'local_textplus'));
        $mform->setExpanded('step2header', true);

        // Display search criteria summary.
        $summary = \html_writer::div(
            \html_writer::tag('strong', get_string('searchterm', 'local_textplus') . ': ') . 
            s($formdata->searchterm),
            'alert alert-info'
        );
        $mform->addElement('html', $summary);

        // Content items will be displayed via custom rendering in index.php
        // This is just a placeholder for the form structure
        $mform->addElement('html', '<div id="content-selection-area"></div>');

        // Hidden fields to preserve step 1 data.
        $mform->addElement('hidden', 'searchterm', $formdata->searchterm);
        $mform->setType('searchterm', PARAM_TEXT);
        $mform->addElement('hidden', 'casesensitive', isset($formdata->casesensitive) ? $formdata->casesensitive : 0);
        $mform->setType('casesensitive', PARAM_INT);

        // Action buttons.
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'backbtn', get_string('back', 'local_textplus'));
        $buttonarray[] = $mform->createElement('submit', 'nextbtn', get_string('next', 'local_textplus'));
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }

    /**
     * Step 3: Replacement options and confirmation
     */
    protected function definition_step3($formdata) {
        $mform = $this->_form;

        // Section header.
        $mform->addElement('header', 'step3header', get_string('step3_header', 'local_textplus'));
        $mform->setExpanded('step3header', true);

        // Add instruction message showing what will be replaced.
        $instruction = \html_writer::div(
            get_string('enterreplacement_instruction', 'local_textplus', s($formdata->searchterm)),
            'alert alert-info'
        );
        $mform->addElement('html', $instruction);

        // Replacement text input.
        $mform->addElement('textarea', 'replacementtext', get_string('replacementtext', 'local_textplus'),
            ['rows' => 5, 'cols' => 50]);
        $mform->setType('replacementtext', PARAM_TEXT);
        $mform->addHelpButton('replacementtext', 'replacementtext', 'local_textplus');
        $mform->setDefault('replacementtext', isset($formdata->replacementtext) ? $formdata->replacementtext : '');

        // Execution mode.
        $modeoptions = [
            'preview' => get_string('mode_preview', 'local_textplus'),
            'execute' => get_string('mode_execute', 'local_textplus'),
        ];
        $mform->addElement('select', 'executionmode', get_string('executionmode', 'local_textplus'),
            $modeoptions);
        $mform->addHelpButton('executionmode', 'executionmode', 'local_textplus');
        $mform->setDefault('executionmode', $formdata->executionmode);

        // Backup confirmation checkbox.
        $mform->addElement('advcheckbox', 'backupconfirm',
            get_string('backupconfirm', 'local_textplus'));
        $mform->addRule('backupconfirm', get_string('backupconfirm_required', 'local_textplus'), 'required', null, 'client');
        $mform->addHelpButton('backupconfirm', 'backupconfirm', 'local_textplus');

        // Final warning.
        $warning = \html_writer::div(
            get_string('final_warning', 'local_textplus'),
            'alert alert-danger'
        );
        $mform->addElement('html', $warning);

        // Hidden fields to preserve previous steps data.
        $mform->addElement('hidden', 'searchterm', $formdata->searchterm);
        $mform->setType('searchterm', PARAM_TEXT);
        $mform->addElement('hidden', 'casesensitive', isset($formdata->casesensitive) ? $formdata->casesensitive : 0);
        $mform->setType('casesensitive', PARAM_INT);

        // Hidden field for selected items (will be populated from session).
        if (isset($formdata->selecteditems)) {
            $mform->addElement('hidden', 'selecteditems', $formdata->selecteditems);
            $mform->setType('selecteditems', PARAM_TEXT);
        }

        // Action buttons.
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'backbtn', get_string('back', 'local_textplus'));
        $buttonarray[] = $mform->createElement('submit', 'executebtn', get_string('execute_replacement', 'local_textplus'));
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->setType('backbtn', PARAM_ALPHANUMEXT);
        
        // Add start over link.
        $startoverlink = \html_writer::link(
            new \moodle_url('/local/textplus/index.php', ['startover' => 1]),
            get_string('startover', 'local_textplus'),
            ['class' => 'btn btn-secondary ml-2']
        );
        $mform->addElement('html', \html_writer::div($startoverlink, 'mt-2'));
    }

    /**
     * Render step indicator
     */
    protected function render_step_indicator($currentstep) {
        $steps = [
            1 => get_string('step1_name', 'local_textplus'),
            2 => get_string('step2_name', 'local_textplus'),
            3 => get_string('step3_name', 'local_textplus'),
        ];

        $html = '<div class="local-textplus-step-indicator mb-4">';
        $html .= '<ol class="list-inline">';
        foreach ($steps as $num => $name) {
            $class = 'list-inline-item local-textplus-badge ';
            if ($num == $currentstep) {
                $class .= 'local-textplus-badge-current';
            } else if ($num < $currentstep) {
                $class .= 'local-textplus-badge-completed';
            } else {
                $class .= 'local-textplus-badge-upcoming';
            }
            $html .= '<li class="' . $class . '">' . $num . '. ' . s($name) . '</li>';
        }
        $html .= '</ol>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Validation
     *
     * @param array $data Form data
     * @param array $files Form files
     * @return array Errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['searchterm'])) {
            $errors['searchterm'] = get_string('required');
        }

        return $errors;
    }
}
