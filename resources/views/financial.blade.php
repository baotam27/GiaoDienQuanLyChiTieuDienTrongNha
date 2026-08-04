@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- KHUNG THIẾT LẬP NGÂN SÁCH & PHÂN BỔ CÁC NGÀY CÒN LẠI -->
    <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 space-y-4">
        <h3 class="text-base font-bold text-white border-b border-slate-700 pb-3 flex items-center gap-2">
            <i data-lucide="target" class="w-5 h-5 text-amber-400"></i> Quản Lý Hạn Mức Ngân Sách
        </h3>
        <form action="{{ route('save.budget') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs text-slate-400">Cài đặt Ngân sách Tháng {{ date('m/Y') }} (VNĐ):</label>
                <div class="flex gap-2 mt-1">
                    <input type="number" name="monthly_budget" value="{{ session('device.monthly_budget', 1000000) }}" class="bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white w-full">
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded">Lưu Ngân Sách</button>
                </div>
            </div>
        </form>

        <div class="space-y-3 pt-2 border-t border-slate-700">
            <div class="flex justify-between text-xs">
                <span class="text-slate-400">Tiến độ hạn mức tháng:</span>
                <b class="{{ $percent >= 100 ? 'text-red-400' : 'text-emerald-400' }}">{{ $percent }}%</b>
            </div>
            <div class="w-full bg-slate-900 rounded-full h-3 overflow-hidden border border-slate-700">
                <div class="h-full {{ $percent >= 100 ? 'bg-red-500' : 'bg-emerald-500' }}" style="width: {{ $percent }}%"></div>
            </div>

            <!-- PHÂN BỔ ĐỘNG CHO CÁC NGÀY CÒN LẠI -->
            <div class="p-3 bg-slate-900 rounded-lg space-y-1.5 text-xs border border-slate-700">
                <p class="text-slate-400">Đã chi tiêu (Ngày 1 - {{ $currentDay }}): <b class="text-emerald-400">{{ number_format($cost) }} VNĐ</b></p>
                <p class="text-slate-400">Hạn mức {{ $remainingDays }} ngày còn lại: <b class="text-amber-400">{{ number_format($dailyBudgetRemaining) }} VNĐ/ngày</b> (~<b>{{ round(($dailyBudgetRemaining / 2000), 1) }} kWh/ngày</b>)</p>
                <p class="text-slate-500 text-[11px] italic">* Giữ nguyên tiền đã dùng, phân bổ cho các ngày còn lại (Báo lỗi nếu < 5 kWh/ngày).</p>
            </div>
        </div>
    </div>

    <!-- BẢNG TÍNH GIÁ ĐIỆN 6 BẬC EVN -->
    <div class="lg:col-span-2 bg-slate-800 p-6 rounded-xl border border-slate-700">
        <h3 class="text-base font-bold text-white border-b border-slate-700 pb-3 flex items-center justify-between">
            <span>Bảng Chi Tiết Giá Điện Bậc Thang EVN (6 Bậc)</span>
            <span class="text-xs text-slate-400 font-normal">Tích lũy: <b class="text-amber-400">{{ number_format(session('device.energy', 0), 2) }} kWh</b></span>
        </h3>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900 text-slate-400">
                    <tr>
                        <th class="p-2.5">Bậc</th>
                        <th class="p-2.5">Mức tiêu thụ</th>
                        <th class="p-2.5">Đơn giá (đ)</th>
                        <th class="p-2.5">Sử dụng (kWh)</th>
                        <th class="p-2.5">Thành tiền (VNĐ)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @foreach($tariffDetail['stages'] as $stage)
                        <tr class="{{ $stage['used'] > 0 ? 'bg-amber-500/10 text-white font-medium' : 'text-slate-500' }}">
                            <td class="p-2.5">Bậc {{ $stage['tier'] }}</td>
                            <td class="p-2.5">{{ $stage['range'] }}</td>
                            <td class="p-2.5">{{ number_format($stage['price']) }}</td>
                            <td class="p-2.5 font-bold">{{ number_format($stage['used'], 2) }}</td>
                            <td class="p-2.5 text-emerald-400 font-bold">{{ number_format($stage['amount']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-right text-xs text-emerald-400 font-bold mt-4">Tổng tiền điện (gồm 8% VAT): {{ number_format($tariffDetail['total']) }} VNĐ</p>
    </div>
</div>
@endsection