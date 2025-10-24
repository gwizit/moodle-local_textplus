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

/**
 * UTF-8 safe base64 decode function with error handling.
 * Standard atob() doesn't handle UTF-8 multi-byte characters (Japanese, Chinese, Arabic, emoji, etc.)
 *
 * @param {string} str Base64 encoded string
 * @returns {string} Decoded string
 */
const base64DecodeUnicode = (str) => {
    try {
        // Method 1: Try the standard UTF-8 decoding approach
        return decodeURIComponent(Array.prototype.map.call(atob(str), function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
    } catch (e) {
        // Method 2: Fallback for malformed sequences - use TextDecoder if available
        try {
            const binaryString = atob(str);
            const bytes = new Uint8Array(binaryString.length);
            for (let i = 0; i < binaryString.length; i++) {
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
 * Show occurrence in modal.
 *
 * @param {HTMLElement} link Link element that was clicked
 */
const showOccurrence = (link) => {
    const modal = document.getElementById('local-textplus-occurrence-modal');
    const title = document.getElementById('local-textplus-occurrence-modal-title');
    const body = document.getElementById('local-textplus-occurrence-modal-body-content');

    // Get base64-encoded data from attributes and decode
    const contextEncoded = link.getAttribute('data-context');
    const matchEncoded = link.getAttribute('data-match');
    const locationEncoded = link.getAttribute('data-location');

    // Decode from base64 using UTF-8 safe function
    const context = base64DecodeUnicode(contextEncoded);
    const match = base64DecodeUnicode(matchEncoded);
    const location = base64DecodeUnicode(locationEncoded);

    title.textContent = location;

    // Clear the body first
    body.textContent = '';

    // For highlighting, we need to split the context and wrap the match
    // Use a case-insensitive search to find all matches
    const contextLower = context.toLowerCase();
    const matchLower = match.toLowerCase();
    let lastIndex = 0;
    let result = '';

    // Find all occurrences of the search term to highlight
    while (true) {
        const index = contextLower.indexOf(matchLower, lastIndex);
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

    // Now split by our markers and create text nodes and highlight spans
    const parts = result.split(/<<<HIGHLIGHT_START>>>|<<<HIGHLIGHT_END>>>/);
    for (let i = 0; i < parts.length; i++) {
        if (i % 2 === 1) {
            // This is a highlighted part
            const span = document.createElement('span');
            span.className = 'highlight';
            span.textContent = parts[i];
            body.appendChild(span);
        } else {
            // This is normal text
            const textNode = document.createTextNode(parts[i]);
            body.appendChild(textNode);
        }
    }

    modal.style.display = 'block';
};

/**
 * Close the occurrence modal.
 */
const closeOccurrenceModal = () => {
    const modal = document.getElementById('local-textplus-occurrence-modal');
    modal.style.display = 'none';
};

/**
 * Initialize the occurrence modal functionality.
 */
export const init = () => {
    const modal = document.getElementById('local-textplus-occurrence-modal');
    const closeBtn = document.querySelector('.local-textplus-occurrence-modal-close');

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            closeOccurrenceModal();
        });
    }

    // Initialize occurrence links
    const occurrenceLinks = document.querySelectorAll('.local-textplus-occurrence-link');
    occurrenceLinks.forEach((link) => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            showOccurrence(link);
        });
    });

    // Close modal when clicking outside of it
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeOccurrenceModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeOccurrenceModal();
        }
    });
};
