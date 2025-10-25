# TextPlus - Troubleshooting Guide# TextPlus - Troubleshooting Guide



**Version:** 1.0.5  **Version:** 1.0.5  

**Updated:** January 2025  **Updated:** January 2025  

**Compatibility:** Moodle 4.3 to 5.1+**Compatibility:** Moodle 4.3 to 5.1+



This guide covers common issues and solutions for the TextPlus plugin.



---------



## Table of Contents



1. [Installation Issues](#installation-issues)## Table of Contents## Table of Contents

2. [Display Issues](#display-issues)

3. [Access and Permission Issues](#access-and-permission-issues)

4. [Code Snippet Pop-up Issues](#code-snippet-pop-up-issues)

5. [Search and Replace Issues](#search-and-replace-issues)1. [Installation Issues](#installation-issues)1. [Installation Issues](#installation-issues)

6. [Performance Issues](#performance-issues)

7. [Wizard Navigation Issues](#wizard-navigation-issues)2. [Display Issues](#display-issues)2. [Display Issues](#display-issues)

8. [Security Issues](#security-issues)

3. [Access and Permission Issues](#access-and-permission-issues)3. [Access and Permission Issues](#access-and-permission-issues)

---

4. [Code Snippet Pop-up Issues](#code-snippet-pop-up-issues)4. [File Search Issues](#file-search-issues)

## Display Issues

5. [Search and Replace Issues](#search-and-replace-issues)5. [File Replacement Issues](#file-replacement-issues)

### Language Strings Showing as [[stringname]]

6. [Performance Issues](#performance-issues)6. [Performance Issues](#performance-issues)

**Symptoms:**

- Text appears as `[[nodatabasecontentsreplaced]]` instead of "No database content were replaced"7. [Wizard Navigation Issues](#wizard-navigation-issues)7. [Image Quality Issues](#image-quality-issues)

- Text appears as `[[preview_mode_warning]]` instead of the actual warning message

8. [Wizard Navigation Issues](#wizard-navigation-issues)

**Cause:** Moodle's language string cache is outdated after plugin installation/update.

---9. [Security Issues](#security-issues)

**Solution 1: Purge All Caches (RECOMMENDED)**

1. Go to **Site administration → Development → Purge all caches**

2. Click "Purge all caches" button

3. Refresh the TextPlus page## Installation Issues---



**Solution 2: Update Language Strings**

1. Go to **Site administration → Language → Language customization**

2. Click "Edit strings" for English language### Error: "corrupted_archive_structure"## Display Issues

3. Click "Continue" (you don't need to edit anything)

4. This will rebuild the language cache



**Solution 3: CLI Method (Fastest)****Cause:** Moodle's strict ZIP validation or browser upload issues.### Language Strings Showing as [[stringname]]

```bash

php admin/cli/purge_caches.php

```

**Solution 1: Manual Installation (RECOMMENDED)****Symptoms:**

**Prevention:**

- Always purge caches after installing or updating any plugin- Text appears as `[[nodatabase contentreplaced]]` instead of "No database content were replaced"

- Enable developer mode during development: **Site administration → Development → Debugging**

1. Extract the ZIP file- Text appears as `[[preview_mode_warning]]` instead of the actual warning message

---

2. Copy the `textplus` folder to `[your-moodle]/local/`

## Installation Issues

3. Ensure the path is: `[moodle]/local/textplus/version.php`**Cause:** Moodle's language string cache is outdated after plugin installation/update.

### Error: "corrupted_archive_structure"

4. Visit **Site administration → Notifications** in Moodle

**Cause:** Moodle's strict ZIP validation or browser upload issues.

5. Click **"Upgrade Moodle database now"****Solution 1: Purge All Caches (RECOMMENDED)**

**Solution 1: Manual Installation (RECOMMENDED)**

1. Go to **Site administration → Development → Purge all caches**

1. Extract the ZIP file

2. Copy the `textplus` folder to `[your-moodle]/local/`**On Linux/Mac:**2. Click "Purge all caches" button

3. Ensure the path is: `[moodle]/local/textplus/version.php`

4. Visit **Site administration → Notifications** in Moodle```bash3. Refresh the TextPlus page

5. Click **"Upgrade Moodle database now"**

cd /path/to/moodle/local/

**On Linux/Mac:**

```bashunzip moodle-local_textplus-v1.0.1.zip**Solution 2: Update Language Strings**

cd /path/to/moodle/local/

unzip moodle-local_textplus-v1.0.5.zipcd ..1. Go to **Site administration → Language → Language customization**

cd ..

sudo chown -R www-data:www-data local/textplussudo chown -R www-data:www-data local/textplus2. Click "Edit strings" for English language

sudo chmod -R 755 local/textplus

php admin/cli/upgrade.phpsudo chmod -R 755 local/textplus3. Click "Continue" (you don't need to edit anything)

```

php admin/cli/upgrade.php4. This will rebuild the language cache

**Solution 2: Try Different Browser**

- Try Chrome, Firefox, or Edge```

- Clear browser cache

- Disable browser extensions temporarily**Solution 3: CLI Method (Fastest)**



### Error: "Unable to detect the plugin type"**Solution 2: Try Different Browser**```bash



**This is NORMAL** - Moodle 5.x security feature.- Try Chrome, Firefox, or Edgephp admin/cli/purge_caches.php



**Solution:**- Clear browser cache```

1. When prompted, select **"Local plugin (local)"** from dropdown

2. Verify folder name shows as **"textplus"**- Disable browser extensions temporarily

3. Click "Install plugin from the ZIP file"

4. Continue with installation**Prevention:**



### Plugin Not Appearing After Installation### Error: "Unable to detect the plugin type"- Always purge caches after installing or updating any plugin



**Solution:**- Enable developer mode during development: **Site administration → Development → Debugging**

1. Go to **Site administration → Notifications** and run any upgrades

2. Clear caches: **Site administration → Development → Purge all caches****This is NORMAL** - Moodle 5.x security feature.

3. Verify files exist:

   ```---

   [moodle]/local/textplus/version.php

   [moodle]/local/textplus/settings.php**Solution:**

   [moodle]/local/textplus/index.php

   ```1. When prompted, select **"Local plugin (local)"** from dropdown## Installation Issues

4. Check PHP error logs for issues

2. Verify folder name shows as **"textplus"**

---

3. Click "Install plugin from the ZIP file"### Error: "corrupted_archive_structure"

## Access and Permission Issues

4. Continue with installation

### Error: "Access denied. This tool is only available to site administrators"

**Cause:** Moodle's strict ZIP validation or browser upload issues.

**Cause:** User does not have site administrator permissions.

### Plugin Not Appearing After Installation

**Solution:**

- This is intentional security restriction**Solution 1: Manual Installation (RECOMMENDED)**

- Only users with `moodle/site:config` capability can access

- Contact your site administrator if you need access**Solution:**

- Administrators can verify permissions at: **Site administration → Users → Permissions**

1. Go to **Site administration → Notifications** and run any upgrades1. Extract the ZIP file

### Session Expired or Invalid Session Key

2. Clear caches: **Site administration → Development → Purge all caches**2. Copy the `TextPlus` folder to `[your-moodle]/local/`

**Cause:** Form session expired or CSRF protection triggered.

3. Verify files exist:3. Ensure the path is: `[moodle]/local/TextPlus/version.php`

**Solution:**

1. Refresh the page and log in again   ```4. Visit **Site administration → Notifications** in Moodle

2. Don't use browser back button - use wizard's "Back" button

3. Don't let the page sit idle for extended periods   [moodle]/local/textplus/version.php5. Click **"Upgrade Moodle database now"**

4. Clear browser cookies and log in again

   [moodle]/local/textplus/settings.php

---

   [moodle]/local/textplus/index.php**On Linux/Mac:**

## Code Snippet Pop-up Issues

   ``````bash

### Pop-up Shows Escaped HTML Characters (&amp;lt;, &amp;gt;, &amp;quot;)

4. Check PHP error logs for issuescd /path/to/moodle/local/

**Fixed in v1.0.1!**

unzip moodle-local_TextPlus-v3.0.0.zip

If you're still seeing this:

1. Verify you're running version 1.0.5 (check **Site administration → Plugins → Local plugins → TextPlus**)---cd ..

2. Clear browser cache (Ctrl+Shift+Delete)

3. Purge Moodle cachessudo chown -R www-data:www-data local/TextPlus

4. Hard refresh the page (Ctrl+F5)

## Display Issuessudo chmod -R 755 local/TextPlus

### Pop-up Shows Garbled Non-English Text (Japanese, Chinese, Arabic)

php admin/cli/upgrade.php

**Symptoms:**

- Japanese text like "このコース" displays as "ãã®ã³ã¼ã¹"### Language Strings Showing as [[stringname]]```

- Characters appear corrupted or mojibake



**Fixed in v1.0.1!**

**Symptoms:****Solution 2: Try Different Browser**

**Solution:**

1. Update to version 1.0.5- Text appears as `[[nodatabasecontentsreplaced]]` instead of actual message- Try Chrome, Firefox, or Edge

2. Clear browser and Moodle caches

3. The plugin now uses UTF-8 safe base64 encoding/decoding- Text appears as `[[preview_mode_warning]]` instead of warning- Clear browser cache



### Pop-up is Blank or Shows Nothing- Disable browser extensions temporarily



**Causes & Solutions:****Cause:** Moodle's language string cache is outdated after plugin installation/update.



1. **Empty Content** (Fixed in v1.0.1)### Error: "Unable to detect the plugin type"

   - Blank contexts are now filtered out automatically

**Solution 1: Purge All Caches (RECOMMENDED)**

2. **JavaScript Error**

   - Open browser console (F12) to check for errors1. Go to **Site administration → Development → Purge all caches****This is NORMAL** - Moodle 5.x security feature.

   - If you see "URIError: malformed URI sequence" - update to v1.0.5

2. Click "Purge all caches" button

3. **Browser Issues**

   - Try different browser (Chrome, Firefox, Edge)3. Refresh the TextPlus page**Solution:**

   - Disable browser extensions

   - Clear browser cache1. When prompted, select **"Local plugin (local)"** from dropdown



### Error: "URIError: malformed URI sequence" in Console**Solution 2: CLI Method (Fastest)**2. Verify folder name shows as **"TextPlus"**



**Fixed in v1.0.1!**```bash3. Click "Install plugin from the ZIP file"



The plugin now has robust error handling with fallback decoding methods.php admin/cli/purge_caches.php4. Continue with installation



**If still occurring:**```

1. Update to version 1.0.5

2. Check browser console for additional errors### Plugin Not Appearing After Installation

3. Try different browser

**Prevention:**

### Clicking X (Close Button) Refreshes the Page

- Always purge caches after installing or updating any plugin**Solution:**

**Fixed in v1.0.1!**

- This is standard Moodle behavior for all plugins1. Go to **Site administration → Notifications** and run any upgrades

The close button now has `type="button"` to prevent form submission.

2. Clear caches: **Site administration → Development → Purge all caches**

**If still occurring:**

- Clear browser cache---3. Verify database content exist:

- Hard refresh (Ctrl+F5)

- Update to v1.0.5   ```



### Code Snippet Not Showing HTML Tags Correctly## Access and Permission Issues   [moodle]/local/TextPlus/version.php



**Expected Behavior (v1.0.1+):**   [moodle]/local/TextPlus/settings.php

- HTML tags like `<div>`, `<p>`, `<a>` should display as text, not be rendered

- You should see the actual HTML code, like viewing source### Error: "Access denied. This tool is only available to site administrators"   [moodle]/local/TextPlus/index.php



**If tags are being rendered:**   ```

1. Update to v1.0.5

2. The plugin now uses `textContent` instead of `innerHTML` for safe display**Cause:** User does not have site administrator permissions.4. Check PHP error logs for issues



---



## Search and Replace Issues**Solution:**---



### No Items Found in Step 1- This is intentional security restriction



**Possible Causes & Solutions:**- Only users with `moodle/site:config` capability can access## Access and Permission Issues



1. **Incorrect Search Term**- Contact your site administrator if you need access

   - Check spelling and case sensitivity

   - Try a simpler, broader search term- Administrators can verify permissions at: **Site administration → Users → Permissions**### Error: "Access denied. This tool is only available to site administrators"

   - Database content may not contain exact text



2. **Wrong Tables Selected**

   - Verify you've selected correct database tables to search### Session Expired or Invalid Session Key**Cause:** User does not have site administrator permissions.

   - Try searching all tables first



3. **Content Not in Database**

   - Some content may be in files, not database**Cause:** Form session expired or CSRF protection triggered.**Solution:**

   - TextPlus searches database text fields only

- This is intentional security restriction in v3.0.0

### Error: "Please select at least one item to replace"

**Solution:**- Only users with `moodle/site:config` capability can access

**Cause:** No checkboxes selected in Step 2.

1. Refresh the page and log in again- Contact your site administrator if you need access

**Solution:**

1. Go back to Step 22. Don't use browser back button - use wizard's "Back" button- Administrators can verify permissions at: **Site administration → Users → Permissions**

2. Check at least one item using the checkboxes

3. Or use "Select All" button3. Don't let the page sit idle for extended periods

4. Click "Next" to proceed

4. Clear browser cookies and log in again**Note:** Unlike previous versions, the `local/TextPlus:manage` capability is NOT sufficient. Site administrator permission is required.

### Backup Confirmation Not Working



**Cause:** Checkbox not checked or JavaScript disabled.

---### Error: "You do not have permission to use this tool"

**Solution:**

1. Scroll down in Step 3

2. Check the box: "I confirm that a recent backup has been made"

3. If checkbox is missing, enable JavaScript in your browser## Code Snippet Pop-up Issues**Cause:** Missing plugin-specific capability.

4. Read the warning message carefully before proceeding



### Content Not Actually Replaced

### Pop-up Shows Escaped HTML Characters (&amp;lt;, &amp;gt;, &amp;quot;)**Solution:**

**Possible Causes:**

1. Ensure you are a site administrator first

1. **Preview Mode Selected**

   - Solution: Change "Execution mode" to "Execute changes" in Step 3**Fixed in v1.0.1!**2. Verify you have `local/TextPlus:view` and `local/TextPlus:manage` capabilities



2. **Permission Errors**3. Check role assignments: **Site administration → Users → Permissions → Assign system roles**

   - Check PHP error logs

   - Verify database user has UPDATE permissionsIf you're still seeing this:



3. **Memory Limit Exceeded**1. Verify you're running version 1.0.1 (check **Site administration → Plugins → Local plugins → TextPlus**)### Session Expired or Invalid Session Key

   - Increase PHP memory limit in php.ini

   - Process fewer items at once2. Clear browser cache (Ctrl+Shift+Delete)



### Some Items Failed to Replace3. Purge Moodle caches**Cause:** Form session expired or CSRF protection triggered.



**Cause:** Various - check replacement log for specific errors.4. Hard refresh the page (Ctrl+F5)



**Common Reasons:****Solution:**

- Permission denied on specific records

- Record locked by another process### Pop-up Shows Garbled Non-English Text (Japanese, Chinese, Arabic)1. Refresh the page and log in again

- Database constraint violations

- Invalid content format2. Don't use browser back button - use wizard's "Back" button



**Solution:****Symptoms:**3. Don't let the page sit idle for extended periods

1. Review detailed replacement log on results page

2. Note which specific items failed- Japanese text like "このコース" displays as "ãã®ã³ã¼ã¹"4. Clear browser cookies and log in again

3. Check PHP and Moodle error logs

4. Try replacing failed items individually- Characters appear corrupted or mojibake



------



## Performance Issues**Fixed in v1.0.1!**



### Wizard Slow or Timing Out## File Search Issues



**Cause:** Too many items to process or server limitations.**Solution:**



**Solutions:**1. Update to version 1.0.1### No database content Found



1. **Use More Specific Search Terms**2. Clear browser and Moodle caches

   - Instead of `test`, use `test_specific_name`

   - This reduces number of items to scan3. The plugin now uses UTF-8 safe base64 encoding/decoding**Possible Causes & Solutions:**



2. **Increase PHP Limits** (in php.ini):

   ```ini

   max_execution_time = 300### Pop-up is Blank or Shows Nothing1. **Incorrect Search Term**

   memory_limit = 512M

   post_max_size = 100M   - Try using wildcards: `logo*` instead of just `logo`

   ```

**Causes & Solutions:**   - Check spelling and case sensitivity

3. **Process in Batches**

   - Select fewer items in Step 2   - Try a simpler, broader search term first

   - Run multiple replacement operations

1. **Empty Content** (Fixed in v1.0.1)

4. **Run During Off-Peak Hours**

   - Perform operations when site traffic is low   - Blank contexts are now filtered out automatically2. **Wrong File Type Selected**



### Session Lost During Processing   - Verify you selected correct type (Images, PDFs, etc.)



**Cause:** Operation taking too long.2. **JavaScript Error**   - Remember: Images = JPG/PNG/WebP only



**Solution:**   - Open browser console (F12) to check for errors

1. Increase PHP session timeout

2. Process fewer items at once   - If you see "URIError: malformed URI sequence" - update to v1.0.13. **Wrong Search Locations**

3. Run during off-peak hours

4. Consider upgrading server resources   - Check both "Include database database content" AND "Include file system" boxes



---3. **Browser Issues**   - Database database content are in Moodle's file storage (`mdl_database content` table)



## Wizard Navigation Issues   - Try different browser (Chrome, Firefox, Edge)   - database contentystem database content are in Moodle installation directories



### Can't Go Back to Previous Step   - Disable browser extensions



**Cause:** Browser back button used instead of wizard's Back button.   - Clear browser cache4. **database content in Unsupported Locations**



**Solution:**   - Plugin only searches specific directories (see README)

- Always use the wizard's "Back" button at bottom of form

- Don't use browser navigation buttons### Error: "URIError: malformed URI sequence" in Console   - Custom theme directories may not be scanned

- Session state may be lost if browser back is used



### Lost My Selections When Going Back

**Fixed in v1.0.1!**### Wildcard Search Not Working

**Cause:** Session data cleared or expired.



**Solution:**

- Content selections in Step 2 are not preserved when going back to Step 1The plugin now has robust error handling with fallback decoding methods.**Cause:** Incorrect wildcard syntax.

- This is intentional - new search may find different items

- Complete the wizard without going back if possible



### Wizard Stuck on a Step**If still occurring:****Correct Syntax:**



**Cause:** Validation error or missing required fields.1. Update to version 1.0.1- `logo*` - Matches logo.png, logo-2024.jpg, logo_old.gif



**Solutions:**2. Check browser console for additional errors- `*logo` - Matches mylogo.png, newlogo.jpg

1. Check for error messages at top of page

2. Verify all required fields are filled3. Try different browser- `banner?` - Matches banner1, banner2, bannerA

3. In Step 3, ensure backup confirmation is checked

4. Refresh page and start over if necessary- `ba*er` - Matches banner, batter, badger

5. Clear browser cache and cookies

### Clicking X (Close Button) Refreshes the Page

---

**Common Mistakes:**

## Security Issues

**Fixed in v1.0.1!**- Using regex instead of wildcards

### Getting CSRF/Session Key Errors

- Using `%` instead of `*` (SQL syntax doesn't work here)

**Cause:** Session security validation failing.

The close button now has `type="button"` to prevent form submission.

**Solutions:**

1. Don't open plugin in multiple browser tabs### Duplicate database content in Results

2. Don't let page sit idle for extended periods

3. Clear cookies and log in again**If still occurring:**

4. Check Moodle session configuration in config.php

- Clear browser cache**Cause:** Same file exists in both database and database contentystem.

### Directory Traversal Error

- Hard refresh (Ctrl+F5)

**Cause:** Security validation preventing invalid file paths.

- Update to v1.0.1**Solution:**

**This is intentional** - protecting against malicious file path manipulation.

- This is normal - Moodle stores database content in database AND database contentystem

**If you get this in error:**

1. Ensure files are within Moodle data directory### Code Snippet Not Showing HTML Tags Correctly- Select which instance(s) to replace in Step 2

2. Don't manually edit form values

3. Contact administrator if legitimate files are blocked- Usually safer to replace database version only



### XSS Protection Errors**Expected Behavior (v1.0.1):**



**Cause:** Content sanitization removing unsafe content.- HTML tags like `<div>`, `<p>`, `<a>` should display as text, not be rendered---



**This is intentional** security protection.- You should see the actual HTML code, like viewing source



**Note:** All user input and output is sanitized to prevent XSS attacks. If content appears modified, it's for security.## File Replacement Issues



---**If tags are being rendered:**



## Still Having Issues?1. Update to v1.0.1### Error: "Please select at least one file to replace"



### Enable Debugging2. The plugin now uses `textContent` instead of `innerHTML` for safe display



1. **Moodle Debugging:****Cause:** No checkboxes selected in Step 2.

   - Go to **Site administration → Development → Debugging**

   - Set to "DEVELOPER: extra Moodle debug messages"---

   - Set "Display debug messages" to "Yes"

**Solution:**

2. **PHP Error Logs:**

   - Linux: `/var/log/apache2/error.log` or `/var/log/php-fpm/error.log`## Search and Replace Issues1. Go back to Step 2

   - XAMPP: `xampp/apache/logs/error.log`

   - WAMP: `wamp/logs/php_error.log`2. Check at least one file using the checkboxes



3. **Moodle Logs:**### No Items Found in Step 13. Or use "Select All" button

   - **Site administration → Reports → Logs**

   - Filter by user and "local_textplus"4. Click "Next" to proceed



### Gather Information**Possible Causes & Solutions:**



When reporting issues, include:### Error: "Extension mismatch: pdf file cannot replace jpg database content"

- Moodle version

- Plugin version (v1.0.5)1. **Incorrect Search Term**

- PHP version

- Exact error message   - Check spelling and case sensitivity**Cause:** Uploaded file type doesn't match target database content.

- Steps to reproduce

- Browser and OS   - Try a simpler, broader search term

- PHP error log entries

- Screenshot if applicable   - Database content may not contain exact text**Solution:**

- Browser console errors (F12 → Console tab)

1. Upload file with matching extension:

### Contact Support

- **GitHub Repository:** https://github.com/gwizit/moodle-local_textplus
- **Bug Tracker:** https://github.com/gwizit/moodle-local_textplus/issues
- **Website:** https://gwizit.com
- **Documentation:** See README.md in plugin directory

- **Documentation:** See README.md in plugin directory   - Try searching all tables first   - JPG → JPG (or PNG/WebP if cross-format enabled)



---2. For images: Enable "Allow cross-format text replacement" in Step 3



## Common Error Messages Reference

| Error Message | Most Common Cause | Quick Fix |
|--------------|-------------------|-----------|
| "Access denied" | Not site administrator | Must be site admin with `moodle/site:config` |
| "No items selected" | Checkboxes not checked | Select items in Step 2 |
| "Backup confirmation required" | Checkbox not checked | Check backup confirmation in Step 3 |
| "Session expired" | Page idle too long | Refresh and start over |

| "Permission denied" | Database permissions | Check database user UPDATE permissions |### Error: "Please select at least one item to replace"**Cause:** Uploaded file doesn't match selected file type category.

| "Memory limit exceeded" | Too many items | Increase PHP memory or process fewer items |

| "URIError: malformed URI" | Old version | Update to v1.0.5 |

| Garbled UTF-8 text | Old version | Update to v1.0.5 |

| `[[stringname]]` displayed | Cache not cleared | Purge all caches |**Cause:** No checkboxes selected in Step 2.**Solution:**



---1. Check file extension matches what you're replacing



*Last Updated: Version 1.0.5 - January 2025***Solution:**2. Verify file is not corrupted


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

- Content selections in Step 2 are not preserved when going back to Step 1
- This is intentional - new search may find different items
- Complete the wizard without going back if possible

### Wizard Stuck on a Step

**Cause:** Validation error or missing required fields.

**Solutions:**
1. Check for error messages at top of page
2. Verify all required fields are filled
3. In Step 3, ensure backup confirmation is checked
4. Refresh page and start over if necessary
5. Clear browser cache and cookies

---

## Still Having Issues?

### Enable Debugging

1. **Moodle Debugging:**
   - Go to **Site administration → Development → Debugging**
   - Set to "DEVELOPER: extra Moodle debug messages"
   - Set "Display debug messages" to "Yes"

2. **PHP Error Logs:**
   - Linux: `/var/log/apache2/error.log` or `/var/log/php-fpm/error.log`

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
| "Backup confirmation required" | Checkbox not checked | Check backup confirmation in Step 3 |
| "Session expired" | Page idle too long | Refresh and start over |
| "Permission denied" | File system permissions | Check web server write permissions |
| "Memory limit exceeded" | Processing too many items | Increase PHP memory or process fewer items |

---

*Last Updated: Version 3.0.5 - October 24, 2025*
