# TextPlus - Troubleshooting Guide# TextPlus - Troubleshooting Guide



**Version:** 1.0.1  ## Version 3.0.3

**Updated:** October 21, 2025  

**Compatibility:** Moodle 4.3 to 5.1+This guide covers common issues and solutions for the TextPlus plugin.



This guide covers common issues and solutions for the TextPlus plugin.**Compatibility:** Moodle 4.3 to 5.1+



------



## Table of Contents## Table of Contents



1. [Installation Issues](#installation-issues)1. [Installation Issues](#installation-issues)

2. [Display Issues](#display-issues)2. [Display Issues](#display-issues)

3. [Access and Permission Issues](#access-and-permission-issues)3. [Access and Permission Issues](#access-and-permission-issues)

4. [Code Snippet Pop-up Issues](#code-snippet-pop-up-issues)4. [File Search Issues](#file-search-issues)

5. [Search and Replace Issues](#search-and-replace-issues)5. [File Replacement Issues](#file-replacement-issues)

6. [Performance Issues](#performance-issues)6. [Performance Issues](#performance-issues)

7. [Wizard Navigation Issues](#wizard-navigation-issues)7. [Image Quality Issues](#image-quality-issues)

8. [Wizard Navigation Issues](#wizard-navigation-issues)

---9. [Security Issues](#security-issues)



## Installation Issues---



### Error: "corrupted_archive_structure"## Display Issues



**Cause:** Moodle's strict ZIP validation or browser upload issues.### Language Strings Showing as [[stringname]]



**Solution 1: Manual Installation (RECOMMENDED)****Symptoms:**

- Text appears as `[[nodatabase contentreplaced]]` instead of "No database content were replaced"

1. Extract the ZIP file- Text appears as `[[preview_mode_warning]]` instead of the actual warning message

2. Copy the `textplus` folder to `[your-moodle]/local/`

3. Ensure the path is: `[moodle]/local/textplus/version.php`**Cause:** Moodle's language string cache is outdated after plugin installation/update.

4. Visit **Site administration → Notifications** in Moodle

5. Click **"Upgrade Moodle database now"****Solution 1: Purge All Caches (RECOMMENDED)**

1. Go to **Site administration → Development → Purge all caches**

**On Linux/Mac:**2. Click "Purge all caches" button

```bash3. Refresh the TextPlus page

cd /path/to/moodle/local/

unzip moodle-local_textplus-v1.0.1.zip**Solution 2: Update Language Strings**

cd ..1. Go to **Site administration → Language → Language customization**

sudo chown -R www-data:www-data local/textplus2. Click "Edit strings" for English language

sudo chmod -R 755 local/textplus3. Click "Continue" (you don't need to edit anything)

php admin/cli/upgrade.php4. This will rebuild the language cache

```

**Solution 3: CLI Method (Fastest)**

**Solution 2: Try Different Browser**```bash

- Try Chrome, Firefox, or Edgephp admin/cli/purge_caches.php

- Clear browser cache```

- Disable browser extensions temporarily

**Prevention:**

### Error: "Unable to detect the plugin type"- Always purge caches after installing or updating any plugin

- Enable developer mode during development: **Site administration → Development → Debugging**

**This is NORMAL** - Moodle 5.x security feature.

---

**Solution:**

1. When prompted, select **"Local plugin (local)"** from dropdown## Installation Issues

2. Verify folder name shows as **"textplus"**

3. Click "Install plugin from the ZIP file"### Error: "corrupted_archive_structure"

4. Continue with installation

**Cause:** Moodle's strict ZIP validation or browser upload issues.

### Plugin Not Appearing After Installation

**Solution 1: Manual Installation (RECOMMENDED)**

**Solution:**

1. Go to **Site administration → Notifications** and run any upgrades1. Extract the ZIP file

2. Clear caches: **Site administration → Development → Purge all caches**2. Copy the `TextPlus` folder to `[your-moodle]/local/`

3. Verify files exist:3. Ensure the path is: `[moodle]/local/TextPlus/version.php`

   ```4. Visit **Site administration → Notifications** in Moodle

   [moodle]/local/textplus/version.php5. Click **"Upgrade Moodle database now"**

   [moodle]/local/textplus/settings.php

   [moodle]/local/textplus/index.php**On Linux/Mac:**

   ``````bash

4. Check PHP error logs for issuescd /path/to/moodle/local/

unzip moodle-local_TextPlus-v3.0.0.zip

---cd ..

sudo chown -R www-data:www-data local/TextPlus

## Display Issuessudo chmod -R 755 local/TextPlus

php admin/cli/upgrade.php

### Language Strings Showing as [[stringname]]```



**Symptoms:****Solution 2: Try Different Browser**

- Text appears as `[[nodatabasecontentsreplaced]]` instead of actual message- Try Chrome, Firefox, or Edge

- Text appears as `[[preview_mode_warning]]` instead of warning- Clear browser cache

- Disable browser extensions temporarily

**Cause:** Moodle's language string cache is outdated after plugin installation/update.

### Error: "Unable to detect the plugin type"

**Solution 1: Purge All Caches (RECOMMENDED)**

1. Go to **Site administration → Development → Purge all caches****This is NORMAL** - Moodle 5.x security feature.

2. Click "Purge all caches" button

3. Refresh the TextPlus page**Solution:**

1. When prompted, select **"Local plugin (local)"** from dropdown

**Solution 2: CLI Method (Fastest)**2. Verify folder name shows as **"TextPlus"**

```bash3. Click "Install plugin from the ZIP file"

php admin/cli/purge_caches.php4. Continue with installation

```

### Plugin Not Appearing After Installation

**Prevention:**

- Always purge caches after installing or updating any plugin**Solution:**

- This is standard Moodle behavior for all plugins1. Go to **Site administration → Notifications** and run any upgrades

2. Clear caches: **Site administration → Development → Purge all caches**

---3. Verify database content exist:

   ```

## Access and Permission Issues   [moodle]/local/TextPlus/version.php

   [moodle]/local/TextPlus/settings.php

### Error: "Access denied. This tool is only available to site administrators"   [moodle]/local/TextPlus/index.php

   ```

**Cause:** User does not have site administrator permissions.4. Check PHP error logs for issues



**Solution:**---

- This is intentional security restriction

- Only users with `moodle/site:config` capability can access## Access and Permission Issues

- Contact your site administrator if you need access

- Administrators can verify permissions at: **Site administration → Users → Permissions**### Error: "Access denied. This tool is only available to site administrators"



### Session Expired or Invalid Session Key**Cause:** User does not have site administrator permissions.



**Cause:** Form session expired or CSRF protection triggered.**Solution:**

- This is intentional security restriction in v3.0.0

**Solution:**- Only users with `moodle/site:config` capability can access

1. Refresh the page and log in again- Contact your site administrator if you need access

2. Don't use browser back button - use wizard's "Back" button- Administrators can verify permissions at: **Site administration → Users → Permissions**

3. Don't let the page sit idle for extended periods

4. Clear browser cookies and log in again**Note:** Unlike previous versions, the `local/TextPlus:manage` capability is NOT sufficient. Site administrator permission is required.



---### Error: "You do not have permission to use this tool"



## Code Snippet Pop-up Issues**Cause:** Missing plugin-specific capability.



### Pop-up Shows Escaped HTML Characters (&amp;lt;, &amp;gt;, &amp;quot;)**Solution:**

1. Ensure you are a site administrator first

**Fixed in v1.0.1!**2. Verify you have `local/TextPlus:view` and `local/TextPlus:manage` capabilities

3. Check role assignments: **Site administration → Users → Permissions → Assign system roles**

If you're still seeing this:

1. Verify you're running version 1.0.1 (check **Site administration → Plugins → Local plugins → TextPlus**)### Session Expired or Invalid Session Key

2. Clear browser cache (Ctrl+Shift+Delete)

3. Purge Moodle caches**Cause:** Form session expired or CSRF protection triggered.

4. Hard refresh the page (Ctrl+F5)

**Solution:**

### Pop-up Shows Garbled Non-English Text (Japanese, Chinese, Arabic)1. Refresh the page and log in again

2. Don't use browser back button - use wizard's "Back" button

**Symptoms:**3. Don't let the page sit idle for extended periods

- Japanese text like "このコース" displays as "ãã®ã³ã¼ã¹"4. Clear browser cookies and log in again

- Characters appear corrupted or mojibake

---

**Fixed in v1.0.1!**

## File Search Issues

**Solution:**

1. Update to version 1.0.1### No database content Found

2. Clear browser and Moodle caches

3. The plugin now uses UTF-8 safe base64 encoding/decoding**Possible Causes & Solutions:**



### Pop-up is Blank or Shows Nothing1. **Incorrect Search Term**

   - Try using wildcards: `logo*` instead of just `logo`

**Causes & Solutions:**   - Check spelling and case sensitivity

   - Try a simpler, broader search term first

1. **Empty Content** (Fixed in v1.0.1)

   - Blank contexts are now filtered out automatically2. **Wrong File Type Selected**

   - Verify you selected correct type (Images, PDFs, etc.)

2. **JavaScript Error**   - Remember: Images = JPG/PNG/WebP only

   - Open browser console (F12) to check for errors

   - If you see "URIError: malformed URI sequence" - update to v1.0.13. **Wrong Search Locations**

   - Check both "Include database database content" AND "Include file system" boxes

3. **Browser Issues**   - Database database content are in Moodle's file storage (`mdl_database content` table)

   - Try different browser (Chrome, Firefox, Edge)   - database contentystem database content are in Moodle installation directories

   - Disable browser extensions

   - Clear browser cache4. **database content in Unsupported Locations**

   - Plugin only searches specific directories (see README)

### Error: "URIError: malformed URI sequence" in Console   - Custom theme directories may not be scanned



**Fixed in v1.0.1!**### Wildcard Search Not Working



The plugin now has robust error handling with fallback decoding methods.**Cause:** Incorrect wildcard syntax.



**If still occurring:****Correct Syntax:**

1. Update to version 1.0.1- `logo*` - Matches logo.png, logo-2024.jpg, logo_old.gif

2. Check browser console for additional errors- `*logo` - Matches mylogo.png, newlogo.jpg

3. Try different browser- `banner?` - Matches banner1, banner2, bannerA

- `ba*er` - Matches banner, batter, badger

### Clicking X (Close Button) Refreshes the Page

**Common Mistakes:**

**Fixed in v1.0.1!**- Using regex instead of wildcards

- Using `%` instead of `*` (SQL syntax doesn't work here)

The close button now has `type="button"` to prevent form submission.

### Duplicate database content in Results

**If still occurring:**

- Clear browser cache**Cause:** Same file exists in both database and database contentystem.

- Hard refresh (Ctrl+F5)

- Update to v1.0.1**Solution:**

- This is normal - Moodle stores database content in database AND database contentystem

### Code Snippet Not Showing HTML Tags Correctly- Select which instance(s) to replace in Step 2

- Usually safer to replace database version only

**Expected Behavior (v1.0.1):**

- HTML tags like `<div>`, `<p>`, `<a>` should display as text, not be rendered---

- You should see the actual HTML code, like viewing source

## File Replacement Issues

**If tags are being rendered:**

1. Update to v1.0.1### Error: "Please select at least one file to replace"

2. The plugin now uses `textContent` instead of `innerHTML` for safe display

**Cause:** No checkboxes selected in Step 2.

---

**Solution:**

## Search and Replace Issues1. Go back to Step 2

2. Check at least one file using the checkboxes

### No Items Found in Step 13. Or use "Select All" button

4. Click "Next" to proceed

**Possible Causes & Solutions:**

### Error: "Extension mismatch: pdf file cannot replace jpg database content"

1. **Incorrect Search Term**

   - Check spelling and case sensitivity**Cause:** Uploaded file type doesn't match target database content.

   - Try a simpler, broader search term

   - Database content may not contain exact text**Solution:**

1. Upload file with matching extension:

2. **Wrong Tables Selected**   - PDF → PDF only

   - Verify you've selected correct database tables to search   - ZIP → ZIP only

   - Try searching all tables first   - JPG → JPG (or PNG/WebP if cross-format enabled)

2. For images: Enable "Allow cross-format text replacement" in Step 3

3. **Content Not in Database**3. Verify GD library is available for cross-format conversion

   - Some content may be in files, not database

   - TextPlus searches database text fields only### Error: "Invalid file type. Please upload a JPEG, PNG, or WebP image"



### Error: "Please select at least one item to replace"**Cause:** Uploaded file doesn't match selected file type category.



**Cause:** No checkboxes selected in Step 2.**Solution:**

1. Check file extension matches what you're replacing

**Solution:**2. Verify file is not corrupted

1. Go back to Step 23. For images: Only JPG, PNG, WebP are supported

2. Check at least one item using the checkboxes4. Try converting file to supported format first

3. Or use "Select All" button

4. Click "Next" to proceed### Backup Confirmation Not Working



### Backup Confirmation Not Working**Cause:** Checkbox not checked or JavaScript disabled.



**Cause:** Checkbox not checked or JavaScript disabled.**Solution:**

1. Scroll down in Step 3

**Solution:**2. Check the box: "I confirm that a recent backup has been made"

1. Scroll down in Step 33. If checkbox is missing, enable JavaScript in your browser

2. Check the box: "I confirm that a recent backup has been made"4. Read the warning message carefully before proceeding

3. If checkbox is missing, enable JavaScript in your browser

4. Read the warning message carefully before proceeding### database content Not Actually Replaced



### Content Not Actually Replaced**Possible Causes:**



**Possible Causes:**1. **Preview Mode Selected**

   - Solution: Change "Execution mode" to "Execute changes" in Step 3

1. **Preview Mode Selected**

   - Solution: Change "Execution mode" to "Execute changes" in Step 32. **Permission Errors**

   - Check PHP error logs

2. **Permission Errors**   - Verify web server has write permissions

   - Check PHP error logs   - On Linux: `sudo chown -R www-data:www-data [moodle-dirroot]`

   - Verify database user has UPDATE permissions

3. **Memory Limit Exceeded**

3. **Memory Limit Exceeded**   - Increase PHP memory limit in php.ini

   - Increase PHP memory limit in php.ini   - Process fewer database content at once

   - Process fewer items at once

### Some database content Failed to Replace

### Some Items Failed to Replace

**Cause:** Various - check replacement log for specific errors.

**Cause:** Various - check replacement log for specific errors.

**Common Reasons:**

**Common Reasons:**- Permission denied on specific database content

- Permission denied on specific records- File locked by another process

- Record locked by another process- Insufficient disk space

- Database constraint violations- File corrupted or invalid format

- Invalid content format

**Solution:**

**Solution:**1. Review detailed replacement log on results page

1. Review detailed replacement log on results page2. Note which specific database content failed

2. Note which specific items failed3. Check PHP error logs for details

3. Check PHP and Moodle error logs4. Try replacing failed database content individually

4. Try replacing failed items individually

---

---

## Performance Issues

## Performance Issues

### Wizard Slow or Timing Out

### Wizard Slow or Timing Out

**Cause:** Too many database content to process or server limitations.

**Cause:** Too many items to process or server limitations.

**Solutions:**

**Solutions:**1. **Use More Specific Search Terms**

   - Instead of `*`, use `logo*` or `*logo`

1. **Use More Specific Search Terms**   - This reduces number of database content to scan

   - Instead of `test`, use `test_specific_name`

   - This reduces number of items to scan2. **Search One Location at a Time**

   - Try database only first

2. **Increase PHP Limits** (in php.ini):   - Then database contentystem only

   ```ini   - Don't check both if not needed

   max_execution_time = 300

   memory_limit = 512M3. **Increase PHP Limits** (in php.ini):

   post_max_size = 100M   ```ini

   ```   max_execution_time = 300

   memory_limit = 512M

3. **Process in Batches**   post_max_size = 100M

   - Select fewer items in Step 2   upload_max_database contentize = 100M

   - Run multiple replacement operations   ```



4. **Run During Off-Peak Hours**4. **Process in Batches**

   - Perform operations when site traffic is low   - Select fewer database content in Step 2

   - Run multiple replacement operations

### Session Lost During Processing

### Session Lost During Processing

**Cause:** Operation taking too long.

**Cause:** Operation taking too long.

**Solution:**

1. Increase PHP session timeout**Solution:**

2. Process fewer items at once1. Increase PHP session timeout

3. Upgrade server resources if needed2. Process fewer database content at once

3. Run during off-peak hours

---4. Consider upgrading server resources



## Wizard Navigation Issues---



### Can't Go Back to Previous Step## Image Quality Issues



**Cause:** Browser back button used instead of wizard's Back button.### Images Appear Blurry or Low Quality



**Solution:****Cause:** Source image smaller than targets or compression issues.

- Always use the wizard's "Back" button at bottom of form

- Don't use browser navigation buttons**Solutions:**

- Session state may be lost if browser back is used1. Use source image larger than or equal to target images

2. Use high-quality source images

### Lost My Selections When Going Back3. For JPEGs, use quality 90+ source images

4. Consider using PNG for graphics to avoid JPEG compression

**Cause:** Session data cleared or expired.

### Transparency Lost

**Solution:**

- Content selections in Step 2 are not preserved when going back to Step 1**Cause:** Converting from PNG/WebP to JPEG.

- This is intentional - new search may find different items

- Complete the wizard without going back if possible**Solutions:**

1. Keep source and target in PNG or WebP format

### Wizard Stuck on a Step2. Don't enable cross-format conversion for transparent images

3. JPEG doesn't support transparency

**Cause:** Validation error or missing required fields.4. Ensure GD library is installed and configured correctly



**Solutions:**### Colors Look Different

1. Check for error messages at top of page

2. Verify all required fields are filled**Cause:** Color profile or format conversion issues.

3. In Step 3, ensure backup confirmation is checked

4. Refresh page and start over if necessary**Solutions:**

5. Clear browser cache and cookies1. Keep same format (JPG→JPG, PNG→PNG)

2. Use sRGB color profile in source images

---3. Test cross-format conversion on a few images first

4. Check GD library configuration

## Still Having Issues?

---

### Enable Debugging

## Wizard Navigation Issues

1. **Moodle Debugging:**

   - Go to **Site administration → Development → Debugging**### Can't Go Back to Previous Step

   - Set to "DEVELOPER: extra Moodle debug messages"

   - Set "Display debug messages" to "Yes"**Cause:** Browser back button used instead of wizard's Back button.



2. **PHP Error Logs:****Solution:**

   - Linux: `/var/log/apache2/error.log` or `/var/log/php-fpm/error.log`- Always use the wizard's "Back" button at bottom of form

   - XAMPP: `xampp/apache/logs/error.log`- Don't use browser navigation buttons

   - WAMP: `wamp/logs/php_error.log`- Session state may be lost if browser back is used



3. **Moodle Logs:**### Lost My Selections When Going Back

   - **Site administration → Reports → Logs**

   - Filter by user and "local_textplus"**Cause:** Session data cleared or expired.



### Gather Information**Solution:**

- File selections in Step 2 are not preserved when going back to Step 1

When reporting issues, include:- This is intentional - new search may find different database content

- Moodle version- Complete the wizard without going back if possible

- Plugin version (v1.0.1)- Session cleared automatically after 2 hours of inactivity

- PHP version

- Exact error message### Wizard Stuck on a Step

- Steps to reproduce

- Browser and OS**Cause:** Validation error or missing required fields.

- PHP error log entries

- Screenshot if applicable**Solutions:**

- Browser console errors (F12 → Console tab)1. Check for error messages at top of page

2. Verify all required fields are filled

### Contact Support3. In Step 3, ensure backup confirmation is checked

4. Refresh page and start over if necessary

- **Website:** https://gwizit.com5. Clear browser cache and cookies

- **Documentation:** See README.md in plugin directory

---

---

## Security Issues

## Common Error Messages Reference

### Getting CSRF/Session Key Errors

| Error Message | Most Common Cause | Quick Fix |

|--------------|-------------------|-----------|**Cause:** Session security validation failing.

| "Access denied" | Not site administrator | Must be site admin with `moodle/site:config` |

| "No items selected" | Checkboxes not checked | Select items in Step 2 |**Solutions:**

| "Backup confirmation required" | Checkbox not checked | Check backup confirmation in Step 3 |1. Don't open plugin in multiple browser tabs

| "Session expired" | Page idle too long | Refresh and start over |2. Don't let page sit idle for extended periods

| "Permission denied" | Database permissions | Check database user UPDATE permissions |3. Clear cookies and log in again

| "Memory limit exceeded" | Too many items | Increase PHP memory or process fewer items |4. Check Moodle session configuration in config.php

| "URIError: malformed URI" | Old version | Update to v1.0.1 |

| Garbled UTF-8 text | Old version | Update to v1.0.1 |### Directory Traversal Error

| `[[stringname]]` displayed | Cache not cleared | Purge all caches |

**Cause:** Security validation preventing invalid file paths.

---

**This is intentional** - protecting against malicious file path manipulation.

*Last Updated: Version 1.0.1 - October 21, 2025*

**If you get this in error:**
1. Ensure database content are within Moodle dirroot
2. Don't manually edit form values
3. Contact administrator if legitimate database content are blocked

### XSS Protection Errors

**Cause:** Content sanitization removing unsafe content.

**This is intentional** security protection.

**Note:** All user input and output is sanitized to prevent XSS attacks. If content appears modified, it's for security.

---

## GD Library Issues

### Warning: "GD library is not available"

**Impact:**
- Cross-format image conversion disabled (JPG→PNG, PNG→WebP, etc.)
- Images can still be replaced with exact same format
- Image resizing may not work properly

**Solutions:**

**On Linux (Ubuntu/Debian):**
```bash
sudo apt-get install php-gd
sudo systemctl restart apache2  # or php-fpm
```

**On Windows (XAMPP):**
1. Edit php.ini
2. Find line: `;extension=gd`
3. Remove semicolon: `extension=gd`
4. Restart Apache

**On cPanel:**
1. Go to "Select PHP Version" or "PHP Extensions"
2. Enable "gd" extension
3. Save changes

**Verification:**
Create file `phpinfo.php` with:
```php
<?php phpinfo(); ?>
```
Look for "GD Support: enabled"

---

## Still Having Issues?

### Enable Debugging

1. **Moodle Debugging:**
   - Go to **Site administration → Development → Debugging**
   - Set to "DEVELOPER: extra Moodle debug messages"
   - Set "Display debug messages" to "Yes"

2. **PHP Error Logs:**
   - Linux: `/var/log/apache2/error.log` or `/var/log/php-fpm/error.log`
   - XAMPP: `xampp/apache/logs/error.log`
   - WAMP: `wamp/logs/php_error.log`

3. **Moodle Logs:**
   - **Site administration → Reports → Logs**
   - Filter by user and "local_TextPlus"

### Gather Information

When reporting issues, include:
- Moodle version
- Plugin version (v3.0.0)
- PHP version
- Exact error message
- Steps to reproduce
- Browser and OS
- PHP error log entries
- Screenshot if applicable

### Contact Support

- **Website:** https://gwizit.com
- **Documentation:** See README.md
- **Changelog:** See CHANGELOG.md

---

## Common Error Messages Reference

| Error Message | Most Common Cause | Quick Fix |
|--------------|-------------------|-----------|
| "Access denied" | Not site administrator | Must be site admin (not just manager) |
| "No database content selected" | Checkboxes not checked | Select database content in Step 2 |
| "Extension mismatch" | Wrong file type uploaded | Upload matching file type |
| "Backup confirmation required" | Checkbox not checked | Check backup confirmation in Step 3 |
| "Session expired" | Page idle too long | Refresh and start over |
| "Invalid file type" | Uploaded file wrong format | Check file mimetype matches |
| "GD library not available" | PHP extension not installed | Install php-gd extension |
| "Permission denied" | File system permissions | Check web server write permissions |
| "Memory limit exceeded" | File too large or too many | Increase PHP memory or process fewer database content |

---

*Last Updated: Version 3.0.3 - October 19, 2025*
