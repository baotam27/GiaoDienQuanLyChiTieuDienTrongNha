@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- BẢNG THỐNG KÊ SO VỚI TRUNG BÌNH THỰC TẾ ĐÃ TIÊU -->
    <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
        <div class="flex justify-between items-center border-b border-slate-700 pb-3 mb-4">
            <div>
                <h3 class="text-sm font-bold text-white">Thống Kê So Với Mức Tiêu Thụ Thực Tế Trung Bình</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Dựa trên dữ liệu thực tế của {{ $totalDaysPassed }} ngày đã qua</p>
            </div>
            <span class="text-xs text-slate-300">TB thực tế/ngày: <b class="text-amber-400">{{ number_format($avgCostPerDayReal) }} VNĐ</b> (~<b>{{ $avgKwhPerDayReal }} kWh</b>)</span>
        </div>

        <div class="overflow-x-auto border border-slate-700 rounded">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900 text-slate-400">
                    <tr>
                        <th class="p-3">Ngày</th>
                        <th class="p-3">Điện năng (kWh)</th>
                        <th class="p-3">Số tiền tiêu thụ (VNĐ)</th>
                        <th class="p-3">Độ chênh lệch so với TB</th>
                        <th class="p-3">Đánh giá tiêu dùng</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @foreach($historyDays as $day)
                        <tr>
                            <td class="p-3 text-white font-medium">{{ $day['date'] ?? $day['date'] }}</td>
                            <td class="p-3 text-amber-400 font-bold">{{ $day['energy_kwh'] }} kWh</td>
                            <td class="p-3 text-emerald-400 font-bold">{{ number_format($day['cost_vnd']) }} VNĐ</td>
                            <td class="p-3">
                                @if($day['status'] === 'EXCEEDED')
                                    <span class="text-red-400 font-bold">+{{ number_format($day['diff_vnd']) }} VNĐ</span>
                                @else
                                    <span class="text-emerald-400 font-bold">-{{ number_format($day['diff_vnd']) }} VNĐ</span>
                                @endif
                            </td>
                            <td class="p-3">
                                @if($day['status'] === 'EXCEEDED')
                                    <span class="px-2 py-0.5 bg-red-500/20 text-red-300 border border-red-500/30 rounded font-bold">CAO HƠN TB</span>
                                @else
                                    <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded font-bold">THẤP HƠN TB</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- LỊCH SỬ TIÊU THỤ CÁC THÁNG TRƯỚC -->
    <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
        <h3 class="text-sm font-bold text-white mb-3">Lịch Sử Chi Phí Tiêu Thụ Các Tháng Trước</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($historyMonths as $month)
                <div class="p-4 bg-slate-900 border border-slate-700 rounded-xl space-y-1">
                    <span class="text-xs text-slate-400 font-semibold">{{ $month['month'] }}</span>
                    <p class="text-xl font-bold text-amber-400">{{ $month['energy_kwh'] }} kWh</p>
                    <p class="text-xs text-emerald-400 font-bold">{{ number_format($month['cost_vnd']) }} VNĐ</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection