// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Occurrence modal functionality for TextPlus plugin.
 *
 * @module     local_textplus/occurrence_modal
 * @copyright  2025 G Wiz IT Solutions {@link https://gwizit.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    'use strict';

    /**
     * UTF-8 safe base64 decode function with error handling.
     * Standard atob() doesn't handle UTF-8 multi-byte characters (Japanese, Chinese, Arabic, emoji, etc.)
     *
     * @param {string} str Base64 encoded string
     * @returns {string} Decoded string
     */
    var base64DecodeUnicode = function(str) {
        try {
            // Method 1: Try the standard UTF-8 decoding approach
            return decodeURIComponent(Array.prototype.map.call(atob(str), function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
        } catch (e) {
            // Method 2: Fallback for malformed sequences - use TextDecoder if available
            try {
                var binaryString = atob(str);
                var bytes = new Uint8Array(binaryString.length);
                for (var i = 0; i < binaryString.length; i++) {
                    bytes[i] = binaryString.charCodeAt(i);
                }
                // Use TextDecoder for robust UTF-8 decoding
                if (typeof TextDecoder !== 'undefined') {
                    return new TextDecoder('utf-8').decode(bytes);
                }
                // Final fallback: return raw atob result
                return binaryString;
            } catch (e2) {
                // eslint-disable-next-line no-console
                console.error('Base64 decode error:', e, e2);
                return '[Error decoding content]';
            }
        }
    };

    /**
     * Convert wildcard pattern to regex for highlighting.
     * Matches PHP wildcard_to_regex logic.
     *
     * @param {string} pattern Pattern with * wildcards
     * @param {boolean} caseSensitive Case sensitive matching
     * @returns {RegExp} Regular expression
     */
    var wildcardToRegex = function(pattern, caseSensitive) {
        caseSensitive = caseSensitive || false;
        // Escape special regex characters except *
        var regexPattern = pattern.replace(/[.+?^${}()|[\]\\]/g, '\\$&');
        // Replace * with \S* (match non-whitespace characters only)
        regexPattern = regexPattern.replace(/\*/g, '\\S*');
        // Create regex with appropriate flags
        var flags = caseSensitive ? 'g' : 'gi';
        return new RegExp(regexPattern, flags);
    };

    /**
     * Check if pattern contains wildcards.
     *
     * @param {string} pattern Pattern to check
     * @returns {boolean} True if contains wildcards
     */
    var hasWildcards = function(pattern) {
        return pattern.indexOf('*') !== -1;
    };

    /**
     * Show occurrence in modal.
     *
     * @param {HTMLElement} link Link element that was clicked
     */
    var showOccurrence = function(link) {
        var modal = document.getElementById('local-textplus-occurrence-modal');
        var title = document.getElementById('local-textplus-occurrence-modal-title');
        var body = document.getElementById('local-textplus-occurrence-modal-body-content');

        // Get base64-encoded data from attributes and decode
        var contextEncoded = link.getAttribute('data-context');
        var matchEncoded = link.getAttribute('data-match');
        var locationEncoded = link.getAttribute('data-location');
        var searchTermEncoded = link.getAttribute('data-searchterm');

        // Decode from base64 using UTF-8 safe function
        var context = base64DecodeUnicode(contextEncoded);
        var match = base64DecodeUnicode(matchEncoded);
        var location = base64DecodeUnicode(locationEncoded);
        var searchTerm = searchTermEncoded ? base64DecodeUnicode(searchTermEncoded) : match;

        title.textContent = location;

        // Clear the body first
        body.textContent = '';

        var result = '';

        // Check if search term contains wildcards
        if (hasWildcards(searchTerm)) {
            // Use regex for wildcard highlighting
            var regex = wildcardToRegex(searchTerm, false);
            var lastIndex = 0;
            var regexMatch;

            // Reset regex state
            regex.lastIndex = 0;

            while ((regexMatch = regex.exec(context)) !== null) {
                // Add text before match
                result += context.substring(lastIndex, regexMatch.index);

                // Add highlighted match
                result += '<<<HIGHLIGHT_START>>>' + regexMatch[0] + '<<<HIGHLIGHT_END>>>';

                lastIndex = regexMatch.index + regexMatch[0].length;

                // Prevent infinite loop on zero-length matches
                if (regexMatch.index === regex.lastIndex) {
                    regex.lastIndex++;
                }
            }

            // Add remaining text
            result += context.substring(lastIndex);
        } else {
            // Standard case-insensitive search for exact matches
            var contextLower = context.toLowerCase();
            var matchLower = match.toLowerCase();
            lastIndex = 0;

            // Find all occurrences of the search term to highlight
            while (true) {
                var index = contextLower.indexOf(matchLower, lastIndex);
                if (index === -1) {
                    // Add remaining text
                    result += context.substring(lastIndex);
                    break;
                }

                // Add text before match
                result += context.substring(lastIndex, index);

                // Add highlighted match
                result += '<<<HIGHLIGHT_START>>>' + context.substring(index, index + match.length) + '<<<HIGHLIGHT_END>>>';

                lastIndex = index + match.length;
            }
        }

        // Now split by our markers and create text nodes and highlight spans
        var parts = result.split(/<<<HIGHLIGHT_START>>>|<<<HIGHLIGHT_END>>>/);
        for (var i = 0; i < parts.length; i++) {
            if (i % 2 === 1) {
                // This is a highlighted part
                var span = document.createElement('span');
                span.className = 'highlight';
                span.textContent = parts[i];
                body.appendChild(span);
            } else {
                // This is normal text
                var textNode = document.createTextNode(parts[i]);
                body.appendChild(textNode);
            }
        }

        modal.style.display = 'block';
    };

    /**
     * Close the occurrence modal.
     */
    var closeOccurrenceModal = function() {
        var modal = document.getElementById('local-textplus-occurrence-modal');
        modal.style.display = 'none';
    };

    /**
     * Initialize the occurrence modal functionality.
     */
    var init = function() {
        var modal = document.getElementById('local-textplus-occurrence-modal');
        var closeBtn = document.querySelector('.local-textplus-occurrence-modal-close');

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                closeOccurrenceModal();
            });
        }

        // Initialize occurrence links
        var occurrenceLinks = document.querySelectorAll('.local-textplus-occurrence-link');
        occurrenceLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                showOccurrence(link);
            });
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeOccurrenceModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeOccurrenceModal();
            }
        });
    };

    return {
        init: init
    };
});
