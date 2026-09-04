/**
 * E-QMS Universal File-Explorer Style Table Sorter
 * PT Putra Kelana Makmur (PKM Group)
 * Enables clickable column sorting on all data tables (Dates, Numbers, Text, Status Badges).
 */
(function() {
    'use strict';

    const MONTHS_MAP = {
        'januari': 0, 'jan': 0, 'january': 0,
        'februari': 1, 'feb': 1, 'february': 1,
        'maret': 2, 'mar': 2, 'march': 2,
        'april': 3, 'apr': 3,
        'mei': 4, 'may': 4,
        'juni': 5, 'jun': 5, 'june': 5,
        'juli': 6, 'jul': 6, 'july': 6,
        'agustus': 7, 'agu': 7, 'agt': 7, 'august': 7, 'aug': 7,
        'september': 8, 'sep': 8, 'sept': 8,
        'oktober': 9, 'okt': 9, 'october': 9, 'oct': 9,
        'november': 10, 'nov': 10,
        'desember': 11, 'des': 11, 'december': 11, 'dec': 11
    };

    function parseDateValue(str) {
        if (!str) return null;
        const cleaned = str.trim().toLowerCase()
            .replace(/wib|wita|wit/g, '')
            .replace(/,/g, '')
            .trim();

        // Format: DD/MM/YYYY or DD-MM-YYYY
        const dmyMatch = cleaned.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})(?:\s+(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?$/);
        if (dmyMatch) {
            const day = parseInt(dmyMatch[1], 10);
            const month = parseInt(dmyMatch[2], 10) - 1;
            const year = parseInt(dmyMatch[3], 10);
            const hour = dmyMatch[4] ? parseInt(dmyMatch[4], 10) : 0;
            const min = dmyMatch[5] ? parseInt(dmyMatch[5], 10) : 0;
            return new Date(year, month, day, hour, min).getTime();
        }

        // Format: YYYY-MM-DD
        const ymdMatch = cleaned.match(/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})(?:\s+(\d{1,2}):(\d{1,2}))?$/);
        if (ymdMatch) {
            const year = parseInt(ymdMatch[1], 10);
            const month = parseInt(ymdMatch[2], 10) - 1;
            const day = parseInt(ymdMatch[3], 10);
            return new Date(year, month, day).getTime();
        }

        // Format: DD MonthName YYYY (e.g. 03 September 2026)
        const nameMatch = cleaned.match(/^(\d{1,2})\s+([a-z]+)\s+(\d{4})(?:\s+(\d{1,2}):(\d{1,2}))?/);
        if (nameMatch) {
            const day = parseInt(nameMatch[1], 10);
            const monthName = nameMatch[2];
            const year = parseInt(nameMatch[3], 10);
            if (MONTHS_MAP.hasOwnProperty(monthName)) {
                const month = MONTHS_MAP[monthName];
                const hour = nameMatch[4] ? parseInt(nameMatch[4], 10) : 0;
                const min = nameMatch[5] ? parseInt(nameMatch[5], 10) : 0;
                return new Date(year, month, day, hour, min).getTime();
            }
        }

        const fallback = Date.parse(cleaned);
        return isNaN(fallback) ? null : fallback;
    }

    function extractCellValue(cell) {
        if (!cell) return '';
        if (cell.hasAttribute('data-sort')) {
            return cell.getAttribute('data-sort').trim();
        }
        return cell.innerText ? cell.innerText.trim() : (cell.textContent ? cell.textContent.trim() : '');
    }

    function compareValues(a, b, isAsc) {
        const valA = a.value;
        const valB = b.value;

        if (valA === '' && valB !== '') return 1;
        if (valB === '' && valA !== '') return -1;
        if (valA === '' && valB === '') return 0;

        // 1. Date comparison
        const dateA = parseDateValue(valA);
        const dateB = parseDateValue(valB);
        if (dateA !== null && dateB !== null) {
            return isAsc ? dateA - dateB : dateB - dateA;
        }

        // 2. Numeric comparison
        const cleanNumA = valA.replace(/[^0-9.-]/g, '');
        const cleanNumB = valB.replace(/[^0-9.-]/g, '');
        const numA = parseFloat(cleanNumA);
        const numB = parseFloat(cleanNumB);
        const isStrictNumA = cleanNumA !== '' && !isNaN(numA) && !isNaN(valA);
        const isStrictNumB = cleanNumB !== '' && !isNaN(numB) && !isNaN(valB);

        if (isStrictNumA && isStrictNumB) {
            return isAsc ? numA - numB : numB - numA;
        }

        // 3. Natural Alphanumeric Comparison
        return isAsc
            ? valA.localeCompare(valB, 'id', { numeric: true, sensitivity: 'base' })
            : valB.localeCompare(valA, 'id', { numeric: true, sensitivity: 'base' });
    }

    function initTableSorter() {
        const tables = document.querySelectorAll('table');
        
        tables.forEach((table) => {
            if (table.dataset.sortableInit === 'true') return;
            const thead = table.querySelector('thead');
            const tbody = table.querySelector('tbody');
            if (!thead || !tbody) return;

            const headerRow = thead.rows[0];
            if (!headerRow) return;

            const headers = Array.from(headerRow.cells);
            let hasSortableCol = false;

            headers.forEach((th, colIndex) => {
                const rawText = th.innerText.trim();
                const headerText = rawText.toLowerCase();

                // Exclude action columns, checkboxes, or no-sort classes
                if (
                    headerText === 'aksi' || 
                    headerText === 'action' || 
                    headerText === 'actions' || 
                    headerText === 'opsi' ||
                    th.classList.contains('no-sort') ||
                    th.classList.contains('no-print') ||
                    th.querySelector('input[type="checkbox"]')
                ) {
                    return;
                }

                hasSortableCol = true;
                th.style.cursor = 'pointer';
                th.style.userSelect = 'none';
                th.style.whiteSpace = 'nowrap';
                th.setAttribute('title', 'Klik untuk mengurutkan');
                th.classList.add('sortable-header', 'group', 'transition-colors', 'whitespace-nowrap');

                // Save initial HTML content
                const initialHtml = th.innerHTML;
                th.innerHTML = '';

                // Build a unified flex container to prevent icon dropping to second line
                const wrapper = document.createElement('div');
                const isCenter = th.classList.contains('text-center') || th.getAttribute('align') === 'center';
                const isRight = th.classList.contains('text-right') || th.getAttribute('align') === 'right';

                wrapper.className = isCenter 
                    ? 'inline-flex items-center justify-center gap-1.5 w-full whitespace-nowrap pointer-events-none'
                    : (isRight 
                        ? 'inline-flex items-center justify-end gap-1.5 w-full whitespace-nowrap pointer-events-none'
                        : 'inline-flex items-center gap-1.5 whitespace-nowrap pointer-events-none');

                const textSpan = document.createElement('span');
                textSpan.className = 'sort-label whitespace-nowrap font-bold';
                textSpan.innerHTML = initialHtml;

                const iconContainer = document.createElement('span');
                iconContainer.className = 'sort-indicator inline-flex items-center text-slate-400 group-hover:text-[#1677B8] transition-colors flex-shrink-0';
                iconContainer.innerHTML = '<i class="ph ph-caret-up-down text-[10px] opacity-60"></i>';

                wrapper.appendChild(textSpan);
                wrapper.appendChild(iconContainer);
                th.appendChild(wrapper);

                th.addEventListener('click', () => {
                    const currentOrder = th.getAttribute('data-order') || 'none';
                    const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

                    // Reset all other headers in this table
                    headers.forEach(h => {
                        h.removeAttribute('data-order');
                        const icon = h.querySelector('.sort-indicator');
                        if (icon) {
                            icon.innerHTML = '<i class="ph ph-caret-up-down text-[10px] opacity-60"></i>';
                            icon.className = 'sort-indicator inline-flex items-center text-slate-400 group-hover:text-[#1677B8] transition-colors flex-shrink-0';
                        }
                    });

                    th.setAttribute('data-order', newOrder);
                    if (newOrder === 'asc') {
                        iconContainer.innerHTML = '<i class="ph ph-caret-up text-[10px] font-extrabold"></i>';
                        iconContainer.className = 'sort-indicator inline-flex items-center text-[#1677B8] flex-shrink-0';
                        th.setAttribute('title', 'Urutan: A-Z / Terlama');
                    } else {
                        iconContainer.innerHTML = '<i class="ph ph-caret-down text-[10px] font-extrabold"></i>';
                        iconContainer.className = 'sort-indicator inline-flex items-center text-[#1677B8] flex-shrink-0';
                        th.setAttribute('title', 'Urutan: Z-A / Terbaru');
                    }

                    // Sort tbody rows
                    const rows = Array.from(tbody.rows);
                    const sortableRows = [];
                    const staticRows = [];

                    rows.forEach(row => {
                        if (row.cells.length === 1 && row.cells[0].colSpan > 1) {
                            staticRows.push(row);
                        } else {
                            const cell = row.cells[colIndex];
                            sortableRows.push({
                                element: row,
                                value: extractCellValue(cell)
                            });
                        }
                    });

                    if (sortableRows.length <= 1) return;

                    sortableRows.sort((a, b) => compareValues(a, b, newOrder === 'asc'));

                    const fragment = document.createDocumentFragment();
                    sortableRows.forEach(item => fragment.appendChild(item.element));
                    staticRows.forEach(row => fragment.appendChild(row));
                    tbody.appendChild(fragment);

                    // Re-index sequence column if first column is "No"
                    const firstHeader = headers[0] ? headers[0].innerText.trim().toLowerCase() : '';
                    if (firstHeader === 'no' || firstHeader === 'no.' || firstHeader === '#') {
                        let counter = 1;
                        sortableRows.forEach(item => {
                            const firstCell = item.element.cells[0];
                            if (firstCell && !firstCell.querySelector('input') && !firstCell.querySelector('a') && !firstCell.querySelector('button')) {
                                firstCell.textContent = counter++;
                            }
                        });
                    }
                });
            });

            if (hasSortableCol) {
                table.dataset.sortableInit = 'true';
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTableSorter);
    } else {
        initTableSorter();
    }

    window.initEqmsTableSorter = initTableSorter;
})();
