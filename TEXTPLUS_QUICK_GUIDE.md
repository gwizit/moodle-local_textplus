# TextPlus Quick Reference Guide

## What is TextPlus?

TextPlus is a Moodle plugin that allows administrators to search for text across the entire Moodle database and replace it in selected locations. It maintains the same 3-step wizard interface as ImagePlus but works with database text content instead of files.

---

## Quick Start

### Access the Tool
1. Log in as site administrator
2. Go to: **Site administration → Server → TextPlus**

### Basic Workflow
1. **Step 1:** Enter text to search for → Click "Find matching text"
2. **Step 2:** Review results, select items to update → Click "Next"
3. **Step 3:** Enter replacement text, confirm backup → Click "Execute Replacement"

---

## Step-by-Step Guide

### Step 1: Define Search Criteria

**What to enter:**
- **Search text**: Exact text string to find (e.g., "old company name")
- **Options**: 
  - ☑️ Case sensitive (matches exact case)
  - ☑️ Search database (should be enabled)

**Tips:**
- Use exact text for best results
- Consider case sensitivity carefully
- Start with specific text, broaden if needed

**Click:** "Find matching text" to search

---

### Step 2: Select Content to Replace

**What you'll see:**
- List of all database records containing your search text
- Information includes:
  - Table and field name
  - Record ID
  - Text preview (with search term highlighted)
  - Context (course name, activity, etc.)

**What to do:**
- ☑️ Select checkboxes for items you want to update
- Use "Select All" / "Deselect All" for bulk operations
- Review each item carefully before selecting

**Navigation:**
- **Back**: Modify search criteria
- **Next**: Proceed to replacement options

**Tip:** Don't select all unless you've reviewed each item!

---

### Step 3: Replacement Options and Confirmation

**Enter replacement text:**
- Type the new text that will replace the search term
- Leave empty to remove the search text entirely

**Choose execution mode:**
- 🔵 **Dry run** (recommended first): Shows what would change
- 🔴 **Execute**: Actually updates the database

**IMPORTANT - Backup Confirmation:**
- ☑️ Check: "I confirm that a recent database backup has been made"
- This is REQUIRED and cannot be undone
- Create a database backup before executing

**Click:** "Execute Replacement"

---

## Safety Features

### Always Follow This Order:
1. ✅ Create database backup
2. ✅ Run in "Dry Run" mode first
3. ✅ Review results carefully
4. ✅ Create another backup (yes, really!)
5. ✅ Run in "Execute" mode
6. ✅ Verify changes
7. ✅ Clear Moodle caches

### Warnings
- ⚠️ Changes are PERMANENT and IRREVERSIBLE
- ⚠️ No built-in undo function
- ⚠️ Always backup before executing
- ⚠️ Test on staging environment first

---

## Common Use Cases

### 1. Update Company Name
**Scenario:** Company rebrand
- Search: "Old Company Name"
- Replace: "New Company Name"
- Check case sensitivity

### 2. Fix Broken Links
**Scenario:** Domain change
- Search: "http://old-domain.com"
- Replace: "https://new-domain.com"
- Case insensitive

### 3. Remove Outdated Information
**Scenario:** Remove deprecated content
- Search: "This course ends December 2023"
- Replace: "" (empty)
- Review carefully

### 4. Update Copyright Year
**Scenario:** Annual update
- Search: "© 2024"
- Replace: "© 2025"
- Case sensitive

---

## What Content is Searched?

TextPlus searches text in these Moodle tables:
- **Courses**: Names, descriptions, summaries
- **Pages**: Page content and introductions
- **Labels**: Label text across all courses
- **Activities**: Quiz descriptions, assignments, forum posts
- **Sections**: Course section summaries
- **Books**: Book chapter content
- And more...

---

## Results Page

After execution, you'll see:

### Statistics
- Items found
- Items replaced successfully
- Items that failed (if any)

### Detailed Log
- Success/failure for each item
- Table and record ID
- Error messages (if any)

### Next Steps
- Review the log
- Clear Moodle caches
- Click "Start Over" for another operation

---

## Troubleshooting

### "No text found"
- Check spelling of search term
- Verify case sensitivity setting
- Try shorter search term
- Content may be in unsupported table

### "No items selected"
- You must check at least one checkbox
- Go back to Step 2 and select items

### "Backup confirmation required"
- You must check the backup confirmation box
- This is mandatory for safety

### "Database error"
- Check database permissions
- Verify database connection
- Contact system administrator

---

## Best Practices

### Before You Start
1. ✅ Create full database backup
2. ✅ Test on staging environment
3. ✅ Use specific search terms
4. ✅ Run dry run first

### During Operation
1. ✅ Review all found items carefully
2. ✅ Don't select all without review
3. ✅ Double-check replacement text
4. ✅ Confirm backup checkbox consciously

### After Operation
1. ✅ Review results log
2. ✅ Test affected pages/activities
3. ✅ Clear Moodle caches
4. ✅ Document what you changed

---

## Technical Requirements

### Minimum Requirements
- Moodle 4.3+
- PHP 7.4+
- Site administrator access
- Database backup capability

### Recommended
- PHP 8.0+
- Staging environment for testing
- Recent database backup
- Maintenance mode during execution

---

## Security

### Access Control
- **Only site administrators** can access
- Requires `moodle/site:config` capability
- Session key verification on all operations

### Data Protection
- SQL injection protection (Moodle DML API)
- XSS protection (output escaping)
- Input validation and sanitization
- Audit logging of all operations

---

## Support

**For help:**
- Website: https://gwizit.com
- Documentation: See textplus/README.md
- Moodle site administrator

**Before contacting support:**
- Have you created a database backup?
- Have you tried dry run mode?
- What error message did you receive?
- What search term did you use?

---

## Quick Tips

💡 **Start small**: Test with specific search term first  
💡 **Dry run always**: Never skip the preview  
💡 **Backup twice**: Before dry run AND before execute  
💡 **Case matters**: Be aware of case sensitivity  
💡 **Review carefully**: Check every selected item  
💡 **Test first**: Use staging environment  
💡 **Document**: Keep notes of what you changed  
💡 **Clear cache**: After every execution  

---

## Keyboard Shortcuts

- **Tab**: Navigate between fields
- **Spacebar**: Toggle checkboxes (when focused)
- **Enter**: Submit form (be careful!)
- **Escape**: Cancel (some dialogs)

---

## FAQs

**Q: Can I undo a replacement?**  
A: No. That's why backups are required.

**Q: How long does it take?**  
A: Depends on database size and number of items. Usually seconds to minutes.

**Q: Will it affect all courses?**  
A: Only items you select in Step 2.

**Q: Can I search with wildcards?**  
A: No, exact text matching only.

**Q: Is HTML formatting preserved?**  
A: Yes, where applicable.

**Q: Do I need to be logged in as admin?**  
A: Yes, site administrator only.

---

**Remember: ALWAYS BACKUP YOUR DATABASE BEFORE USING TEXTPLUS!**

---

*TextPlus by G Wiz IT Solutions - https://gwizit.com*
