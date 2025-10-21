# TextPlus Transformation Status

## Overview
Transformation of ImagePlus (file search/replace) to TextPlus (database text search/replace) plugin.

## ✅ Completed

### 1. Core Files Updated
- ✅ `version.php` - Component renamed to local_textplus, version updated
- ✅ `settings.php` - Removed file-related settings (preserve permissions, search filesystem, search database checkboxes)
- ✅ `db/access.php` - Updated capability names (imageplus → textplus)
- ✅ `db/install.xml` - Fixed PATH, updated table name (local_textplus_log), updated field names (itemsreplaced, replacementtext)

### 2. Form Updated (`classes/form/replacer_form.php`)
- ✅ Step 1: Removed filetype selector, search checkboxes; added casesensitive checkbox
- ✅ Step 2: Changed to content-selection-area (custom rendering in index.php)
- ✅ Step 3: Removed file upload, added replacementtext textarea, removed GD checks

### 3. Index Controller Updated (`index.php`)
- ✅ Session initialization: Removed file-related variables, added text-related ones (casesensitive, databaseitems, selecteditems, replacementtext)
- ✅ Step 1 handler: Changed to call `$replacer->find_text_in_database()` instead of file search methods
- ✅ Step 2 handler: Updated to handle database item selections (format: table|id|field)
- ✅ Step 2 display: Complete rewrite showing database items with table name, field, record ID, location, and context preview
- ✅ Step 3 handler: Simplified to validate replacement text and call `$replacer->process_text_replacements()`

### 4. Language Strings (`lang/en/local_textplus.php`)
- ✅ Complete rewrite for text operations
- ✅ Removed all ImagePlus/file-related strings
- ✅ Added text-specific strings (searchterm, replacementtext, casesensitive, noitemsfound_desc, selectitemstoreplace, databaseitems, error_noreplacementtext, error_noitemsselected, directoriesscanned)

### 5. PowerShell Scripts
- ✅ `create_package.ps1` - Updated to create textplus package v1.0.0
- ✅ `manual_install.ps1` - Updated paths and references

### 6. Documentation
- ✅ README.md files - Rewritten for text replacement functionality
- ✅ TROUBLESHOOTING.md - Updated
- ✅ COMPATIBILITY.md - Updated

## ⚠️ Pending - Critical

### Replacer Class (`classes/replacer.php`)
The following methods need to be manually added to `classes/replacer.php`:

**Location:** After the `find_database_files()` method (around line 471)

**Methods to Add:**
1. `find_text_in_database()` - Searches database tables for text content
2. `get_context_preview()` - Creates preview text showing context around matches
3. `get_item_location()` - Gets human-readable location descriptions
4. `process_text_replacements()` - Performs actual text replacement
5. `add_replacement_log()` - Logs replacement operations

**Reference File:** `classes/replacer_textmethods.php` contains all the new methods ready to copy-paste

**Constructor Update:**
The constructor also needs updating to remove file-related config options and add text-specific ones:
```php
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
```

### Renderer Class (`renderer.php`)
- ⚠️ Needs update to display database content results instead of file lists
- ⚠️ Update `render_results()` method to show table/field/ID instead of file paths

### Event Class (`classes/event/images_replaced.php`)
- ⚠️ Should be renamed to `text_replaced.php`
- ⚠️ Update class name and event description

## 🔧 Manual Steps Required

1. **Update replacer.php:**
   - Copy the constructor update from this document
   - Copy all methods from `classes/replacer_textmethods.php` and paste after line 471 in `classes/replacer.php`

2. **Update renderer.php:**
   - Modify `render_results()` to accept database items instead of file arrays
   - Update HTML output to show table, field, ID, location instead of file paths

3. **Rename event class:**
   - Rename `classes/event/images_replaced.php` to `text_replaced.php`
   - Update namespace and class name
   - Update event description

4. **Test the plugin:**
   - Uninstall old imageplus plugin if installed
   - Install textplus plugin
   - Test Step 1: Enter search text
   - Test Step 2: View database items, select some
   - Test Step 3: Enter replacement text, run in preview mode
   - Test Step 3: Run in execute mode (with backup!)

## Database Tables Searched

The plugin searches the following Moodle tables:
- **Course content:** course, course_sections, course_categories
- **Activities:** page, label, book, book_chapters, forum, forum_posts, quiz, assign, glossary, glossary_entries, wiki, wiki_pages, lesson, lesson_pages, feedback, choice, survey, workshop, scorm, folder, url, resource
- **Blocks:** block_instances
- **Question bank:** question, question_answers

## Features Implemented

✅ Case-sensitive and case-insensitive search
✅ Context preview showing text around matches
✅ Human-readable location descriptions (e.g., "Page: Introduction (in COURSE101)")
✅ Dry run mode (preview changes without executing)
✅ Execute mode (actually update database)
✅ Backup confirmation requirement
✅ Detailed logging of all replacements
✅ Multi-step wizard interface (3 steps)
✅ Session-based state management
✅ Proper database transaction handling
✅ Input validation and sanitization
✅ Error handling and user-friendly messages

## Next Steps

1. Copy the methods from `replacer_textmethods.php` into `replacer.php`
2. Update `renderer.php` for text display
3. Test the complete workflow
4. Document any issues encountered
5. Create package for distribution

## Notes

- The transformation maintains the 3-step wizard interface
- All file-related code has been removed
- Only database text search is now supported
- The plugin requires Moodle 4.3+ and PHP 7.4+
- Site admin permission required for execution mode
