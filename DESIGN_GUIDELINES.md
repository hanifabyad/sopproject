# e-QMS UI & Component Design Guidelines (PT PKM Group)

Dokumen panduan desain antarmuka (UI/UX) resmi untuk Electronic Quality Management System (e-QMS) PT Putra Kelana Makmur (PKM Group).

---

## 1. Identitas Resmi Perusahaan
- **Nama PT**: `PT PUTRA KELANA MAKMUR (PKM GROUP)`
- **Sistem**: `Electronic Quality Management System (e-QMS)`
- **Header Gradient Utama**: `bg-gradient-to-r from-[#1677B8] to-[#00b4d8]`
- **Warna Aksen / Highlight**:
  - Primary Blue: `#1677B8`
  - Sky Blue: `#00b4d8`
  - Gold Accent: `#ffe16e` / `#ffd92f`
  - Light Blue Table/Card Banner: `bg-[#f0f9ff]` dengan `border-blue-200`
  - Canvas Background: `#f8fafc`

---

## 2. Standar Tombol (Buttons) & Kontrol UI
- **Corner Radius**: **WAJIB `rounded-[2px]`** pada seluruh tombol, input field, badge, dan kartu data (menghindari tampilan rounded pill/slop).
- **Tipografi Tombol**: `font-bold text-xs capitalize tracking-wider`
- **Variasi Tombol**:
  1. **Primary Button (Biru Utama)**:
     `px-4 py-2 bg-[#1677B8] hover:bg-[#125d91] text-white font-bold text-xs rounded-[2px] shadow-xs transition-all flex items-center gap-1.5 cursor-pointer`
  2. **Secondary / Outline Button**:
     `px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-[2px] border border-slate-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer`
  3. **Header Ghost Button (Di atas Gradient Header)**:
     `px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/25 text-white rounded-[2px] font-bold text-xs capitalize tracking-wider shadow-sm transition-all flex items-center gap-2 cursor-pointer`
  4. **Header Solid White Button**:
     `px-4 py-2 bg-white text-[#1677B8] hover:bg-slate-100 rounded-[2px] font-extrabold text-xs capitalize tracking-wider shadow-md transition-all flex items-center gap-2 cursor-pointer`
- **Tombol Kembali (Back Button)**:
  - Gunakan `<x-back-button href="..." text="..." />` atau link standar dengan ikon panah kiri dan `rounded-[2px]`.

---

## 3. Standar Form & Modal Upload Berkas
- **Modal Container**:
  - Backdrop blur: `fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4`
  - Modal Box: `bg-white rounded-xl w-full max-w-lg shadow-2xl border border-slate-200 overflow-hidden`
  - Modal Header: `bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white p-5 flex items-center justify-between`
- **Pilihan Target Upload E-Library**:
  1. **Tab Dokumen Mutu & SOP Sah**:
     - `1. Dokumen Mutu` (Visi, Misi, Kebijakan, Manual Mutu)
     - `2. SOP Sah` (Prosedur Operasional Standar)
     - `3. Jobdesk` (Uraian Jabatan)
     - `4. KPI & Target` (Key Performance Indicator / Sasaran Mutu)
     - `5. IK & Formulir` (Instruksi Kerja & Format Standar)
  2. **Folder / Subfolder Khusus Departemen**:
     - Direktori Utama (Root Departemen)
     - Subfolder yang ada di departemen terkait
     - Opsi `+ Buat Folder Baru Sekaligus...`
- **Input Fields**:
  - `w-full bg-slate-50 border border-slate-300 rounded-[2px] p-2.5 font-bold text-xs text-slate-900 focus:bg-white focus:ring-1 focus:ring-[#1677B8] outline-none`

---

## 4. Standar Scrollbar (Ultra-Sleek Glassmorphic)
- **Sidebar**: Ultra-thin 4px, `rgba(255, 255, 255, 0.2)` transparan, hover `rgba(255, 255, 255, 0.5)`.
- **Content Area**: 6px slate-300 (`#cbd5e1`) dengan track `#f1f5f9`.
- **Aturan Bebas Scrollbar Ganda**: Sub-menu accordion dilarang memiliki `overflow-y-auto` internal terpisah agar sidebar hanya memiliki 1 alur scroll yang mulus.
