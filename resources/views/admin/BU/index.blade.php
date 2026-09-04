@extends('layouts.admin')

@section('title', 'Business Unit - Divisi Utama')
@section('header_title', 'Divisi Business Unit PT PKM Group')

@section('content')
<div class="space-y-6 bu-support-scope">
    
    <!-- HEADER BAR -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-[#1677B8] to-[#00b4d8] text-white rounded-lg p-6 border border-white/10 shadow-sm">
        <div>
            <h2 class="text-xl font-extrabold tracking-tight">Divisi Business Unit</h2>
            <p class="text-xs text-white/85 mt-0.5 font-medium">Monitoring dokumen operasional seluruh divisi unit bisnis PT PKM Group</p>
        </div>
    </div>

    <!-- ENTERPRISE DATA TABLE (TRACKING STYLE) -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-sand-200/60 space-y-4">
        <!-- HEADER BIRU MUDA (TRACKING STYLE) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#f0f9ff] border border-blue-200 p-3 rounded-[2px]">
            <div class="flex items-center space-x-2 text-xs font-bold text-[#1677B8]">
                <i class="ph ph-table text-base"></i>
                <span class="capitalize text-slate-900 font-extrabold">Daftar Divisi Utama PT PKM Group</span>
            </div>
            <span class="text-[11px] text-[#1677B8] font-bold bg-white px-2.5 py-1.5 rounded-[2px] border border-blue-200 whitespace-nowrap shadow-2xs">
                Total {{ count($statsDivisi) }} Divisi Utama
            </span>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-[2px]">
            <table class="tracking-table w-full text-left border-collapse">
                <thead class="bg-[#1677B8] border-b border-[#1258a0] text-[10.5px] font-bold uppercase tracking-wider text-white">
                    <tr>
                        <th class="py-2.5 px-2 text-center w-8">No</th>
                        <th class="py-2.5 px-3">Divisi / Entitas</th>
                        <th class="py-2.5 px-3">Cakupan Unit Bisnis</th>
                        <th class="py-2.5 px-3 text-center">Total Dokumen</th>
                        <th class="py-2.5 px-3 text-center">Dokumen Aktif</th>
                        <th class="py-2.5 px-3 text-center">Perlu Revisi / Menunggu</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs font-semibold text-slate-800">
                    @foreach($statsDivisi as $namaDivisi => $data)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-2.5 px-2 text-center font-bold text-slate-400 text-[11px] align-middle">
                            {{ $loop->iteration }}
                        </td>
                        <td class="py-2.5 px-3 align-middle">
                            <a href="{{ route('admin.BU.divisi.show', $namaDivisi) }}" class="flex items-center space-x-3 group hover:opacity-90 transition-opacity cursor-pointer">
                                <div class="flex-shrink-0">
                                    @if($namaDivisi == 'RETAIL')
                                        <i class="ph ph-gas-pump text-2xl text-[#00b4d8] group-hover:scale-110 transition-transform"></i>
                                    @elseif($namaDivisi == 'KOMERSIL')
                                        <i class="ph ph-boat text-2xl text-[#00b4d8] group-hover:scale-110 transition-transform"></i>
                                    @elseif($namaDivisi == 'SCM')
                                        <i class="ph ph-truck text-2xl text-[#00b4d8] group-hover:scale-110 transition-transform"></i>
                                    @else
                                        <i class="ph ph-bank text-2xl text-[#00b4d8] group-hover:scale-110 transition-transform"></i>
                                    @endif
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 text-xs hover:underline group-hover:text-[#1677B8] transition-colors">{{ $namaDivisi }}</span>
                                    <span class="text-[10px] text-slate-500 font-normal block leading-tight">Divisi Operasional Utama</span>
                                </div>
                            </a>
                        </td>
                        <td class="py-2.5 px-3 align-middle">
                            <span class="inline-flex px-2 py-0.5 bg-slate-100 border border-slate-200 rounded-[2px] text-[10px] font-bold text-slate-800">
                                {{ $data['bu_count'] }} Unit Bisnis
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-center align-middle font-mono font-bold text-xs text-slate-900">
                            {{ $data['total'] }} Dokumen
                        </td>
                        <td class="py-2.5 px-3 text-center align-middle">
                            <span class="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 border border-emerald-300 px-2 py-0.5 rounded-[2px] text-[10px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                <span>{{ $data['active'] }} Aktif</span>
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-center align-middle">
                            <span class="inline-flex items-center gap-1.5 text-rose-700 bg-rose-50 border border-rose-300 px-2 py-0.5 rounded-[2px] text-[10px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                <span>{{ $data['inactive'] }} Menunggu/Revisi</span>
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-right align-middle">
                            <x-interactive-button text="Buka Unit" variant="blue" icon="ph ph-arrow-right text-xs" href="{{ route('admin.BU.divisi.show', $namaDivisi) }}" />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


