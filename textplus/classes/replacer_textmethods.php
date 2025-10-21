<?php
// Add these methods to replacer.php after the find_database_files() method (around line 471)

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
                    
                    // Get human-readable location
                    $location = $this->get_item_location($table, $record->id);

                    $items[] = (object)[
                        'table' => $table,
                        'field' => $field,
                        'id' => $record->id,
                        'location' => $location,
                        'context_preview' => $preview,
                        'content' => $content,
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
        
        $this->add_output("\nProcessing item " . ($index + 1) . "/" . count($items), 'info');
        $this->add_output("Location: {$item['table']}.{$item['field']} (ID: {$item['id']})", 'info');

        try {
            // Get current content
            $record = $DB->get_record($item['table'], ['id' => $item['id']], 'id, ' . $item['field']);
            
            if (!$record) {
                $this->add_output("Error: Record not found", 'error');
                $this->stats['items_failed']++;
                $this->add_replacement_log($item, 'failed', 'Record not found');
                continue;
            }

            $currentcontent = $record->{$item['field']};
            
            // Perform replacement
            if ($casesensitive) {
                $newcontent = str_replace($searchterm, $replacementtext, $currentcontent);
            } else {
                $newcontent = str_ireplace($searchterm, $replacementtext, $currentcontent);
            }

            // Check if anything changed
            if ($currentcontent === $newcontent) {
                $this->add_output("No changes needed (text not found)", 'info');
                $this->add_replacement_log($item, 'skipped', 'Text not found in current content');
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
                $updaterecord->id = $item['id'];
                $updaterecord->{$item['field']} = $newcontent;
                
                $DB->update_record($item['table'], $updaterecord);
                
                $this->add_output("✓ Replaced {$occurrences} occurrence(s)", 'success');
                $this->stats['items_replaced']++;
                $this->add_replacement_log($item, 'success', "Replaced {$occurrences} occurrence(s)");
            } else {
                $this->add_output("✓ Would replace {$occurrences} occurrence(s) (DRY RUN)", 'info');
                $this->stats['items_replaced']++;
                $this->add_replacement_log($item, 'preview', "Would replace {$occurrences} occurrence(s)");
            }

        } catch (\Exception $e) {
            $this->add_output("Error: " . $e->getMessage(), 'error');
            $this->stats['items_failed']++;
            $this->add_replacement_log($item, 'failed', $e->getMessage());
        }
    }

    $this->add_output("\nCompleted: {$this->stats['items_replaced']} items processed, {$this->stats['items_failed']} failed", 'success');

    return true;
}

/**
 * Add entry to replacement log
 *
 * @param array $item Item being processed
 * @param string $status Status (success, failed, skipped, preview)
 * @param string $message Log message
 */
private function add_replacement_log($item, $status, $message) {
    $this->replacementlog[] = [
        'table' => $item['table'],
        'field' => $item['field'],
        'id' => $item['id'],
        'status' => $status,
        'message' => $message,
    ];
}
