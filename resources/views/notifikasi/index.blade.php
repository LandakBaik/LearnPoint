@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'semua' }">

    <!-- Header Title & Badge -->
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Notifikasi</h1>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                Pusat Aktivitas
            </span>
        </div>
        <p class="text-sm text-gray-500 mt-1">Pantau aktivitas terbaru terkait tugas, kelas, dan pembelajaran.</p>
    </div>

    <!-- Top Card (Summary & Tabs) -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-5">
        
        <!-- Summary Info Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
            <!-- Left Info -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div class="text-sm font-bold text-gray-900">
                    {{ $notifikasis->count() }} notifikasi <span class="text-gray-400 font-normal">(0 belum dibaca)</span>
                </div>
            </div>

            <!-- Right Sync Badge -->
            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Sinkronisasi otomatis Kemendikbud Belajar.id</span>
            </div>
        </div>
    <!-- Notification List / Empty State -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 sm:p-12 text-center">
        @forelse($notifikasis as $notif)
            <!-- Future notification items can render here -->
        @empty
            <div class="max-w-md mx-auto py-4 space-y-4">
                <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mx-auto border border-blue-100 shadow-inner">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Belum Ada Notifikasi</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Saat ini belum ada notifikasi atau aktivitas baru untuk akun Anda. Semua pembaruan terkait tugas, kelas, dan pembelajaran akan tampil di sini.
                    </p>
                </div>
            </div>
        @endforelse
    </div>

</div>
@endsection
