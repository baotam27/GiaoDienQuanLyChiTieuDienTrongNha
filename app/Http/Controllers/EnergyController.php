<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EnergyController extends Controller
{
    // Khởi tạo Session và nạp dữ liệu lịch sử mặc định (Ngày & Tháng)
    private function initSession()
    {
        // 1. Khởi tạo trạng thái thiết bị thời gian thực
        if (!session()->has('device')) {
            session([
                'device' => [
                    'device_id' => 'MAIN_PANEL_01',
                    'voltage' => 220.5,
                    'current' => 4.12,
                    'power' => 908.0,
                    'energy' => 215.4,
                    'relay_state' => true,
                    'p_max' => 3000,
                    'monthly_budget' => 1000000,
                    'target_power' => 908
                ]
            ]);
        }

        // 2. Khởi tạo Lịch sử các Ngày đã qua (Đảm bảo luôn có dữ liệu hiển thị bên Phân Tích)
        if (!session()->has('history_days') || empty(session('history_days'))) {
            session([
                'history_days' => [
                    ['date' => '01/08/2026', 'energy_kwh' => 6.8, 'cost_vnd' => 16500],
                    ['date' => '02/08/2026', 'energy_kwh' => 9.2, 'cost_vnd' => 22400],
                    ['date' => '03/08/2026', 'energy_kwh' => 5.4, 'cost_vnd' => 13100],
                    ['date' => '04/08/2026', 'energy_kwh' => 7.1, 'cost_vnd' => 17200],
                ]
            ]);
        }

        // 3. Khởi tạo Lịch sử các Tháng trước (Đảm bảo luôn có dữ liệu hiển thị bên Phân Tích)
        if (!session()->has('history_months') || empty(session('history_months'))) {
            session([
                'history_months' => [
                    ['month' => 'Tháng 05/2026', 'energy_kwh' => 185.2, 'cost_vnd' => 435000],
                    ['month' => 'Tháng 06/2026', 'energy_kwh' => 210.8, 'cost_vnd' => 512000],
                    ['month' => 'Tháng 07/2026', 'energy_kwh' => 195.4, 'cost_vnd' => 468000],
                ]
            ]);
        }

        // 4. Khởi tạo chuỗi đồ thị realtime 24h
        if (!session()->has('history')) {
            $history = [];
            $now = time();
            for ($i = 20; $i >= 0; $i--) {
                $p = rand(600, 1400);
                $history[] = [
                    'timestamp' => date('H:i:s', $now - ($i * 5)),
                    'voltage' => 220.0 + (rand(-15, 15) / 10),
                    'current' => round($p / 220, 2),
                    'power' => $p,
                    'energy' => round(210 + ($i * 0.1), 2),
                    'is_overload' => ($p > session('device.p_max', 3000))
                ];
            }
            session(['history' => $history]);
        }

        if (!session()->has('logs')) {
            session(['logs' => [
                ['time' => date('H:i:s'), 'type' => 'INFO', 'msg' => 'Hệ thống IoT Tủ Điện Chính khởi tạo thành công']
            ]]);
        }

        $this->updateSimulation();
    }

    // Cập nhật trạng thái thời gian thực
    private function updateSimulation()
    {
        $d = session('device');

        if ($d['voltage'] < 180 || $d['voltage'] > 240) {
            if ($d['relay_state']) {
                $d['relay_state'] = false;
                $logs = session('logs', []);
                $logs[] = ['time' => date('H:i:s'), 'type' => 'ERROR', 'msg' => "SỰ CỐ ĐIỆN ÁP: Đã tự động ngắt Rơ-le bảo vệ tủ chính ({$d['voltage']}V)!"];
                session(['logs' => $logs]);
            }
        }

        if (!$d['relay_state']) {
            $d['power'] = 0;
            $d['current'] = 0;
        } else {
            $noise = rand(-25, 25);
            $d['power'] = max(0, $d['target_power'] + $noise);
            $d['current'] = round($d['power'] / max(1, $d['voltage']), 2);
        }

        $d['energy'] += ($d['power'] / 1000) * (2 / 3600) * 10;
        session(['device' => $d]);

        $history = session('history', []);
        $history[] = [
            'timestamp' => date('H:i:s'),
            'voltage' => $d['voltage'],
            'current' => $d['current'],
            'power' => $d['power'],
            'energy' => round($d['energy'], 2),
            'is_overload' => ($d['power'] > $d['p_max'])
        ];

        if (count($history) > 30) array_shift($history);
        session(['history' => $history]);
    }

    // Thuật toán tính tiền điện 6 bậc EVN sinh hoạt kèm VAT 8%
    public static function calculateEVNTariffDetail($kWh)
    {
        $stages = [
            ['tier' => 1, 'range' => '0 - 50 kWh', 'price' => 1893, 'used' => 0, 'amount' => 0],
            ['tier' => 2, 'range' => '51 - 100 kWh', 'price' => 1956, 'used' => 0, 'amount' => 0],
            ['tier' => 3, 'range' => '101 - 200 kWh', 'price' => 2271, 'used' => 0, 'amount' => 0],
            ['tier' => 4, 'range' => '201 - 300 kWh', 'price' => 2860, 'used' => 0, 'amount' => 0],
            ['tier' => 5, 'range' => '301 - 400 kWh', 'price' => 3197, 'used' => 0, 'amount' => 0],
            ['tier' => 6, 'range' => '> 400 kWh', 'price' => 3302, 'used' => 0, 'amount' => 0],
        ];

        $temp = $kWh;
        if ($temp > 0) { $used = min($temp, 50); $stages[0]['used'] = $used; $stages[0]['amount'] = $used * 1893; $temp -= $used; }
        if ($temp > 0) { $used = min($temp, 50); $stages[1]['used'] = $used; $stages[1]['amount'] = $used * 1956; $temp -= $used; }
        if ($temp > 0) { $used = min($temp, 100); $stages[2]['used'] = $used; $stages[2]['amount'] = $used * 2271; $temp -= $used; }
        if ($temp > 0) { $used = min($temp, 100); $stages[3]['used'] = $used; $stages[3]['amount'] = $used * 2860; $temp -= $used; }
        if ($temp > 0) { $used = min($temp, 100); $stages[4]['used'] = $used; $stages[4]['amount'] = $used * 3197; $temp -= $used; }
        if ($temp > 0) { $stages[5]['used'] = $temp; $stages[5]['amount'] = $temp * 3302; }

        $subtotal = array_sum(array_column($stages, 'amount'));
        $vat = round($subtotal * 0.08);
        return ['stages' => $stages, 'subtotal' => $subtotal, 'vat' => $vat, 'total' => $subtotal + $vat];
    }

    public static function calculateEVNTariff($kWh)
    {
        return self::calculateEVNTariffDetail($kWh)['total'];
    }

    public function dashboard()
    {
        $this->initSession();
        $cost = self::calculateEVNTariff(session('device.energy'));
        return view('dashboard', compact('cost'));
    }

    public function financial()
    {
        $this->initSession();
        $kWh = session('device.energy', 0);
        $tariffDetail = self::calculateEVNTariffDetail($kWh);
        $cost = $tariffDetail['total'];
        $forecastCost = round($cost * 1.28);
        $budget = session('device.monthly_budget', 1000000);
        $percent = min(100, round(($cost / $budget) * 100));
        
        $daysInMonth = (int)date('t');
        $currentDay = (int)date('j');
        $remainingDays = max(1, $daysInMonth - $currentDay + 1);

        $remainingBudget = max(0, $budget - $cost);
        $dailyBudgetRemaining = round($remainingBudget / $remainingDays);

        return view('financial', compact('cost', 'forecastCost', 'percent', 'dailyBudgetRemaining', 'remainingDays', 'daysInMonth', 'currentDay', 'tariffDetail'));
    }

    // Trang Phân Tích: Lấy đầy đủ danh sách history_days và history_months từ Session
    public function analytics()
    {
        $this->initSession();
        $historyDays = session('history_days', []);
        $historyMonths = session('history_months', []);
        $totalDaysPassed = count($historyDays);
        
        $totalCostPassed = array_sum(array_column($historyDays, 'cost_vnd'));
        $totalKwhPassed = array_sum(array_column($historyDays, 'energy_kwh'));

        $avgCostPerDayReal = $totalDaysPassed > 0 ? round($totalCostPassed / $totalDaysPassed) : 0;
        $avgKwhPerDayReal = $totalDaysPassed > 0 ? round($totalKwhPassed / $totalDaysPassed, 2) : 0;

        foreach ($historyDays as &$day) {
            $day['status'] = ($day['cost_vnd'] > $avgCostPerDayReal) ? 'EXCEEDED' : 'NORMAL';
            $day['diff_vnd'] = abs($day['cost_vnd'] - $avgCostPerDayReal);
        }

        return view('analytics', compact('avgCostPerDayReal', 'avgKwhPerDayReal', 'totalDaysPassed', 'historyDays', 'historyMonths'));
    }

    public function safety()
    {
        $this->initSession();
        return view('safety');
    }

    public function simulator()
    {
        $this->initSession();
        return view('simulator');
    }

    public function saveBudget(Request $request)
    {
        $budget = floatval($request->input('monthly_budget'));
        $currentCost = self::calculateEVNTariff(session('device.energy'));

        if ($budget < $currentCost) {
            return redirect()->back()->with('error_popup', 'Hạn mức ngân sách không thể nhỏ hơn số tiền điện thực tế đã chi tiêu (' . number_format($currentCost) . ' VNĐ)!');
        }

        if ($budget < 100000) {
            return redirect()->back()->with('error_popup', 'Cảnh báo: Tổng ngân sách tháng quá thấp (< 100.000 VNĐ) không đủ duy trì điện sinh hoạt!');
        }

        $daysInMonth = (int)date('t');
        $currentDay = (int)date('j');
        $remainingDays = max(1, $daysInMonth - $currentDay + 1);
        
        $remainingBudget = max(0, $budget - $currentCost);
        $dailyBudgetRemainingVnd = round($remainingBudget / $remainingDays);
        $dailyBudgetRemainingKwh = round($dailyBudgetRemainingVnd / 2000, 1);

        if ($dailyBudgetRemainingKwh < 5.0 || $dailyBudgetRemainingVnd < 10000) {
            return redirect()->back()->with('error_popup', 'CẢNH BÁO HẠN MỨC QUÁ THẤP: Hạn mức còn lại chỉ đủ ' . $dailyBudgetRemainingKwh . ' kWh/ngày (' . number_format($dailyBudgetRemainingVnd) . ' VNĐ/ngày) cho ' . $remainingDays . ' ngày còn lại. Ngưỡng này thấp hơn mức tối thiểu 5 kWh/ngày!');
        }

        $d = session('device');
        $d['monthly_budget'] = $budget;
        session(['device' => $d]);

        return redirect()->back()->with('success', 'Đã cập nhật ngân sách mới thành công!');
    }

    public function toggleRelay()
    {
        $d = session('device');
        $d['relay_state'] = !$d['relay_state'];
        session(['device' => $d]);
        return back();
    }

    public function savePmax(Request $request)
    {
        $d = session('device');
        $d['p_max'] = floatval($request->input('p_max'));
        session(['device' => $d]);
        return redirect()->route('safety');
    }

    public function updateSimulator(Request $request)
    {
        $d = session('device');
        $d['target_power'] = floatval($request->input('target_power'));
        $d['voltage'] = floatval($request->input('voltage'));
        session(['device' => $d]);
        return redirect()->route('simulator');
    }

    public function setScenario($type)
    {
        $d = session('device');
        if ($type === 'normal') {
            $d['target_power'] = 900;
            $d['voltage'] = 220;
            $d['relay_state'] = true;
        } elseif ($type === 'overload') {
            $d['target_power'] = 3600;
            $d['voltage'] = 220;
            $d['relay_state'] = true;
        } elseif ($type === 'low_v') {
            $d['target_power'] = 1200;
            $d['voltage'] = 165;
            $d['relay_state'] = false;
        } elseif ($type === 'high_v') {
            $d['target_power'] = 1200;
            $d['voltage'] = 255;
            $d['relay_state'] = false;
        }
        session(['device' => $d]);
        return redirect()->route('simulator');
    }

    // Nạp file JSON trực tiếp để đè lại Lịch sử Ngày & Tháng
    public function importJson(Request $request)
    {
        if ($request->hasFile('json_file')) {
            $content = file_get_contents($request->file('json_file')->getRealPath());
            $data = json_decode($content, true);

            if ($data && isset($data['daily_records'])) {
                session([
                    'history_days' => $data['daily_records'],
                    'history_months' => $data['monthly_records'] ?? []
                ]);
            }
        }
        return redirect()->route('analytics')->with('success', 'Đã nạp thành công dữ liệu lịch sử từ File JSON!');
    }

    public function exportJson()
    {
        $exportData = [
            'device_info' => ['device_id' => session('device.device_id')],
            'current_status' => session('device'),
            'daily_records' => session('history_days'),
            'monthly_records' => session('history_months')
        ];
        return response()->json($exportData)->header('Content-Disposition', 'attachment; filename="IoT_Panel_Data.json"');
    }

    public function exportCsv()
    {
        $data = session('history', []);
        $csv = "Timestamp,Voltage(V),Current(A),Power(W),Energy(kWh)\n";
        foreach ($data as $row) {
            $csv .= "{$row['timestamp']},{$row['voltage']},{$row['current']},{$row['power']},{$row['energy']}\n";
        }
        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="IoT_Data.csv"']);
    }
}