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
 * English language strings for TextPlus plugin
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions {@link https://gwizit.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     G Wiz IT Solutions
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TextPlus';
$string['textplus:manage'] = 'Manage text replacement operations';
$string['textplus:view'] = 'View TextPlus tool';

// Main page strings.
$string['heading'] = 'TextPlus - Database Text Search & Replace Tool';
$string['description'] = 'This powerful Moodle plugin helps administrators search and selectively replace text across their entire Moodle database. What makes it different from Moodle\'s built-in tools or other plugins is that it lets you preview every change before it happens. You can see exactly which code snippets and text will be updated, giving you full control and peace of mind. With its easy step-by-step wizard and strong security features, you can safely update course content, activities, pages, and labels with confidence.<br><br><strong style="color: #d32f2f;">⚠️ IMPORTANT WARNING:</strong> This is a powerful tool that makes <strong>permanent changes</strong> to your Moodle database. Administrators should use this plugin with <strong>extreme caution</strong>. <strong>ALWAYS create a complete database backup</strong> before using this tool. Text replacements <strong>cannot be undone</strong>. Test in dry run mode first, then verify results carefully before executing changes on a production site.';
$string['searchterm'] = 'Search text';
$string['searchterm_help'] = 'Enter the text string you want to find in the database. You can use the asterisk (*) as a wildcard to match any characters. For example: "http://*.example.com" will match "http://www.example.com", "http://blog.example.com", etc. Case-sensitive search can be toggled below.';
$string['replacementtext'] = 'Replacement text';
$string['replacementtext_help'] = 'Enter the text that will replace all instances of the search text. Leave empty to remove the search text entirely.';
$string['casesensitive'] = 'Case sensitive search';
$string['casesensitive_help'] = 'When enabled, the search will match case exactly (e.g., "Moodle" will not match "moodle"). When disabled, all case variations will be matched.';
$string['searchdatabase'] = 'Search database content';
$string['searchdatabase_help'] = 'Search Moodle\'s database for text content in pages, activities, course descriptions, labels, and other text fields.';

// Execution mode options.
$string['mode_preview'] = 'Dry run (safe - no changes)';
$string['mode_execute'] = 'Execute changes (will modify database)';

// Button labels.
$string['findbtn'] = 'Find matching text';
$string['replacebtn'] = 'Replace text';
$string['startover'] = 'Start over';
$string['back'] = 'Back';
$string['next'] = 'Next';
$string['execute_replacement'] = 'Execute Replacement';

// Multi-step wizard.
$string['step1_name'] = 'Search Criteria';
$string['step2_name'] = 'Content Selection';
$string['step3_name'] = 'Replacement Options';
$string['step1_header'] = 'Step 1: Define Search Criteria';
$string['step2_header'] = 'Step 2: Select Content to Replace';
$string['step3_header'] = 'Step 3: Replacement Options and Confirmation';
$string['backupconfirm'] = 'I confirm that a recent database backup has been made';
$string['backupconfirm_help'] = 'You must confirm that you have a recent database backup before proceeding with text replacement. This operation cannot be undone.';
$string['backupconfirm_required'] = 'You must confirm that a database backup has been made before proceeding';
$string['final_warning'] = '<strong>WARNING:</strong> This operation will permanently replace text in the selected database records. Make sure you have a recent database backup before proceeding. This action cannot be undone!';
$string['noitemsselected'] = 'No items selected';
$string['selectitemstoreplace'] = 'Select the content items you want to update:';
$string['itemsselected'] = '{$a} item(s) selected';
$string['enterreplacement_instruction'] = 'Enter the replacement text below. This will replace all instances of <strong>"{$a}"</strong> in the selected content.';
$string['executionmode'] = 'Execution mode';
$string['executionmode_help'] = 'Dry run mode lets you see what would be changed without making any modifications. Execute mode will actually update the database.';

// Results page.
$string['resultstitle'] = 'Results';
$string['databaseresults'] = 'Database Search Results';
$string['databaseitems'] = 'Database Items';
$string['processingoutput'] = 'Processing Output';
$string['replacementlog'] = 'Replacement Log';
$string['replacementlog_header'] = 'Detailed replacement status for each item';
$string['replacementlog_summary'] = 'Total: {$a->total} items | Successful: {$a->success} | Failed: {$a->failed}';
$string['itemscount'] = 'Items found';
$string['dbitemscount'] = 'Database items found';
$string['noitemsfound'] = 'No matching text found';
$string['noitemsfound_desc'] = 'No database content containing "{$a}" was found.';
$string['noitemsreplaced'] = 'No items were updated';
$string['noitemsreplaced_desc'] = 'No items were successfully updated. Check the error messages above for details.';
$string['itemsreplaced'] = 'Items Updated';
$string['operationcomplete'] = 'Operation completed!';
$string['operationcomplete_preview'] = 'This was a dry run - no database records were actually modified.';
$string['operationcomplete_execute'] = 'Database records have been updated.';
$string['operationcomplete_clearcache'] = 'You may want to <a href="{$a}" target="_blank">clear Moodle caches</a>.';
$string['preview_mode_warning'] = '<strong>DRY RUN MODE:</strong> No database records have been modified. This was a preview run to show what would be changed. To actually replace text, select "Execute changes" mode in Step 3.';
$string['itemsreplaced_db'] = 'Items Updated (Database)';
$string['viewitem'] = 'View item';
$string['selectall'] = 'Select / Deselect all';
$string['confirmreplacement'] = 'Replace in selected items';
$string['confirmreplacement_confirm'] = 'Are you sure you want to replace text in';
$string['error_noreplacementtext'] = 'Please enter replacement text.';
$string['codesnippet'] = 'Code Snippet';
$string['id_label'] = 'ID';
$string['directoriesscanned'] = 'Database Tables Searched';
$string['directories_list'] = 'The plugin searches text content in tables: course, course_sections, page, label, book_chapters, forum_posts, quiz, assign, glossary, wiki_pages, and other activity tables.';

// Statistics.
$string['stats_found'] = 'Items found';
$string['stats_replaced'] = 'Items replaced';
$string['stats_failed'] = 'Items failed';
$string['stats_dbfound'] = 'DB items found';
$string['stats_dbreplaced'] = 'DB items replaced';
$string['stats_dbfailed'] = 'DB items failed';

// Tables searched.
$string['tablessearched'] = 'Database tables searched';
$string['tables_list'] = 'course, course_sections, page, label, book_chapters, forum_posts, quiz, assign, and other activity tables';

// Errors.
$string['error_nosearchterm'] = 'Please enter a search text.';
$string['error_nopermission'] = 'You do not have permission to use this tool.';
$string['error_noitemsselected'] = 'Please select at least one item to update.';
$string['error_requiresiteadmin'] = 'Access denied. This tool is only available to site administrators. Please contact your site administrator if you need access to this functionality.';
$string['error_requiresiteadmin_formsubmission'] = 'User attempted form submission without site:config capability';
$string['error_databaseerror'] = 'Database error occurred: {$a}';

// Warnings.
$string['warning_selectall'] = '⚠️ You have selected all items. It is strongly recommended to manually review each item before replacing to ensure you are updating the correct content.';
$string['warning_nosearchterm'] = 'Please enter a search term to begin.';

// Settings.
$string['settingstitle'] = 'TextPlus Settings';
$string['defaultsearchterm'] = 'Default search term';
$string['defaultsearchterm_desc'] = 'The default text to search for in the database.';
$string['defaultmode'] = 'Default execution mode';
$string['defaultmode_desc'] = 'Whether to run in dry run mode by default (recommended).';

// Cache definitions.
$string['cachedef_wizarddata'] = 'Temporary wizard state data for the multi-step replacement process';

// Privacy.
$string['privacy:metadata:local_textplus_log'] = 'Log of text replacement operations';
$string['privacy:metadata:local_textplus_log:userid'] = 'The user who performed the operation';
$string['privacy:metadata:local_textplus_log:searchterm'] = 'The search term used';
$string['privacy:metadata:local_textplus_log:itemsreplaced'] = 'Number of items updated';
$string['privacy:metadata:local_textplus_log:timemodified'] = 'When the operation was performed';

// Log strings.
$string['eventtextreplaced'] = 'Text replaced';
$string['event_description'] = 'The user with id \'{$a->userid}\' replaced text matching term \'{$a->searchterm}\'. Database items replaced: {$a->itemsreplaced}.';

// Credits.
$string['credits'] = 'Developed by <a href="https://gwizit.com" target="_blank" rel="noopener noreferrer">G Wiz IT Solutions</a> | <a href="https://square.link/u/DMRTvZ0Y" target="_blank" rel="noopener noreferrer">💝 Support This Project</a> | <a href="https://github.com/gwizit/moodle-local_textplus/issues" target="_blank" rel="noopener noreferrer">🛠️ Support</a>';
$string['donation_message'] = '💝 <strong>Found this plugin useful?</strong> Please consider <a href="https://square.link/u/DMRTvZ0Y" target="_blank" rel="noopener noreferrer">making a donation</a> to help us maintain and improve this plugin. Need help? <a href="https://github.com/gwizit/moodle-local_textplus/issues" target="_blank" rel="noopener noreferrer">Report an issue</a>.';

// Additional template strings.
$string['totaloccurrences'] = 'Total occurrences';
$string['occurrences'] = 'occurrence(s)';
$string['occurrence'] = 'occurrence';
$string['occurrences_plural'] = 'occurrences';
$string['viewoccurrences'] = 'View occurrences';
$string['deselectall'] = 'Deselect all';
$string['preview'] = 'Preview';
$string['stats_occurrences'] = 'Occurrences replaced';

// Edwiser Page Builder strings.
$string['edwiser_detected_published'] = 'Edwiser Page Builder detected - including published pages';
$string['edwiser_detected_drafts'] = 'Edwiser Page Builder drafts detected - including draft pages';
$string['edwiser_detected_blocks'] = 'Edwiser Page Builder blocks detected - including reusable blocks';
$string['edwiser_detected_layouts'] = 'Edwiser Page Builder block layouts detected - including block card layouts';
$string['edwiser_remui_format'] = 'Edwiser RemUI Format detected - including layout pages';
$string['edwiser_remui_theme'] = 'Edwiser RemUI Theme detected - including theme configuration content';
$string['edwiser_content_blocks'] = 'Edwiser Page Builder content blocks will be searched in block_instances table';
$string['edwiser_blocks_searched'] = 'Searched {$a->total} Edwiser blocks, found {$a->matches} matches';
$string['searching_database'] = 'Searching Moodle database for text content...';
$string['searching_table_field'] = 'Searching {$a->table}.{$a->field}...';
$string['found_items_database'] = 'Found {$a} matching items in database';
$string['error_searching_database'] = 'Error searching database: {$a}';
$string['error_no_searchterm'] = 'Error: No search term provided';

// Processing strings.
$string['processing_mode'] = '{$a}: Processing {$a->count} items...';
$string['processing_search_replace'] = 'Search: \'{$a->search}\' → Replace: \'{$a->replace}\'';
$string['processing_item'] = 'Processing item {$a->current}/{$a->total}';
$string['processing_location'] = 'Location: {$a->table}.{$a->field} (ID: {$a->id})';
$string['processing_noitems'] = 'No items to process';
$string['processing_error_notfound'] = 'Error: Record not found';
$string['processing_nochanges'] = 'No changes needed (text not found)';
$string['processing_replaced'] = '✓ Replaced {$a} occurrence(s)';
$string['processing_would_replace'] = '✓ Would replace {$a} occurrence(s) (DRY RUN)';
$string['processing_error'] = 'Error: {$a}';
$string['processing_completed'] = 'Completed: {$a->replaced} items processed, {$a->failed} failed';

// Item description strings (used in get_item_description method).
$string['desc_coursesection_in'] = 'Course section in: {$a}';
$string['desc_section_id'] = 'Section ID: {$a}';
$string['desc_activity_in'] = '{$a->type}: {$a->name} (in {$a->course})';
$string['desc_activity_id'] = '{$a->type} ID: {$a->id}';
$string['desc_course_label'] = 'Course {$a}';
$string['desc_forumpost_in'] = 'Forum post in: {$a}';
$string['desc_forumpost_id'] = 'Forum post ID: {$a}';
$string['desc_bookchapter_in'] = 'Book chapter: {$a->title} (in {$a->book})';
$string['desc_chapter'] = 'Chapter: {$a}';
$string['desc_bookchapter_id'] = 'Book chapter ID: {$a}';
$string['desc_glossaryentry_in'] = 'Glossary entry: {$a->concept} (in {$a->glossary})';
$string['desc_entry'] = 'Entry: {$a}';
$string['desc_glossaryentry_id'] = 'Glossary entry ID: {$a}';
$string['desc_wikipage'] = 'Wiki page: {$a}';
$string['desc_wikipage_id'] = 'Wiki page ID: {$a}';
$string['desc_question'] = 'Question: {$a}';
$string['desc_question_id'] = 'Question ID: {$a}';
$string['desc_epb_block'] = 'Edwiser Page Builder Block ({$a->blockname}) on {$a->status} Page: {$a->pagename}';
$string['desc_epb_block_pageid'] = 'Edwiser Page Builder Block on Page ID: {$a}';
$string['desc_block_id'] = 'Block ID: {$a}';
$string['desc_epb_published_page'] = 'Edwiser Page Builder - Published Page: {$a->pagename}{$a->status}';
$string['desc_epb_published_id'] = 'Edwiser Published Page ID: {$a}';
$string['desc_epb_draft_page'] = 'Edwiser Page Builder - Draft Page: {$a}';
$string['desc_epb_draft_id'] = 'Edwiser Draft Page ID: {$a}';
$string['desc_epb_reusable_block'] = 'Edwiser Page Builder - Reusable Block: {$a}';
$string['desc_epb_block_id'] = 'Edwiser Block ID: {$a}';
$string['desc_epb_block_layout'] = 'Edwiser Page Builder - Block Layout: {$a->name}{$a->parent}';
$string['desc_epb_layout_id'] = 'Edwiser Block Layout ID: {$a}';
$string['desc_remui_layout_section'] = 'Edwiser RemUI Layout: {$a->course} - {$a->section}';
$string['desc_remui_layout'] = 'Edwiser RemUI Layout: {$a}';
$string['desc_remui_layout_id'] = 'Edwiser RemUI Layout ID: {$a}';
$string['desc_remui_theme_setting'] = 'Edwiser RemUI Theme Setting: {$a}';
$string['desc_config_id'] = 'Config ID: {$a}';
$string['desc_section_label'] = 'Section {$a}';
$string['desc_sectionid_label'] = 'Section ID: {$a}';
$string['desc_courseid_label'] = 'Course ID: {$a}';
$string['desc_published_status'] = 'Published';
$string['desc_draft_status'] = 'Draft';
$string['desc_hidden_status'] = ' (Hidden)';
$string['desc_default_id'] = '{$a->type} ID: {$a->id}';
