@extends('layouts.app')
@section('title', 'My Health Overview')
@section('header_title', 'My Health Overview')

@section('content')
    <!-- Welcome Section -->
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Hello, {{ Auth::user()->name }}! 👋</h1>
            <p class="text-gray-500 mt-1">Here is your daily health and medication summary.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('schedules.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors shadow-sm">
                View Schedule
            </a>
            <a href="{{ route('medicines.create') ?? '#' }}" class="px-4 py-2 bg-[#185FA5] text-white rounded-lg hover:bg-[#145294] text-sm font-medium transition-colors shadow-sm shadow-[#185FA5]/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Medicine
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <x-stat-card 
            title="Today's Medicines" 
            value="{{ count($todaySchedules ?? []) }}" 
            subtitle="Scheduled for today"
            color="primary"
            icon="calendar-clock"
        />

        <x-stat-card 
            title="Adherence Rate" 
            value="{{ $adherenceRate ?? '85' }}%" 
            subtitle="This week"
            trend="up"
            trendValue="5%"
            color="success"
            icon="activity"
        />

        <x-stat-card 
            title="Active Medicines" 
            value="{{ $medicineCount ?? 0 }}" 
            subtitle="Currently taking"
            color="warning"
            icon="pill"
        />

        <x-stat-card 
            title="Family Members" 
            value="{{ count($familyMembers ?? []) }}" 
            subtitle="Under your care"
            color="primary"
            icon="users"
        />


    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Today's Schedule -->
        <div class="lg:col-span-2 space-y-6">
            
            @if(isset($alerts) && count($alerts) > 0)
            <!-- Alerts / Interactions -->
            <div class="bg-[#fde9e9] rounded-xl p-5 border border-[#f9c4c4] flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-[#E24B4A]/10 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-[#E24B4A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-[#b03333]">Medicine Interaction Alert</h4>
                    <p class="text-sm text-[#c93f3e] mt-1">We detected a potential interaction between {{ $alerts[0]['med1'] ?? 'Medicine A' }} and {{ $alerts[0]['med2'] ?? 'Medicine B' }}. Please consult your doctor.</p>
                </div>
            </div>
            @endif

            <div class="bg-white rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">Today's Schedule</h3>
                    <span class="bg-gray-100 text-gray-600 text-xs font-medium px-2.5 py-1 rounded-full">{{ date('D, M d') }}</span>
                </div>
                
                <div class="divide-y divide-gray-50">
                    @forelse($todaySchedules ?? [1,2,3] as $schedule)
                    <div class="p-6 hover:bg-[#F8FAFF] transition-colors flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl {{ $loop->first ? 'bg-[#1D9E75]/10 text-[#1D9E75]' : 'bg-[#185FA5]/10 text-[#185FA5]' }} flex items-center justify-center shrink-0">
                            @if($loop->first)
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-base font-semibold text-gray-900">{{ is_object($schedule) ? $schedule->medicine->name : 'Amlodipine 5mg' }}</h4>
                                <span class="text-sm font-medium text-gray-500">{{ is_object($schedule) ? $schedule->time : ($loop->first ? '08:00 AM' : '14:00 PM') }}</span>
                            </div>
                            <p class="text-sm text-gray-500">1 Tablet • After Meal</p>
                        </div>
                        <div class="shrink-0 ml-4">
                            @if($loop->first)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#e6f7f1] text-[#1D9E75] text-xs font-semibold rounded-lg">
                                    Taken
                                </span>
                            @else
                                <button class="px-4 py-2 bg-white border border-[#185FA5] text-[#185FA5] hover:bg-[#185FA5] hover:text-white rounded-lg text-sm font-medium transition-colors">
                                    Take Now
                                </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h4 class="text-gray-900 font-medium">All caught up!</h4>
                        <p class="text-sm text-gray-500 mt-1">No more medicines scheduled for today.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Sidebar (Activity & Adherence) -->
        <div class="space-y-6">
            
            <!-- Compliance Widget -->
            <div class="bg-[#185FA5] rounded-xl shadow-[0_8px_30px_rgb(24,95,165,0.2)] p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-20 h-20 bg-white opacity-10 rounded-full blur-xl"></div>
                
                <h3 class="text-lg font-semibold mb-2 relative z-10">Weekly Goal</h3>
                <p class="text-white/80 text-sm mb-6 relative z-10">Keep up the good work! You're on track this week.</p>
                
                <div class="flex items-end gap-4 relative z-10">
                    <div class="text-4xl font-bold">{{ $adherenceRate ?? '85' }}%</div>
                    <div class="text-sm text-white/80 mb-1">Adherence</div>
                </div>
                
                <div class="w-full bg-black/20 rounded-full h-2 mt-4 relative z-10">
                    <div class="bg-[#1D9E75] h-2 rounded-full" style="width: {{ $adherenceRate ?? '85' }}%"></div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Recent Activity</h3>
                </div>
                <div class="p-5">
                    <div class="relative border-l-2 border-gray-100 ml-3 space-y-6">
                        
                        @forelse($recentActivity ?? [1,2,3] as $log)
                        <div class="relative pl-6">
                            <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-white border-2 border-[#185FA5]"></div>
                            <p class="text-sm font-medium text-gray-900">{{ is_object($log) ? $log->action : 'Taken Metformin 500mg' }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ is_object($log) ? $log->created_at->diffForHumans() : '2 hours ago' }}</p>
                        </div>
                        @empty
                        <div class="relative pl-6 text-sm text-gray-500">
                            No recent activity.
                        </div>
                        @endforelse
                        
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
