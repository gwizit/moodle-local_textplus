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
 * Text Replacer main interface
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions {@link https://gwizit.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     G Wiz IT Solutions
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_textplus_tool');

require_login();

// Require site administrator permission.
$systemcontext = context_system::instance();
if (!has_capability('moodle/site:config', $systemcontext)) {
    // Display error page for non-administrators.
    $PAGE->set_url(new moodle_url('/local/textplus/index.php'));
    $PAGE->set_context($systemcontext);
    $PAGE->set_title(get_string('pluginname', 'local_textplus'));
    $PAGE->set_heading(get_string('heading', 'local_textplus'));
    
    echo $OUTPUT->header();
    echo $OUTPUT->notification(
        get_string('error_requiresiteadmin', 'local_textplus'),
        \core\output\notification::NOTIFY_ERROR
    );
    echo $OUTPUT->footer();
    exit;
}

require_capability('local/textplus:view', context_system::instance());

$PAGE->set_url(new moodle_url('/local/textplus/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_textplus'));
$PAGE->set_heading(get_string('heading', 'local_textplus'));

// Get default settings.
$defaultsearchterm = get_config('local_textplus', 'defaultsearchterm');
$defaultmode = get_config('local_textplus', 'defaultmode');

// Set defaults if not configured.
if ($defaultsearchterm === false) {
    $defaultsearchterm = '';
}
if ($defaultmode === false) {
    $defaultmode = 'preview';
}

// Get current step.
$step = optional_param('step', 1, PARAM_INT);
$backbtn = optional_param('backbtn', '', PARAM_RAW);
$nextbtn = optional_param('nextbtn', '', PARAM_RAW);
$executebtn = optional_param('executebtn', '', PARAM_RAW);

// Handle "Start Over" by clearing session.
$startover = optional_param('startover', '', PARAM_RAW);
if ($startover) {
    unset($SESSION->textplus_wizard);
    redirect($PAGE->url);
}

// Initialize or retrieve session data.
if (!isset($SESSION->textplus_wizard)) {
    $SESSION->textplus_wizard = new stdClass();
    $SESSION->textplus_wizard->searchterm = $defaultsearchterm;
    $SESSION->textplus_wizard->casesensitive = 0;
    $SESSION->textplus_wizard->executionmode = $defaultmode;
    $SESSION->textplus_wizard->replacementtext = '';
    $SESSION->textplus_wizard->databaseitems = [];
    $SESSION->textplus_wizard->selecteditems = [];
}

// Prepare form custom data.
$formdata = clone $SESSION->textplus_wizard;
$customdata = [
    'formdata' => $formdata,
    'step' => $step,
];

// Create form.
$mform = new \local_textplus\form\replacer_form(null, $customdata);

// STEP 2: Handle content selection (uses custom HTML form, not moodleform)
if ($step == 2 && $nextbtn) {
    require_sesskey();
    require_capability('moodle/site:config', context_system::instance());
    require_capability('local/textplus:manage', context_system::instance());
    
    // Get selected database items from submitted form - sanitize input.
    $selecteditems = optional_param_array('database_items', [], PARAM_RAW);
    
    // Validate at least one item is selected.
    if (empty($selecteditems)) {
        redirect($PAGE->url . '?step=2', get_string('error_noitemsselected', 'local_textplus'),
            null, \core\output\notification::NOTIFY_ERROR);
    }
    
    // Validate and parse selections (format: table|id|field).
    $validatedselections = [];
    foreach ($selecteditems as $itemkey) {
        $parts = explode('|', $itemkey);
        if (count($parts) === 3) {
            $validatedselections[] = [
                'table' => clean_param($parts[0], PARAM_ALPHANUMEXT),
                'id' => clean_param($parts[1], PARAM_INT),
                'field' => clean_param($parts[2], PARAM_ALPHANUMEXT)
            ];
        }
    }
    
    // Save validated selections.
    $SESSION->textplus_wizard->selecteditems = $validatedselections;
    
    // Move to step 3.
    $step = 3;
    $customdata['step'] = $step;
    $customdata['formdata'] = $SESSION->textplus_wizard;
    
    $mform = new \local_textplus\form\replacer_form(null, $customdata);
}

// STEP 2: Handle back button separately
if ($step == 2 && $backbtn) {
    require_sesskey();
    $step = 1;
    $customdata['step'] = $step;
    $customdata['formdata'] = $SESSION->textplus_wizard;
    $mform = new \local_textplus\form\replacer_form(null, $customdata);
}

// STEP 3: Handle back button separately (before form validation)
if ($step == 3 && $backbtn) {
    require_sesskey();
    $step = 2;
    $customdata['step'] = $step;
    $customdata['formdata'] = $SESSION->textplus_wizard;
    $mform = new \local_textplus\form\replacer_form(null, $customdata);
}

// Handle form submission (for steps 1 and 3 only - step 2 handled above)
if ($fromform = $mform->get_data()) {
    require_sesskey();
    
    // Verify site administrator permission for all form submissions.
    if (!has_capability('moodle/site:config', context_system::instance())) {
        print_error('error_requiresiteadmin', 'local_textplus');
    }
    
    // STEP 1: Search for text in database
    if ($step == 1 && !$backbtn) {
        require_capability('local/textplus:manage', context_system::instance());
        
        // Save search criteria to session (already sanitized by moodle form).
        $SESSION->textplus_wizard->searchterm = $fromform->searchterm;
        $SESSION->textplus_wizard->casesensitive = isset($fromform->casesensitive) ? $fromform->casesensitive : 0;
        
        $config = [
            'search_term' => $fromform->searchterm,
            'case_sensitive' => (bool)$fromform->casesensitive,
            'dry_run' => true,
        ];
        
        $replacer = new \local_textplus\replacer($config);
        $databaseitems = $replacer->find_text_in_database();
        
        // Store found database items in session.
        $SESSION->textplus_wizard->databaseitems = $databaseitems;
        
        // Move to step 2.
        $step = 2;
        $customdata['step'] = $step;
        $customdata['formdata'] = $SESSION->textplus_wizard;
        $mform = new \local_textplus\form\replacer_form(null, $customdata);
        
    // STEP 3: Execute text replacement
    } else if ($step == 3 && $executebtn) {
        // Double-check site administrator permission for text replacement.
        if (!has_capability('moodle/site:config', context_system::instance())) {
            print_error('error_requiresiteadmin', 'local_textplus');
        }
        
        require_capability('local/textplus:manage', context_system::instance());
        confirm_sesskey();
        
        // Verify backup confirmation.
        if (empty($fromform->backupconfirm)) {
            redirect($PAGE->url . '?step=3', get_string('backupconfirm_required', 'local_textplus'),
                null, \core\output\notification::NOTIFY_ERROR);
        }
        
        // Save final options (already sanitized by form).
        $SESSION->textplus_wizard->executionmode = $fromform->executionmode;
        $SESSION->textplus_wizard->replacementtext = $fromform->replacementtext;
        
        // Validate replacement text is provided.
        if (empty($fromform->replacementtext) && $fromform->replacementtext !== '0') {
            redirect($PAGE->url . '?step=3', get_string('error_noreplacementtext', 'local_textplus'),
                null, \core\output\notification::NOTIFY_ERROR);
        }
        
        // Create replacer instance with final configuration.
        $config = [
            'search_term' => $SESSION->textplus_wizard->searchterm,
            'replacement_text' => $SESSION->textplus_wizard->replacementtext,
            'case_sensitive' => (bool)$SESSION->textplus_wizard->casesensitive,
            'dry_run' => ($SESSION->textplus_wizard->executionmode === 'preview'),
        ];
        
        $replacer = new \local_textplus\replacer($config);
        
        // Get selected items from session.
        $itemstoprocess = $SESSION->textplus_wizard->selecteditems;
        
        // Process text replacements.
        $replacer->process_text_replacements($itemstoprocess);
        
        // Log operation.
        $replacer->log_operation($USER->id);
        
        // Trigger event.
        $event = \local_textplus\event\images_replaced::create([
            'context' => context_system::instance(),
            'other' => [
                'searchterm' => $SESSION->textplus_wizard->searchterm,
                'replacementtext' => $SESSION->textplus_wizard->replacementtext,
                'itemsreplaced' => $replacer->get_stats()['items_replaced'],
            ],
        ]);
        $event->trigger();
        
        // Display results.
        echo $OUTPUT->header();
        $renderer = $PAGE->get_renderer('local_textplus');
        echo $renderer->render_results($replacer, $config['dry_run']);
        
        // Clear session (Start Over button is rendered by the renderer).
        unset($SESSION->textplus_wizard);
        
        echo $OUTPUT->footer();
        exit;
    }
}

// Display the form.
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('heading', 'local_textplus'));

// Show description only on step 1.
if ($step == 1) {
    echo html_writer::tag('p', get_string('description', 'local_textplus'));
    
    // Check GD library availability and display warning if missing.
    if (!\local_textplus\replacer::is_gd_available()) {
        echo $OUTPUT->notification(get_string('warning_nogd_detailed', 'local_textplus'),
            \core\output\notification::NOTIFY_WARNING);
    }
    
    // Credits.
    echo html_writer::tag('p', get_string('credits', 'local_textplus'), ['class' => 'alert alert-info']);
}

// Display the form only for steps 1 and 3 (step 2 uses custom HTML form).
if ($step != 2) {
    $mform->display();
}

// STEP 2: Display content selection checkboxes.
if ($step == 2 && !empty($SESSION->textplus_wizard)) {
    // Verify user still has permission.
    if (!has_capability('moodle/site:config', context_system::instance())) {
        echo $OUTPUT->notification(
            get_string('error_requiresiteadmin', 'local_textplus'),
            \core\output\notification::NOTIFY_ERROR
        );
        echo $OUTPUT->footer();
        exit;
    }
    
    // Display step indicator manually for step 2.
    echo '<div class="step-indicator mb-4">';
    echo '<ol class="list-inline">';
    $steps = [
        1 => get_string('step1_name', 'local_textplus'),
        2 => get_string('step2_name', 'local_textplus'),
        3 => get_string('step3_name', 'local_textplus'),
    ];
    foreach ($steps as $num => $name) {
        $class = 'list-inline-item badge ';
        if ($num == 2) {
            $class .= 'badge-primary';
        } else if ($num < 2) {
            $class .= 'badge-success';
        } else {
            $class .= 'badge-secondary';
        }
        echo '<li class="' . $class . '">' . $num . '. ' . s($name) . '</li>';
    }
    echo '</ol>';
    echo '</div>';
    
    $databaseitems = $SESSION->textplus_wizard->databaseitems;
    
    if (empty($databaseitems)) {
        echo $OUTPUT->notification(
            get_string('noitemsfound_desc', 'local_textplus', s($SESSION->textplus_wizard->searchterm)),
            \core\output\notification::NOTIFY_WARNING
        );
        
        // Show Start Over button when no items found.
        echo html_writer::div(
            html_writer::link(
                new moodle_url('/local/textplus/index.php', ['startover' => 1]), 
                get_string('startover', 'local_textplus'),
                ['class' => 'btn btn-primary']
            ),
            'mt-3'
        );
    } else {
        // Add custom CSS for step 2.
        echo html_writer::start_tag('style');
        echo '
            .item-list { background: #ffffff; border: 1px solid #dee2e6; border-radius: 6px; 
                        margin: 20px 0; max-height: 500px; overflow-y: auto; }
            .item { padding: 12px 15px; border-bottom: 1px solid #dee2e6; display: flex;
                        align-items: flex-start; gap: 10px; }
            .item:last-child { border-bottom: none; }
            .item:hover { background: #f8f9fa; }
            .item input[type="checkbox"] { margin-top: 3px; flex-shrink: 0; }
            .item-info { flex: 1; }
            .item-location { color: #0056b3; font-weight: 600; margin-bottom: 4px; }
            .item-table { color: #666; font-size: 0.9em; font-family: monospace; }
            .item-context { color: #666; font-size: 0.9em; margin-top: 2px; word-break: break-word; }
            .section-header { background: #f8f9fa; padding: 12px 15px; border-bottom: 2px solid #dee2e6;
                            font-weight: bold; margin-top: 20px; border-radius: 6px 6px 0 0; }
            .select-all-btn { margin: 10px 0; }
        ';
        echo html_writer::end_tag('style');
        
        echo html_writer::start_tag('form', [
            'method' => 'post',
            'action' => $PAGE->url->out(false),
            'id' => 'itemselectionform'
        ]);
        
        echo html_writer::tag('p', get_string('selectitemstoreplace', 'local_textplus'), 
            ['class' => 'lead']);
        
        // Database items section.
        if (!empty($databaseitems)) {
            echo html_writer::div(
                get_string('databaseitems', 'local_textplus'),
                'section-header'
            );
            
            echo html_writer::div(
                html_writer::link('#', get_string('selectall', 'local_textplus'), 
                    ['id' => 'select-all-items', 'class' => 'btn btn-sm btn-secondary']),
                'select-all-btn'
            );
            
            echo html_writer::start_tag('div', ['class' => 'item-list']);
            foreach ($databaseitems as $item) {
                // Create unique item key for checkbox value (format: table|id|field).
                $itemkey = s($item->table) . '|' . (int)$item->id . '|' . s($item->field);
                $checkboxid = 'item_' . md5($itemkey);
                
                echo html_writer::start_div('item');
                
                // Checkbox.
                echo html_writer::checkbox('database_items[]', $itemkey, false, '', 
                    ['class' => 'item-checkbox', 'id' => $checkboxid]);
                
                // Item info.
                echo html_writer::start_tag('label', ['for' => $checkboxid, 'style' => 'flex: 1; margin: 0; cursor: pointer;']);
                echo html_writer::div(s($item->location), 'item-location');
                echo html_writer::div(s($item->table) . '.' . s($item->field) . ' (ID: ' . (int)$item->id . ')', 'item-table');
                if (!empty($item->context_preview)) {
                    echo html_writer::div('"...' . s($item->context_preview) . '..."', 'item-context');
                }
                echo html_writer::end_tag('label');
                
                echo html_writer::end_div();
            }
            echo html_writer::end_tag('div');
            
            // JavaScript for select all.
            $selectalltext = addslashes_js(get_string('selectall', 'local_textplus'));
            $deselectalltext = 'Deselect All';
            $warningtext = addslashes_js(get_string('warning_selectall', 'local_textplus'));
            echo html_writer::script("
                document.getElementById('select-all-items').addEventListener('click', function(e) {
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input.item-checkbox');
                    var allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    
                    if (!allChecked) {
                        // Selecting all - show warning
                        alert('{$warningtext}');
                    }
                    
                    checkboxes.forEach(function(cb) {
                        cb.checked = !allChecked;
                    });
                    this.textContent = allChecked ? '{$selectalltext}' : '{$deselectalltext}';
                });
            ");
        }
        
        // Hidden fields to preserve step data.
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'step', 'value' => 2]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        
        // Buttons.
        echo html_writer::start_div('mt-3');
        echo html_writer::tag('button', get_string('back', 'local_textplus'), [
            'type' => 'submit',
            'name' => 'backbtn',
            'value' => '1',
            'class' => 'btn btn-secondary mr-2'
        ]);
        echo html_writer::tag('button', get_string('next', 'local_textplus'), [
            'type' => 'submit',
            'name' => 'nextbtn',
            'value' => '1',
            'class' => 'btn btn-primary'
        ]);
        echo html_writer::end_div();
        
        echo html_writer::end_tag('form');
    }
}

// Display directories info on step 1.
if ($step == 1) {
    echo html_writer::start_div('alert alert-info mt-3');
    echo html_writer::tag('strong', get_string('directoriesscanned', 'local_textplus'));
    echo html_writer::tag('p', get_string('directories_list', 'local_textplus'));
    echo html_writer::end_div();
}

echo $OUTPUT->footer();
