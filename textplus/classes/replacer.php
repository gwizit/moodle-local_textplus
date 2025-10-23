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
 * Text replacer core class
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions {@link https://gwizit.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     G Wiz IT Solutions
 */

namespace local_textplus;

defined('MOODLE_INTERNAL') || die();

/**
 * Main Text replacer class
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class replacer {
    /** @var array Configuration options */
    private $config;

    /** @var array Statistics */
    private $stats;

    /** @var array Replacement log entries */
    private $replacementlog;

    /** @var array Output messages */
    private $output;

    /**
     * Constructor
     *
     * @param array $config Configuration options
     */
    public function __construct($config = []) {
        global $CFG;

        $this->config = array_merge([
            'search_term' => '',
            'replacement_text' => '',
            'case_sensitive' => false,
            'dry_run' => true,
        ], $config);

        $this->stats = [
            'items_found' => 0,
            'items_replaced' => 0,
            'items_failed' => 0,
        ];

        $this->output = [];
        $this->replacementlog = [];
    }

    /**
     * Find text in database tables
     *
     * @return array Array of database items containing the search text
     */
    public function find_text_in_database() {
        global $DB, $CFG;

        $this->add_output(get_string('searching_database', 'local_textplus'), 'info');

        $searchterm = $this->config['search_term'];
        $casesensitive = isset($this->config['case_sensitive']) ? $this->config['case_sensitive'] : false;
        
        if (empty($searchterm)) {
            $this->add_output(get_string('error_no_searchterm', 'local_textplus'), 'error');
            return [];
        }

        $items = [];

        // Define tables and fields to search
        $searchtables = [
            // Course content
            'course' => ['fullname', 'shortname', 'summary'],
            'course_sections' => ['name', 'summary'],
            'course_categories' => ['name', 'description'],
            
            // Activities
            'page' => ['name', 'intro', 'content'],
            'label' => ['name', 'intro'],
            'book' => ['name', 'intro'],
            'book_chapters' => ['title', 'content'],
            'forum' => ['name', 'intro'],
            'forum_posts' => ['subject', 'message'],
            'forum_discussions' => ['name'],
            'quiz' => ['name', 'intro'],
            'assign' => ['name', 'intro'],
            'glossary' => ['name', 'intro'],
            'glossary_entries' => ['concept', 'definition'],
            'wiki' => ['name', 'intro'],
            'wiki_pages' => ['title', 'cachedcontent'],
            'lesson' => ['name', 'intro'],
            'lesson_pages' => ['title', 'contents'],
            'feedback' => ['name', 'intro'],
            'choice' => ['name', 'intro'],
            'survey' => ['name', 'intro'],
            'workshop' => ['name', 'intro'],
            'scorm' => ['name', 'intro'],
            'folder' => ['name', 'intro'],
            'url' => ['name', 'intro'],
            'resource' => ['name', 'intro'],
            
            // Blocks
            'block_instances' => ['configdata'],
            
            // Question bank
            'question' => ['name', 'questiontext'],
            'question_answers' => ['answer', 'feedback'],
        ];

        // Check if Edwiser Page Builder is installed and add its tables
        if ($DB->get_manager()->table_exists('edw_pages')) {
            $this->add_output(get_string('edwiser_detected_published', 'local_textplus'), 'info');
            $searchtables['edw_pages'] = ['pagename', 'pagedesc', 'pagecontent', 'seotag', 'seodesc'];
            
            // IMPORTANT: Edwiser pages store their content in block_instances!
            // We need to search block_instances where pagetypepattern = 'epb-page-publish'
            $this->add_output(get_string('edwiser_content_blocks', 'local_textplus'), 'info');
        }

        // Check if Edwiser Page Builder draft table exists
        if ($DB->get_manager()->table_exists('edw_pages_draft')) {
            $this->add_output(get_string('edwiser_detected_drafts', 'local_textplus'), 'info');
            $searchtables['edw_pages_draft'] = ['pagename', 'pagedesc', 'pagecontent', 'seotag', 'seodesc'];
        }

        // Check if Edwiser Page Builder blocks table exists
        if ($DB->get_manager()->table_exists('edw_page_blocks')) {
            $this->add_output(get_string('edwiser_detected_blocks', 'local_textplus'), 'info');
            $searchtables['edw_page_blocks'] = ['title', 'label', 'content'];
        }

        // Check if Edwiser Page Builder block layouts table exists
        if ($DB->get_manager()->table_exists('edw_page_block_layouts')) {
            $this->add_output(get_string('edwiser_detected_layouts', 'local_textplus'), 'info');
            $searchtables['edw_page_block_layouts'] = ['title', 'label', 'content'];
        }

        // Check if Edwiser RemUI Format is installed (stores page layouts in JSON)
        if ($DB->get_manager()->table_exists('format_remuilayout')) {
            $this->add_output(get_string('edwiser_remui_format', 'local_textplus'), 'info');
            $searchtables['format_remuilayout'] = ['layoutdata'];
        }

        // Check if Edwiser RemUI theme is installed (stores content in config_plugins table)
        if ($DB->record_exists('config_plugins', ['plugin' => 'theme_remui'])) {
            $this->add_output(get_string('edwiser_remui_theme', 'local_textplus'), 'info');
            // Note: config_plugins table will be searched with special handling for plugin='theme_remui'
            $searchtables['config_plugins'] = ['value'];
        }

        try {
            foreach ($searchtables as $table => $fields) {
                // Check if table exists
                if (!$DB->get_manager()->table_exists($table)) {
                    continue;
                }

                foreach ($fields as $field) {
                    // Check if field exists
                    if (!$DB->get_manager()->field_exists($table, $field)) {
                        continue;
                    }

                    $this->add_output(get_string('searching_table_field', 'local_textplus', 
                        (object)['table' => $table, 'field' => $field]), 'info');

                    // Build SQL based on case sensitivity and table-specific requirements
                    if ($table === 'config_plugins') {
                        // Special handling for config_plugins - only search theme_remui records
                        if ($casesensitive) {
                            $sql = "SELECT id, {$field} as content, plugin, name
                                    FROM {{$table}}
                                    WHERE plugin = :plugin
                                    AND " . $DB->sql_like($field, ':searchterm', true, true);
                        } else {
                            $sql = "SELECT id, {$field} as content, plugin, name
                                    FROM {{$table}}
                                    WHERE plugin = :plugin
                                    AND " . $DB->sql_like($field, ':searchterm', false, false);
                        }
                        $params = [
                            'plugin' => 'theme_remui',
                            'searchterm' => '%' . $DB->sql_like_escape($searchterm) . '%'
                        ];
                    } else if ($table === 'block_instances' && $field === 'configdata') {
                        // Special handling for block_instances - search Edwiser Page Builder blocks
                        // We need to get ALL blocks first, then decode and search in PHP
                        // because we can't search base64-encoded serialized data with SQL LIKE
                        $sql = "SELECT id, {$field} as content, blockname, pagetypepattern, subpagepattern
                                FROM {{$table}}
                                WHERE (pagetypepattern = :pagetype1 OR pagetypepattern = :pagetype2)";
                        $params = [
                            'pagetype1' => 'epb-page-publish',
                            'pagetype2' => 'epb-page-draft',
                        ];
                        
                        // Get all Edwiser blocks
                        $allrecords = $DB->get_records_sql($sql, $params);
                        
                        // Now decode and search each one
                        $records = [];
                        foreach ($allrecords as $record) {
                            // Decode to JSON for searching
                            $decoded_json = $this->decode_base64_serialized($record->content, false);
                            if ($decoded_json !== false) {
                                // Search in decoded JSON content
                                $found = false;
                                if ($casesensitive) {
                                    $found = (strpos($decoded_json, $searchterm) !== false);
                                } else {
                                    $found = (stripos($decoded_json, $searchterm) !== false);
                                }
                                
                                if ($found) {
                                    // Extract actual HTML for snippet display
                                    $decoded_html = $this->decode_base64_serialized($record->content, true);
                                    
                                    // Store BOTH: JSON for finding occurrences, HTML for display preference
                                    // We'll use whichever one contains the search term
                                    if ($casesensitive) {
                                        $html_has_match = (strpos($decoded_html, $searchterm) !== false);
                                    } else {
                                        $html_has_match = (stripos($decoded_html, $searchterm) !== false);
                                    }
                                    
                                    // Use HTML if it contains the match, otherwise use JSON
                                    $record->decoded_content = $html_has_match ? $decoded_html : $decoded_json;
                                    
                                    $records[] = $record;
                                }
                            }
                        }
                        
                        $this->add_output(get_string('edwiser_blocks_searched', 'local_textplus',
                            (object)['total' => count($allrecords), 'matches' => count($records)]), 'info');
                    } else {
                        // Standard search for other tables
                        if ($casesensitive) {
                            $sql = "SELECT id, {$field} as content
                                    FROM {{$table}}
                                    WHERE " . $DB->sql_like($field, ':searchterm', true, true);
                        } else {
                            $sql = "SELECT id, {$field} as content
                                    FROM {{$table}}
                                    WHERE " . $DB->sql_like($field, ':searchterm', false, false);
                        }
                        $params = ['searchterm' => '%' . $DB->sql_like_escape($searchterm) . '%'];
                        
                        // Execute the SQL query for non-Edwiser tables
                        $records = $DB->get_records_sql($sql, $params);
                    }

                    foreach ($records as $record) {
                        // For Edwiser blocks, use decoded content for snippets
                        $content = $record->content;
                        $searchable_content = isset($record->decoded_content) ? $record->decoded_content : $content;
                        
                        // Get context preview (50 chars before and after match)
                        $preview = $this->get_context_preview($searchable_content, $searchterm, $casesensitive);
                        
                        // Get all occurrences with context
                        $occurrences = $this->get_all_occurrences($searchable_content, $searchterm, $casesensitive);
                        
                        // Get human-readable location
                        $location = $this->get_item_location($table, $record->id);
                        
                        // Get URL if available
                        $url = $this->get_item_url($table, $record->id);

                        $items[] = (object)[
                            'table' => $table,
                            'field' => $field,
                            'id' => $record->id,
                            'location' => $location,
                            'context_preview' => $preview,
                            'content' => $content,
                            'url' => $url,
                            'occurrences' => $occurrences,
                        ];
                    }
                }
            }

            $this->add_output(get_string('found_items_database', 'local_textplus', count($items)), 'success');
            $this->stats['items_found'] = count($items);

            return $items;

        } catch (\Exception $e) {
            $this->add_output(get_string('error_searching_database', 'local_textplus', $e->getMessage()), 'error');
            return [];
        }
    }

    /**
     * Check if a field contains JSON data that needs special handling
     *
     * @param string $table Table name
     * @param string $field Field name
     * @return bool True if field contains JSON data
     */
    protected function is_json_field($table, $field) {
        $jsonfields = [
            'format_remuilayout' => ['layoutdata'],
            'edw_pages' => ['pagecontent'],
            'edw_pages_draft' => ['pagecontent'],
            'edw_page_blocks' => ['content'],
            'edw_page_block_layouts' => ['content'],
        ];
        
        return isset($jsonfields[$table]) && in_array($field, $jsonfields[$table]);
    }

    /**
     * Check if a field contains base64-encoded serialized PHP data
     *
     * @param string $table Table name
     * @param string $field Field name
     * @return bool True if field contains base64 serialized data
     */
    protected function is_base64_serialized_field($table, $field) {
        $base64fields = [
            'block_instances' => ['configdata'],
        ];
        
        return isset($base64fields[$table]) && in_array($field, $base64fields[$table]);
    }

    /**
     * Decode base64-encoded serialized data to plain text for searching
     *
     * @param string $content Base64-encoded serialized content
     * @param bool $extract_html If true, extract actual HTML content from Edwiser structure
     * @return string|false Decoded plain text content, or false on failure
     */
    protected function decode_base64_serialized($content, $extract_html = false) {
        try {
            // Decode from base64
            $decoded = base64_decode($content, true);
            
            if ($decoded === false) {
                return false;
            }
            
            // Unserialize PHP data
            $unserialized = @unserialize($decoded);
            
            if ($unserialized === false) {
                return false;
            }
            
            // If we need to extract HTML for snippet display
            if ($extract_html) {
                return $this->extract_html_from_edwiser_block($unserialized);
            }
            
            // Convert to JSON for easy text searching
            // This flattens all strings in the structure
            $json = json_encode($unserialized);
            
            return $json;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Extract HTML content from Edwiser block structure for display
     *
     * @param object $data Unserialized Edwiser block data
     * @return string Extracted HTML content
     */
    protected function extract_html_from_edwiser_block($data) {
        $html = '';
        
        // Edwiser blocks typically have: html->text, css->text, js->text
        if (isset($data->html) && is_array($data->html) && isset($data->html['text'])) {
            $html .= $data->html['text'];
        }
        
        if (isset($data->css) && is_array($data->css) && isset($data->css['text'])) {
            $html .= "\n\n/* CSS */\n" . $data->css['text'];
        }
        
        if (isset($data->js) && is_array($data->js) && isset($data->js['text'])) {
            $html .= "\n\n/* JavaScript */\n" . $data->js['text'];
        }
        
        // If no content was extracted, return JSON representation as fallback
        if (empty($html)) {
            $html = json_encode($data, JSON_PRETTY_PRINT);
        }
        
        return $html;
    }

    /**
     * Process base64-encoded serialized PHP data for search and replace
     *
     * @param string $content Base64-encoded serialized content
     * @param string $searchterm Search term
     * @param string $replacement Replacement text
     * @param bool $casesensitive Case sensitive search
     * @return array [modified_content, occurrences_count]
     */
    protected function process_base64_serialized_field($content, $searchterm, $replacement, $casesensitive = false) {
        try {
            // Decode from base64
            $decoded = base64_decode($content, true);
            
            if ($decoded === false) {
                // Not valid base64, try as plain serialized
                $decoded = $content;
            }
            
            // Unserialize PHP data
            $unserialized = @unserialize($decoded);
            
            if ($unserialized === false) {
                // Not serialized data, treat as regular text
                return [$content, 0];
            }
            
            $occurrences = 0;
            $modified = false;
            
            // Recursively search and replace in the unserialized structure
            $this->search_replace_recursive($unserialized, $searchterm, $replacement, $casesensitive, $occurrences, $modified);
            
            if ($modified) {
                // Re-serialize and re-encode
                $reserialized = serialize($unserialized);
                $reencoded = base64_encode($reserialized);
                return [$reencoded, $occurrences];
            }
            
            return [$content, $occurrences];
            
        } catch (\Exception $e) {
            // If anything fails, return original
            return [$content, 0];
        }
    }

    /**
     * Recursively search and replace in any PHP data structure (arrays, objects, strings)
     *
     * @param mixed &$data Data to process (passed by reference)
     * @param string $searchterm Search term
     * @param string $replacement Replacement text
     * @param bool $casesensitive Case sensitive search
     * @param int &$occurrences Occurrence counter (passed by reference)
     * @param bool &$modified Modified flag (passed by reference)
     */
    protected function search_replace_recursive(&$data, $searchterm, $replacement, $casesensitive, &$occurrences, &$modified) {
        if (is_string($data)) {
            // Count occurrences
            if ($casesensitive) {
                $count = substr_count($data, $searchterm);
                if ($count > 0) {
                    $data = str_replace($searchterm, $replacement, $data);
                    $occurrences += $count;
                    $modified = true;
                }
            } else {
                $count = substr_count(strtolower($data), strtolower($searchterm));
                if ($count > 0) {
                    $data = str_ireplace($searchterm, $replacement, $data);
                    $occurrences += $count;
                    $modified = true;
                }
            }
        } else if (is_array($data)) {
            foreach ($data as $key => &$value) {
                $this->search_replace_recursive($value, $searchterm, $replacement, $casesensitive, $occurrences, $modified);
            }
            unset($value); // Break reference
        } else if (is_object($data)) {
            // Handle objects by converting to array, processing, then back
            $vars = get_object_vars($data);
            foreach ($vars as $key => $value) {
                $this->search_replace_recursive($data->$key, $searchterm, $replacement, $casesensitive, $occurrences, $modified);
            }
        }
    }

    /**
     * Process JSON field content for search and replace
     *
     * @param string $content JSON content
     * @param string $searchterm Search term
     * @param string $replacement Replacement text
     * @param bool $casesensitive Case sensitive search
     * @return array [modified_content, occurrences_count]
     */
    protected function process_json_field($content, $searchterm, $replacement, $casesensitive = false) {
        $decoded = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Not valid JSON, treat as regular text
            return [$content, 0];
        }
        
        $occurrences = 0;
        $modified = false;
        
        // Recursively search and replace in JSON structure
        $this->search_replace_recursive($decoded, $searchterm, $replacement, $casesensitive, $occurrences, $modified);
        
        if ($modified) {
            $newcontent = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return [$newcontent, $occurrences];
        }
        
        return [$content, $occurrences];
    }

    /**
     * Get context preview showing text around the match
     *
     * @param string $content Full content
     * @param string $searchterm Search term
     * @param bool $casesensitive Case sensitive search
     * @return string Context preview
     */
    private function get_context_preview($content, $searchterm, $casesensitive = false) {
        // Strip HTML tags for preview
        $plaintext = strip_tags($content);
        
        // Find position of search term
        if ($casesensitive) {
            $pos = strpos($plaintext, $searchterm);
        } else {
            $pos = stripos($plaintext, $searchterm);
        }

        if ($pos === false) {
            return substr($plaintext, 0, 100);
        }

        // Get 50 characters before and after
        $start = max(0, $pos - 50);
        $length = strlen($searchterm) + 100;
        $preview = substr($plaintext, $start, $length);

        // Clean up
        $preview = trim($preview);
        if ($start > 0) {
            $preview = '...' . $preview;
        }
        if (strlen($plaintext) > $start + $length) {
            $preview .= '...';
        }

        return $preview;
    }

    /**
     * Get all occurrences of search term in content with context
     *
     * @param string $content Full content
     * @param string $searchterm Search term
     * @param bool $casesensitive Case sensitive search
     * @return array Array of occurrence contexts
     */
    private function get_all_occurrences($content, $searchterm, $casesensitive = false) {
        $occurrences = [];
        $offset = 0;
        $searchlen = strlen($searchterm);
        
        // Search in the original content (not stripped) to match SQL results
        while (true) {
            // Find next occurrence in original content
            if ($casesensitive) {
                $pos = strpos($content, $searchterm, $offset);
            } else {
                $pos = stripos($content, $searchterm, $offset);
            }
            
            if ($pos === false) {
                break;
            }
            
            // Get a large context around this occurrence (1000 chars before and after)
            $contextstart = max(0, $pos - 1000);
            $contextlength = min(strlen($content) - $contextstart, $searchlen + 2000);
            $contextraw = substr($content, $contextstart, $contextlength);
            
            // Store raw content - escaping will be done at display time in JavaScript
            $context = $contextraw;
            
            // Add ellipsis if we're not at the start/end
            if ($contextstart > 0) {
                $context = '...' . $context;
            }
            if ($contextstart + $contextlength < strlen($content)) {
                $context .= '...';
            }
            
            $occurrences[] = [
                'position' => $pos,
                'context' => $context,
                'match' => substr($content, $pos, $searchlen)
            ];
            
            $offset = $pos + 1; // Move past this occurrence
        }
        
        return $occurrences;
    }

    /**
     * Get URL for a database item
     *
     * @param string $table Table name
     * @param int $recordid Record ID
     * @return string|null URL or null if not applicable
     */
    private function get_item_url($table, $recordid) {
        global $DB, $CFG;

        try {
            switch ($table) {
                case 'course':
                    return new \moodle_url('/course/view.php', ['id' => $recordid]);

                case 'course_sections':
                    $section = $DB->get_record('course_sections', ['id' => $recordid], 'course, section');
                    if ($section) {
                        return new \moodle_url('/course/view.php', [
                            'id' => $section->course,
                            'section' => $section->section
                        ]);
                    }
                    return null;

                case 'course_categories':
                    return new \moodle_url('/course/index.php', ['categoryid' => $recordid]);

                case 'page':
                    $cm = get_coursemodule_from_instance('page', $recordid);
                    if ($cm) {
                        return new \moodle_url('/mod/page/view.php', ['id' => $cm->id]);
                    }
                    return null;

                case 'book':
                    $cm = get_coursemodule_from_instance('book', $recordid);
                    if ($cm) {
                        return new \moodle_url('/mod/book/view.php', ['id' => $cm->id]);
                    }
                    return null;

                case 'book_chapters':
                    $chapter = $DB->get_record('book_chapters', ['id' => $recordid], 'bookid');
                    if ($chapter) {
                        $cm = get_coursemodule_from_instance('book', $chapter->bookid);
                        if ($cm) {
                            return new \moodle_url('/mod/book/view.php', [
                                'id' => $cm->id,
                                'chapterid' => $recordid
                            ]);
                        }
                    }
                    return null;

                case 'forum':
                case 'quiz':
                case 'assign':
                case 'glossary':
                case 'wiki':
                case 'lesson':
                case 'feedback':
                case 'choice':
                case 'survey':
                case 'workshop':
                case 'scorm':
                case 'folder':
                case 'url':
                case 'resource':
                case 'label':
                    $cm = get_coursemodule_from_instance($table, $recordid);
                    if ($cm) {
                        return new \moodle_url("/mod/{$table}/view.php", ['id' => $cm->id]);
                    }
                    return null;

                case 'forum_posts':
                    $post = $DB->get_record('forum_posts', ['id' => $recordid], 'discussion');
                    if ($post) {
                        return new \moodle_url('/mod/forum/discuss.php', [
                            'd' => $post->discussion,
                            'parent' => $recordid
                        ]);
                    }
                    return null;

                case 'forum_discussions':
                    return new \moodle_url('/mod/forum/discuss.php', ['d' => $recordid]);

                case 'glossary_entries':
                    $entry = $DB->get_record('glossary_entries', ['id' => $recordid], 'glossaryid');
                    if ($entry) {
                        $cm = get_coursemodule_from_instance('glossary', $entry->glossaryid);
                        if ($cm) {
                            return new \moodle_url('/mod/glossary/view.php', [
                                'id' => $cm->id,
                                'mode' => 'entry',
                                'hook' => $recordid
                            ]);
                        }
                    }
                    return null;

                case 'wiki_pages':
                    $page = $DB->get_record('wiki_pages', ['id' => $recordid], 'subwikiid');
                    if ($page) {
                        return new \moodle_url('/mod/wiki/view.php', ['pageid' => $recordid]);
                    }
                    return null;

                case 'lesson_pages':
                    $page = $DB->get_record('lesson_pages', ['id' => $recordid], 'lessonid');
                    if ($page) {
                        $cm = get_coursemodule_from_instance('lesson', $page->lessonid);
                        if ($cm) {
                            return new \moodle_url('/mod/lesson/view.php', [
                                'id' => $cm->id,
                                'pageid' => $recordid
                            ]);
                        }
                    }
                    return null;

                case 'question':
                    return new \moodle_url('/question/question.php', ['id' => $recordid]);

                case 'block_instances':
                    // Check if this is an Edwiser Page Builder block
                    $block = $DB->get_record('block_instances', ['id' => $recordid], 'pagetypepattern, subpagepattern');
                    if ($block && ($block->pagetypepattern === 'epb-page-publish' || $block->pagetypepattern === 'epb-page-draft')) {
                        if ($block->pagetypepattern === 'epb-page-publish') {
                            return new \moodle_url('/local/edwiserpagebuilder/page.php', ['id' => $block->subpagepattern]);
                        } else {
                            return new \moodle_url('/local/edwiserpagebuilder/pagedraft.php', ['id' => $block->subpagepattern]);
                        }
                    }
                    return null;

                case 'edw_pages':
                    return new \moodle_url('/local/edwiserpagebuilder/page.php', ['id' => $recordid]);

                case 'edw_pages_draft':
                    return new \moodle_url('/local/edwiserpagebuilder/pagedraft.php', ['id' => $recordid]);

                case 'edw_page_blocks':
                case 'edw_page_block_layouts':
                    return new \moodle_url('/local/edwiserpagebuilder/managepages.php');

                case 'format_remuilayout':
                    $layout = $DB->get_record('format_remuilayout', ['id' => $recordid], 'courseid, sectionid');
                    if ($layout && $layout->courseid) {
                        if ($layout->sectionid) {
                            return new \moodle_url('/course/view.php', [
                                'id' => $layout->courseid,
                                'section' => $layout->sectionid
                            ]);
                        } else {
                            return new \moodle_url('/course/view.php', ['id' => $layout->courseid]);
                        }
                    }
                    return null;

                case 'config_plugins':
                    // Link to theme settings page
                    return new \moodle_url('/admin/settings.php', ['section' => 'themesettingremui']);

                default:
                    return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get human-readable location for database item
     *
     * @param string $table Table name
     * @param int $recordid Record ID
     * @return string Location description
     */
    private function get_item_location($table, $recordid) {
        global $DB;

        try {
            switch ($table) {
                case 'course':
                    $record = $DB->get_record('course', ['id' => $recordid], 'fullname, shortname');
                    return $record ? "Course: {$record->fullname} ({$record->shortname})" : "Course ID: {$recordid}";

                case 'course_sections':
                    $section = $DB->get_record('course_sections', ['id' => $recordid], 'course, section');
                    if ($section) {
                        $course = $DB->get_record('course', ['id' => $section->course], 'shortname');
                        return $course ? "Course section in: {$course->shortname}" : "Section ID: {$recordid}";
                    }
                    return "Section ID: {$recordid}";

                case 'page':
                case 'label':
                case 'book':
                case 'forum':
                case 'quiz':
                case 'assign':
                case 'glossary':
                case 'wiki':
                case 'lesson':
                case 'feedback':
                case 'choice':
                case 'survey':
                case 'workshop':
                case 'scorm':
                case 'folder':
                case 'url':
                case 'resource':
                    $activity = $DB->get_record($table, ['id' => $recordid], 'name, course');
                    if ($activity) {
                        $course = $DB->get_record('course', ['id' => $activity->course], 'shortname');
                        $coursename = $course ? $course->shortname : "Course {$activity->course}";
                        return ucfirst($table) . ": {$activity->name} (in {$coursename})";
                    }
                    return ucfirst($table) . " ID: {$recordid}";

                case 'forum_posts':
                    $post = $DB->get_record('forum_posts', ['id' => $recordid], 'discussion');
                    if ($post) {
                        $discussion = $DB->get_record('forum_discussions', ['id' => $post->discussion], 'name, forum');
                        if ($discussion) {
                            return "Forum post in: {$discussion->name}";
                        }
                    }
                    return "Forum post ID: {$recordid}";

                case 'book_chapters':
                    $chapter = $DB->get_record('book_chapters', ['id' => $recordid], 'title, bookid');
                    if ($chapter) {
                        $book = $DB->get_record('book', ['id' => $chapter->bookid], 'name');
                        return $book ? "Book chapter: {$chapter->title} (in {$book->name})" : "Chapter: {$chapter->title}";
                    }
                    return "Book chapter ID: {$recordid}";

                case 'glossary_entries':
                    $entry = $DB->get_record('glossary_entries', ['id' => $recordid], 'concept, glossaryid');
                    if ($entry) {
                        $glossary = $DB->get_record('glossary', ['id' => $entry->glossaryid], 'name');
                        return $glossary ? "Glossary entry: {$entry->concept} (in {$glossary->name})" : "Entry: {$entry->concept}";
                    }
                    return "Glossary entry ID: {$recordid}";

                case 'wiki_pages':
                    $page = $DB->get_record('wiki_pages', ['id' => $recordid], 'title, subwikiid');
                    if ($page) {
                        return "Wiki page: {$page->title}";
                    }
                    return "Wiki page ID: {$recordid}";

                case 'question':
                    $question = $DB->get_record('question', ['id' => $recordid], 'name');
                    return $question ? "Question: {$question->name}" : "Question ID: {$recordid}";

                case 'block_instances':
                    // Check if this is an Edwiser Page Builder block
                    $block = $DB->get_record('block_instances', ['id' => $recordid], 'blockname, pagetypepattern, subpagepattern');
                    if ($block && ($block->pagetypepattern === 'epb-page-publish' || $block->pagetypepattern === 'epb-page-draft')) {
                        $pagetype = ($block->pagetypepattern === 'epb-page-publish') ? 'edw_pages' : 'edw_pages_draft';
                        $page = $DB->get_record($pagetype, ['id' => $block->subpagepattern], 'pagename');
                        if ($page) {
                            $status = ($block->pagetypepattern === 'epb-page-publish') ? 'Published' : 'Draft';
                            $blockname = ucwords(str_replace('_', ' ', $block->blockname));
                            return "Edwiser Page Builder Block ({$blockname}) on {$status} Page: {$page->pagename}";
                        }
                        return "Edwiser Page Builder Block on Page ID: {$block->subpagepattern}";
                    }
                    return "Block ID: {$recordid}";

                case 'edw_pages':
                    $page = $DB->get_record('edw_pages', ['id' => $recordid], 'pagename, visible');
                    if ($page) {
                        $status = $page->visible ? '' : ' (Hidden)';
                        return "Edwiser Page Builder - Published Page: {$page->pagename}{$status}";
                    }
                    return "Edwiser Published Page ID: {$recordid}";

                case 'edw_pages_draft':
                    $page = $DB->get_record('edw_pages_draft', ['id' => $recordid], 'pagename');
                    if ($page) {
                        return "Edwiser Page Builder - Draft Page: {$page->pagename}";
                    }
                    return "Edwiser Draft Page ID: {$recordid}";

                case 'edw_page_blocks':
                    $block = $DB->get_record('edw_page_blocks', ['id' => $recordid], 'title, label');
                    if ($block) {
                        $name = $block->title ?: $block->label;
                        return "Edwiser Page Builder - Reusable Block: {$name}";
                    }
                    return "Edwiser Block ID: {$recordid}";

                case 'edw_page_block_layouts':
                    $layout = $DB->get_record('edw_page_block_layouts', ['id' => $recordid], 'title, label, belongsto');
                    if ($layout) {
                        $name = $layout->title ?: $layout->label;
                        $parent = $layout->belongsto ? " (in {$layout->belongsto})" : '';
                        return "Edwiser Page Builder - Block Layout: {$name}{$parent}";
                    }
                    return "Edwiser Block Layout ID: {$recordid}";

                case 'format_remuilayout':
                    $layout = $DB->get_record('format_remuilayout', ['id' => $recordid], 'courseid, sectionid');
                    if ($layout) {
                        $course = $DB->get_record('course', ['id' => $layout->courseid], 'fullname');
                        $coursename = $course ? $course->fullname : "Course ID: {$layout->courseid}";
                        if ($layout->sectionid) {
                            $section = $DB->get_record('course_sections', ['id' => $layout->sectionid], 'name, section');
                            $sectionname = $section ? ($section->name ?: "Section {$section->section}") : "Section ID: {$layout->sectionid}";
                            return "Edwiser RemUI Layout: {$coursename} - {$sectionname}";
                        }
                        return "Edwiser RemUI Layout: {$coursename}";
                    }
                    return "Edwiser RemUI Layout ID: {$recordid}";

                case 'config_plugins':
                    $config = $DB->get_record('config_plugins', ['id' => $recordid], 'plugin, name');
                    if ($config) {
                        // Format the config name for better readability
                        $configname = ucwords(str_replace(['_', '-'], ' ', $config->name));
                        return "Edwiser RemUI Theme Setting: {$configname}";
                    }
                    return "Config ID: {$recordid}";

                default:
                    return ucfirst($table) . " ID: {$recordid}";
            }
        } catch (\Exception $e) {
            return ucfirst($table) . " ID: {$recordid}";
        }
    }

    /**
     * Process text replacements in database
     *
     * @param array $items Array of items to process
     * @return bool Success status
     */
    public function process_text_replacements($items) {
        global $DB;

        if (empty($items)) {
            $this->add_output(get_string('processing_noitems', 'local_textplus'), 'warning');
            return true;
        }

        $searchterm = $this->config['search_term'];
        $replacementtext = isset($this->config['replacement_text']) ? $this->config['replacement_text'] : '';
        $casesensitive = isset($this->config['case_sensitive']) ? $this->config['case_sensitive'] : false;
        $dryrun = $this->config['dry_run'];

        $mode = $dryrun ? 'DRY RUN' : 'EXECUTE';
        $this->add_output("\n{$mode}: " . get_string('processing_mode', 'local_textplus', 
            (object)['count' => count($items)]), 'info');
        $this->add_output(get_string('processing_search_replace', 'local_textplus',
            (object)['search' => $searchterm, 'replace' => $replacementtext]), 'info');

        foreach ($items as $index => $item) {
            $this->stats['items_found']++;
            
            // Support both array and object notation for flexibility
            $table = is_array($item) ? $item['table'] : $item->table;
            $field = is_array($item) ? $item['field'] : $item->field;
            $id = is_array($item) ? $item['id'] : $item->id;
            
            $this->add_output("\n" . get_string('processing_item', 'local_textplus',
                (object)['current' => $index + 1, 'total' => count($items)]), 'info');
            $this->add_output(get_string('processing_location', 'local_textplus',
                (object)['table' => $table, 'field' => $field, 'id' => $id]), 'info');

            try {
                // Get current content
                $record = $DB->get_record($table, ['id' => $id], 'id, ' . $field);
                
                if (!$record) {
                    $this->add_output(get_string('processing_error_notfound', 'local_textplus'), 'error');
                    $this->stats['items_failed']++;
                    $this->add_replacement_log($table, $field, $id, 'failed', 'Record not found');
                    continue;
                }

                $currentcontent = $record->{$field};
                
                // Check if this is a base64-encoded serialized field (block_instances configdata)
                if ($this->is_base64_serialized_field($table, $field)) {
                    // Process base64-encoded serialized PHP data
                    list($newcontent, $occurrences) = $this->process_base64_serialized_field(
                        $currentcontent,
                        $searchterm,
                        $replacementtext,
                        $casesensitive
                    );
                } else if ($this->is_json_field($table, $field)) {
                    // Process JSON field
                    list($newcontent, $occurrences) = $this->process_json_field(
                        $currentcontent, 
                        $searchterm, 
                        $replacementtext, 
                        $casesensitive
                    );
                } else {
                    // Perform standard replacement
                    if ($casesensitive) {
                        $newcontent = str_replace($searchterm, $replacementtext, $currentcontent);
                        $occurrences = substr_count($currentcontent, $searchterm);
                    } else {
                        $newcontent = str_ireplace($searchterm, $replacementtext, $currentcontent);
                        $occurrences = substr_count(strtolower($currentcontent), strtolower($searchterm));
                    }
                }

                // Check if anything changed
                if ($currentcontent === $newcontent) {
                    $this->add_output(get_string('processing_nochanges', 'local_textplus'), 'info');
                    $this->add_replacement_log($table, $field, $id, 'skipped', 'Text not found in current content');
                    continue;
                }

                if (!$dryrun) {
                    // Update the database
                    $updaterecord = new \stdClass();
                    $updaterecord->id = $id;
                    $updaterecord->{$field} = $newcontent;
                    
                    $DB->update_record($table, $updaterecord);
                    
                    $this->add_output(get_string('processing_replaced', 'local_textplus', $occurrences), 'success');
                    $this->stats['items_replaced']++;
                    $this->add_replacement_log($table, $field, $id, 'success', "Replaced {$occurrences} occurrence(s)");
                } else {
                    $this->add_output(get_string('processing_would_replace', 'local_textplus', $occurrences), 'info');
                    $this->stats['items_replaced']++;
                    $this->add_replacement_log($table, $field, $id, 'preview', "Would replace {$occurrences} occurrence(s)");
                }

            } catch (\Exception $e) {
                $this->add_output(get_string('processing_error', 'local_textplus', $e->getMessage()), 'error');
                $this->stats['items_failed']++;
                $this->add_replacement_log($table, $field, $id, 'failed', $e->getMessage());
            }
        }

        $this->add_output("\n" . get_string('processing_completed', 'local_textplus',
            (object)['replaced' => $this->stats['items_replaced'], 'failed' => $this->stats['items_failed']]), 'success');

        return true;
    }

    /**
     * Add entry to replacement log
     *
     * @param string $table Table name
     * @param string $field Field name
     * @param int $id Record ID
     * @param string $status Status (success, failed, skipped, preview)
     * @param string $message Log message
     */
    private function add_replacement_log($table, $field, $id, $status, $message) {
        $this->replacementlog[] = [
            'table' => $table,
            'field' => $field,
            'id' => $id,
            'status' => $status,
            'message' => $message,
        ];
    }

    /**
     * Add output message
     *
     * @param string $message Message text
     * @param string $type Message type (info, success, warning, error)
     */
    private function add_output($message, $type = 'info') {
        $this->output[] = [
            'message' => $message,
            'type' => $type,
            'time' => time(),
        ];
    }

    /**
     * Get output messages
     *
     * @return array Array of output messages
     */
    public function get_output() {
        return $this->output;
    }

    /**
     * Get statistics
     *
     * @return array Statistics array
     */
    public function get_stats() {
        return $this->stats;
    }

    /**
     * Get replacement log
     *
     * @return array Replacement log entries
     */
    public function get_replacement_log() {
        return $this->replacementlog;
    }

    /**
     * Log operation to database
     *
     * @param int $userid User ID
     * @return bool Success status
     */
    public function log_operation($userid) {
        global $DB;

        $record = new \stdClass();
        $record->userid = $userid;
        $record->searchterm = $this->config['search_term'];
        $record->itemsreplaced = $this->stats['items_replaced'];
        $record->replacementtext = isset($this->config['replacement_text']) ? $this->config['replacement_text'] : '';
        $record->casesensitive = isset($this->config['case_sensitive']) ? $this->config['case_sensitive'] : 0;
        $record->dryrun = $this->config['dry_run'] ? 1 : 0;
        $record->timecreated = time();
        $record->timemodified = time();

        try {
            $DB->insert_record('local_textplus_log', $record);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
