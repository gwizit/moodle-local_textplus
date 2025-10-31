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

/**
 * Helper function to extract form data from wizard data
 * Only includes scalar values - excludes arrays which cause form errors
 *
 * @param stdClass $wizard_data Wizard data from cache
 * @return stdClass Form data with only scalar values
 */
function get_form_data_from_wizard($wizard_data) {
    $form_data = new stdClass();
    $form_data->searchterm = $wizard_data->searchterm;
    $form_data->casesensitive = $wizard_data->casesensitive;
    $form_data->executionmode = $wizard_data->executionmode;
    $form_data->replacementtext = $wizard_data->replacementtext;
    // Note: databaseitems and selecteditems are NOT included - they stay in cache only
    return $form_data;
}

admin_externalpage_setup('local_textplus_tool');

require_login();

// Require site administrator permission.
$system_context = context_system::instance();
if (!has_capability('moodle/site:config', $system_context)) {
    // Display error page for non-administrators.
    $PAGE->set_url(new moodle_url('/local/textplus/index.php'));
    $PAGE->set_context($system_context);
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

// Initialize cache for wizard data.
$cache = cache::make('local_textplus', 'wizarddata');

// Get default settings.
$default_search_term = get_config('local_textplus', 'defaultsearchterm');
$default_mode = get_config('local_textplus', 'defaultmode');

// Set defaults if not configured.
if ($default_search_term === false) {
    $default_search_term = '';
}
if ($default_mode === false) {
    $default_mode = 'preview';
}

// Get current step.
$step = optional_param('step', 1, PARAM_INT);
$back_btn = optional_param('backbtn', '', PARAM_ALPHA);
$next_btn = optional_param('nextbtn', '', PARAM_ALPHA);
$execute_btn = optional_param('executebtn', '', PARAM_ALPHA);

// Handle "Start Over" by clearing cache.
$start_over = optional_param('startover', '', PARAM_ALPHA);
if ($start_over) {
    $cache->delete('wizard');
    redirect($PAGE->url);
}

// Initialize or retrieve wizard data from cache.
$wizard_data = $cache->get('wizard');
if (!$wizard_data) {
    $wizard_data = new stdClass();
    $wizard_data->searchterm = $default_search_term;
    $wizard_data->casesensitive = 0;
    $wizard_data->executionmode = $default_mode;
    $wizard_data->replacementtext = '';
    $wizard_data->databaseitems = [];
    $wizard_data->selecteditems = [];
    $cache->set('wizard', $wizard_data);
}

// Prepare form custom data.
// Only pass scalar values to form - arrays cause htmlspecialchars errors
$form_data = get_form_data_from_wizard($wizard_data);

$custom_data = [
    'formdata' => $form_data,
    'step' => $step,
];

// Create form.
$mform = new \local_textplus\form\replacer_form(null, $custom_data);

// STEP 2: Handle content selection (uses custom HTML form, not moodleform)
if ($step == 2 && $next_btn) {
    require_sesskey();
    require_capability('moodle/site:config', context_system::instance());
    require_capability('local/textplus:manage', context_system::instance());
    
    // Get selected database items from submitted form - sanitize input.
    $selected_items = optional_param_array('database_items', [], PARAM_TEXT);
    
    // Validate at least one item is selected.
    if (empty($selected_items)) {
        redirect($PAGE->url . '?step=2', get_string('error_noitemsselected', 'local_textplus'),
            null, \core\output\notification::NOTIFY_ERROR);
    }
    
    // Validate and parse selections (format: table|id|field).
    $validated_selections = [];
    foreach ($selected_items as $item_key) {
        $parts = explode('|', $item_key);
        if (count($parts) === 3) {
            $validated_selections[] = [
                'table' => clean_param($parts[0], PARAM_ALPHANUMEXT),
                'id' => clean_param($parts[1], PARAM_INT),
                'field' => clean_param($parts[2], PARAM_ALPHANUMEXT)
            ];
        }
    }
    
    // Save validated selections to cache.
    $wizard_data = $cache->get('wizard');
    $wizard_data->selecteditems = $validated_selections;
    $cache->set('wizard', $wizard_data);
    
    // Move to step 3.
    $step = 3;
    $custom_data['step'] = $step;
    $custom_data['formdata'] = get_form_data_from_wizard($wizard_data);
    
    $mform = new \local_textplus\form\replacer_form(null, $custom_data);
}

// STEP 2: Handle back button separately
if ($step == 2 && $back_btn) {
    require_sesskey();
    $step = 1;
    $wizard_data = $cache->get('wizard');
    $custom_data['step'] = $step;
    $custom_data['formdata'] = get_form_data_from_wizard($wizard_data);
    $mform = new \local_textplus\form\replacer_form(null, $custom_data);
}

// STEP 3: Handle back button separately (before form validation)
if ($step == 3 && $back_btn) {
    require_sesskey();
    $step = 2;
    $wizard_data = $cache->get('wizard');
    $custom_data['step'] = $step;
    $custom_data['formdata'] = get_form_data_from_wizard($wizard_data);
    $mform = new \local_textplus\form\replacer_form(null, $custom_data);
}

// Handle form submission (for steps 1 and 3 only - step 2 handled above)
if ($from_form = $mform->get_data()) {
    require_sesskey();
    
    // Verify site administrator permission for all form submissions.
    if (!has_capability('moodle/site:config', context_system::instance())) {
        throw new moodle_exception('error_requiresiteadmin', 'local_textplus', '', null, 
            get_string('error_requiresiteadmin_formsubmission', 'local_textplus'));
    }
    
    // STEP 1: Search for text in database
    if ($step == 1 && !$back_btn) {
        require_capability('local/textplus:manage', context_system::instance());
        
        // Save search criteria to cache (already sanitized by moodle form).
        $wizard_data = $cache->get('wizard');
        $wizard_data->searchterm = $from_form->searchterm;
        $wizard_data->casesensitive = isset($from_form->casesensitive) ? $from_form->casesensitive : 0;
        
        $config = [
            'search_term' => $from_form->searchterm,
            'case_sensitive' => (bool)$from_form->casesensitive,
            'dry_run' => true,
        ];
        
        $replacer = new \local_textplus\replacer($config);
        $database_items = $replacer->find_text_in_database();
        
        // Store found database items in cache.
        $wizard_data->databaseitems = $database_items;
        $cache->set('wizard', $wizard_data);
        
        // Move to step 2.
        $step = 2;
        $custom_data['step'] = $step;
        $custom_data['formdata'] = get_form_data_from_wizard($wizard_data);
        $mform = new \local_textplus\form\replacer_form(null, $custom_data);
        
    // STEP 3: Execute text replacement
    } else if ($step == 3 && $execute_btn) {
        // Double-check site administrator permission for text replacement.
        if (!has_capability('moodle/site:config', context_system::instance())) {
            throw new moodle_exception('error_requiresiteadmin', 'local_textplus', '', null,
                get_string('error_requiresiteadmin_formsubmission', 'local_textplus'));
        }
        
        require_capability('local/textplus:manage', context_system::instance());
        confirm_sesskey();
        
        // Verify backup confirmation.
        if (empty($from_form->backupconfirm)) {
            redirect($PAGE->url . '?step=3', get_string('backupconfirm_required', 'local_textplus'),
                null, \core\output\notification::NOTIFY_ERROR);
        }
        
        // Get wizard data from cache.
        $wizard_data = $cache->get('wizard');
        
        // Save final options (already sanitized by form).
        $wizard_data->executionmode = $from_form->executionmode;
        $wizard_data->replacementtext = $from_form->replacementtext;
        $cache->set('wizard', $wizard_data);
        
        // Validate replacement text is provided.
        if (empty($from_form->replacementtext) && $from_form->replacementtext !== '0') {
            redirect($PAGE->url . '?step=3', get_string('error_noreplacementtext', 'local_textplus'),
                null, \core\output\notification::NOTIFY_ERROR);
        }
        
        // Create replacer instance with final configuration.
        $config = [
            'search_term' => $wizard_data->searchterm,
            'replacement_text' => $wizard_data->replacementtext,
            'case_sensitive' => (bool)$wizard_data->casesensitive,
            'dry_run' => ($wizard_data->executionmode === 'preview'),
        ];
        
        $replacer = new \local_textplus\replacer($config);
        
        // Get selected items from cache.
        $items_to_process = $wizard_data->selecteditems;
        
        // Process text replacements.
        $replacer->process_text_replacements($items_to_process);
        
        // Log operation.
        $replacer->log_operation($USER->id);
        
        // Trigger event.
        $event = \local_textplus\event\images_replaced::create([
            'context' => context_system::instance(),
            'other' => [
                'searchterm' => $wizard_data->searchterm,
                'replacementtext' => $wizard_data->replacementtext,
                'itemsreplaced' => $replacer->get_stats()['items_replaced'],
            ],
        ]);
        $event->trigger();
        
        // Display results.
        echo $OUTPUT->header();
        $renderer = $PAGE->get_renderer('local_textplus');
        echo $renderer->render_results($replacer, $items_to_process, $config['dry_run']);
        
        // Clear cache (Start Over button is rendered by the renderer).
        $cache->delete('wizard');
        
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
    
    // Credits.
    echo html_writer::tag('p', get_string('credits', 'local_textplus'), ['class' => 'alert alert-info']);
}

// Display the form only for steps 1 and 3 (step 2 uses custom HTML form).
if ($step != 2) {
    $mform->display();
}

// STEP 2: Display content selection checkboxes.
if ($step == 2 && !empty($wizard_data)) {
    // Verify user still has permission.
    if (!has_capability('moodle/site:config', context_system::instance())) {
        echo $OUTPUT->notification(
            get_string('error_requiresiteadmin', 'local_textplus'),
            \core\output\notification::NOTIFY_ERROR
        );
        echo $OUTPUT->footer();
        exit;
    }
    
    // Get renderer.
    $renderer = $PAGE->get_renderer('local_textplus');
    
    // Display step indicator using template.
    echo $renderer->render_step_indicator(2);
    
    $database_items = $wizard_data->databaseitems;
    
    if (empty($database_items)) {
        // Use template for no items found message.
        echo $renderer->render_no_items_found($wizard_data->searchterm);
    } else {
        // Use template for item selection.
        echo $renderer->render_item_selection($database_items, $wizard_data->searchterm);
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
