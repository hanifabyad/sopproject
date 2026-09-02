@props([
    'pdfUrl',
    'readOnly' => false,
    'initialAnnotations' => null,
    'inputName' => 'annotations',
    'formId' => 'review-form',
    'height' => '680px',
])

@php
    $annotatorId = 'pdf_annotator_' . uniqid();
    $rawInitial = is_array($initialAnnotations) ? json_encode($initialAnnotations) : ($initialAnnotations ?: '[]');
@endphp

<div id="{{ $annotatorId }}" class="pdf-annotator-container flex flex-col bg-slate-900 rounded-lg border border-slate-800 shadow-sm overflow-hidden" style="height: {{ $height }};">
    
    <!-- 🧰 TOP SLIM NAVIGATION & VIEWPORT BAR -->
    <div class="bg-slate-900 text-white px-3 py-2 flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 text-xs select-none flex-shrink-0 z-20">
        
        <!-- Left Title / Info Badge -->
        <div class="flex items-center gap-2">
            @if(!$readOnly)
                <div class="flex items-center gap-1.5 text-xs font-bold text-slate-200">
                    <i class="ph ph-palette text-sm text-[#00b4d8]"></i>
                    <span class="hidden sm:inline">Toolbar Anotasi</span>
                </div>
            @else
                <div class="flex items-center gap-2 text-white font-bold text-xs">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    <span>Anotasi & Coretan Visual Reviewer</span>
                </div>
            @endif
        </div>

        <!-- Right: Page Navigation, View Mode & Zoom -->
        <div class="flex items-center gap-2 ml-auto">
            
            <!-- View Mode Switcher (1 Halaman vs Mode Scroll) -->
            <div class="flex items-center bg-slate-800 p-0.5 rounded text-[10px] font-bold border border-slate-700">
                <button type="button" id="btn-mode-single" class="px-2 py-0.5 rounded text-slate-400 hover:text-white cursor-pointer transition-all" title="Tampilkan 1 Halaman">
                    1 Halaman
                </button>
                <button type="button" id="btn-mode-scroll" class="px-2 py-0.5 rounded bg-[#1677B8] text-white cursor-pointer transition-all" title="Mode Scroll Bertumpuk (Default)">
                    Scroll
                </button>
            </div>

            <!-- Page Navigation -->
            <div class="flex items-center gap-1 bg-slate-800 px-2 py-0.5 rounded text-[11px] border border-slate-700">
                <button type="button" id="btn-prev-page" class="hover:text-amber-400 disabled:opacity-30 cursor-pointer border-none bg-transparent text-white p-0.5" title="Halaman Sebelumnya">
                    <i class="ph ph-caret-left text-sm font-bold"></i>
                </button>
                <span class="font-mono text-slate-200 font-bold px-1 text-[11px]">
                    <span id="current-page-num" class="text-amber-300">1</span> / <span id="total-page-num">1</span>
                </span>
                <button type="button" id="btn-next-page" class="hover:text-amber-400 disabled:opacity-30 cursor-pointer border-none bg-transparent text-white p-0.5" title="Halaman Berikutnya">
                    <i class="ph ph-caret-right text-sm font-bold"></i>
                </button>
            </div>

            <!-- Zoom Controls -->
            <div class="flex items-center gap-1 bg-slate-800 px-2 py-0.5 rounded text-[11px] border border-slate-700">
                <button type="button" id="btn-zoom-out" class="hover:text-amber-400 cursor-pointer border-none bg-transparent text-white p-0.5" title="Perkecil (-)">
                    <i class="ph ph-minus text-sm"></i>
                </button>
                <span id="zoom-level-text" class="font-mono font-bold px-1 text-slate-200 text-[10.5px]">Fit</span>
                <button type="button" id="btn-zoom-in" class="hover:text-amber-400 cursor-pointer border-none bg-transparent text-white p-0.5" title="Perbesar (+)">
                    <i class="ph ph-plus text-sm"></i>
                </button>
                <button type="button" id="btn-zoom-fit" class="hover:text-amber-400 text-[10px] font-bold px-1.5 text-slate-300 hover:text-white cursor-pointer border-none bg-transparent" title="Sesuaikan Lebar (Fit)">
                    Fit
                </button>
            </div>

            @if(!$readOnly)
            <div class="hidden sm:inline-flex items-center gap-1 bg-emerald-950/80 border border-emerald-700/60 px-2 py-0.5 rounded text-[10px] font-bold text-emerald-300">
                <i class="ph ph-pencil-line"></i>
                <span id="annotation-count-badge">0 Coretan</span>
            </div>
            @endif
        </div>
    </div>

    <!-- MAIN BODY: LEFT VERTICAL TOOLBAR + RIGHT CANVAS VIEWPORT -->
    <div class="flex-1 flex flex-row overflow-hidden relative">
        
        @if(!$readOnly)
        <!-- 🎨 LEFT VERTICAL TOOLBAR -->
        <div class="w-14 bg-slate-900 border-r border-slate-800 flex flex-col items-center py-2.5 px-1 gap-1.5 select-none z-20 flex-shrink-0 overflow-y-auto custom-scrollbar">
            
            <!-- Select Tool -->
            <button type="button" data-tool="select" class="tool-btn active w-11 h-10 rounded-lg bg-slate-700 text-white flex flex-col items-center justify-center font-bold text-[9px] transition-all cursor-pointer shadow-sm hover:scale-105" title="Mode Pilih / Pindah Objek">
                <i class="ph ph-cursor text-base"></i>
                <span class="text-[8.5px] leading-none mt-0.5">Pilih</span>
            </button>

            <span class="w-6 h-[1px] bg-slate-800 my-0.5"></span>

            <!-- Circle / Lingkaran -->
            <button type="button" data-tool="circle" class="tool-btn w-11 h-10 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex flex-col items-center justify-center font-bold text-[9px] transition-all cursor-pointer hover:scale-105" title="Beri Lingkaran">
                <i class="ph ph-circle text-base text-rose-400"></i>
                <span class="text-[8.5px] leading-none mt-0.5">Lingkar</span>
            </button>

            <!-- Rectangle / Kotak -->
            <button type="button" data-tool="rect" class="tool-btn w-11 h-10 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex flex-col items-center justify-center font-bold text-[9px] transition-all cursor-pointer hover:scale-105" title="Beri Kotak">
                <i class="ph ph-square text-base text-rose-400"></i>
                <span class="text-[8.5px] leading-none mt-0.5">Kotak</span>
            </button>

            <!-- Arrow / Panah -->
            <button type="button" data-tool="arrow" class="tool-btn w-11 h-10 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex flex-col items-center justify-center font-bold text-[9px] transition-all cursor-pointer hover:scale-105" title="Tanda Panah Penunjuk">
                <i class="ph ph-arrow-up-right text-base text-amber-400"></i>
                <span class="text-[8.5px] leading-none mt-0.5">Panah</span>
            </button>

            <!-- Freehand Draw -->
            <button type="button" data-tool="draw" class="tool-btn w-11 h-10 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex flex-col items-center justify-center font-bold text-[9px] transition-all cursor-pointer hover:scale-105" title="Pena Coretan Bebas">
                <i class="ph ph-pencil-simple text-base text-blue-400"></i>
                <span class="text-[8.5px] leading-none mt-0.5">Pena</span>
            </button>

            <!-- Sticky Note / Callout Text -->
            <button type="button" data-tool="text" class="tool-btn w-11 h-10 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex flex-col items-center justify-center font-bold text-[9px] transition-all cursor-pointer hover:scale-105" title="Ketik Teks Catatan">
                <i class="ph ph-text-t text-base text-emerald-400"></i>
                <span class="text-[8.5px] leading-none mt-0.5">Teks</span>
            </button>

            <!-- Highlighter -->
            <button type="button" data-tool="highlighter" class="tool-btn w-11 h-10 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex flex-col items-center justify-center font-bold text-[9px] transition-all cursor-pointer hover:scale-105" title="Stabilo Kuning">
                <i class="ph ph-highlighter text-base text-yellow-300"></i>
                <span class="text-[8.5px] leading-none mt-0.5">Stabilo</span>
            </button>

            <span class="w-6 h-[1px] bg-slate-800 my-0.5"></span>

            <!-- Color Palette (Vertical Grid) -->
            <div class="grid grid-cols-2 gap-1.5 bg-slate-800/80 p-1.5 rounded-lg border border-slate-700/50">
                <button type="button" data-color="#dc2626" class="color-btn w-3.5 h-3.5 rounded-full bg-red-600 ring-2 ring-white cursor-pointer hover:scale-110 transition-transform" title="Merah"></button>
                <button type="button" data-color="#d97706" class="color-btn w-3.5 h-3.5 rounded-full bg-amber-500 cursor-pointer hover:scale-110 transition-transform" title="Amber / Oranye"></button>
                <button type="button" data-color="#2563eb" class="color-btn w-3.5 h-3.5 rounded-full bg-blue-600 cursor-pointer hover:scale-110 transition-transform" title="Biru"></button>
                <button type="button" data-color="#059669" class="color-btn w-3.5 h-3.5 rounded-full bg-emerald-600 cursor-pointer hover:scale-110 transition-transform" title="Hijau"></button>
            </div>

            <span class="w-6 h-[1px] bg-slate-800 my-0.5"></span>

            <!-- Delete Selected -->
            <button type="button" id="btn-delete-selected" class="w-11 h-8 rounded-lg bg-red-900/40 hover:bg-red-800 text-red-300 hover:text-white flex items-center justify-center transition-all cursor-pointer" title="Hapus Objek Terpilih">
                <i class="ph ph-trash text-sm"></i>
            </button>

            <!-- Reset / Clear Page -->
            <button type="button" id="btn-clear-page" class="w-11 h-7 rounded bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-200 text-[8.5px] font-bold transition-all cursor-pointer" title="Bersihkan Coretan Halaman Ini">
                Reset
            </button>
        </div>
        @endif

        <!-- 📜 WORKSPACE CANVAS VIEWPORT (SCROLLABLE & FIXED HEIGHT) -->
        <div id="annotator-viewport-wrapper" class="flex-1 bg-slate-900/95 overflow-y-auto overflow-x-auto p-4 flex flex-col items-center justify-start relative select-none">
            
            <div id="pdf-pages-container" class="space-y-6 flex flex-col items-center max-w-full my-auto">
                <!-- Rendered Pages will appear here -->
            </div>

            <!-- Loading Indicator -->
            <div id="annotator-loading-overlay" class="absolute inset-0 bg-slate-950/85 backdrop-blur-sm flex flex-col items-center justify-center text-white z-30">
                <div class="w-10 h-10 border-4 border-slate-700 border-t-[#1677B8] rounded-full animate-spin mb-3"></div>
                <p class="text-xs font-bold tracking-wide">Memuat Dokumen & Kanvas Anotasi...</p>
                <p class="text-[10px] text-slate-400 mt-1">Kualitas berkas asli 100% dipertahankan (tanpa kompresi)</p>
            </div>
        </div>

    </div>

    <!-- Hidden Input for Form Submission -->
    <input type="hidden" name="{{ $inputName }}" id="annotator-hidden-payload" form="{{ $formId }}" value="{{ $rawInitial }}">
</div>

<!-- Load PDF.js and Fabric.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>

<script>
(function() {
    const root = document.getElementById('{{ $annotatorId }}');
    if (!root) return;

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const pdfUrl = @json($pdfUrl);
    const readOnly = @json($readOnly);
    let allAnnotations = @json(json_decode($rawInitial, true)) || {};
    if (typeof allAnnotations === 'string') {
        try { allAnnotations = JSON.parse(allAnnotations); } catch(e) { allAnnotations = {}; }
    }

    const pagesContainer = root.querySelector('#pdf-pages-container');
    const viewportWrapper = root.querySelector('#annotator-viewport-wrapper');
    const loadingOverlay = root.querySelector('#annotator-loading-overlay');
    const currentPageText = root.querySelector('#current-page-num');
    const totalPageText = root.querySelector('#total-page-num');
    const zoomText = root.querySelector('#zoom-level-text');
    const countBadge = root.querySelector('#annotation-count-badge');
    const hiddenPayload = root.querySelector('#annotator-hidden-payload');

    let pdfDoc = null;
    let currentScale = 1.15;
    let currentPage = 1;
    let viewMode = 'scroll'; // 'scroll' (Default) or 'single'
    let activeTool = 'select';
    let activeColor = '#dc2626';
    let fabricCanvases = {}; // pageNum -> fabric.Canvas instance

    // Helper to calculate Fit scale dynamically
    async function calculateFitScale() {
        if (!pdfDoc) return 1.15;
        try {
            const page = await pdfDoc.getPage(1);
            const unscaledViewport = page.getViewport({ scale: 1.0 });
            const viewportWidth = viewportWrapper.clientWidth || 700;
            const availableWidth = Math.max(300, viewportWidth - 48);
            return Math.max(0.6, Math.min(2.0, availableWidth / unscaledViewport.width));
        } catch(e) {
            return 1.15;
        }
    }

    // 1. TOOL SWITCHING LOGIC
    const toolBtns = root.querySelectorAll('.tool-btn');
    toolBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            toolBtns.forEach(b => {
                b.classList.remove('active', 'bg-slate-700', 'text-white');
                b.classList.add('bg-slate-800', 'text-slate-300');
            });
            btn.classList.add('active', 'bg-slate-700', 'text-white');
            btn.classList.remove('bg-slate-800', 'text-slate-300');
            activeTool = btn.getAttribute('data-tool');
            updateDrawingMode();
        });
    });

    const colorBtns = root.querySelectorAll('.color-btn');
    colorBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            colorBtns.forEach(b => b.classList.remove('ring-2', 'ring-white'));
            btn.classList.add('ring-2', 'ring-white');
            activeColor = btn.getAttribute('data-color');
            updateDrawingMode();
        });
    });

    function updateDrawingMode() {
        Object.keys(fabricCanvases).forEach(pageNum => {
            const canvas = fabricCanvases[pageNum];
            if (!canvas) return;

            canvas.isDrawingMode = (activeTool === 'draw');
            if (canvas.isDrawingMode) {
                canvas.freeDrawingBrush.color = activeColor;
                canvas.freeDrawingBrush.width = 3.5;
            }

            canvas.selection = (activeTool === 'select');
            canvas.forEachObject(obj => {
                obj.selectable = (activeTool === 'select' && !readOnly);
                obj.evented = (activeTool === 'select' && !readOnly);
            });
            canvas.defaultCursor = (activeTool === 'select') ? 'default' : 'crosshair';
        });
    }

    // 2. VIEW MODE TOGGLE (1 HALAMAN VS SCROLL)
    const btnModeSingle = root.querySelector('#btn-mode-single');
    const btnModeScroll = root.querySelector('#btn-mode-scroll');

    btnModeSingle?.addEventListener('click', () => {
        if (viewMode === 'single') return;
        viewMode = 'single';
        btnModeSingle.classList.add('bg-[#1677B8]', 'text-white');
        btnModeSingle.classList.remove('text-slate-400');
        btnModeScroll.classList.remove('bg-[#1677B8]', 'text-white');
        btnModeScroll.classList.add('text-slate-400');
        renderView();
    });

    btnModeScroll?.addEventListener('click', () => {
        if (viewMode === 'scroll') return;
        viewMode = 'scroll';
        btnModeScroll.classList.add('bg-[#1677B8]', 'text-white');
        btnModeScroll.classList.remove('text-slate-400');
        btnModeSingle.classList.remove('bg-[#1677B8]', 'text-white');
        btnModeSingle.classList.add('text-slate-400');
        renderView();
    });

    // 3. PAGE NAVIGATION
    const btnPrev = root.querySelector('#btn-prev-page');
    const btnNext = root.querySelector('#btn-next-page');

    btnPrev?.addEventListener('click', () => {
        if (currentPage <= 1) return;
        saveCurrentPageData();
        currentPage--;
        if (viewMode === 'single') {
            renderView();
        } else {
            scrollToPage(currentPage);
        }
        updateNavButtons();
    });

    btnNext?.addEventListener('click', () => {
        if (!pdfDoc || currentPage >= pdfDoc.numPages) return;
        saveCurrentPageData();
        currentPage++;
        if (viewMode === 'single') {
            renderView();
        } else {
            scrollToPage(currentPage);
        }
        updateNavButtons();
    });

    function scrollToPage(pageNum) {
        const targetPage = root.querySelector(`.pdf-page-wrapper[data-page="${pageNum}"]`);
        if (targetPage) {
            targetPage.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function updateNavButtons() {
        if (!pdfDoc) return;
        currentPageText.textContent = currentPage;
        totalPageText.textContent = pdfDoc.numPages;
        if (btnPrev) btnPrev.disabled = (currentPage <= 1);
        if (btnNext) btnNext.disabled = (currentPage >= pdfDoc.numPages);
    }

    // 4. DELETE & CLEAR TOOLS
    const btnDelete = root.querySelector('#btn-delete-selected');
    if (btnDelete) {
        btnDelete.addEventListener('click', () => {
            const canvas = fabricCanvases[currentPage];
            if (!canvas) return;
            const activeObjs = canvas.getActiveObjects();
            if (activeObjs && activeObjs.length > 0) {
                activeObjs.forEach(obj => canvas.remove(obj));
                canvas.discardActiveObject();
                canvas.renderAll();
                saveCurrentPageData();
                syncPayload();
            }
        });
    }

    const btnClearPage = root.querySelector('#btn-clear-page');
    if (btnClearPage) {
        btnClearPage.addEventListener('click', () => {
            if (!confirm('Hapus semua coretan di halaman ini?')) return;
            const canvas = fabricCanvases[currentPage];
            if (canvas) {
                canvas.clear();
                canvas.renderAll();
                delete allAnnotations[currentPage];
                syncPayload();
            }
        });
    }

    // 5. ZOOM CONTROLS
    root.querySelector('#btn-zoom-in')?.addEventListener('click', () => {
        if (currentScale >= 2.5) return;
        currentScale += 0.2;
        saveCurrentPageData();
        renderView();
    });

    root.querySelector('#btn-zoom-out')?.addEventListener('click', () => {
        if (currentScale <= 0.6) return;
        currentScale -= 0.2;
        saveCurrentPageData();
        renderView();
    });

    root.querySelector('#btn-zoom-fit')?.addEventListener('click', async () => {
        currentScale = await calculateFitScale();
        saveCurrentPageData();
        renderView();
    });

    // 6. LOAD PDF DOCUMENT (DEFAULT SCROLL & FIT)
    async function loadPdf() {
        try {
            loadingOverlay.classList.remove('hidden');
            const loadingTask = pdfjsLib.getDocument({ url: pdfUrl });
            pdfDoc = await loadingTask.promise;
            totalPageText.textContent = pdfDoc.numPages;
            
            // Calculate default Fit scale dynamically
            currentScale = await calculateFitScale();
            
            updateNavButtons();
            await renderView();
            loadingOverlay.classList.add('hidden');
        } catch (err) {
            console.error('Error loading PDF in annotator:', err);
            loadingOverlay.innerHTML = `
                <div class="text-center p-4">
                    <i class="ph ph-warning-circle text-4xl text-rose-500 mb-2"></i>
                    <p class="text-xs font-bold text-white">Gagal memuat pratinjau PDF.</p>
                    <p class="text-[10px] text-slate-400 mt-1">${err.message || 'Berkas tidak dapat diakses'}</p>
                </div>
            `;
        }
    }

    // 7. RENDER VIEW (SINGLE PAGE OR SCROLL)
    async function renderView() {
        if (!pdfDoc) return;
        zoomText.textContent = Math.round(currentScale * 100) + '%';
        pagesContainer.innerHTML = '';
        fabricCanvases = {};

        if (viewMode === 'single') {
            // Hanya render 1 halaman aktif
            await renderSinglePage(currentPage, allAnnotations[currentPage]);
        } else {
            // Render semua halaman bertumpuk (Default Mode)
            for (let p = 1; p <= pdfDoc.numPages; p++) {
                await renderSinglePage(p, allAnnotations[p]);
            }
        }

        updateDrawingMode();
        updateNavButtons();
        syncPayload();
    }

    async function renderSinglePage(pageNum, savedPageJson) {
        const page = await pdfDoc.getPage(pageNum);
        const viewport = page.getViewport({ scale: currentScale });

        // Container per halaman
        const pageWrapper = document.createElement('div');
        pageWrapper.className = 'pdf-page-wrapper relative bg-white shadow-2xl rounded border border-slate-400/80 mx-auto';
        pageWrapper.style.width = `${viewport.width}px`;
        pageWrapper.style.height = `${viewport.height}px`;
        pageWrapper.dataset.page = pageNum;

        // PDF Render Canvas (HiDPI / Sharp Rendering)
        const pdfCanvas = document.createElement('canvas');
        const dpr = window.devicePixelRatio || 1;
        pdfCanvas.width = viewport.width * dpr;
        pdfCanvas.height = viewport.height * dpr;
        pdfCanvas.style.width = `${viewport.width}px`;
        pdfCanvas.style.height = `${viewport.height}px`;
        pdfCanvas.className = 'absolute top-0 left-0 z-0';
        
        const ctx = pdfCanvas.getContext('2d');
        ctx.scale(dpr, dpr);
        pageWrapper.appendChild(pdfCanvas);

        // Fabric Annotation Canvas
        const annotCanvasEl = document.createElement('canvas');
        annotCanvasEl.width = viewport.width;
        annotCanvasEl.height = viewport.height;
        annotCanvasEl.className = 'absolute top-0 left-0 z-10';
        pageWrapper.appendChild(annotCanvasEl);

        pagesContainer.appendChild(pageWrapper);

        // Render PDF page sharply
        await page.render({ canvasContext: ctx, viewport: viewport }).promise;

        // Init Fabric Canvas with setDimensions and synchronized setZoom
        const fabricCanvas = new fabric.Canvas(annotCanvasEl, {
            width: viewport.width,
            height: viewport.height,
            selection: (activeTool === 'select' && !readOnly),
            isDrawingMode: (activeTool === 'draw' && !readOnly),
        });
        fabricCanvas.setDimensions({ width: viewport.width, height: viewport.height });
        fabricCanvas.setZoom(currentScale);

        fabricCanvases[pageNum] = fabricCanvas;

        // Normalize and load saved annotations in Base PDF Coordinates (scale = 1.0)
        const baseData = normalizeToBase(savedPageJson);

        if (baseData && baseData.objects && baseData.objects.length > 0) {
            fabricCanvas.loadFromJSON(baseData, () => {
                fabricCanvas.forEachObject(obj => {
                    obj.selectable = (activeTool === 'select' && !readOnly);
                    obj.evented = (activeTool === 'select' && !readOnly);
                });
                fabricCanvas.renderAll();
                syncPayload();
            });
        }

        // Attach Interactive Drawing Handlers
        if (!readOnly) {
            attachDrawingHandlers(fabricCanvas, pageNum);
        }
    }

    // Convert any legacy or version 2 format into clean Base PDF points (scale = 1.0)
    function normalizeToBase(jsonData) {
        if (!jsonData) return { objects: [] };
        if (jsonData.version === 2 && jsonData.data) {
            const s = jsonData.scale || 1.15;
            const objs = JSON.parse(JSON.stringify(jsonData.data.objects || []));
            objs.forEach(o => {
                o.left = (o.left || 0) / s;
                o.top = (o.top || 0) / s;
                o.scaleX = (o.scaleX || 1) / s;
                o.scaleY = (o.scaleY || 1) / s;
                if (o.strokeWidth) o.strokeWidth = o.strokeWidth / s;
                if (o.fontSize) o.fontSize = o.fontSize / s;
            });
            return { objects: objs, _isBase: true };
        } else if (jsonData.objects) {
            if (jsonData._isBase) {
                return jsonData;
            }
            const s = 1.15;
            const objs = JSON.parse(JSON.stringify(jsonData.objects || []));
            objs.forEach(o => {
                o.left = (o.left || 0) / s;
                o.top = (o.top || 0) / s;
                o.scaleX = (o.scaleX || 1) / s;
                o.scaleY = (o.scaleY || 1) / s;
                if (o.strokeWidth) o.strokeWidth = o.strokeWidth / s;
                if (o.fontSize) o.fontSize = o.fontSize / s;
            });
            return { objects: objs, _isBase: true };
        }
        return jsonData;
    }

    // 8. SHAPE DRAWING LOGIC (MOUSEDOWN / MOVE / UP)
    function attachDrawingHandlers(canvas, pageNum) {
        let isDown = false;
        let origX = 0, origY = 0;
        let activeShape = null;

        canvas.on('mouse:down', function(o) {
            if (activeTool === 'select' || activeTool === 'draw') return;

            isDown = true;
            const pointer = canvas.getPointer(o.e);
            origX = pointer.x;
            origY = pointer.y;

            if (activeTool === 'circle') {
                activeShape = new fabric.Ellipse({
                    left: origX,
                    top: origY,
                    originX: 'left',
                    originY: 'top',
                    rx: 0,
                    ry: 0,
                    stroke: activeColor,
                    strokeWidth: 3,
                    fill: 'rgba(220, 38, 38, 0.08)',
                    selectable: false,
                });
                canvas.add(activeShape);
            } else if (activeTool === 'rect') {
                activeShape = new fabric.Rect({
                    left: origX,
                    top: origY,
                    originX: 'left',
                    originY: 'top',
                    width: 0,
                    height: 0,
                    stroke: activeColor,
                    strokeWidth: 3,
                    fill: 'rgba(220, 38, 38, 0.08)',
                    selectable: false,
                });
                canvas.add(activeShape);
            } else if (activeTool === 'highlighter') {
                activeShape = new fabric.Rect({
                    left: origX,
                    top: origY,
                    originX: 'left',
                    originY: 'top',
                    width: 0,
                    height: 18,
                    fill: 'rgba(250, 204, 21, 0.45)',
                    selectable: false,
                });
                canvas.add(activeShape);
            } else if (activeTool === 'arrow') {
                const points = [origX, origY, origX, origY];
                activeShape = new fabric.Line(points, {
                    stroke: activeColor,
                    strokeWidth: 3,
                    originX: 'center',
                    originY: 'center',
                    selectable: false,
                });
                canvas.add(activeShape);
            } else if (activeTool === 'text') {
                const textInput = prompt('Ketikkan catatan perbaikan / instruksi revisi:');
                if (textInput && textInput.trim()) {
                    const stickyBox = new fabric.Textbox(textInput.trim(), {
                        left: origX,
                        top: origY,
                        width: 200,
                        fontSize: 12,
                        fontWeight: 'bold',
                        fontFamily: 'Arial',
                        fill: '#1e293b',
                        backgroundColor: '#fef08a',
                        borderColor: '#ca8a04',
                        padding: 6,
                        cornerColor: '#ca8a04',
                        cornerSize: 6,
                        shadow: 'rgba(0,0,0,0.15) 2px 2px 4px',
                    });
                    canvas.add(stickyBox);
                    canvas.setActiveObject(stickyBox);
                    canvas.renderAll();
                    saveCurrentPageData();
                    syncPayload();
                }
                isDown = false;
            }
        });

        canvas.on('mouse:move', function(o) {
            if (!isDown || !activeShape) return;
            const pointer = canvas.getPointer(o.e);

            if (activeTool === 'circle') {
                const rx = Math.abs(origX - pointer.x) / 2;
                const ry = Math.abs(origY - pointer.y) / 2;
                activeShape.set({
                    left: Math.min(origX, pointer.x),
                    top: Math.min(origY, pointer.y),
                    rx: rx,
                    ry: ry
                });
            } else if (activeTool === 'rect') {
                activeShape.set({
                    left: Math.min(origX, pointer.x),
                    top: Math.min(origY, pointer.y),
                    width: Math.abs(origX - pointer.x),
                    height: Math.abs(origY - pointer.y)
                });
            } else if (activeTool === 'highlighter') {
                activeShape.set({
                    left: Math.min(origX, pointer.x),
                    top: Math.min(origY, pointer.y),
                    width: Math.abs(origX - pointer.x),
                    height: Math.max(14, Math.abs(origY - pointer.y))
                });
            } else if (activeTool === 'arrow') {
                activeShape.set({ x2: pointer.x, y2: pointer.y });
            }

            canvas.renderAll();
        });

        canvas.on('mouse:up', function() {
            if (isDown && activeShape) {
                if (activeTool === 'arrow') {
                    const x1 = activeShape.x1, y1 = activeShape.y1;
                    const x2 = activeShape.x2, y2 = activeShape.y2;
                    const dx = x2 - x1, dy = y2 - y1;
                    const angle = Math.atan2(dy, dx) * 180 / Math.PI;

                    const triangle = new fabric.Triangle({
                        left: x2,
                        top: y2,
                        originX: 'center',
                        originY: 'center',
                        pointType: 'arrow_start',
                        angle: angle + 90,
                        width: 10,
                        height: 10,
                        fill: activeColor,
                    });

                    const group = new fabric.Group([activeShape, triangle], {
                        selectable: true,
                    });
                    canvas.remove(activeShape);
                    canvas.add(group);
                } else {
                    activeShape.set({ selectable: true });
                }

                activeShape = null;
                isDown = false;
                canvas.renderAll();
                saveCurrentPageData();
                syncPayload();
            }
        });

        canvas.on('object:modified', () => { saveCurrentPageData(); syncPayload(); });
        canvas.on('object:added', () => { saveCurrentPageData(); syncPayload(); });
        canvas.on('object:removed', () => { saveCurrentPageData(); syncPayload(); });
    }

    // 9. PERSISTENCE & PAYLOAD SYNC (INVARIANT BASE PDF POINT COORDINATES)
    function saveCurrentPageData() {
        Object.keys(fabricCanvases).forEach(p => {
            const canvas = fabricCanvases[p];
            if (canvas && canvas.getObjects().length > 0) {
                const baseJson = canvas.toJSON();
                baseJson._isBase = true;
                allAnnotations[p] = baseJson;
            } else if (canvas && canvas.getObjects().length === 0) {
                delete allAnnotations[p];
            }
        });
    }

    function syncPayload() {
        saveCurrentPageData();
        const jsonStr = JSON.stringify(allAnnotations);
        if (hiddenPayload) {
            hiddenPayload.value = jsonStr;
        }

        // Also ensure form element contains synchronized hidden input
        const targetForm = document.getElementById('{{ $formId }}') || document.querySelector('form#review-form');
        if (targetForm) {
            let inputInForm = targetForm.querySelector('input[name="{{ $inputName }}"]');
            if (!inputInForm) {
                inputInForm = document.createElement('input');
                inputInForm.type = 'hidden';
                inputInForm.name = '{{ $inputName }}';
                targetForm.appendChild(inputInForm);
            }
            inputInForm.value = jsonStr;
        }

        // Count total shapes across all pages
        let totalCount = 0;
        Object.keys(allAnnotations).forEach(p => {
            const pageItem = allAnnotations[p];
            if (pageItem) {
                if (pageItem.objects) {
                    totalCount += pageItem.objects.length;
                } else if (pageItem.data && pageItem.data.objects) {
                    totalCount += pageItem.data.objects.length;
                }
            }
        });

        if (countBadge) {
            countBadge.textContent = `${totalCount} Coretan`;
        }
    }

    window.syncAnnotatorPayload = syncPayload;

    // Sync on form submit
    const targetForm = document.getElementById('{{ $formId }}');
    if (targetForm) {
        targetForm.addEventListener('submit', () => {
            syncPayload();
        });
    }

    // Scroll Observer for scroll mode
    viewportWrapper.addEventListener('scroll', () => {
        if (viewMode !== 'scroll') return;
        const wrappers = root.querySelectorAll('.pdf-page-wrapper');
        const containerTop = viewportWrapper.scrollTop;
        wrappers.forEach(w => {
            const top = w.offsetTop - 50;
            const bottom = top + w.offsetHeight;
            if (containerTop >= top && containerTop < bottom) {
                currentPage = parseInt(w.dataset.page) || currentPage;
                updateNavButtons();
            }
        });
    });

    // Initial load
    loadPdf();
})();
</script>
