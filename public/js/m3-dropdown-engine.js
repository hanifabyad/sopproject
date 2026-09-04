/**
 * Material 3 / Radix UI Universal Dropdown Engine
 * - Smooth sweep-down & sweep-up physics for Sidebar Navigation
 * - Universal animated custom dropdown for all <select> elements across e-QMS
 * - Staggered entrance animations for all dropdown options
 * - 100% solid, non-overlapping styling and bulletproof form compatibility
 * PT PKM Group
 */
(function() {
    'use strict';

    // ------------------------------------------------------------------------
    // 1. DUAL-PHYSICS RIPPLE EFFECT
    // ------------------------------------------------------------------------
    document.addEventListener('pointerdown', function(e) {
        if (e.button !== 0) return;
        const target = e.target.closest('.m3-dropdown-trigger, .sidebar-chevron-btn, .sidebar-sub-menu a, .m3-select-trigger, .m3-select-item, [data-m3-ripple]');
        if (!target) return;

        const rect = target.getBoundingClientRect();
        if (rect.width === 0 || rect.height === 0) return;

        const clickX = e.clientX - rect.left;
        const clickY = e.clientY - rect.top;

        const ripple = document.createElement('span');
        ripple.className = 'm3-ripple-wave';

        const maxDim = Math.max(rect.width, rect.height);
        const size = maxDim * 2;
        ripple.style.width = size + 'px';
        ripple.style.height = size + 'px';
        ripple.style.left = (clickX - size / 2) + 'px';
        ripple.style.top = (clickY - size / 2) + 'px';

        const computedPos = window.getComputedStyle(target).position;
        if (computedPos === 'static') {
            target.style.position = 'relative';
        }
        target.style.overflow = 'hidden';
        target.appendChild(ripple);

        const removeRipple = () => {
            ripple.style.opacity = '0';
            setTimeout(() => {
                if (ripple.parentNode) ripple.parentNode.removeChild(ripple);
            }, 300);
        };

        target.addEventListener('pointerup', removeRipple, { once: true });
        target.addEventListener('pointerleave', removeRipple, { once: true });
        target.addEventListener('pointercancel', removeRipple, { once: true });
    });

    // ------------------------------------------------------------------------
    // 2. SIDEBAR SUBMENU INTERCEPTOR (SMOOTH CLOSING & OPENING)
    // ------------------------------------------------------------------------
    window.toggleNavGroup = function(groupId) {
        const sidebar = document.getElementById('sidebar');
        const mainContainer = document.getElementById('main-container');
        if (sidebar && sidebar.classList.contains('w-16')) {
            sidebar.classList.remove('w-16');
            sidebar.classList.add('w-64', 'shadow-2xl', 'z-50');
            if (mainContainer && window.innerWidth >= 1024) {
                mainContainer.classList.remove('lg:pl-16');
                mainContainer.classList.add('lg:pl-64');
            }
        }

        const menu = document.getElementById(groupId);
        const chevron = document.getElementById(groupId + '-chevron');
        if (!menu) return;

        const isHidden = menu.classList.contains('hidden');
        if (isHidden) {
            menu.classList.remove('hidden', 'm3-closing');
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            menu.classList.add('m3-closing');
            if (chevron) chevron.classList.remove('rotate-180');
            setTimeout(() => {
                menu.classList.add('hidden');
                menu.classList.remove('m3-closing');
            }, 220);
        }
    };

    // ------------------------------------------------------------------------
    // 3. UNIVERSAL ANIMATED SELECT DROPDOWN ENGINE
    // ------------------------------------------------------------------------
    let activeOpenMenu = null;
    let activeOpenTrigger = null;

    function closeActiveMenu() {
        if (!activeOpenMenu) return;
        const menu = activeOpenMenu;
        const trigger = activeOpenTrigger;
        activeOpenMenu = null;
        activeOpenTrigger = null;

        if (trigger) {
            trigger.classList.remove('active');
            const caret = trigger.querySelector('.m3-select-caret');
            if (caret) caret.classList.remove('rotate-180');
        }

        menu.classList.add('m3-closing');
        setTimeout(() => {
            if (menu.parentNode) {
                menu.parentNode.removeChild(menu);
            }
        }, 200);
    }

    // Close on click outside or escape
    document.addEventListener('pointerdown', function(e) {
        if (!activeOpenMenu) return;
        if (activeOpenMenu.contains(e.target) || (activeOpenTrigger && activeOpenTrigger.contains(e.target))) {
            return;
        }
        closeActiveMenu();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && activeOpenMenu) {
            closeActiveMenu();
        }
    });

    window.addEventListener('resize', closeActiveMenu);
    window.addEventListener('scroll', function(e) {
        if (!activeOpenMenu) return;
        // Don't close if scrolling inside the dropdown menu itself
        if (activeOpenMenu.contains(e.target)) return;
        closeActiveMenu();
    }, true);

    function enhanceSelect(select) {
        if (select.dataset.m3Enhanced === 'true') return;
        if (select.getAttribute('data-native') !== null) return;
        if (select.classList.contains('m3-enhanced')) return;

        select.dataset.m3Enhanced = 'true';
        select.classList.add('m3-enhanced');

        // Create Wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'm3-select-wrapper';
        
        // Match display or width of original select
        const origStyle = window.getComputedStyle(select);
        if (origStyle.width) {
            wrapper.style.width = select.style.width || (select.classList.contains('w-full') ? '100%' : 'auto');
        }
        if (select.classList.contains('flex-1')) {
            wrapper.classList.add('flex-1');
        }

        // Insert wrapper before select
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        // Hide native select visually but keep fully functional and accessible
        select.style.cssText = 'position: absolute !important; opacity: 0 !important; pointer-events: none !important; width: 1px !important; height: 1px !important; margin: -1px !important; clip: rect(0,0,0,0) !important; padding: 0 !important; border: 0 !important;';

        // Create Custom Trigger
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'm3-select-trigger ' + select.className.replace('m3-enhanced', '').trim();
        
        // Clean up trigger classes (remove native select appearance)
        trigger.style.appearance = 'none';
        trigger.style.webkitAppearance = 'none';
        trigger.style.display = 'flex';
        trigger.style.alignItems = 'center';
        trigger.style.justifyContent = 'space-between';
        trigger.style.textAlign = 'left';
        trigger.style.cursor = 'pointer';
        trigger.style.userSelect = 'none';
        trigger.style.width = '100%';

        const labelSpan = document.createElement('span');
        labelSpan.className = 'truncate pointer-events-none flex-1';

        const caretIcon = document.createElement('i');
        caretIcon.className = 'ph ph-caret-down text-xs text-slate-400 m3-select-caret ml-2 flex-shrink-0 transition-transform duration-250';

        trigger.appendChild(labelSpan);
        trigger.appendChild(caretIcon);
        wrapper.appendChild(trigger);

        function updateTriggerText() {
            const selectedOpt = select.options[select.selectedIndex];
            labelSpan.textContent = selectedOpt ? selectedOpt.text : (select.options[0]?.text || 'Pilih...');
        }
        updateTriggerText();

        // Listen for programmatic value change
        select.addEventListener('change', updateTriggerText);
        if (select.form) {
            select.form.addEventListener('reset', () => setTimeout(updateTriggerText, 50));
        }

        // Mutation observer to detect dynamic option additions/removals
        const observer = new MutationObserver(() => {
            updateTriggerText();
        });
        observer.observe(select, { childList: true, subtree: true });

        // Trigger Click Handler
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (activeOpenTrigger === trigger) {
                closeActiveMenu();
                return;
            }

            closeActiveMenu();

            if (select.disabled) return;

            // Open menu
            trigger.classList.add('active');
            caretIcon.classList.add('rotate-180');
            activeOpenTrigger = trigger;

            const rect = trigger.getBoundingClientRect();
            const menuHeight = Math.min(select.options.length * 34 + 16, 260);
            const spaceBelow = window.innerHeight - rect.bottom;
            const openUp = spaceBelow < menuHeight && rect.top > menuHeight;

            const menu = document.createElement('div');
            menu.className = 'm3-select-menu' + (openUp ? ' flip-up' : '');
            menu.style.position = 'fixed';
            menu.style.left = rect.left + 'px';
            menu.style.width = Math.max(rect.width, 180) + 'px';
            menu.style.boxSizing = 'border-box';
            menu.style.zIndex = '999999';

            if (openUp) {
                menu.style.bottom = (window.innerHeight - rect.top + 4) + 'px';
                menu.style.top = 'auto';
            } else {
                menu.style.top = (rect.bottom + 4) + 'px';
                menu.style.bottom = 'auto';
            }

            // Populate options
            Array.from(select.options).forEach((opt, idx) => {
                const item = document.createElement('div');
                item.className = 'm3-select-item' + (opt.selected ? ' selected' : '');
                item.style.animationDelay = Math.min(idx * 15, 150) + 'ms';

                const itemText = document.createElement('span');
                itemText.className = 'truncate flex-1';
                itemText.textContent = opt.text;
                item.appendChild(itemText);

                if (opt.selected) {
                    const checkIcon = document.createElement('i');
                    checkIcon.className = 'ph ph-check text-[#1677B8] font-bold text-xs ml-2 flex-shrink-0';
                    item.appendChild(checkIcon);
                }

                item.addEventListener('click', function(evt) {
                    evt.stopPropagation();
                    select.value = opt.value;
                    updateTriggerText();
                    closeActiveMenu();

                    // Dispatch change event
                    const changeEvent = new Event('change', { bubbles: true });
                    const inputEvent = new Event('input', { bubbles: true });
                    select.dispatchEvent(changeEvent);
                    select.dispatchEvent(inputEvent);

                    if (typeof select.onchange === 'function') {
                        select.onchange();
                    }
                });

                menu.appendChild(item);
            });

            document.body.appendChild(menu);
            activeOpenMenu = menu;

            // Scroll selected item into view
            const selectedItem = menu.querySelector('.selected');
            if (selectedItem) {
                selectedItem.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    function initAllSelects() {
        document.querySelectorAll('select:not(.m3-enhanced):not([data-native])').forEach(sel => {
            // Only enhance selects that are not explicitly hidden
            if (sel.style.display === 'none' && !sel.closest('.modal, [role="dialog"], [x-data]')) {
                return;
            }
            enhanceSelect(sel);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllSelects);
    } else {
        initAllSelects();
    }

    // Auto-enhance newly added selects (e.g. dynamic modals)
    const bodyObserver = new MutationObserver(() => {
        initAllSelects();
    });
    bodyObserver.observe(document.body, { childList: true, subtree: true });

})();
