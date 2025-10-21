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

        $this->add_output("Searching Moodle database for text content...", 'info');

        $searchterm = $this->config['search_term'];
        $casesensitive = isset($this->config['case_sensitive']) ? $this->config['case_sensitive'] : false;
        
        if (empty($searchterm)) {
            $this->add_output("Error: No search term provided", 'error');
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

        // Check if Edwiser RemUI Page Builder is installed and add its tables
        if ($DB->get_manager()->table_exists('edwiser_remui_pages')) {
            $this->add_output("Edwiser RemUI Page Builder detected - including custom pages", 'info');
            $searchtables['edwiser_remui_pages'] = ['title', 'content'];
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

                    $this->add_output("Searching {$table}.{$field}...", 'info');

                    // Build SQL based on case sensitivity
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
                    
                    $records = $DB->get_records_sql($sql, $params);

                    foreach ($records as $record) {
                        // Get context preview (50 chars before and after match)
                        $content = $record->content;
                        $preview = $this->get_context_preview($content, $searchterm, $casesensitive);
                        
                        // Get all occurrences with context
                        $occurrences = $this->get_all_occurrences($content, $searchterm, $casesensitive);
                        
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

            $this->add_output("Found " . count($items) . " matching items in database", 'success');
            $this->stats['items_found'] = count($items);

            return $items;

        } catch (\Exception $e) {
            $this->add_output("Error searching database: " . $e->getMessage(), 'error');
            return [];
        }
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
            
            // HTML-escape the context to show tags as visible text (code view)
            $context = htmlspecialchars($contextraw, ENT_QUOTES, 'UTF-8');
            
            // Preserve line breaks by converting them to <br> tags
            $context = nl2br($context);
            
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
                'match' => htmlspecialchars(substr($content, $pos, $searchlen), ENT_QUOTES, 'UTF-8')
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

                case 'edwiser_remui_pages':
                    return new \moodle_url('/local/edwiserpagebuilder/page.php', ['id' => $recordid]);

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

                case 'edwiser_remui_pages':
                    $page = $DB->get_record('edwiser_remui_pages', ['id' => $recordid], 'title');
                    if ($page) {
                        return "Edwiser RemUI Custom Page: {$page->title}";
                    }
                    return "Edwiser RemUI Page ID: {$recordid}";

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
            $this->add_output("No items to process", 'warning');
            return true;
        }

        $searchterm = $this->config['search_term'];
        $replacementtext = isset($this->config['replacement_text']) ? $this->config['replacement_text'] : '';
        $casesensitive = isset($this->config['case_sensitive']) ? $this->config['case_sensitive'] : false;
        $dryrun = $this->config['dry_run'];

        $mode = $dryrun ? 'DRY RUN' : 'EXECUTE';
        $this->add_output("\n{$mode}: Processing " . count($items) . " items...", 'info');
        $this->add_output("Search: '{$searchterm}' → Replace: '{$replacementtext}'", 'info');

        foreach ($items as $index => $item) {
            $this->stats['items_found']++;
            
            // Support both array and object notation for flexibility
            $table = is_array($item) ? $item['table'] : $item->table;
            $field = is_array($item) ? $item['field'] : $item->field;
            $id = is_array($item) ? $item['id'] : $item->id;
            
            $this->add_output("\nProcessing item " . ($index + 1) . "/" . count($items), 'info');
            $this->add_output("Location: {$table}.{$field} (ID: {$id})", 'info');

            try {
                // Get current content
                $record = $DB->get_record($table, ['id' => $id], 'id, ' . $field);
                
                if (!$record) {
                    $this->add_output("Error: Record not found", 'error');
                    $this->stats['items_failed']++;
                    $this->add_replacement_log($table, $field, $id, 'failed', 'Record not found');
                    continue;
                }

                $currentcontent = $record->{$field};
                
                // Perform replacement
                if ($casesensitive) {
                    $newcontent = str_replace($searchterm, $replacementtext, $currentcontent);
                } else {
                    $newcontent = str_ireplace($searchterm, $replacementtext, $currentcontent);
                }

                // Check if anything changed
                if ($currentcontent === $newcontent) {
                    $this->add_output("No changes needed (text not found)", 'info');
                    $this->add_replacement_log($table, $field, $id, 'skipped', 'Text not found in current content');
                    continue;
                }

                // Count occurrences replaced
                if ($casesensitive) {
                    $occurrences = substr_count($currentcontent, $searchterm);
                } else {
                    $occurrences = substr_count(strtolower($currentcontent), strtolower($searchterm));
                }

                if (!$dryrun) {
                    // Update the database
                    $updaterecord = new \stdClass();
                    $updaterecord->id = $id;
                    $updaterecord->{$field} = $newcontent;
                    
                    $DB->update_record($table, $updaterecord);
                    
                    $this->add_output("✓ Replaced {$occurrences} occurrence(s)", 'success');
                    $this->stats['items_replaced']++;
                    $this->add_replacement_log($table, $field, $id, 'success', "Replaced {$occurrences} occurrence(s)");
                } else {
                    $this->add_output("✓ Would replace {$occurrences} occurrence(s) (DRY RUN)", 'info');
                    $this->stats['items_replaced']++;
                    $this->add_replacement_log($table, $field, $id, 'preview', "Would replace {$occurrences} occurrence(s)");
                }

            } catch (\Exception $e) {
                $this->add_output("Error: " . $e->getMessage(), 'error');
                $this->stats['items_failed']++;
                $this->add_replacement_log($table, $field, $id, 'failed', $e->getMessage());
            }
        }

        $this->add_output("\nCompleted: {$this->stats['items_replaced']} items processed, {$this->stats['items_failed']} failed", 'success');

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
