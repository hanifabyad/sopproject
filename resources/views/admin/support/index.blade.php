@extends('layouts.admin')

@section('title', 'Support - Total SOP')
@section('header_title', 'Monitoring Departemen Support e-QMS')

@section('content')
<div class="space-y-6">
    
    <!-- HEADER BAR -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white rounded-lg p-6 border border-[#cfc6ac]/60 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 mb-1">
                <span class="px-2.5 py-0.5 bg-[#ffd92f]/20 text-[#705d00] font-extrabold text-[10px] uppercase rounded tracking-wider border border-[#ffd92f]/40">Departemen Support</span>
            </div>
            <h2 class="text-xl font-extrabold text-[#1e1c14] uppercase tracking-tight">Monitoring SOP Support</h2>
            <p class="text-xs text-[#4d4633] mt-0.5">Ringkasan status dokumen operasional departemen penunjang PT PKM Group</p>
        </div>
    </div>

    <!-- ENTERPRISE LINEAR DATA TABLE -->
    <div class="border border-[#cfc6ac]/70 rounded-md bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-body-main">
                <thead class="bg-[#eee8db] border-b border-[#cfc6ac] text-[12px] font-semibold uppercase tracking-wider text-[#4d4633]">
                    <tr>
                        <th class="py-3 px-4 text-center w-12">#</th>
                        <th class="py-3 px-4">Departemen / Unit</th>
                        <th class="py-3 px-4 text-center">Total SOP</th>
                        <th class="py-3 px-4 text-center">Dokumen Aktif</th>
                        <th class="py-3 px-4 text-center">Perlu Revisi / Menunggu</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e8e2d6]">
                    @foreach($stats as $dept => $data)
                    <tr class="border-b border-[#e8e2d6] hover:bg-[#f7f6f2] transition-colors">
                        <td class="py-3.5 px-4 text-center text-xs font-bold text-[#4d4633]">
                            {{ $loop->iteration }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-md bg-[#333028] text-[#ffe16e] flex items-center justify-center font-bold shadow-sm">
                                    <span class="material-symbols-outlined text-base">folder_managed</span>
                                </div>
                                <div>
                                    <span class="font-semibold text-[#1e1c14] text-[14px] uppercase block leading-tight">{{ $dept }} DEPT.</span>
                                    <span class="text-[10px] text-[#4d4633] font-normal uppercase">Support Unit</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 bg-[#f7f6f2] border border-[#cfc6ac]/60 font-bold text-[#1e1c14] text-xs rounded-md inline-block">
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
                            <a href="{{ route('admin.support.show', $dept) }}" class="px-4 py-1.5 rounded-md bg-[#705d00] text-white hover:bg-[#544600] text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                                <span>Buka Dokumen</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
