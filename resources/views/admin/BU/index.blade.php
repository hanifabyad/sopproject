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

    <!-- ENTERPRISE LINEAR DATA TABLE -->
    <div class="border border-sand-200/70 rounded-md bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-body-main">
                <thead class="bg-sand-50 border-b border-sand-200 text-xs font-semibold text-on-surface-variant">
                    <tr>
                        <th class="py-3 px-4 text-center w-12">#</th>
                        <th class="py-3 px-4">Divisi / Entitas</th>
                        <th class="py-3 px-4">Cakupan Unit</th>
                        <th class="py-3 px-4 text-center">Total SOP</th>
                        <th class="py-3 px-4 text-center">Dokumen Aktif</th>
                        <th class="py-3 px-4 text-center">Perlu Revisi / Menunggu</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e8e2d6]">
                    @foreach($statsDivisi as $namaDivisi => $data)
                    <tr class="border-b border-[#e8e2d6] hover:bg-canvas transition-colors">
                        <td class="py-3.5 px-4 text-center text-xs font-bold text-on-surface-variant">
                            {{ $loop->iteration }}
                        </td>
                        <td class="py-3.5 px-4">
                            <a href="{{ route('admin.BU.divisi.show', $namaDivisi) }}" class="flex items-center space-x-3 group hover:opacity-85 transition-opacity cursor-pointer">
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
                                    <span class="font-semibold text-on-surface text-xs hover:underline group-hover:text-[#1677B8] transition-colors">{{ $namaDivisi }}</span>
                                    <span class="text-[11px] text-on-surface-variant font-normal block leading-tight">Divisi Utama</span>
                                </div>
                            </a>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-xs font-semibold text-on-surface-variant bg-canvas px-2.5 py-1 rounded-md border border-sand-200/40 whitespace-nowrap">
                                {{ $data['bu_count'] }} Unit Bisnis
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 bg-canvas border border-sand-200/60 font-bold text-on-surface text-xs rounded-md inline-block">
                                {{ $data['total'] }} Dokumen
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 rounded-md text-xs font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                <span>{{ $data['active'] }} Dokumen</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1.5 text-rose-700 bg-rose-50 border border-rose-200/80 px-2.5 py-1 rounded-md text-xs font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                <span>{{ $data['inactive'] }} Dokumen</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <x-interactive-button href="{{ route('admin.BU.divisi.show', $namaDivisi) }}" text="Buka Dokumen" variant="outline" />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
