@extends('layouts.app')

@section('content')
<div class="bg-slate-800 p-6 rounded-xl border border-amber-500/30 space-y-6">
    <div class="flex flex-wrap justify-between items-center border-b border-slate-700 pb-4 gap-4">
        <h3 class="text-base font-bold text-white">Bảng Giả Lập Tương Tác & Nạp Dữ Liệu Tủ Điện</h3>
        <div class="flex gap-2">
            <a href="{{ route('export.json') }}" class="px-3 py-1.5 bg-slate-700 text-xs font-bold rounded text-white">Xuất JSON</a>
            <a href="{{ route('export.csv') }}" class="px-3 py-1.5 bg-slate-700 text-xs font-bold rounded text-white">Xuất CSV</a>
        </div>
    </div>

    <!-- FORM NẠP FILE JSON DỮ LIỆU THIẾT BỊ -->
    <form action="{{ route('import.json') }}" method="POST" enctype="multipart/form-data" class="bg-slate-900/80 p-4 rounded-xl border border-slate-700 space-y-2">
        @csrf
        <label class="text-xs font-bold text-amber-400">Import File JSON Dữ Liệu Thiết Bị Tủ Điện:</label>
        <div class="flex gap-2">
            <input type="file" name="json_file" accept=".json" class="bg-slate-800 border border-slate-700 rounded px-3 py-1.5 text-xs text-white w-full">
            <button type="submit" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded">Nạp Data</button>
        </div>
    </form>

    <!-- KỊCH BẢN THỬ NGHIỆM TƯƠNG TÁC -->
    <div class="pt-2">
        <h4 class="text-xs font-bold text-slate-300 uppercase mb-3">Các Kịch Bản Thử Nghiệm Tương Tác:</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            <a href="{{ route('scenario', 'normal') }}" class="p-3 bg-slate-700/50 hover:bg-slate-700 border border-slate-600 rounded-lg block">
                <b class="text-xs text-emerald-400">1. Tải Thường (900W - 220V)</b>
                <p class="text-[11px] text-slate-400 mt-1">Chạy bình thường, rơ-le đóng.</p>
            </a>
            <a href="{{ route('scenario', 'overload') }}" class="p-3 bg-red-950/40 hover:bg-red-900/50 border border-red-700/60 rounded-lg block">
                <b class="text-xs text-red-400">2. Quá Tải (3600W)</b>
                <p class="text-[11px] text-slate-400 mt-1">Cảnh báo quá tải + Âm còi lặp.</p>
            </a>
            <a href="{{ route('scenario', 'low_v') }}" class="p-3 bg-yellow-950/40 hover:bg-yellow-900/50 border border-yellow-700/60 rounded-lg block">
                <b class="text-xs text-yellow-400">3. Sụt Áp Lưới (165V)</b>
                <p class="text-[11px] text-slate-400 mt-1">Tự động ngắt rơ-le tổng khẩn cấp.</p>
            </a>
            <a href="{{ route('scenario', 'high_v') }}" class="p-3 bg-amber-950/40 hover:bg-amber-900/50 border border-amber-700/60 rounded-lg block">
                <b class="text-xs text-amber-400">4. Quá Áp Lưới (255V)</b>
                <p class="text-[11px] text-slate-400 mt-1">Tự động ngắt rơ-le bảo vệ.</p>
            </a>
        </div>
    </div>
</div>
@endsection