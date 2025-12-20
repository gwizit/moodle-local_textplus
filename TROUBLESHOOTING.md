# TextPlus - Troubleshooting Guide

**Plugin Name:** TextPlus (local_textplus)  
**Version:** 3.0.7  
**Updated:** December 2025  
**Compatibility:** Moodle 4.3 to 5.1+

This guide covers common issues and solutions for the TextPlus plugin. For complete documentation, see [textplus/README.md](textplus/README.md).

---

## Quick Reference

### Most Common Issues

1. **Language strings showing as [[stringname]]** → [Purge all caches](#language-strings-showing-as-stringname)
2. **Access denied error** → [Check site administrator permissions](#access-denied-error)
3. **Installation failed** → [Try manual installation](#installation-issues)
4. **Code snippet pop-ups broken** → [Update to v1.0.5](#code-snippet-issues)
5. **Search finds no items** → [Check search term and tables](#search-finds-no-items)

---

## Installation Issues

### Error: "corrupted_archive_structure"

**Solution:** Use manual installation method
1. Extract ZIP file
2. Copy `textplus` folder to `[moodle]/local/`
3. Visit Site administration → Notifications
4. Run database upgrade
5. Purge all caches

See [textplus/TROUBLESHOOTING.md](textplus/TROUBLESHOOTING.md#installation-issues) for detailed steps.

### Error: "Unable to detect the plugin type"

**This is normal** for Moodle 5.x. Select "Local plugin (local)" from dropdown when prompted.

---

## Display Issues

### Language Strings Showing as [[stringname]]

**Quick Fix:**
1. Go to Site administration → Development → Purge all caches
2. Click "Purge all caches"
3. Refresh page

**CLI Method:**
```bash
php admin/cli/purge_caches.php
```

**Prevention:** Always purge caches after installing or updating any Moodle plugin.

---

## Access and Permission Issues

### Access Denied Error

**Requirements:**
- Must have `moodle/site:config` capability (site administrator)
- Plugin-specific capabilities (`local/textplus:view`, `local/textplus:manage`)

**Check:**
1. Log in as site administrator
2. Verify permissions at: Site administration → Users → Permissions

### Session Expired

**Solutions:**
- Don't let page sit idle for extended periods
- Don't use browser back button (use wizard's Back button)
- Clear cookies and log in again

---

## Code Snippet Issues

### Pop-ups Show Escaped HTML or Garbled Text

**Fixed in v1.0.1+**

If still occurring:
1. Update to v1.0.5
2. Clear browser cache (Ctrl+Shift+Delete)
3. Purge Moodle caches
4. Hard refresh (Ctrl+F5)

### Pop-ups Are Blank

**Solutions:**
- Check browser console (F12) for JavaScript errors
- Try different browser
- Disable browser extensions
- Update to v1.0.5

See [textplus/TROUBLESHOOTING.md](textplus/TROUBLESHOOTING.md#code-snippet-pop-up-issues) for detailed solutions.

---

## Search and Replace Issues

### Search Finds No Items

**Check:**
1. Search term spelling and case sensitivity
2. Correct database tables selected
3. Content exists in supported tables

**Try:**
- Broader, simpler search term
- Search all tables option
- Verify content is in database (not just files)

### No Items Selected Error

**Solution:** Check at least one checkbox in Step 2, or use "Select All" button.

### Backup Confirmation Required

**Solution:** Scroll down in Step 3 and check the backup confirmation checkbox.

### Content Not Replaced

**Common Causes:**
1. **Preview mode** - Change to "Execute changes" in Step 3
2. **Permission errors** - Check database user has UPDATE permissions
3. **Memory limit** - Increase PHP memory_limit or process fewer items

See [textplus/TROUBLESHOOTING.md](textplus/TROUBLESHOOTING.md#search-and-replace-issues) for detailed troubleshooting.

---

## Performance Issues

### Wizard Slow or Timing Out

**Quick Fixes:**
1. Use more specific search terms
2. Increase PHP limits (memory_limit, max_execution_time)
3. Process in smaller batches
4. Run during off-peak hours

**Recommended PHP Settings:**
```ini
max_execution_time = 300
memory_limit = 512M
post_max_size = 100M
```

---

## Wizard Navigation Issues

### Lost Selections When Going Back

**This is intentional behavior:**
- Step 2 selections are not preserved when returning to Step 1
- New search may find different items
- Complete wizard without going back if possible

### Wizard Stuck on Step

**Solutions:**
1. Check for error messages at top of page
2. Verify all required fields filled
3. Ensure backup confirmation checked (Step 3)
4. Refresh page and start over if needed

---

## Security and Safety

### CSRF/Session Key Errors

**Prevention:**
- Don't open plugin in multiple tabs
- Don't let page idle too long
- Use wizard navigation buttons (not browser back)

**Note:** The "Start over" action now requires a valid `sesskey`. If you have an old bookmarked start-over URL, use the UI "Start over" button/link instead.

### Getting XSS Protection Warnings

**This is intentional** - The plugin sanitizes all input/output for security. Modified content appearance is for protection against XSS attacks.

---

## Error Messages Quick Reference

| Error Message | Cause | Quick Fix |
|--------------|-------|-----------|
| Access denied | Not site admin | Login with site admin account |
| No items selected | No checkboxes checked | Select items in Step 2 |
| Backup confirmation required | Checkbox not checked | Check backup box in Step 3 |
| Session expired | Page idle | Refresh and login again |
| [[stringname]] | Cache not cleared | Purge all caches |
| URIError | Old version | Update to v1.0.5 |

---

## Getting Help

### Enable Debugging

**Moodle Debugging:**
1. Site administration → Development → Debugging
2. Set to "DEVELOPER: extra Moodle debug messages"
3. Enable "Display debug messages"

**Check Logs:**
- PHP error logs (location varies by server)
- Moodle logs: Site administration → Reports → Logs
- Browser console (F12 → Console)

### Report Issues

When reporting issues, include:
- Moodle version
- Plugin version (v3.0.7)
- PHP version
- Exact error message
- Steps to reproduce
- Browser and OS
- Error log entries
- Screenshots

### Contact Support

- **GitHub Repository:** https://github.com/gwizit/moodle-local_textplus
- **Support / Issues:** https://github.com/gwizit/moodle-local_textplus/issues
- **Developer Website:** https://gwizit.com
- **Detailed Guide:** [textplus/TROUBLESHOOTING.md](textplus/TROUBLESHOOTING.md)

---

## Additional Resources

- **Complete Documentation:** [textplus/README.md](textplus/README.md)
- **Security Audit:** [SECURITY_AUDIT.md](SECURITY_AUDIT.md)
- **Compliance Report:** [MOODLE_COMPLIANCE_REPORT.md](MOODLE_COMPLIANCE_REPORT.md)
- **Template Refactoring:** [TEMPLATE_REFACTORING.md](TEMPLATE_REFACTORING.md)

---

*For detailed troubleshooting with step-by-step solutions, see [textplus/TROUBLESHOOTING.md](textplus/TROUBLESHOOTING.md)*

*Last Updated: Version 3.0.7 - December 19, 2025*
