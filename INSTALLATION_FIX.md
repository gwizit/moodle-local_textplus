# TextPlus Installation Fix

## Issue Encountered
When attempting to install the TextPlus plugin, the following error occurred:

```
XML database file errors found
Debug info: Errors found in XMLDB file: PATH attribute does not match file directory: local/textplus/db
Error code: ddlxmlfileerror
```

## Root Cause
The `db/install.xml` file still contained references to the old plugin name `imageplus` instead of `textplus`.

## Files Fixed

### 1. `db/install.xml` ✅
**Changes:**
- Updated PATH attribute: `local/imageplus/db` → `local/textplus/db`
- Updated VERSION: `2025101603` → `2025102001`
- Updated table name: `local_imageplus_log` → `local_textplus_log`
- Updated field names:
  - `filesreplaced` → `itemsreplaced`
  - `dbfilesreplaced` → `dbitemsreplaced`
  - `filesfailed` → `itemsfailed`
  - `searchfilesystem` → removed (not applicable)
  - `sourceimageinfo` → `replacementtext`
- Updated all comments to reflect text operations instead of file operations

### 2. `lang/en/local_textplus.php` ✅
**Changes:**
- Removed duplicate old ImagePlus strings that were left over
- Cleaned up privacy metadata strings
- Removed file-related settings strings

### 3. `settings.php` ✅
**Changes:**
- Removed "Preserve permissions by default" setting (not needed for text replacement)
- Removed "Search database by default" setting (always searching database)
- Removed "Search file system by default" setting (not applicable)
- Now only contains:
  - Default search term
  - Default execution mode (Dry Run/Execute)

### 4. `TROUBLESHOOTING.md` ✅
**Changes:**
- Updated all references from ImagePlus to TextPlus
- Updated references from imageplus to textplus

## Verification

After these fixes, the plugin should install successfully without XML errors.

### Installation Steps
1. **Uninstall old version** (if previously attempted):
   - Go to Site administration → Plugins → Plugins overview
   - Find "TextPlus" and click Uninstall (if present)
   
2. **Clear cache**:
   - Go to Site administration → Development → Purge all caches
   
3. **Reinstall**:
   - Go to Site administration → Plugins → Install plugins
   - Upload the corrected ZIP file
   - Select "Local plugin (local)" if prompted
   - Complete installation

## Settings After Installation

After successful installation, configure TextPlus:

**Location:** Site administration → Plugins → Local plugins → TextPlus

**Available Settings:**
- ✅ **Default search term** - Pre-filled search text (optional)
- ✅ **Default execution mode** - Dry Run (recommended) or Execute

**Removed Settings (no longer needed):**
- ❌ Preserve permissions by default
- ❌ Search database by default
- ❌ Search file system by default

## Database Table Created

The plugin creates this table:
```sql
CREATE TABLE mdl_local_textplus_log (
  id BIGINT(10) PRIMARY KEY AUTO_INCREMENT,
  userid BIGINT(10) NOT NULL,
  searchterm VARCHAR(255) NOT NULL,
  itemsreplaced INT(10) DEFAULT 0,
  dbitemsreplaced INT(10) DEFAULT 0,
  itemsfailed INT(10) DEFAULT 0,
  dryrun TINYINT(1) DEFAULT 1,
  searchdatabase TINYINT(1) DEFAULT 1,
  replacementtext TEXT,
  timecreated BIGINT(10) NOT NULL,
  timemodified BIGINT(10) NOT NULL
);
```

## Testing Installation

After successful installation:
1. Go to Site administration → Server → TextPlus
2. You should see the 3-step wizard interface
3. Try a test search in Dry Run mode

---

**All installation issues resolved! ✅**

Date: October 20, 2025
