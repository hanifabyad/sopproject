<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-QMS - Upload Perbaikan Dokumen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans min-h-screen">

    <div class="max-w-3xl mx-auto py-12 px-4">
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            
            <div class="mb-8 border-b border-gray-100 pb-6">
                <a href="{{ route('admin.BU.detail', $document->id) }}" class="text-sm text-blue-900 font-semibold hover:underline">← Kembali ke Detail Dokumen</a>
                <h1 class="text-3xl font-black text-gray-800 mt-3 uppercase tracking-tight">Perbaiki Dokumen SOP</h1>
                <p class="text-sm text-gray-500 mt-1">Isi kolom file di bawah ini <span class="text-red-600 font-bold">hanya pada file PDF yang ingin diubah/direvisi saja</span>. Kolom yang dikosongkan akan otomatis menggunakan file lama.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    @foreach ($errors->all() as $error)
                        <p class="text-red-600 text-xs font-semibold">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('admin.BU.update_revision', $document->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Judul Dokumen / SOP</label>
                    <input type="text" name="title" value="{{ old('title', $document->title) }}" required
                        class="w-full border border-gray-200 bg-gray-50 rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-900 focus:bg-white focus:outline-none transition-all">
                </div>

                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                    <label class="block text-sm font-bold text-gray-800 mb-1">1. File Cover (PDF)</label>
                    <p class="text-xs text-gray-400 mb-2">File saat ini: <span class="font-mono text-gray-600">{{ basename($document->file_cover) }}</span></p>
                    <input type="file" name="file_cover" accept="application/pdf"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                @if(empty($document->company_header))
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                    <label class="block text-sm font-bold text-gray-800 mb-1">2. File Lembar Pengesahan (PDF)</label>
                    <p class="text-xs text-gray-400 mb-2">File saat ini: <span class="font-mono text-gray-600">{{ basename($document->file_lp) }}</span></p>
                    <input type="file" name="file_lp" accept="application/pdf"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                @else
                <div class="p-4 bg-blue-50/20 rounded-2xl border border-dashed border-blue-200">
                    <label class="block text-sm font-bold text-blue-800 mb-1">2. Lembar Pengesahan (Auto-Generated)</label>
                    <p class="text-xs text-blue-600 font-semibold leading-relaxed">
                        Tabel Lembar Pengesahan di-generate secara otomatis oleh sistem menggunakan data peninjau aktif. Berkas LP akan diperbarui secara otomatis.
                    </p>
                </div>
                @endif

                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                    <label class="block text-sm font-bold text-gray-800 mb-1">3. File Isi SOP (PDF)</label>
                    <p class="text-xs text-gray-400 mb-2">File saat ini: <span class="font-mono text-gray-600">{{ basename($document->file_isi) }}</span></p>
                    <input type="file" name="file_isi" accept="application/pdf"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                    <label class="block text-sm font-bold text-gray-800 mb-1">4. File Lampiran (PDF) - <span class="text-gray-400 font-normal">Opsional (Maksimal 20 file)</span></label>
                    
                    @php $allAtts = $document->all_attachments; @endphp
                    @if($allAtts->count() > 0)
                        <p class="text-xs font-bold text-gray-600 mb-2">Lampiran Saat Ini (Centang file yang ingin DIHAPUS):</p>
                        <div class="space-y-2 mb-4">
                            @foreach($allAtts as $att)
                                <label class="flex items-center justify-between p-2.5 bg-white rounded-xl border border-gray-200 text-xs font-medium cursor-pointer hover:bg-red-50/50 transition-all">
                                    <div class="flex items-center gap-2 truncate pr-2">
                                        <i class="fa-solid fa-file-pdf text-red-500"></i>
                                        <span class="font-mono text-gray-700 truncate">{{ $att->original_name ?? basename($att->file_path) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-red-600 font-bold">
                                        <input type="checkbox" name="deleted_attachments[]" value="{{ $att->id }}" class="rounded text-red-600 focus:ring-red-500">
                                        <span>Hapus</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400 mb-2">File saat ini: <span class="font-mono text-gray-600">Tidak ada lampiran</span></p>
                    @endif

                    <label class="block text-xs font-bold text-gray-700 mb-1">Tambah Lampiran Baru (PDF):</label>
                    <input type="file" name="file_lampiran[]" multiple accept="application/pdf"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="pt-4">
                    <button type="submit" 
                        class="w-full bg-blue-900 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-blue-800 active:scale-95 transition-all uppercase tracking-wider text-sm">
                        🚀 Kirim File Perbaikan & Lewati yang Sudah Approve
                    </button>
                </div>

            </form>

        </div>
    </div>

</body>
</html>