# TextPlus Form Updates - Simplified for Text Search & Replace

## Overview
Updated `classes/form/replacer_form.php` to remove all file-related functionality and focus solely on database text search and replace operations.

## Changes Made

### **Step 1: Search Criteria** ✅

**Removed:**
- ❌ File type selector (image, PDF, ZIP, doc, video, audio)
- ❌ "Search database" checkbox (always enabled)
- ❌ "Search file system" checkbox (not applicable)

**Added:**
- ✅ Case sensitive search checkbox

**Now includes:**
- Search term input (required)
- Case sensitive option
- Find matching text button

---

### **Step 2: Content Selection** ✅

**Updated:**
- Changed from "File selection" to "Content selection"
- Removed file type display from summary
- Changed selection area ID from `file-selection-area` to `content-selection-area`

**Removed hidden fields:**
- ❌ `filetype`
- ❌ `searchdatabase`
- ❌ `searchfilesystem`

**Added hidden fields:**
- ✅ `casesensitive`

**Now preserves:**
- Search term
- Case sensitive setting
- Selected content items

---

### **Step 3: Replacement Options** ✅

**Completely redesigned for text replacement:**

**Removed:**
- ❌ File upload (filepicker)
- ❌ File type validation
- ❌ GD library checks
- ❌ Cross-format image conversion option
- ❌ Preserve permissions checkbox
- ❌ All file-related logic

**Added:**
- ✅ Replacement text textarea (5 rows, 50 cols)
- ✅ Instruction message showing what text will be replaced
- ✅ Help text for replacement field

**Kept:**
- ✅ Execution mode (Preview/Execute → Dry Run/Execute)
- ✅ Backup confirmation checkbox (now for database backup)
- ✅ Final warning message
- ✅ Back and Execute buttons
- ✅ Start over link

**Now preserves:**
- Search term
- Case sensitive setting
- Selected items
- Replacement text

---

### **Validation Function** ✅

**Simplified validation:**
- Only validates that search term is not empty
- Removed database/filesystem checkbox validation (not needed)

---

## Form Flow

### **Step 1: Search Text**
```
[Search term: ________________]
[☐ Case sensitive search]
[Find matching text]
```

### **Step 2: Select Content**
```
Search term: "old text"
[List of database records containing the search term]
[☐] Course: Introduction - Field: summary
[☐] Page: Welcome - Field: content
[☐] Label: Notice - Field: intro
...
[Back] [Next]
```

### **Step 3: Replace Text**
```
Replacement text: _________________
                  _________________
Execution mode: [Dry Run ▼]
[☐] I confirm database backup has been made
⚠️ WARNING: Permanent changes!
[Back] [Execute Replacement]
```

---

## Hidden Fields

### Step 1 → Step 2:
- `searchterm`
- `casesensitive`

### Step 2 → Step 3:
- `searchterm`
- `casesensitive`
- `selecteditems` (from session)

---

## Language Strings Required

The form now uses these language strings:

**Step 1:**
- `step1_header`
- `searchterm`
- `searchterm_help`
- `casesensitive`
- `casesensitive_help`
- `findbtn`

**Step 2:**
- `step2_header`
- `searchterm` (for summary)
- `back`
- `next`

**Step 3:**
- `step3_header`
- `enterreplacement_instruction`
- `replacementtext`
- `replacementtext_help`
- `executionmode`
- `executionmode_help`
- `mode_preview`
- `mode_execute`
- `backupconfirm`
- `backupconfirm_help`
- `backupconfirm_required`
- `final_warning`
- `execute_replacement`
- `startover`
- `back`

---

## Next Steps

To complete the TextPlus functionality, you'll need to:

1. **Update `index.php`:**
   - Remove file search logic
   - Implement database text search in Step 1
   - Display database records in Step 2
   - Handle text replacement in Step 3
   - Remove file upload handling

2. **Update session variables:**
   - Change `filetype` → not needed
   - Change `searchdatabase` → always true
   - Change `searchfilesystem` → remove
   - Add `casesensitive`
   - Change `selectedfiles` → `selecteditems`
   - Add `replacementtext`

3. **Update `replacer.php` class:**
   - Remove file operation methods
   - Add database search methods
   - Add text replacement methods
   - Query text content from database tables

4. **Update `renderer.php`:**
   - Remove file list rendering
   - Add database content list rendering
   - Show text preview with search term highlighted
   - Display table/field/record information

---

**Form Updates Complete!** ✅

The form is now fully adapted for text search and replace operations.

Date: October 20, 2025
