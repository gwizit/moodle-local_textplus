# TextPlus - Moodle Plugin
## Development Repository

**Plugin Name:** TextPlus  
**Type:** Local Plugin (local_textplus)  
**Developer:** G Wiz IT Solutions  
**Website:** https://gwizit.com  
**Version:** 3.0.7  
**License:** GNU GPL v3 or later

---

## 🎯 About This Plugin

This powerful Moodle plugin helps administrators search and selectively replace text across their entire Moodle database. What makes it different from Moodle's built-in tools or other plugins is that it lets you preview every change before it happens. You can see exactly which code snippets and text will be updated, giving you full control and peace of mind. With its easy step-by-step wizard and strong security features, you can safely update course content, activities, pages, and labels with confidence.

**Key Features:**
- 🔍 Smart text search across database tables
- 🎯 Interactive content selection with checkboxes
- 👁️ Preview every change before execution
- 🔐 Administrator-only access with multiple security layers
- 📊 Detailed operation logging and results
- ✅ GDPR compliant with Privacy API
- 🛡️ A+ Security Rating (see SECURITY_AUDIT.md)
- 🏗️ Modern architecture with Mustache templates, Output API, and ES6 modules
- 🔄 Safe dry run mode for testing
- 📝 Multi-table support (course, pages, activities, labels, and more)

---

## 📋 Documentation

This repository contains comprehensive documentation:

- **[MOODLE_COMPLIANCE_REPORT.md](MOODLE_COMPLIANCE_REPORT.md)** - Moodle plugin checklist compliance review and submission guide
- **[SECURITY_AUDIT.md](SECURITY_AUDIT.md)** - Comprehensive security assessment (A+ rating)
- **[textplus/README.md](textplus/README.md)** - Complete user documentation and installation guide
- **[textplus/TROUBLESHOOTING.md](textplus/TROUBLESHOOTING.md)** - Common issues and solutions
- **[textplus/INSTALL.txt](textplus/INSTALL.txt)** - Installation instructions

---

## 🚀 Quick Start

### Installation

**Method 1: Create ZIP and Upload (Recommended)**

1. **Create the package**:
   ```powershell
   .\create_package.ps1
   ```

2. **Upload to Moodle**:
   - Log in to Moodle as administrator
   - Go to Site administration → Plugins → Install plugins
   - Upload the created ZIP file
   - **If prompted "Unable to detect the plugin type":**
     - Select **"Local plugin (local)"** from the dropdown
     - Verify the plugin folder name is **"textplus"**
     - Click "Install plugin from the ZIP file"
   - Follow the installation wizard

3. **Clear caches** (Important!):
   - Go to Site administration → Development → Purge all caches
   - Or run: `php admin/cli/purge_caches.php`

**Method 2: Manual Installation**

1. Copy the `textplus` folder to `[moodle-root]/local/`
2. Visit Site administration → Notifications
3. Follow the installation wizard
4. Clear all caches

For detailed installation instructions, see [textplus/README.md](textplus/README.md).

---

## 🔗 Links

- **Source Code:** https://github.com/gwizit/moodle-local_textplus
- **Support / Issues:** https://github.com/gwizit/moodle-local_textplus/issues
- **Developer Website:** https://gwizit.com
- **Moodle Plugins Directory:** *(pending submission)*

---

## ✅ Plugin Status

**Current Status:** ✅ **Ready for Submission**

- ✅ All critical issues fixed
- ✅ Security guidelines fully compliant (A+ rating)
- ✅ Privacy API implemented
- ✅ GitHub Issues tracker active
- ✅ Comprehensive documentation
- ✅ Moodle 4.3 - 5.1+ compatible
- ✅ Modern architecture with proper template separation
- ✅ Repository follows naming convention: `moodle-local_textplus`

See [MOODLE_COMPLIANCE_REPORT.md](MOODLE_COMPLIANCE_REPORT.md) for detailed compliance status.

---

## 📦 What's Included

Complete Moodle plugin with:
- ✅ Multi-step wizard interface
- ✅ Database-wide text search
- ✅ Safe preview mode (dry run)
- ✅ Interactive content selection
- ✅ Comprehensive security controls
- ✅ Complete documentation
- ✅ GDPR compliance
- ✅ Privacy API implementation
- ✅ Events API logging
- ✅ A+ Security implementation

---

## 🛡️ Security

TextPlus has been thoroughly reviewed and achieves an **A+ security rating**:

- ✅ Site administrator-only access
- ✅ Multiple permission layers
- ✅ XSS protection throughout
- ✅ CSRF protection on all actions
- ✅ SQL injection prevention
- ✅ Input validation and sanitization
- ✅ Output escaping
- ✅ Database transaction safety

See [SECURITY_AUDIT.md](SECURITY_AUDIT.md) for complete security assessment.

**Note:** The wizard "Start over" action clears server-side wizard state and now requires a valid `sesskey` (CSRF protection). Use the UI buttons/links rather than bookmarking the start-over URL.

---

## 📝 Requirements

### Moodle Requirements
- **Moodle version:** 4.3 to 5.1+ (fully tested)
- **PHP version:** 8.0 or higher (required by Moodle 4.3)

### PHP Extensions
- **Required:** mbstring, mysqli/pgsql, json
- **Standard PHP libraries** (included by default)

### Server Requirements
- Database access with sufficient permissions
- Sufficient PHP memory limit (128MB minimum, 256MB+ recommended for large batches)
- PHP `max_execution_time` sufficient for batch operations

---

## 🤝 Contributing

Contributions are welcome!

- **Support / Issues:** https://github.com/gwizit/moodle-local_textplus/issues
- **Submit pull requests:** https://github.com/gwizit/moodle-local_textplus
- **Contact us:** Through https://gwizit.com

Please follow Moodle coding standards when contributing.

---

## 📄 License

This plugin is licensed under the [GNU GPL v3 or later](LICENSE).

---

## 💝 Support

**Found this plugin useful?** Consider supporting its development!

- **Donate:** https://square.link/u/DMRTvZ0Y
- **Support / Issues:** https://github.com/gwizit/moodle-local_textplus/issues
- **Professional support:** Contact via https://gwizit.com

---

## 🏆 Credits

**Developed by:** [G Wiz IT Solutions](https://gwizit.com)  
**Copyright:** 2025 G Wiz IT Solutions  
**License:** GNU GPL v3 or later

---

**Thank you for using TextPlus!** 🎓
