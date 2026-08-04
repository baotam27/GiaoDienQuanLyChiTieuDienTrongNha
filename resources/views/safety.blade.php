@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Form cài đặt Pmax -->
    <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 space-y-3">
        <h3 class="text-sm font-bold text-white">Cài Đặt Ngưỡng Quá Tải Tủ Điện (Pmax)</h3>
        <form action="{{ route('save.pmax') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs text-slate-400">Ngưỡng Pmax (W):</label>
                <input type="number" name="p_max" value="{{ session('device.p_max', 3000) }}" class="bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white w-full mt-1">
            </div>
            <button type="submit" class="w-full py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded">Lưu Ngưỡng</button>
        </form>
    </div>

    <!-- Trạng thái Rơ-le và Điện áp -->
    <div class="md:col-span-2 bg-slate-800 p-5 rounded-xl border border-slate-700">
        <h3 class="text-sm font-bold text-white mb-3">Trạng Thái Rơ-Le & Điện Áp Lưới Tủ Chính</h3>
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="p-3 bg-slate-900 rounded border border-slate-700">
                <span class="text-slate-400">Điện áp hiện tại:</span>
                <p class="text-lg font-bold text-white mt-1">{{ session('device.voltage', 220) }} V</p>
            </div>
            <div class="p-3 bg-slate-900 rounded border border-slate-700">
                <span class="text-slate-400">Trạng thái Rơ-le tổng:</span>
                <p class="text-lg font-bold {{ session('device.relay_state', true) ? 'text-emerald-400' : 'text-red-400' }} mt-1">
                    {{ session('device.relay_state', true) ? 'ĐANG CẤP ĐIỆN' : 'ĐÃ NGẮT BẢO VỆ' }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Nhật ký Audit Logs -->
<div class="bg-slate-800 p-5 rounded-xl border border-slate-700 mt-6">
    <h3 class="text-sm font-bold text-white mb-3">Nhật Ký Cảnh Báo Sự Cố Tủ Điện (Audit Logs)</h3>
    <div class="overflow-x-auto max-h-48 overflow-y-auto border border-slate-700 rounded">
        <table class="w-full text-left text-xs text-slate-300">
            <thead class="bg-slate-900 text-slate-400 sticky top-0">
                <tr><th class="p-2">Thời gian</th><th class="p-2">Loại</th><th class="p-2">Nội dung chi tiết</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @foreach(array_reverse(session('logs', [])) as $log)
                    <tr>
                        <td class="p-2 text-slate-400">{{ $log['time'] ?? '' }}</td>
                        <td class="p-2 font-bold {{ ($log['type'] ?? '') == 'ERROR' ? 'text-red-400' : 'text-blue-400' }}">{{ $log['type'] ?? 'INFO' }}</td>
                        <td class="p-2">{{ $log['msg'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection