@extends('layouts.app')

@section('title', 'Dashboard Utama')
@section('header', 'Ringkasan Kesehatan')
@section('subheader', 'Pantau jadwal obat, stok, dan tingkat kepatuhan Anda hari ini.')

@section('content')

    <!-- Dashboard Stat Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
        
        <!-- Total Medicines -->
        <x-stat-card 
            title="Total Obat" 
            value="12" 
            icon="pill" 
            color="primary"
            subtitle="Di kotak obat Anda" 
        />

        <!-- Schedules Today -->
        <x-stat-card 
            title="Jadwal Hari Ini" 
            value="4" 
            icon="calendar-clock" 
            color="primary"
            trend="neutral"
            trendValue="2 Tersisa"
            subtitle="Belum diminum" 
        />

        <!-- Compliance Percentage -->
        <x-stat-card 
            title="Kepatuhan" 
            value="92%" 
            icon="activity" 
            color="success"
            trend="up"
            trendValue="+5%"
            subtitle="Dari minggu lalu" 
        />

        <!-- Unread Alerts -->
        <x-stat-card 
            title="Peringatan" 
            value="3" 
            icon="bell-ring" 
            color="warning"
            subtitle="Peringatan belum dibaca" 
        />

        <!-- Expiring Medicines (EcoMed) -->
        <x-stat-card 
            title="Kedaluwarsa" 
            value="1" 
            icon="alert-triangle" 
            color="danger"
            subtitle="Dalam 30 hari ke depan" 
        />
        
    </div>

    <!-- Main Dashboard Content area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Upcoming Schedules -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-[#042C53]">Jadwal Minum Terdekat</h2>
                    <a href="{{ route('schedules.index') }}" class="text-sm font-medium text-[#185FA5] hover:underline">Lihat Semua</a>
                </div>
                
                <!-- Empty State (Placeholder) -->
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto bg-blue-50 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="check-circle" class="w-8 h-8 text-[#185FA5]"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Semua obat sudah diminum!</h3>
                    <p class="text-gray-500 mt-2">Tidak ada jadwal minum obat dalam waktu dekat.</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Alerts & EcoMed Mini -->
        <div class="space-y-6">
            
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-[#042C53] mb-4">Aksi Cepat</h2>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('medicines.create') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-xl hover:bg-blue-50 transition-colors group">
                        <i data-lucide="plus-circle" class="w-6 h-6 text-[#185FA5] mb-2 group-hover:scale-110 transition-transform"></i>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-[#185FA5]">Tambah Obat</span>
                    </a>
                    <a href="{{ route('schedules.create') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-xl hover:bg-emerald-50 transition-colors group">
                        <i data-lucide="calendar-plus" class="w-6 h-6 text-[#1D9E75] mb-2 group-hover:scale-110 transition-transform"></i>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-[#1D9E75]">Buat Jadwal</span>
                    </a>
                </div>
            </div>

            <!-- EcoMed Mini Widget -->
            <div class="bg-gradient-to-br from-[#1D9E75] to-emerald-800 rounded-xl shadow-md p-6 text-white relative overflow-hidden">
                <!-- Decorative Icon -->
                <i data-lucide="leaf" class="absolute -bottom-4 -right-4 w-32 h-32 text-white opacity-10"></i>
                
                <h2 class="text-lg font-bold mb-2 relative z-10">EcoMed (SDG 12)</h2>
                <p class="text-emerald-50 text-sm mb-4 relative z-10">Lacak obat kedaluwarsa dan kelola limbah medis rumah tangga dengan benar.</p>
                
                <a href="{{ route('ecomed.index') }}" class="inline-flex items-center text-sm font-bold bg-white text-[#1D9E75] px-4 py-2 rounded-lg hover:bg-emerald-50 transition-colors relative z-10 shadow-sm">
                    Kelola EcoMed
                    <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                </a>
            </div>

        </div>
    </div>

@endsection
