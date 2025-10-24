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
 * Item selection functionality for TextPlus plugin.
 *
 * @module     local_textplus/item_selection
 * @copyright  2025 G Wiz IT Solutions {@link https://gwizit.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialize the item selection functionality.
 *
 * @param {Object} config Configuration object
 * @param {string} config.selectAllText Text for select all button
 * @param {string} config.deselectAllText Text for deselect all button
 * @param {string} config.warningText Warning text when selecting all
 */
export const init = (config) => {
    const selectAllBtn = document.getElementById('local-textplus-select-all');

    /**
     * Handle select all for items.
     *
     * @param {Event} e Click event
     */
    const handleSelectAll = (e) => {
        e.preventDefault();
        const checkboxes = document.querySelectorAll('input.local-textplus-item-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);

        if (!allChecked) {
            // Selecting all - show warning
            // eslint-disable-next-line no-alert
            alert(config.warningText);
        }

        checkboxes.forEach((cb) => {
            cb.checked = !allChecked;
        });
        e.target.textContent = allChecked ? config.selectAllText : config.deselectAllText;
    };

    // Add event listener if element exists
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', handleSelectAll);
    }

    // Initialize occurrence modal handlers
    const viewOccurrenceLinks = document.querySelectorAll('.local-textplus-view-occurrences');
    viewOccurrenceLinks.forEach((link) => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const itemId = link.dataset.itemId;
            // This would be implemented with occurrence_modal module
            // For now, just log it
            // eslint-disable-next-line no-console
            console.log('View occurrences for item:', itemId);
        });
    });
};
