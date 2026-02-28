# Changes Summary - TextPlus Plugin Updates

---

## Version 3.1.0 - Moodle 5.2 Compatibility

### Date: February 28, 2026

### ✅ COMPLETE

#### Bootstrap 5 Migration
Moodle 5.2 uses Bootstrap 5, which renamed several CSS utility classes. The following changes were made:

| File | Old (Bootstrap 4) | New (Bootstrap 5) |
|------|-------------------|-------------------|
| `templates/item_selection.mustache` | `mr-2` | `me-2` |
| `classes/form/replacer_form.php` | `ml-2` | `ms-2` |

#### No External API Migration Needed
This plugin does not use Moodle external services (`externallib.php`), so no migration from legacy global classes to the `core_external\*` namespace was required.

#### Full Compatibility Audit
The following Bootstrap 4 patterns were verified as **not present** in the codebase — no changes needed:
- `data-toggle` → `data-bs-toggle`
- `badge-success` / `badge-secondary` → `text-bg-success` / `text-bg-secondary`
- `custom-control custom-checkbox` → `form-check`
- `font-weight-bold` → `fw-bold`
- `text-left` / `text-right` → `text-start` / `text-end`
- `btn-block` → `d-block w-100`
- `form-row` → `row g-3`
- `form-control` on `<select>` → `form-select` (Moodle form API handles this internally)
- `float-left` / `float-right` → `float-start` / `float-end`

---

## Version 3.0.7 - Security Hardening

### Date: December 19, 2025

### ✅ COMPLETE

#### Changes Made
- CSRF hardening: the "Start over" wizard action now requires a valid sesskey.
- Reverse-tabnabbing mitigation: links opened with `target="_blank"` now include `rel="noopener noreferrer"`.
- Defense-in-depth: unserialization restricted to `stdClass` objects only.
- Statistics: tracked total occurrences replaced for accurate results reporting.

---

## Version 1.0.3 - Cache API Migration

### Date: October 24, 2025

### ✅ COMPLETE - Migrated to Moodle Cache API

#### Problem Solved
Following Moodle plugin review feedback, the plugin now uses Moodle's Cache API instead of direct `$SESSION` usage. This aligns with Moodle coding standards and best practices.

**Reference:** [Moodle Universal Cache (MUC)](https://moodledev.io/docs/5.0/apis/subsystems/muc)

#### Changes Made

**1. Created Cache Definition**
- **File:** `db/caches.php` (NEW)
- **Cache Type:** Session-based cache
- **Purpose:** Store wizard state data across multi-step form process

```php
$definitions = [
    'wizarddata' => [
        'mode' => cache_store::MODE_SESSION,
        'simplekeys' => true,
        'simpledata' => false,
        'staticacceleration' => true,
        'staticaccelerationsize' => 1,
    ],
];
```

**2. Updated index.php**
- Replaced all `$SESSION->textplus_wizard` references with Cache API calls
- Added helper functions:
  - `get_wizard_cache()` - Get cache instance
  - `get_wizard_data()` - Retrieve wizard state
  - `set_wizard_data()` - Save wizard state
  - `clear_wizard_data()` - Clear wizard state

**3. Benefits**
- ✅ Follows Moodle coding standards
- ✅ Better abstraction and flexibility
- ✅ Supports multiple cache backends
- ✅ Cleaner separation of concerns
- ✅ Easier to test and maintain
- ✅ Standards compliant for Moodle plugin directory

**4. Version Update**
- Version: `1.0.2` → `1.0.3`
- Updated all documentation references

**Status:** ✅ READY FOR MOODLE PLUGIN REVIEW

---

## Version 1.0.1 - Edwiser Page Builder Support

### Date: October 22, 2025

### ✅ COMPLETE - Full Edwiser Page Builder Support Added

### Problem Solved
Edwiser Page Builder content is now fully searchable! The plugin searches **ALL** Edwiser tables where content is stored:
- Published pages
- Draft pages  
- Reusable blocks
- Block layouts
- Theme configuration
- Course format layouts

---

## 📊 Tables Now Supported

### 1. Edwiser Page Builder - Published Pages ⭐ PRIMARY
- **Table:** `mdl_edw_pages`
- **Fields:** `pagename`, `pagedesc`, `pagecontent` (JSON), `seotag`, `seodesc`
- **JSON Structure:** `{"text": "<p>HTML content here</p>"}`

### 2. Edwiser Page Builder - Draft Pages
- **Table:** `mdl_edw_pages_draft`
- **Fields:** Same as published pages
- **Purpose:** Unpublished/work-in-progress pages

### 3. Edwiser Page Builder - Reusable Blocks
- **Table:** `mdl_edw_page_blocks`
- **Fields:** `title`, `label`, `content` (JSON)

### 4. Edwiser Page Builder - Block Layouts
- **Table:** `mdl_edw_page_block_layouts`
- **Fields:** `title`, `label`, `content` (JSON)

### 5. Edwiser RemUI Theme Configuration
- **Table:** `mdl_config_plugins`
- **Filter:** `WHERE plugin = 'theme_remui'`
- **Field:** `value`

### 6. Edwiser RemUI Format Layouts
- **Table:** `mdl_format_remuilayout`
- **Field:** `layoutdata` (JSON)

---

## 🔧 Code Changes

### File: `textplus/classes/replacer.php`

**1. Added Edwiser Page Builder Tables (Lines ~135-155)**
```php
// Published pages
if ($DB->get_manager()->table_exists('edw_pages')) {
    $searchtables['edw_pages'] = ['pagename', 'pagedesc', 'pagecontent', 'seotag', 'seodesc'];
}

// Draft pages
if ($DB->get_manager()->table_exists('edw_pages_draft')) {
    $searchtables['edw_pages_draft'] = ['pagename', 'pagedesc', 'pagecontent', 'seotag', 'seodesc'];
}

// Reusable blocks
if ($DB->get_manager()->table_exists('edw_page_blocks')) {
    $searchtables['edw_page_blocks'] = ['title', 'label', 'content'];
}

// Block layouts
if ($DB->get_manager()->table_exists('edw_page_block_layouts')) {
    $searchtables['edw_page_block_layouts'] = ['title', 'label', 'content'];
}
```

**2. Updated JSON Field Detection (Lines ~250)**
```php
protected function is_json_field($table, $field) {
    $jsonfields = [
        'edw_pages' => ['pagecontent'],
        'edw_pages_draft' => ['pagecontent'],
        'edw_page_blocks' => ['content'],
        'edw_page_block_layouts' => ['content'],
        'format_remuilayout' => ['layoutdata'],
        'block_instances' => ['configdata'],
    ];
    return isset($jsonfields[$table]) && in_array($field, $jsonfields[$table]);
}
```

**3. Added URL Generation (Lines ~560)**
- Published pages → `/local/edwiserpagebuilder/page.php?id=X`
- Draft pages → `/local/edwiserpagebuilder/pagedraft.php?id=X`
- Blocks/Layouts → `/local/edwiserpagebuilder/managepages.php`

**4. Added Human-Readable Locations (Lines ~690)**
- "Edwiser Page Builder - Published Page: [Name]"
- "Edwiser Page Builder - Draft Page: [Name]"
- "Edwiser Page Builder - Reusable Block: [Name]"
- "Edwiser Page Builder - Block Layout: [Name]"

---

## 🎯 Use Case Example

### Replacing URL: `pyramidonlinelearning.com/moodle` → `pyramidonlinelearning.com`

**What Gets Found:**
1. ✅ Published pages with the URL
2. ✅ Draft pages with the URL
3. ✅ Reusable blocks with the URL
4. ✅ Block layouts with the URL
5. ✅ Theme settings with the URL
6. ✅ Course layouts with the URL

**JSON Handling Example:**
```json
// Before:
{
  "text": "<p>Visit <a href='http://pyramidonlinelearning.com/moodle'>our site</a></p>"
}

// After replacement:
{
  "text": "<p>Visit <a href='http://pyramidonlinelearning.com'>our site</a></p>"
}
```

---

## 🧪 Testing Instructions

### Step 1: Run Test Script
```
http://your-moodle-site.com/local/textplus/test_edwiser.php
```

**What it shows:**
- Edwiser Page Builder installation status
- List of published pages
- Search for specific URLs
- Which tables contain your content

### Step 2: Use Text Replacer
```
http://your-moodle-site.com/local/textplus/index.php
```

**Process:**
1. Enter search term: `pyramidonlinelearning.com/moodle`
2. Click "Next"
3. Review found items (should show Edwiser pages)
4. Select items to update
5. Enter replacement: `pyramidonlinelearning.com`
6. **Use Preview mode first!**
7. Execute changes
8. Clear Moodle caches

---

## 📁 Files Created/Modified

### Modified
- ✅ `textplus/classes/replacer.php` - Core search engine with Edwiser support

### Created
- ✅ `textplus/EDWISER_SUPPORT.md` - Complete technical documentation
- ✅ `textplus/TEST_INSTRUCTIONS.md` - User testing guide
- ✅ `textplus/test_edwiser.php` - Interactive test tool
- ✅ `textplus/CHANGES_SUMMARY.md` - This file

---

## ⚠️ Important Notes

### JSON Safety
- ✅ All JSON operations have error handling
- ✅ Falls back to text search if JSON is invalid
- ✅ Preserves exact JSON structure
- ✅ Uses `JSON_UNESCAPED_SLASHES` and `JSON_UNESCAPED_UNICODE`

### Database Safety
- ✅ Uses parameterized queries (no SQL injection)
- ✅ Moodle DML API compliance
- ✅ Transaction support
- ✅ Only searches non-deleted pages (`deleted = 0`)

### Performance
- ✅ Indexed field searches
- ✅ Efficient JSON decode (once per record)
- ✅ Recursive search through JSON structures
- ✅ Accurate occurrence counting

---

## 🎉 What You Can Now Replace

### Edwiser Page Builder Content
- Page titles and descriptions
- Page HTML content (inside JSON)
- SEO tags and descriptions
- Reusable block content
- Block layout templates

### Edwiser RemUI Theme
- Frontpage content blocks
- Announcement text
- About us sections
- Custom HTML areas
- All HTML editor fields

### Course Layouts
- Custom section layouts
- Course-specific formatting

---

## 🔍 Verification Steps

### 1. Check Test Script
```bash
# Access test page
curl http://your-site.com/local/textplus/test_edwiser.php
```

### 2. Search for Content
- Go to Text Replacer
- Search: `pyramidonlinelearning.com/moodle`
- Expected results: "Edwiser Page Builder" items appear

### 3. Preview Changes
- Select one or two items
- Use "Preview" mode
- Verify replacements look correct

### 4. Execute
- Backup database first!
- Execute changes
- Clear caches
- Test pages load correctly

---

## 📞 Troubleshooting

### "No items found"
1. Check test script - are pages listed?
2. Verify Edwiser Page Builder is installed
3. Check database for `mdl_edw_pages` table
4. Ensure pages are not deleted (`deleted = 0`)

### "Permission denied"
- Must be site administrator
- Requires `moodle/site:config` capability

### "JSON error"
- Plugin will fall back to text search
- Check PHP logs for details
- Verify JSON structure in database

---

## ✨ Result

Your Text Replacer plugin now has **COMPLETE** Edwiser support:

✅ **6 table types** searched  
✅ **JSON content** properly handled  
✅ **Published & draft** pages included  
✅ **Reusable blocks** searchable  
✅ **Theme config** included  
✅ **Direct links** to edit pages  

The URL `pyramidonlinelearning.com/moodle` can now be found and replaced with `pyramidonlinelearning.com` across **ALL** Edwiser content!

---

**Status:** ✅ READY FOR PRODUCTION USE
**Last Updated:** October 22, 2025
