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
 * Initialize the occurrence modal.
 */
export const init = () => {
    const modal = document.getElementById('local-textplus-occurrence-modal');
    const modalTitle = document.getElementById('local-textplus-occurrence-modal-title');
    const modalBody = document.getElementById('local-textplus-occurrence-modal-body');
    const closeBtn = document.querySelector('.local-textplus-occurrence-modal-close');

    if (!modal || !modalTitle || !modalBody || !closeBtn) {
        // Elements don't exist on this page, nothing to initialize
        return;
    }

    /**
     * Close and clear the modal.
     */
    const closeModal = () => {
        modal.style.display = 'none';
        modalTitle.innerHTML = '';
        modalBody.innerHTML = '';
    };

    /**
     * Open the modal with occurrence data.
     *
     * @param {string} title Modal title
     * @param {string} content Modal body content
     */
    const openModal = (title, content) => {
        modalTitle.innerHTML = title;
        modalBody.innerHTML = content;
        modal.style.display = 'block';
    };

    // Close modal when clicking X or outside modal
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });

    // Add click handlers to all view occurrence links
    document.addEventListener('click', (e) => {
        const target = e.target;
        if (target.classList.contains('local-textplus-view-occurrences')) {
            e.preventDefault();
            e.stopPropagation();

            const itemId = target.dataset.itemId;
            if (!itemId) {
                return;
            }

            // For now, show a loading message
            // In a real implementation, this would make an AJAX call to fetch occurrences
            const title = 'Occurrences for Item ' + itemId;
            const content = '<div class="alert alert-info">Loading occurrences...</div>' +
                           '<p>This feature would display all text occurrences found in this item.</p>';

            openModal(title, content);

            return false;
        }
    });

    // Export for use by other modules
    return {
        open: openModal,
        close: closeModal
    };
};
