@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card Điện áp tủ chính -->
    <div class="bg-slate-800 p-5 rounded-xl border {{ (session('device.voltage', 220) < 180 || session('device.voltage', 220) > 240) ? 'border-yellow-500 bg-yellow-950/40 animate-pulse' : 'border-slate-700' }}">
        <span class="text-xs text-slate-400 font-medium">Điện Áp Tủ Chính (U)</span>
        <div class="mt-2 flex items-baseline gap-1">
            <span class="text-3xl font-bold text-white">{{ number_format(session('device.voltage', 220), 1) }}</span>
            <span class="text-slate-400 text-sm">V</span>
        </div>
    </div>

    <!-- Card Dòng điện tải -->
    <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
        <span class="text-xs text-slate-400 font-medium">Dòng Điện Tải (I)</span>
        <div class="mt-2 flex items-baseline gap-1">
            <span class="text-3xl font-bold text-white">{{ number_format(session('device.current', 0), 2) }}</span>
            <span class="text-slate-400 text-sm">A</span>
        </div>
    </div>

    <!-- Card Công suất tổng -->
    <div class="bg-slate-800 p-5 rounded-xl border {{ session('device.power', 0) > session('device.p_max', 3000) ? 'border-red-500 bg-red-950/50 animate-pulse' : 'border-slate-700' }}">
        <span class="text-xs text-slate-400 font-medium">Công Suất Tổng (P)</span>
        <div class="mt-2 flex items-baseline gap-1">
            <span class="text-3xl font-bold text-white">{{ session('device.power', 0) }}</span>
            <span class="text-slate-400 text-sm">W</span>
        </div>
    </div>

    <!-- Card Tiền điện tạm tính -->
    <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
        <span class="text-xs text-slate-400 font-medium">Tiền Điện Tạm Tính</span>
        <div class="mt-2 flex items-baseline gap-1">
            <span class="text-3xl font-bold text-emerald-400">{{ number_format($cost ?? 0) }}</span>
            <span class="text-emerald-500 text-xs">VNĐ</span>
        </div>
    </div>
</div>

<!-- Đồ thị thời gian thực -->
<div class="bg-slate-800 p-5 rounded-xl border border-slate-700 mt-6">
    <h3 class="text-sm font-bold text-white mb-4">Diễn Biến Công Suất Thời Gian Thực</h3>
    <div class="h-64"><canvas id="mainChart"></canvas></div>
</div>

<script>
    const historyData = @json(session('history', []));
    new Chart(document.getElementById('mainChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: historyData.map(item => item.timestamp),
            datasets: [{ label: 'Công suất (W)', data: historyData.map(item => item.power), borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.1)', fill: true }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
@endsection