<!-- PROTECTED PDF.JS CANVAS VIEWER (READ-ONLY / ANTI-DOWNLOAD) -->
<div id="pdfModal" class="fixed inset-0 z-[100] hidden bg-slate-900/90 backdrop-blur-xs flex flex-col p-2 sm:p-4 md:p-6 transition-all" onclick="closePDF()">
    <div class="w-full h-full flex flex-col max-w-6xl mx-auto shadow-2xl rounded-[2px] overflow-hidden bg-slate-950 border border-slate-700" onclick="event.stopPropagation()">
        
        <!-- VIEWER TOPBAR -->
        <div class="bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white px-3 sm:px-4 py-2 flex flex-wrap items-center justify-between gap-2 flex-shrink-0 shadow-sm border-b border-white/10 select-none">
            
            <!-- LEFT: TITLE & SECURITY BADGE -->
            <div class="flex items-center space-x-2.5 min-w-0 flex-1">
                <span class="bg-[#ffe16e] text-slate-900 text-[10px] px-2 py-0.5 rounded-[2px] font-black uppercase tracking-wider flex-shrink-0">
                    e-QMS
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[2px] text-[10px] font-semibold bg-rose-500/25 text-white border border-rose-300/30 flex-shrink-0">
                    <i class="ph ph-shield-check text-xs"></i>
                    <span>Hanya Baca</span>
                </span>
                <h3 id="pdfViewerDocTitle" class="font-bold text-xs tracking-wide text-white truncate max-w-xs md:max-w-md" title="Judul Dokumen">
                    Digital Document Control Viewer
                </h3>
            </div>

            <!-- CENTER: CONTROLS (PAGES & ZOOM) -->
            <div class="flex items-center space-x-1.5 sm:space-x-2 bg-black/20 px-2 py-1 rounded border border-white/10 flex-shrink-0">
                <!-- Page Navigation -->
                <button type="button" onclick="pdfChangePage(-1)" id="pdfPrevPageBtn" class="p-1 rounded hover:bg-white/20 text-white/90 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer transition-colors" title="Halaman Sebelumnya">
                    <i class="ph ph-caret-left text-sm"></i>
                </button>
                <div class="text-[11px] font-mono text-white flex items-center gap-1 px-1">
                    <span>Hal</span>
                    <span id="pdfCurrentPageNum" class="font-bold text-[#ffe16e]">1</span>
                    <span class="text-white/60">/</span>
                    <span id="pdfTotalPageNum" class="text-white/80">1</span>
                </div>
                <button type="button" onclick="pdfChangePage(1)" id="pdfNextPageBtn" class="p-1 rounded hover:bg-white/20 text-white/90 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer transition-colors" title="Halaman Berikutnya">
                    <i class="ph ph-caret-right text-sm"></i>
                </button>

                <span class="text-white/30 mx-1">|</span>

                <!-- Zoom Controls -->
                <button type="button" onclick="pdfZoom(-0.15)" class="p-1 rounded hover:bg-white/20 text-white/90 hover:text-white cursor-pointer transition-colors" title="Perkecil (-)">
                    <i class="ph ph-minus text-xs"></i>
                </button>
                <span id="pdfZoomLabel" class="text-[11px] font-mono text-white min-w-[38px] text-center font-semibold">100%</span>
                <button type="button" onclick="pdfZoom(0.15)" class="p-1 rounded hover:bg-white/20 text-white/90 hover:text-white cursor-pointer transition-colors" title="Perbesar (+)">
                    <i class="ph ph-plus text-xs"></i>
                </button>
                <button type="button" onclick="pdfFitWidth()" class="px-1.5 py-0.5 rounded hover:bg-white/20 text-[10px] font-semibold text-white/90 hover:text-white border border-white/20 cursor-pointer transition-colors" title="Sesuaikan Lebar Halaman">
                    Fit
                </button>
            </div>

            <!-- RIGHT: ADMIN DOWNLOAD & CLOSE -->
            <div class="flex items-center space-x-2 flex-shrink-0">
                @if(Auth::check() && Auth::user()->role === 'admin')
                    <a id="pdfAdminDownloadBtn" href="#" download 
                       class="px-2.5 py-1 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold rounded-[2px] border border-white/25 flex items-center gap-1 transition-colors shadow-xs" 
                       title="Download Dokumen Asli (Akses Khusus Administrator)">
                        <i class="ph ph-download-simple text-sm"></i>
                        <span class="hidden md:inline">Download (Admin)</span>
                    </a>
                @endif
                <button type="button" onclick="closePDF()" class="flex items-center gap-1 px-3 py-1 bg-white text-[#1677B8] hover:bg-slate-100 text-xs font-bold rounded-[2px] shadow-sm cursor-pointer transition-colors">
                    <i class="ph ph-x text-sm"></i>
                    <span>Tutup (ESC)</span>
                </button>
            </div>
        </div>

        <!-- VIEWER CANVAS CONTAINER (SCROLLABLE VERTICALLY) -->
        <div id="pdfViewerScrollContainer" 
             class="flex-1 w-full bg-slate-900 overflow-y-auto overflow-x-auto p-4 sm:p-6 flex flex-col items-center gap-6 relative select-none custom-scrollbar" 
             oncontextmenu="return false;"
             style="-webkit-touch-callout: none; -webkit-user-select: none; user-select: none;">
            
            <!-- Loading Indicator -->
            <div id="pdfLoadingSpinner" class="flex flex-col items-center justify-center py-24 text-white/80 space-y-3">
                <i class="ph ph-spinner animate-spin text-4xl text-[#00b4d8]"></i>
                <p class="text-xs font-medium tracking-wide">Memuat halaman dokumen kontrol e-QMS...</p>
            </div>

            <!-- Error Indicator -->
            <div id="pdfErrorNotice" class="hidden flex-col items-center justify-center py-20 text-rose-300 space-y-2 text-center max-w-md">
                <i class="ph ph-warning-circle text-4xl text-rose-400"></i>
                <h4 class="text-sm font-bold text-white">Gagal Memuat Dokumen</h4>
                <p class="text-xs text-rose-200/80 font-normal">Dokumen tidak dapat dimuat atau format tidak didukung oleh browser.</p>
            </div>

            <!-- Canvas Pages Stack -->
            <div id="pdfPagesContainer" class="flex flex-col items-center gap-6 w-full"></div>
        </div>
    </div>
</div>

<!-- PDF.JS CDN SCRIPT -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    // Inisialisasi worker PDF.js
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    let currentPdfDoc = null;
    let currentPdfScale = 1.0;
    let currentPdfUrl = '';
    let isRenderingPdf = false;
    let totalPdfPages = 0;
    let activePageNumber = 1;

    // Data watermark dinamis user
    const pdfWatermarkText = @json('PT PKM GROUP • DOKUMEN RESMI E-LIBRARY • ' . (Auth::user()?->full_name ?: (Auth::user()?->username ?: 'USER')) . ' • ' . date('d-m-Y'));

    function viewPDF(url, title = 'Digital Document Control Viewer') {
        currentPdfUrl = url;
        document.getElementById('pdfViewerDocTitle').textContent = title;
        document.getElementById('pdfViewerDocTitle').title = title;

        @if(Auth::check() && Auth::user()->role === 'admin')
            const adminDownloadBtn = document.getElementById('pdfAdminDownloadBtn');
            if (adminDownloadBtn) {
                adminDownloadBtn.href = url;
            }
        @endif

        // Reset state
        currentPdfScale = 1.0;
        activePageNumber = 1;
        document.getElementById('pdfZoomLabel').textContent = '100%';
        document.getElementById('pdfCurrentPageNum').textContent = '1';
        document.getElementById('pdfTotalPageNum').textContent = '...';
        
        document.getElementById('pdfLoadingSpinner').classList.remove('hidden');
        document.getElementById('pdfErrorNotice').classList.add('hidden');
        document.getElementById('pdfPagesContainer').innerHTML = '';
        
        document.getElementById('pdfModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Load document via PDF.js
        const loadingTask = pdfjsLib.getDocument({
            url: url,
            withCredentials: true,
            cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
            cMapPacked: true,
        });

        loadingTask.promise.then(function(pdfDoc) {
            currentPdfDoc = pdfDoc;
            totalPdfPages = pdfDoc.numPages;
            document.getElementById('pdfTotalPageNum').textContent = totalPdfPages;
            document.getElementById('pdfLoadingSpinner').classList.add('hidden');
            
            // Auto fit width pada render pertama
            const containerWidth = document.getElementById('pdfViewerScrollContainer').clientWidth;
            pdfDoc.getPage(1).then(function(firstPage) {
                const viewport = firstPage.getViewport({ scale: 1.0 });
                const availableWidth = Math.max(containerWidth - 60, 320);
                const optimalScale = Math.min(Math.max(availableWidth / viewport.width, 0.6), 1.6);
                currentPdfScale = optimalScale;
                document.getElementById('pdfZoomLabel').textContent = Math.round(currentPdfScale * 100) + '%';
                renderAllPdfPages();
            });
        }).catch(function(err) {
            console.error('Error loading PDF:', err);
            document.getElementById('pdfLoadingSpinner').classList.add('hidden');
            document.getElementById('pdfErrorNotice').classList.remove('hidden');
        });
    }

    async function renderAllPdfPages() {
        if (!currentPdfDoc || isRenderingPdf) return;
        isRenderingPdf = true;

        const container = document.getElementById('pdfPagesContainer');
        container.innerHTML = '';

        for (let pageNum = 1; pageNum <= totalPdfPages; pageNum++) {
            try {
                const page = await currentPdfDoc.getPage(pageNum);
                const viewport = page.getViewport({ scale: currentPdfScale });

                // Wrapper halaman (dengan bayangan & border halus)
                const pageWrapper = document.createElement('div');
                pageWrapper.className = 'relative shadow-xl rounded-[2px] bg-white border border-slate-700 overflow-hidden';
                pageWrapper.id = `pdf-page-${pageNum}`;
                pageWrapper.style.width = `${viewport.width}px`;
                pageWrapper.style.height = `${viewport.height}px`;

                // Canvas Halaman
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                canvas.className = 'block';

                pageWrapper.appendChild(canvas);

                // Watermark Transparan Anti-Bocor
                const watermarkOverlay = document.createElement('div');
                watermarkOverlay.className = 'absolute inset-0 pointer-events-none select-none flex items-center justify-center z-10 overflow-hidden';
                watermarkOverlay.style.background = 'repeating-linear-gradient(45deg, transparent, transparent 150px, rgba(0,0,0,0.035) 150px, rgba(0,0,0,0.035) 300px)';
                
                // Floating watermark text
                const watermarkTextEl = document.createElement('div');
                watermarkTextEl.className = 'text-[13px] font-black text-slate-900/10 uppercase tracking-widest text-center select-none transform -rotate-30 leading-loose';
                watermarkTextEl.innerHTML = `${pdfWatermarkText}<br>${pdfWatermarkText}<br>${pdfWatermarkText}`;
                watermarkOverlay.appendChild(watermarkTextEl);

                pageWrapper.appendChild(watermarkOverlay);
                container.appendChild(pageWrapper);

                await page.render({
                    canvasContext: ctx,
                    viewport: viewport
                }).promise;

            } catch (pageErr) {
                console.warn(`Error rendering page ${pageNum}:`, pageErr);
            }
        }

        isRenderingPdf = false;
        updatePageNavButtons();
    }

    function pdfZoom(delta) {
        if (!currentPdfDoc || isRenderingPdf) return;
        const newScale = Math.min(Math.max(currentPdfScale + delta, 0.4), 2.5);
        if (Math.abs(newScale - currentPdfScale) > 0.02) {
            currentPdfScale = newScale;
            document.getElementById('pdfZoomLabel').textContent = Math.round(currentPdfScale * 100) + '%';
            renderAllPdfPages();
        }
    }

    function pdfFitWidth() {
        if (!currentPdfDoc || isRenderingPdf) return;
        const containerWidth = document.getElementById('pdfViewerScrollContainer').clientWidth;
        currentPdfDoc.getPage(1).then(function(page) {
            const viewport = page.getViewport({ scale: 1.0 });
            const availableWidth = Math.max(containerWidth - 60, 320);
            currentPdfScale = availableWidth / viewport.width;
            document.getElementById('pdfZoomLabel').textContent = Math.round(currentPdfScale * 100) + '%';
            renderAllPdfPages();
        });
    }

    function pdfChangePage(offset) {
        const targetPage = Math.min(Math.max(activePageNumber + offset, 1), totalPdfPages);
        if (targetPage !== activePageNumber) {
            activePageNumber = targetPage;
            document.getElementById('pdfCurrentPageNum').textContent = activePageNumber;
            const targetEl = document.getElementById(`pdf-page-${activePageNumber}`);
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            updatePageNavButtons();
        }
    }

    function updatePageNavButtons() {
        const prevBtn = document.getElementById('pdfPrevPageBtn');
        const nextBtn = document.getElementById('pdfNextPageBtn');
        if (prevBtn) prevBtn.disabled = activePageNumber <= 1;
        if (nextBtn) nextBtn.disabled = activePageNumber >= totalPdfPages;
    }

    // Update nomor halaman otomatis saat pengguna scroll
    document.addEventListener('DOMContentLoaded', () => {
        const scrollContainer = document.getElementById('pdfViewerScrollContainer');
        if (scrollContainer) {
            let scrollTimeout;
            scrollContainer.addEventListener('scroll', () => {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    const pages = document.querySelectorAll('#pdfPagesContainer > div');
                    const containerTop = scrollContainer.getBoundingClientRect().top;
                    pages.forEach((pageEl, idx) => {
                        const rect = pageEl.getBoundingClientRect();
                        if (rect.top <= containerTop + 200 && rect.bottom >= containerTop + 100) {
                            activePageNumber = idx + 1;
                            document.getElementById('pdfCurrentPageNum').textContent = activePageNumber;
                            updatePageNavButtons();
                        }
                    });
                }, 100);
            });
        }
    });

    function closePDF() {
        document.getElementById('pdfModal').classList.add('hidden');
        document.getElementById('pdfPagesContainer').innerHTML = '';
        currentPdfDoc = null;
        currentPdfUrl = '';
        document.body.style.overflow = 'auto';
    }

    // Proteksi Keyboard Shortcuts (Ctrl+S, Ctrl+P, Ctrl+U)
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('pdfModal');
        const isModalOpen = modal && !modal.classList.contains('hidden');

        if (e.key === 'Escape' || e.keyCode === 27) {
            if (isModalOpen) closePDF();
        }

        if (isModalOpen) {
            // Blokir Ctrl+S (Save), Ctrl+P (Print), Ctrl+U (View Source)
            if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S' || e.key === 'p' || e.key === 'P' || e.key === 'u' || e.key === 'U')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }
    });
</script>
