# Moodle TextPlus Plugin

A powerful Moodle plugin that allows site administrators to search and replace text content across their entire Moodle database. Find and update text in pages, activities, course content, labels, and more with a user-friendly multi-step wizard interface and comprehensive security controls.

**Developed by:** [G Wiz IT Solutions](https://gwizit.com)  
**Plugin Type:** Local  
**Version:** v1.0.0  
**Compatibility:** Moodle 4.3 to 5.1+  
**License:** GNU GPL v3 or later

---

## What's New in Version 1.0.0

### 🎯 Multi-Step Wizard Interface
- **Step 1: Search Criteria** - Define what text to find
- **Step 2: Content Selection** - Review and select specific content items with checkboxes
- **Step 3: Replacement Options** - Configure replacement settings with mandatory backup confirmation
- Visual step indicator showing progress
- Back/Next navigation between steps
- Session-based state management for seamless navigation

### 🔐 Enhanced Security
- **Site Administrator Only Access** - Restricted to users with `moodle/site:config` capability
- **Comprehensive XSS Protection** - All user input and output properly sanitized
- **Input Validation** - SQL injection prevention and strict input validation
- **Session Key Verification** - Multiple checkpoints throughout the workflow
- **Database Backup Confirmation** - Mandatory checkbox before executing replacements

### ✨ Improved User Experience
- Progressive disclosure - only relevant options shown at each step
- Interactive content selection with "Select All/Deselect All" functionality
- Clear error messages and user guidance
- Final warning before executing destructive operations
- Better visual feedback at each step

---

## Features

### Core Features
- 🔍 **Database-Wide Text Search**: Find text content in pages, activities, course descriptions, labels, and other text fields
- 🗄️ **Comprehensive Database Scanning**: Search across all major Moodle tables containing text content
- � **Selective Replacement**: Choose exactly which content items to update with checkbox selection
- 🔒 **Safe Dry Run Mode**: Test replacements without making any changes to the database
- 📊 **Detailed Replacement Log**: See success/failure status for every item updated
- 🔐 **Case Sensitivity Control**: Choose between case-sensitive and case-insensitive search
- 🌍 **Multi-Language Support**: Ready for internationalization
- 📝 **Operation History**: Database logging of all replacement operations
- ⚡ **Batch Processing**: Replace multiple files in a single operation

### Supported Content Types
- ✅ **Course Content**: Course names, descriptions, summaries
- ✅ **Pages**: Page content and introductions
- ✅ **Labels**: Label text across all courses
- ✅ **Activities**: Quiz descriptions, assignment details, forum posts
- ✅ **Sections**: Course section summaries and names
- ✅ **Books**: Book chapter content

---

## Installation

### Method 1: Via Moodle Plugin Installer (Recommended)

1. Download the plugin ZIP file
2. Log in to your Moodle site as an administrator
3. Go to **Site administration** → **Plugins** → **Install plugins**
4. Upload the ZIP file
5. **If prompted** with "Unable to detect the plugin type":
   - Select **"Local plugin (local)"** from the "Plugin type" dropdown
   - Confirm the plugin folder name shows as **"textplus"**
6. Click "Install plugin from the ZIP file"
7. Follow the on-screen instructions to complete the installation

**Note**: Some Moodle installations require manual plugin type selection for security. This is normal behavior.

### Method 2: Manual Installation (If ZIP upload fails)

**Recommended if you get "corrupted_archive_structure" error:**

1. Extract the plugin ZIP file
2. Copy the `textplus` folder to `[moodle-root]/local/`
3. Log in to your Moodle site as an administrator
4. Navigate to **Site administration** → **Notifications**
5. Click **"Upgrade Moodle database now"**
6. Follow the on-screen instructions to complete the installation

**Tip**: On Windows, you can use the included `manual_install.ps1` helper script.

### Method 3: Via Command Line

```bash
cd [moodle-root]/local/
git clone [repository-url] textplus
cd [moodle-root]
php admin/cli/upgrade.php
```

### Post-Installation: Clear Caches

**IMPORTANT:** After installing or updating the plugin, always clear Moodle's caches:

**Method 1: Via Web Interface**
1. Go to **Site administration** → **Development** → **Purge all caches**
2. Click "Purge all caches" button

**Method 2: Via Command Line (Faster)**
```bash
php admin/cli/purge_caches.php
```

**Why?** Moodle caches language strings. If you don't clear caches, you might see text displayed as `[[stringname]]` instead of the actual text. This is normal Moodle behavior for all plugins.

---

## Configuration

After installation, configure the plugin defaults:

1. Go to **Site administration** → **Plugins** → **Local plugins** → **TextPlus**
2. Configure the following settings:
   - **Default search term**: Default text to search for in database
   - **Default execution mode**: Dry Run or Execute (Dry Run recommended for safety)
   - **Search database by default**: Enable database searching (should be enabled)

---

## Usage

### Accessing the Tool

1. Log in as a **site administrator** (requires `moodle/site:config` capability)
2. Go to **Site administration** → **Server** → **TextPlus**

**Note:** Non-administrators will see an access denied error. This is intentional for security.

### Using the Multi-Step Wizard

#### Step 1: Define Search Criteria

1. **Enter Search Text**: Type the exact text string to search for
   - Simple text: `old company name`, `deprecated link`, `outdated information`
   - Case sensitive search option available
   
2. **Search Options**: Configure search parameters
   - Enable/disable case-sensitive search
   - Select which database tables to search
   - Documents (DOC, DOCX, ODT, TXT)
   - Videos (MP4, AVI, MOV, WebM)

3. Click **Find matching text** to proceed to Step 2

#### Step 2: Select Content to Replace

1. **Review Found Items**: See all database records containing your search text
   - Shows table name, field, record ID, and context
   - Preview of the text content with search term highlighted
   - Information about where the content appears (course, activity, etc.)

2. **Select Items**: 
   - Use checkboxes to select specific items to update
   - Use **Select All/Deselect All** buttons for bulk selection
   - Review each item carefully before selection

3. **Navigation**:
   - Click **Back** to modify search criteria
   - Click **Next** to proceed to replacement options

**Note:** At least one item must be selected to proceed.

#### Step 3: Replacement Options and Confirmation

1. **Enter Replacement Text**: 
   - Type the new text that will replace the search term
   - Leave empty to remove the search text completely
   - The replacement will maintain HTML formatting where applicable

2. **Configure Options**:
   - **Execution mode**: 
     - **Dry run**: See what would be changed without modifying the database (safe - recommended first)
     - **Execute changes**: Actually perform the replacements

3. **Database Backup Confirmation** ⚠️:
   - ☑️ **I confirm that a recent database backup has been made** (REQUIRED)
   - This checkbox must be checked before proceeding
   - Replacement operations cannot be undone

4. **Final Warning**: Read the warning about irreversible changes

5. Click **Execute Replacement** to complete the operation

### After Execution

- View detailed results showing success/failure for each item
- Review replacement log with statistics
- Click **Start Over** to begin a new replacement operation
- Consider clearing Moodle caches after replacements

### Database Tables Searched

The plugin searches text content in the following Moodle tables:
- `course` - Course names, descriptions, summaries
- `course_sections` - Course section summaries
- `page` - Page content
- `label` - Label text
- `book_chapters` - Book chapter content
- `forum_posts` - Forum post content
- `quiz` - Quiz descriptions
- `assign` - Assignment descriptions
- And other activity tables

---

## How It Works

1. **Search Phase**: The plugin scans selected database tables for text content containing your search term
2. **Analysis**: For each matching record, it determines:
   - Table and field location
   - Record context (course, activity, etc.)
   - Content preview
3. **Replacement**: The search text is:
   - Replaced with the new text in selected records
   - HTML formatting is preserved
   - Character encoding is maintained
4. **Database Updates**: For database records:
   - Records are updated with new text content
   - Timestamps are updated appropriately
   - Data integrity is maintained
5. **Logging**: All operations are logged for audit purposes

---

## Permissions

The plugin defines two capabilities and requires site administrator access:

- **`moodle/site:config`**: **REQUIRED** - Site administrator permission (checked before any access)
- **`local/textplus:view`**: View the TextPlus tool
- **`local/textplus:manage`**: Perform text replacement operations

**Security Note:** Only users with site administrator permissions can access this plugin. Non-administrators will see an error message directing them to contact their site administrator.

---

## Security Features

### Access Control
- Site administrator-only access (`moodle/site:config` capability required)
- Multiple permission checks throughout the workflow
- Session key verification on all form submissions
- Confirm session key on destructive operations (Step 3)

### Input Validation & Sanitization
- All user input validated and sanitized by Moodle form API
- Search text validated with `PARAM_TEXT` to prevent SQL injection
- Database record IDs validated as integers with `PARAM_INT`
- All text content escaped before database queries
- Text input sanitized before display

### Output Protection
- All displayed content escaped with `s()` function to prevent XSS
- JavaScript strings escaped with `addslashes_js()`
- HTML output uses Moodle's `html_writer` class
- Search terms and replacement text sanitized before display

### Database Query Protection
- Parameterized queries prevent SQL injection
- All database operations use Moodle's DML API
- Record validation before updates
- Transaction support for data integrity

### Mandatory Backup Confirmation
- Checkbox confirmation required before executing replacements
- Warning message displayed about irreversible operations
- User must acknowledge database backup has been made

---

## Best Practices

### Safety First
1. ✅ Always run in **Dry Run mode** first to see what will be changed
2. ✅ **BACKUP YOUR DATABASE** before running replacements
3. ✅ Use the backup confirmation checkbox consciously - it's there for a reason
4. ✅ Test with a specific search term on a small set of content first
5. ✅ Review the content selection list carefully in Step 2 before proceeding
6. ✅ Consider running a test on a staging/development environment first

### Performance
1. ⚡ For large sites, use specific search terms to limit matches
2. ⚡ Run operations during low-traffic periods
3. ⚡ Process content in smaller batches if you have many matches
4. ⚡ Increase PHP memory limit for large batch operations
5. ⚡ Consider database optimization after large replacements

### Text Replacement
1. 📝 Use exact text strings for search terms
2. 📝 Double-check replacement text for typos before executing
3. 📝 Be aware of case sensitivity settings
4. 📝 Consider HTML formatting when replacing text in rich content
5. 📝 Test replacement on a few items before bulk operations

### Workflow
1. 📋 **Step 1**: Start with specific search terms, then broaden if needed
2. 📋 **Step 2**: Carefully review and select only the content you intend to update
3. 📋 **Step 3**: Always check "Dry Run mode" first, then run again in "Execute mode"
4. 📋 Document what you've changed for future reference
5. 📋 Clear Moodle caches after completing replacements

---

## Requirements

### Moodle Requirements
- **Moodle version**: 4.3 to 5.1+ (fully tested and compatible)
- **PHP version**: 7.4 or higher (8.0+ recommended)

### PHP Extensions
- **Standard PHP libraries** (included by default)
  - Required for: Database operations and text processing
  - No special extensions needed

### Server Requirements
- Database access with sufficient permissions
- Sufficient PHP memory limit (128MB minimum, 256MB+ recommended for large batches)
- PHP `max_execution_time` sufficient for batch operations

---

## Troubleshooting

### Text Not Found
- Check that search term matches text exactly (case sensitivity matters)
- Verify you have selected the correct database tables
- Try a shorter or more general search term
- Check if the text is in a supported table

### GD Library Not Available
- **Symptom**: Warning displayed on main page
- **Impact**: Image cross-format conversion disabled (JPG→JPG only, PNG→PNG only)
- **Solution**: Ask system administrator to install PHP GD extension
- **Workaround**: Replace images with exact same format only

### Extension Mismatch Error
- **Symptom**: "Extension mismatch: pdf file cannot replace jpg files"
- **Cause**: Trying to replace PDF with JPG, or vice versa
- **Solution**: Upload a file with the same extension as target files
- **Note**: Images can cross-convert if "Allow cross-format" is enabled and GD is available

### Permission Denied Errors
- Verify you have the `local/textplus:manage` capability
- Check database permissions
- Ensure database user has update permissions on necessary tables

### Memory Errors
- Increase PHP memory limit in php.ini
- Process fewer items at once
- Use selective checkboxes to process content in batches

### No Items Selected Error
- **Symptom**: Alert when clicking "Replace in selected items"
- **Cause**: No checkboxes are selected
- **Solution**: Check at least one item to update

### Database Connection Issues
- Verify database connection settings
- Check database user permissions
- Ensure sufficient database resources

---

## Changelog

### Version 1.0.0 (2025-10-20)
**Initial Release - TextPlus**

**Core Features:**
- 🎯 Multi-step wizard interface (3 steps with visual progress)
- 🔐 Site administrator-only access
- ✨ Interactive content selection with checkboxes
- 📊 Database-wide text search and replace
- ⚠️ Prominent safety warnings and database backup reminders
- 🔒 Mandatory database backup confirmation checkbox
- � Dry run mode for safe testing
- 📁 Support for major Moodle content types

**Security:**
- Comprehensive SQL injection protection
- XSS protection throughout
- Session key verification
- Input validation and sanitization

**Supported Content:**
- Course names, descriptions, and summaries
- Page content
- Label text
- Activity descriptions
- Forum posts
- Book chapters
- And more

---

## Support

For issues, questions, or feature requests:

- **Website**: [https://gwizit.com](https://gwizit.com)
- **Email**: Contact through gwizit.com
- **Moodle Plugins**: [Plugin page on Moodle.org]

---

## License

This plugin is licensed under the [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html).

---

## Credits

**Developed by:** G Wiz IT Solutions  
**Website:** [https://gwizit.com](https://gwizit.com)  
**Copyright:** 2025 G Wiz IT Solutions

---

## Technical Details

### Database Tables

**`mdl_local_textplus_log`**
- Stores operation history
- Tracks search terms, items processed, success/failure counts
- Includes dry run indicator and timestamp information

### File Structure
```
textplus/
├── classes/
│   ├── event/
│   │   └── text_replaced.php
│   ├── form/
│   │   └── replacer_form.php
│   ├── privacy/
│   │   └── provider.php
│   └── replacer.php
├── db/
│   ├── access.php
│   └── install.xml
├── lang/
│   └── en/
│       └── local_textplus.php
├── index.php
├── renderer.php
├── settings.php
├── version.php
└── README.md
```

---

## Privacy

This plugin implements Moodle's Privacy API and is GDPR compliant:
- Logs which user performed replacement operations
- Stores search terms and operation results
- Provides data export for user data
- Supports data deletion requests
- Does not access personal user data unless specifically targeted

---

## Security

- Requires administrator/manager capabilities
- Session key validation on all operations
- SQL injection protection via Moodle's DML API
- XSS protection via proper output escaping
- Input validation and sanitization

---

## Contributing

Contributions are welcome! Please contact G Wiz IT Solutions through [gwizit.com](https://gwizit.com) for more information.

---

**Thank you for using TextPlus by G Wiz IT Solutions!** 🎓
