/**
 * E-QMS Custom Modern Dialog & Confirm Modal System
 * PT Putra Kelana Makmur (PKM Group)
 * Replaces default browser alert() and confirm() with modern, accessible, styled modals.
 */
(function() {
    'use strict';

    // Inject Dialog HTML if not already present
    function ensureDialogDOM() {
        if (document.getElementById('eqms-global-dialog')) return;

        const dialogHtml = `
        <div id="eqms-global-dialog" class="fixed inset-0 z-[99999] hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-200 opacity-0">
            <div id="eqms-dialog-card" class="bg-white rounded-xl w-full max-w-md shadow-2xl border border-slate-200 overflow-hidden transform scale-95 opacity-0 transition-all duration-200 ease-out">
                <!-- Header Accent Bar -->
                <div id="eqms-dialog-bar" class="h-1.5 w-full bg-[#1677B8]"></div>
                
                <div class="p-6 space-y-4">
                    <div class="flex items-start gap-3.5">
                        <div id="eqms-dialog-icon-wrapper" class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-blue-50 text-[#1677B8]">
                            <i id="eqms-dialog-icon" class="ph ph-info text-2xl"></i>
                        </div>
                        <div class="flex-1 min-w-0 pt-0.5">
                            <h3 id="eqms-dialog-title" class="text-sm font-black text-slate-900 leading-snug">Konfirmasi Aksi</h3>
                            <p id="eqms-dialog-message" class="text-xs text-slate-600 mt-1 font-medium leading-relaxed whitespace-pre-line"></p>
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" id="eqms-dialog-cancel" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-[2px] transition-all cursor-pointer border-none">
                            Batal
                        </button>
                        <button type="button" id="eqms-dialog-confirm" class="px-5 py-2 bg-[#1677B8] hover:bg-[#1260a0] text-white font-extrabold text-xs rounded-[2px] shadow-sm transition-all flex items-center gap-1.5 cursor-pointer border-none">
                            <span>Lanjutkan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;

        document.body.insertAdjacentHTML('beforeend', dialogHtml);
    }

    /**
     * Show custom confirm promise
     */
    window.showCustomConfirm = function(options) {
        ensureDialogDOM();
        
        return new Promise((resolve) => {
            const modal = document.getElementById('eqms-global-dialog');
            const card = document.getElementById('eqms-dialog-card');
            const bar = document.getElementById('eqms-dialog-bar');
            const iconWrapper = document.getElementById('eqms-dialog-icon-wrapper');
            const icon = document.getElementById('eqms-dialog-icon');
            const titleEl = document.getElementById('eqms-dialog-title');
            const msgEl = document.getElementById('eqms-dialog-message');
            const cancelBtn = document.getElementById('eqms-dialog-cancel');
            const confirmBtn = document.getElementById('eqms-dialog-confirm');

            const message = typeof options === 'string' ? options : (options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?');
            const title = options.title || 'Konfirmasi Tindakan';
            const confirmText = options.confirmText || 'Lanjutkan';
            const cancelText = options.cancelText || 'Batal';
            const isAlertOnly = options.isAlert || false;

            msgEl.textContent = message;
            titleEl.textContent = title;
            confirmBtn.querySelector('span').textContent = confirmText;
            cancelBtn.textContent = cancelText;

            if (isAlertOnly) {
                cancelBtn.classList.add('hidden');
            } else {
                cancelBtn.classList.remove('hidden');
            }

            // Determine theme type from text
            const lowerMsg = (message + ' ' + title).toLowerCase();
            if (lowerMsg.includes('hapus') || lowerMsg.includes('delete') || lowerMsg.includes('keluar') || lowerMsg.includes('logout') || lowerMsg.includes('tolak') || lowerMsg.includes('reject')) {
                bar.className = 'h-1.5 w-full bg-rose-600';
                iconWrapper.className = 'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-rose-50 text-rose-600';
                icon.className = lowerMsg.includes('logout') || lowerMsg.includes('keluar') ? 'ph ph-sign-out text-2xl' : 'ph ph-trash text-2xl';
                confirmBtn.className = 'px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-[2px] shadow-sm transition-all flex items-center gap-1.5 cursor-pointer border-none';
            } else if (lowerMsg.includes('pulih') || lowerMsg.includes('restore') || lowerMsg.includes('setuju') || lowerMsg.includes('approve') || lowerMsg.includes('kirim') || lowerMsg.includes('sukses')) {
                bar.className = 'h-1.5 w-full bg-emerald-600';
                iconWrapper.className = 'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-emerald-50 text-emerald-600';
                icon.className = 'ph ph-check-circle text-2xl';
                confirmBtn.className = 'px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-xs rounded-[2px] shadow-sm transition-all flex items-center gap-1.5 cursor-pointer border-none';
            } else if (lowerMsg.includes('analisis') || lowerMsg.includes('regenerate') || lowerMsg.includes('revisi') || lowerMsg.includes('peringatan') || lowerMsg.includes('warning')) {
                bar.className = 'h-1.5 w-full bg-amber-500';
                iconWrapper.className = 'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-amber-50 text-amber-600';
                icon.className = 'ph ph-warning-circle text-2xl';
                confirmBtn.className = 'px-5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-900 font-extrabold text-xs rounded-[2px] shadow-sm transition-all flex items-center gap-1.5 cursor-pointer border-none';
            } else {
                bar.className = 'h-1.5 w-full bg-[#1677B8]';
                iconWrapper.className = 'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-sky-50 text-[#1677B8]';
                icon.className = 'ph ph-info text-2xl';
                confirmBtn.className = 'px-5 py-2 bg-[#1677B8] hover:bg-[#1260a0] text-white font-extrabold text-xs rounded-[2px] shadow-sm transition-all flex items-center gap-1.5 cursor-pointer border-none';
            }

            const closeDialog = (result) => {
                card.classList.remove('scale-100', 'opacity-100');
                card.classList.add('scale-95', 'opacity-0');
                modal.classList.remove('opacity-100');
                modal.classList.add('opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    resolve(result);
                }, 150);
            };

            const onConfirmClick = () => {
                cleanupListeners();
                closeDialog(true);
            };

            const onCancelClick = () => {
                cleanupListeners();
                closeDialog(false);
            };

            const onKeyDown = (e) => {
                if (e.key === 'Escape') {
                    cleanupListeners();
                    closeDialog(false);
                } else if (e.key === 'Enter') {
                    cleanupListeners();
                    closeDialog(true);
                }
            };

            const onBackdropClick = (e) => {
                if (e.target === modal) {
                    cleanupListeners();
                    closeDialog(false);
                }
            };

            function cleanupListeners() {
                confirmBtn.removeEventListener('click', onConfirmClick);
                cancelBtn.removeEventListener('click', onCancelClick);
                document.removeEventListener('keydown', onKeyDown);
                modal.removeEventListener('click', onBackdropClick);
            }

            confirmBtn.addEventListener('click', onConfirmClick);
            cancelBtn.addEventListener('click', onCancelClick);
            document.addEventListener('keydown', onKeyDown);
            modal.addEventListener('click', onBackdropClick);

            // Open with animation
            modal.classList.remove('hidden');
            modal.offsetHeight; // force reflow
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
            confirmBtn.focus();
        });
    };

    /**
     * Intercept standard forms, buttons, and links using confirm()
     */
    function attachGlobalConfirmInterceptors() {
        // 1. Intercept forms with onsubmit="return confirm(...)"
        document.querySelectorAll('form').forEach(form => {
            if (form.dataset.customConfirmInit === 'true') return;
            const onsubmitAttr = form.getAttribute('onsubmit');
            if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
                if (match) {
                    const message = match[1];
                    form.removeAttribute('onsubmit');
                    form.setAttribute('data-confirm-msg', message);
                    form.dataset.customConfirmInit = 'true';

                    form.addEventListener('submit', function(e) {
                        if (form.dataset.confirmed === 'true') {
                            form.dataset.confirmed = 'false';
                            return true;
                        }
                        e.preventDefault();
                        window.showCustomConfirm({
                            message: message,
                            title: message.toLowerCase().includes('hapus') ? 'Konfirmasi Penghapusan' : (message.toLowerCase().includes('keluar') ? 'Konfirmasi Keluar' : 'Konfirmasi Tindakan'),
                            confirmText: message.toLowerCase().includes('hapus') ? 'Hapus' : (message.toLowerCase().includes('keluar') ? 'Keluar' : 'Ya, Lanjutkan')
                        }).then(confirmed => {
                            if (confirmed) {
                                form.dataset.confirmed = 'true';
                                form.submit();
                            }
                        });
                    });
                }
            }
        });

        // 2. Intercept buttons or links with onclick="return confirm(...)"
        document.querySelectorAll('button[onclick*="confirm("], a[onclick*="confirm("]').forEach(el => {
            if (el.dataset.customConfirmInit === 'true') return;
            const onclickAttr = el.getAttribute('onclick');
            const match = onclickAttr.match(/confirm\(['"](.*?)['"]\)/);
            if (match) {
                const message = match[1];
                el.removeAttribute('onclick');
                el.setAttribute('data-confirm-msg', message);
                el.dataset.customConfirmInit = 'true';

                el.addEventListener('click', function(e) {
                    if (el.dataset.confirmed === 'true') {
                        el.dataset.confirmed = 'false';
                        return true;
                    }
                    e.preventDefault();
                    window.showCustomConfirm({
                        message: message,
                        title: message.toLowerCase().includes('hapus') ? 'Konfirmasi Penghapusan' : 'Konfirmasi Tindakan',
                        confirmText: message.toLowerCase().includes('hapus') ? 'Hapus' : 'Ya, Lanjutkan'
                    }).then(confirmed => {
                        if (confirmed) {
                            el.dataset.confirmed = 'true';
                            if (el.tagName === 'A' && el.href) {
                                window.location.href = el.href;
                            } else if (el.type === 'submit' && el.form) {
                                el.form.submit();
                            } else {
                                el.click();
                            }
                        }
                    });
                });
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            ensureDialogDOM();
            attachGlobalConfirmInterceptors();
        });
    } else {
        ensureDialogDOM();
        attachGlobalConfirmInterceptors();
    }

    // Observer for dynamically injected elements/modals
    const observer = new MutationObserver(() => {
        attachGlobalConfirmInterceptors();
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });
})();
