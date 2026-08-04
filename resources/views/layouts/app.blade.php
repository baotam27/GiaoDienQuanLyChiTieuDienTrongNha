<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống IoT Giám Sát Tủ Điện Chính - Laravel</title>
    <!-- Nhúng Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Nhúng Thư viện vẽ biểu đồ Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Nhúng Thư viện biểu tượng Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-900 text-slate-100 flex min-h-screen">

    <!-- PHẦN 1: THANH ĐIỀU HƯỚNG SIDEBAR BÊN TRÁI -->
    <aside class="w-64 bg-slate-800 border-r border-slate-700 min-h-screen p-4 flex flex-col justify-between">
        <div>
            <!-- Tiêu đề Logo Tủ điện -->
            <div class="flex items-center gap-3 px-2 py-4 border-b border-slate-700 mb-6">
                <i data-lucide="zap" class="text-yellow-400 w-7 h-7"></i>
                <div>
                    <h1 class="font-bold text-white text-base">IoT Main Panel</h1>
                    <p class="text-[11px] text-slate-400">Tủ Điện Chính Gia Đình</p>
                </div>
            </div>

            <!-- Điều hướng các trang chức năng -->
            <nav class="space-y-1">
                <p class="text-[10px] font-bold text-slate-500 uppercase px-3 mb-2">Tổng quan</p>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-300 hover:bg-slate-700' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                </a>

                <p class="text-[10px] font-bold text-slate-500 uppercase px-3 mt-6 mb-2">Chức năng</p>
                <a href="{{ route('financial') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('financial') ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-300 hover:bg-slate-700' }}">
                    <i data-lucide="dollar-sign" class="w-4 h-4 text-emerald-400"></i> Tài Chính & Bậc Thang
                </a>
                <a href="{{ route('analytics') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('analytics') ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-300 hover:bg-slate-700' }}">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-blue-400"></i> Phân Tích Ngày / Tháng
                </a>
                <a href="{{ route('safety') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('safety') ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-300 hover:bg-slate-700' }}">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-red-400"></i> An Toàn Tủ Điện
                </a>

                <p class="text-[10px] font-bold text-slate-500 uppercase px-3 mt-6 mb-2">Giả lập</p>
                <a href="{{ route('simulator') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('simulator') ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-300 hover:bg-slate-700' }}">
                    <i data-lucide="sliders" class="w-4 h-4 text-amber-400"></i> Giả Lập & Import
                </a>
            </nav>
        </div>

        <!-- Nút bấm Đóng/Ngắt Rơ-le bảo vệ -->
        <div class="bg-slate-900/60 p-3 rounded-lg border border-slate-700">
            <form action="{{ route('toggle.relay') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-2 px-3 {{ session('device.relay_state', true) ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white text-xs font-bold rounded flex items-center justify-center gap-2">
                    <i data-lucide="power" class="w-4 h-4"></i>
                    {{ session('device.relay_state', true) ? 'NGẮT RƠ-LE TỔNG' : 'ĐÓNG RƠ-LE CẤP ĐIỆN' }}
                </button>
            </form>
        </div>
    </aside>

    <!-- PHẦN 2: KHU VỰC NỘI DUNG HIỂN THỊ -->
    <div class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="bg-slate-800 border-b border-slate-700 px-6 py-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <span class="text-sm font-semibold text-slate-300">Định danh tủ: <b class="text-amber-400">{{ session('device.device_id', 'MAIN_PANEL_01') }}</b></span>
            
            <div class="flex items-center gap-3">
                <!-- Nút bật/tắt còi báo động lặp -->
                <button id="sirenBtn" onclick="toggleSiren()" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs border border-red-500/50 flex items-center gap-1 font-bold animate-pulse">
                    <i data-lucide="volume-2" class="w-4 h-4"></i> BẬT / TẮT CÒI CẢNH BÁO
                </button>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>ONLINE</span>
                </div>
            </div>
        </header>

        <!-- POP-UP CẢNH BÁO KHI NHẬP NGÂN SÁCH VÔ LÝ HOẶC THẤP HƠN TIỀN ĐÃ DÙNG -->
        @if(session('error_popup'))
            <div id="errorPopup" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
                <div class="bg-slate-800 border-2 border-red-500 rounded-2xl p-6 max-w-md w-full text-center space-y-4 shadow-2xl animate-bounce">
                    <div class="w-12 h-12 bg-red-500/20 text-red-400 rounded-full flex items-center justify-center mx-auto">
                        <i data-lucide="alert-triangle" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">LỖI THIẾT LẬP NGÂN SÁCH</h3>
                    <p class="text-xs text-slate-300">{{ session('error_popup') }}</p>
                    <button onclick="document.getElementById('errorPopup').remove()" class="w-full py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg">Đã Hiểu & Sửa Lại</button>
                </div>
            </div>
        @endif

        <!-- Banner cảnh báo sự cố điện áp -->
        @if(session('device.voltage', 220) < 180 || session('device.voltage', 220) > 240)
            <div class="bg-yellow-500 text-slate-950 px-6 py-3 font-extrabold text-center flex justify-center items-center shadow-2xl animate-pulse">
                <span>CẢNH BÁO BẤT THƯỜNG ĐIỆN ÁP TỦ CHÍNH ({{ session('device.voltage') }}V): ĐÃ TỰ ĐỘNG NGẮT RƠ-LE BẢO VỆ!</span>
            </div>
        @endif

        <!-- Banner cảnh báo quá tải -->
        @if(session('device.power', 0) > session('device.p_max', 3000))
            <div class="bg-red-600 text-white px-6 py-3 font-extrabold text-center flex justify-center items-center shadow-2xl animate-bounce">
                <span>CẢNH BÁO QUÁ TẢI TỦ CHÍNH: CÔNG SUẤT {{ session('device.power') }}W VƯỢT NGƯỠNG {{ session('device.p_max') }}W!</span>
            </div>
        @endif

        <!-- Nội dung các View -->
        <main class="p-6 flex-1 space-y-6">
            @yield('content')
        </main>
    </div>

    <!-- PHẦN 3: JAVASCRIPT PHÁT CÒI CẢNH BÁO LẶP LIÊN TỤC -->
    <script>
        lucide.createIcons();

        let audioCtx = null;
        let sirenInterval = null;
        let isSirenOn = false;

        function playBeepSound() {
            try {
                if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                let osc = audioCtx.createOscillator();
                let gain = audioCtx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(1000, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.2);
            } catch(e){}
        }

        function toggleSiren() {
            isSirenOn = !isSirenOn;
            if (isSirenOn) {
                sirenInterval = setInterval(playBeepSound, 500); // Lặp liên tục mỗi 0.5s
            } else {
                clearInterval(sirenInterval);
            }
        }

        // Tự động kích hoạt còi khi có sự cố và nhấp chuột
        @if(session('device.power', 0) > session('device.p_max', 3000) || session('device.voltage', 220) < 180 || session('device.voltage', 220) > 240)
            window.addEventListener('click', () => {
                if (!isSirenOn) {
                    toggleSiren();
                }
            }, { once: true });
        @endif
    </script>
</body>
</html>