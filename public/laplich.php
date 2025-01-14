<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>CPU Scheduler</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
            background-color: #f0f2f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #1a73e8;
            margin-bottom: 20px;
        }

        .process-form {
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #444;
        }

        input[type="number"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .process-list {
            margin-bottom: 20px;
        }

        .process-container {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            background-color: #f8f9fa;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #1a73e8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s;
        }

        .btn:hover {
            background-color: #1557b0;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .results {
            margin-top: 30px;
        }

        .gantt-chart {
            margin-top: 20px;
            overflow-x: auto;
        }

        .gantt-bar {
            height: 40px;
            background-color: #1a73e8;
            margin: 5px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .error {
            color: #dc3545;
            margin-top: 5px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            input[type="number"],
            select {
                font-size: 14px;
            }
        }
        .gantt-chart {
            margin-top: 30px;
            overflow-x: auto;
            padding: 20px;
        }

        .timeline {
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-left: 50px;
        }

        .process-timeline {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }

        .gantt-bar {
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1px;
            font-size: 14px;
            border-radius: 4px;
        }

        .legend {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .statistics {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .statistics p {
            margin: 5px 0;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>LẬP LỊCH CPU</h1>
        
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $processes = [];
            $numProcesses = $_POST['num_processes'] ?? 0;
            
            for ($i = 0; $i < $numProcesses; $i++) {
                if (isset($_POST["arrival_time_$i"]) && isset($_POST["burst_time_$i"])) {
                    $process = [
                        'pid' => $i + 1,
                        'arrival_time' => (int)$_POST["arrival_time_$i"],
                        'burst_time' => (int)$_POST["burst_time_$i"],
                        'io_time' => isset($_POST["io_time_$i"]) ? (int)$_POST["io_time_$i"] : 0,
                        'io_burst_time' => isset($_POST["io_burst_time_$i"]) ? (int)$_POST["io_burst_time_$i"] : 0
                    ];
                    $processes[] = $process;
                }
            }

            if (!empty($processes)) {
                $algorithm = $_POST['algorithm'] ?? '';
                
                // Thực hiện tính toán dựa trên thuật toán được chọn
                switch ($algorithm) {
                    case 'fcfs':
                        $results = calculateFCFS($processes);
                        break;
                    case 'sjf':
                        $results = calculateSJF($processes);
                        break;
                    case 'srtf':
                        $results = calculateSRTF($processes);
                        break;
                    case 'rr':
                        $quantum = $_POST['quantum'] ?? 1;
                        $results = calculateRR($processes, $quantum);
                        break;
                }
                
                // Hiển thị kết quả
                displayResults($results);
            }
        }

        // Định nghĩa hàm tính toán lịch trình FCFS, nhận tham số là mảng các tiến trình
function calculateFCFS($processes) {
    // Sắp xếp các tiến trình theo thời gian đến (arrival_time) tăng dần
    usort($processes, function($a, $b) {
        return $a['arrival_time'] - $b['arrival_time'];
    });

    // Khởi tạo thời gian hiện tại = 0
    $currentTime = 0;
    // Khởi tạo mảng kết quả để lưu thông tin xử lý của từng tiến trình
    $results = [];

    // Duyệt qua từng tiến trình theo thứ tự đã sắp xếp
    foreach ($processes as $process) {
        // Tính thời gian chờ = max(0, thời gian hiện tại - thời gian đến)
        $waitingTime = max(0, $currentTime - $process['arrival_time']);
        // Tính thời điểm bắt đầu = max(thời gian hiện tại, thời gian đến)
        $startTime = max($currentTime, $process['arrival_time']);
        // Cập nhật thời gian hiện tại = thời điểm bắt đầu
        $currentTime = $startTime;

        // Lấy các thông tin về thời gian xử lý và I/O của tiến trình
        $totalBurstTime = $process['burst_time'];        // Tổng thời gian xử lý
        $ioStartTime = $process['io_time'];             // Thời điểm bắt đầu I/O
        $ioBurstTime = $process['io_burst_time'];       // Thời gian thực hiện I/O

        // Kiểm tra nếu tiến trình có I/O
        if ($ioStartTime > 0 && $ioBurstTime > 0) {
            // Thực thi CPU đến thời điểm I/O
            $currentTime += $ioStartTime;
            // Tính thời điểm kết thúc I/O
            $ioEndTime = $currentTime + $ioBurstTime;
            // Tính thời gian xử lý CPU còn lại sau I/O
            $remainingBurst = $totalBurstTime - $ioStartTime;
            // Cập nhật thời gian hiện tại sau khi hoàn thành I/O và xử lý CPU còn lại
            $currentTime = $ioEndTime + $remainingBurst;
        } else {
            // Nếu không có I/O, cộng toàn bộ thời gian xử lý vào thời gian hiện tại
            $currentTime += $totalBurstTime;
        }

        // Thêm kết quả xử lý của tiến trình vào mảng kết quả
        $results[] = [
            'pid' => $process['pid'],                    // ID của tiến trình
            'waiting_time' => $waitingTime,              // Thời gian chờ
            'turnaround_time' => $currentTime - $process['arrival_time'],  // Thời gian lưu lại trong hệ thống
            'completion_time' => $currentTime,           // Thời gian hoàn thành
            'start_time' => $startTime,                  // Thời gian bắt đầu
            'response_time' => $startTime - $process['arrival_time'],  // Thời gian đáp ứng
            'io_start' => $ioStartTime > 0 ? $startTime + $ioStartTime : 0,  // Thời điểm bắt đầu I/O
            'io_end' => $ioStartTime > 0 ? $startTime + $ioStartTime + $ioBurstTime : 0   // Thời điểm kết thúc I/O
        ];
    }

    // Trả về mảng kết quả chứa thông tin xử lý của tất cả các tiến trình
    return $results;
}
        function calculateSJF($processes) {
            $currentTime = 0;
            $completed = [];
            $results = [];
            $readyQueue = [];
            $blockedQueue = [];
            
            // Add safety counter to prevent infinite loops
            $safetyCounter = 0;
            $maxIterations = 1000; // Adjust based on your needs
            
            while (count($completed) < count($processes)) {
                $safetyCounter++;
                if ($safetyCounter > $maxIterations) {
                    break; // Emergency exit if loop runs too long
                }
                
                // Update ready queue with arrived processes
                foreach ($processes as $process) {
                    if (!in_array($process['pid'], $completed) && 
                        $process['arrival_time'] <= $currentTime &&
                        !in_array($process['pid'], array_column($readyQueue, 'pid')) &&
                        !in_array($process['pid'], array_column($blockedQueue, 'pid'))) {
                        // Add remaining burst time calculation
                        $remainingBurst = $process['burst_time'];
                        foreach ($results as $result) {
                            if ($result['pid'] === $process['pid']) {
                                $remainingBurst -= ($currentTime - $result['start_time']);
                            }
                        }
                        if ($remainingBurst > 0) {
                            $process['burst_time'] = $remainingBurst;
                            $readyQueue[] = $process;
                        }
                    }
                }
                
                // Check and update blocked queue
                if (!empty($blockedQueue)) {
                    foreach ($blockedQueue as $key => $blocked) {
                        if ($currentTime >= $blocked['io_end']) {
                            $readyQueue[] = [
                                'pid' => $blocked['pid'],
                                'burst_time' => $blocked['remaining_burst'],
                                'arrival_time' => $blocked['io_end'],
                                'io_time' => 0, // Reset I/O time after it's done
                                'io_burst_time' => 0
                            ];
                            unset($blockedQueue[$key]);
                        }
                    }
                    $blockedQueue = array_values($blockedQueue); // Reindex array
                }
                
                // If no process in ready queue, increment time and continue
                if (empty($readyQueue)) {
                    $currentTime++;
                    continue;
                }
                
                // Sort by burst time (SJF logic)
                usort($readyQueue, function($a, $b) {
                    if ($a['burst_time'] == $b['burst_time']) {
                        return $a['arrival_time'] - $b['arrival_time']; // Secondary sort by arrival time
                    }
                    return $a['burst_time'] - $b['burst_time'];
                });
                
                $process = array_shift($readyQueue);
                $originalProcess = null;
                foreach ($processes as $p) {
                    if ($p['pid'] === $process['pid']) {
                        $originalProcess = $p;
                        break;
                    }
                }
                
                // Handle I/O
                if ($originalProcess['io_time'] > 0 && 
                    $originalProcess['io_burst_time'] > 0 && 
                    !in_array($process['pid'], $completed)) {
                    
                    $ioStart = $currentTime + $originalProcess['io_time'];
                    $ioEnd = $ioStart + $originalProcess['io_burst_time'];
                    $remainingBurst = $originalProcess['burst_time'] - $originalProcess['io_time'];
                    
                    if ($remainingBurst > 0) {
                        $blockedQueue[] = [
                            'pid' => $process['pid'],
                            'io_end' => $ioEnd,
                            'remaining_burst' => $remainingBurst
                        ];
                    }
                    
                    $currentTime += $originalProcess['io_time'];
                } else {
                    $currentTime += $process['burst_time'];
                }
                
                // Update results
                if (!isset($results[$process['pid']])) {
                    $results[$process['pid']] = [
                        'pid' => $process['pid'],
                        'waiting_time' => max(0, $currentTime - $originalProcess['arrival_time'] - $originalProcess['burst_time']),
                        'turnaround_time' => $currentTime - $originalProcess['arrival_time'],
                        'completion_time' => $currentTime,
                        'start_time' => $currentTime - $process['burst_time'],
                        'response_time' => $currentTime - $process['burst_time'] - $originalProcess['arrival_time'],
                        'io_start' => $originalProcess['io_time'] > 0 ? $currentTime - $process['burst_time'] + $originalProcess['io_time'] : 0,
                        'io_end' => $originalProcess['io_time'] > 0 ? $currentTime - $process['burst_time'] + $originalProcess['io_time'] + $originalProcess['io_burst_time'] : 0
                    ];
                }
                
                // Mark process as completed if no remaining burst time and not in blocked queue
                if (!in_array($process['pid'], $completed) && 
                    !in_array($process['pid'], array_column($blockedQueue, 'pid'))) {
                    $completed[] = $process['pid'];
                }
            }
            
            return array_values($results);
        }
        
        function calculateSRTF($processes) {
            $n = count($processes);
            $rt = array_fill(0, $n, 0);
            $ioTime = array_fill(0, $n, 0);
            $ioStart = array_fill(0, $n, 0);
            $complete = array_fill(0, $n, false);
            $currentTime = 0;
            $completed = 0;
            $firstResponse = array_fill(0, $n, -1);
            $blockedUntil = array_fill(0, $n, 0);
            
            // Khởi tạo remaining time
            for ($i = 0; $i < $n; $i++) {
                $rt[$i] = $processes[$i]['burst_time'];
                $ioTime[$i] = $processes[$i]['io_time'];
                if ($processes[$i]['io_time'] > 0) {
                    $ioStart[$i] = $processes[$i]['io_time'];
                }
            }
            
            $results = array_fill(0, $n, [
                'waiting_time' => 0,
                'turnaround_time' => 0,
                'completion_time' => 0,
                'start_time' => -1,
                'response_time' => 0,
                'io_start' => 0,
                'io_end' => 0
            ]);
            
            while ($completed != $n) {
                $shortest = -1;
                $min = PHP_INT_MAX;
                
                for ($i = 0; $i < $n; $i++) {
                    if ($processes[$i]['arrival_time'] <= $currentTime && !$complete[$i] && 
                        $rt[$i] < $min && $currentTime >= $blockedUntil[$i]) {
                        $min = $rt[$i];
                        $shortest = $i;
                    }
                }
                
                if ($shortest == -1) {
                    $currentTime++;
                    continue;
                }
                
                // Ghi nhận thời gian đáp ứng đầu tiên
                if ($firstResponse[$shortest] == -1) {
                    $firstResponse[$shortest] = $currentTime;
                    $results[$shortest]['response_time'] = $currentTime - $processes[$shortest]['arrival_time'];
                }
                
                if ($results[$shortest]['start_time'] == -1) {
                    $results[$shortest]['start_time'] = $currentTime;
                }
                
                $rt[$shortest]--;
                
                // Kiểm tra I/O
                if ($ioTime[$shortest] > 0 && $rt[$shortest] == $processes[$shortest]['burst_time'] - $ioStart[$shortest]) {
                    $results[$shortest]['io_start'] = $currentTime + 1;
                    $results[$shortest]['io_end'] = $currentTime + 1 + $processes[$shortest]['io_burst_time'];
                    $blockedUntil[$shortest] = $results[$shortest]['io_end'];
                }
                
                if ($rt[$shortest] == 0) {
                    $complete[$shortest] = true;
                    $completed++;
                    
                    $results[$shortest]['completion_time'] = $currentTime + 1;
                    $results[$shortest]['turnaround_time'] = 
                        $results[$shortest]['completion_time'] - 
                        $processes[$shortest]['arrival_time'];
                    $results[$shortest]['waiting_time'] = 
                        $results[$shortest]['turnaround_time'] - 
                        $processes[$shortest]['burst_time'];
                    $results[$shortest]['pid'] = $processes[$shortest]['pid'];
                }
                
                $currentTime++;
            }
            
            return array_values(array_filter($results, function($r) {
                return isset($r['pid']);
            }));
        }
        
        function calculateRR($processes, $quantum) {
            $n = count($processes);
            $remaining_burst_time = array_column($processes, 'burst_time');
            $completion_time = array_fill(0, $n, 0);
            $waiting_time = array_fill(0, $n, 0);
            $turnaround_time = array_fill(0, $n, 0);
            $response_time = array_fill(0, $n, -1);
            $current_time = 0;
            $ready_queue = [];
            $io_queue = [];
            $completed = 0;
            $timeline = [];
        
            // Tìm thời gian đến sớm nhất
            $current_time = min(array_column($processes, 'arrival_time'));
        
            // Thêm các process đến đầu tiên vào ready queue
            for ($i = 0; $i < $n; $i++) {
                if ($processes[$i]['arrival_time'] <= $current_time) {
                    $ready_queue[] = $i;
                }
            }
        
            while ($completed < $n) {
                if (empty($ready_queue)) {
                    $current_time++;
                    // Kiểm tra các process mới đến
                    for ($i = 0; $i < $n; $i++) {
                        if ($remaining_burst_time[$i] > 0 && 
                            $processes[$i]['arrival_time'] <= $current_time && 
                            !in_array($i, $ready_queue) &&
                            !in_array($i, array_column($io_queue, 'pid'))) {
                            $ready_queue[] = $i;
                        }
                    }
                    continue;
                }
        
                $current_process = array_shift($ready_queue);
        
                // Ghi nhận thời gian đáp ứng
                if ($response_time[$current_process] == -1) {
                    $response_time[$current_process] = $current_time - $processes[$current_process]['arrival_time'];
                }
        
                // Tính toán thời gian thực thi trong quantum này
                $execute_time = min($quantum, $remaining_burst_time[$current_process]);
                $remaining_before_io = $processes[$current_process]['io_time'] - 
                    ($processes[$current_process]['burst_time'] - $remaining_burst_time[$current_process]);
        
                // Xử lý I/O nếu cần
                if ($processes[$current_process]['io_time'] > 0 && 
                    $remaining_before_io > 0 && 
                    $remaining_before_io <= $execute_time) {
                    
                    // Thực thi đến điểm I/O
                    $execute_time = $remaining_before_io;
                    $current_time += $execute_time;
                    $remaining_burst_time[$current_process] -= $execute_time;
        
                    // Thêm vào hàng đợi I/O
                    $io_queue[] = [
                        'pid' => $current_process,
                        'end_time' => $current_time + $processes[$current_process]['io_burst_time']
                    ];
        
                    $timeline[] = [
                        'pid' => $current_process,
                        'start' => $current_time - $execute_time,
                        'end' => $current_time,
                        'type' => 'CPU'
                    ];
        
                    $timeline[] = [
                        'pid' => $current_process,
                        'start' => $current_time,
                        'end' => $current_time + $processes[$current_process]['io_burst_time'],
                        'type' => 'IO'
                    ];
                } else {
                    // Thực thi bình thường
                    $current_time += $execute_time;
                    $remaining_burst_time[$current_process] -= $execute_time;
        
                    $timeline[] = [
                        'pid' => $current_process,
                        'start' => $current_time - $execute_time,
                        'end' => $current_time,
                        'type' => 'CPU'
                    ];
                }
        
                // Kiểm tra các process hoàn thành I/O
                foreach ($io_queue as $key => $io_process) {
                    if ($current_time >= $io_process['end_time']) {
                        if ($remaining_burst_time[$io_process['pid']] > 0) {
                            $ready_queue[] = $io_process['pid'];
                        }
                        unset($io_queue[$key]);
                    }
                }
                $io_queue = array_values($io_queue);
        
                // Thêm các process mới đến vào ready queue
                for ($i = 0; $i < $n; $i++) {
                    if ($remaining_burst_time[$i] > 0 && 
                        $processes[$i]['arrival_time'] <= $current_time && 
                        !in_array($i, $ready_queue) &&
                        !in_array($i, array_column($io_queue, 'pid'))) {
                        $ready_queue[] = $i;
                    }
                }
        
                // Xử lý process chưa hoàn thành
                if ($remaining_burst_time[$current_process] > 0 && 
                    !in_array($current_process, array_column($io_queue, 'pid'))) {
                    $ready_queue[] = $current_process;
                }
        
                // Kiểm tra hoàn thành
                if ($remaining_burst_time[$current_process] == 0 && 
                    !in_array($current_process, array_column($io_queue, 'pid'))) {
                    $completion_time[$current_process] = $current_time;
                    $turnaround_time[$current_process] = $completion_time[$current_process] - 
                        $processes[$current_process]['arrival_time'];
                    $waiting_time[$current_process] = $turnaround_time[$current_process] - 
                        $processes[$current_process]['burst_time'];
                    $completed++;
                }
            }
        
            // Tạo kết quả
            $results = [];
            for ($i = 0; $i < $n; $i++) {
                $results[] = [
                    'pid' => $processes[$i]['pid'],
                    'completion_time' => $completion_time[$i],
                    'turnaround_time' => $turnaround_time[$i],
                    'waiting_time' => $waiting_time[$i],
                    'response_time' => $response_time[$i],
                    'start_time' => min(array_filter($timeline, function($t) use ($i) {
                        return $t['pid'] == $i;
                    }))['start'] ?? 0,
                    'io_start' => current(array_filter($timeline, function($t) use ($i) {
                        return $t['pid'] == $i && $t['type'] == 'IO';
                    }))['start'] ?? 0,
                    'io_end' => current(array_filter($timeline, function($t) use ($i) {
                        return $t['pid'] == $i && $t['type'] == 'IO';
                    }))['end'] ?? 0
                ];
            }
        
            return $results;
        }
        
        
        function displayResults($results) {
            echo "<div class='results'>";
            echo "<h2>Kết quả</h2>";
            
            // Hiển thị bảng kết quả
            echo "<table>";
            echo "<tr>
                    <th>Process ID</th>
                    <th>Thời gian chờ</th>
                    <th>Thời gian hoàn thành</th>
                    <th>Thời gian xử lý</th>
                    <th>Thời gian đáp ứng</th>
                    <th>Thời điểm bắt đầu I/O</th>
                    <th>Thời điểm kết thúc I/O</th>
                  </tr>";
            
            $totalWait = 0;
            $totalTurnaround = 0;
            $totalResponse = 0;
            
            foreach ($results as $result) {
                echo "<tr>";
                echo "<td>P{$result['pid']}</td>";
                echo "<td>{$result['waiting_time']}</td>";
                echo "<td>{$result['turnaround_time']}</td>";
                echo "<td>{$result['completion_time']}</td>";
                echo "<td>{$result['response_time']}</td>";
                echo "<td>" . ($result['io_start'] > 0 ? $result['io_start'] : '-') . "</td>";
                echo "<td>" . ($result['io_end'] > 0 ? $result['io_end'] : '-') . "</td>";
                echo "</tr>";
                
                $totalWait += $result['waiting_time'];
                $totalTurnaround += $result['turnaround_time'];
                $totalResponse += $result['response_time'];
            }
            
            echo "</table>";
            
            // Hiển thị thống kê trung bình
            $n = count($results);
            $avgWait = $totalWait / $n;
            $avgTurnaround = $totalTurnaround / $n;
            $avgResponse = $totalResponse / $n;
            
            echo "<div class='statistics'>";
            echo "<p>Thời gian chờ trung bình: " . number_format($avgWait, 2) . "</p>";
            echo "<p>Thời gian xoay vòng trung bình: " . number_format($avgTurnaround, 2) . "</p>";
            echo "<p>Thời gian đáp ứng trung bình: " . number_format($avgResponse, 2) . "</p>";
    echo "</div>";
    
    // Hiển thị biểu đồ Gantt
    echo "<div class='gantt-chart'>";
    echo "<h3>Biểu đồ Gantt</h3>";
    
    // Sắp xếp các process theo thời gian bắt đầu
    usort($results, function($a, $b) {
        return $a['start_time'] - $b['start_time'];
    });
    
    $scale = 50; // Pixels per time unit
    $maxTime = 0;
    foreach ($results as $result) {
        $maxTime = max($maxTime, $result['completion_time']);
    }
    
    // Tạo timeline
    echo "<div class='timeline' style='margin-bottom: 10px;'>";
    for ($t = 0; $t <= $maxTime; $t += 2) {
        echo "<span style='display: inline-block; width: " . ($scale * 2) . "px; text-align: left;'>$t</span>";
    }
    echo "</div>";
    
    // Vẽ Gantt chart cho mỗi process
    foreach ($results as $result) {
        echo "<div class='process-timeline' style='margin: 5px 0; height: 40px; position: relative;'>";
        
        // Label cho process
        echo "<div style='position: absolute; left: 0; width: 50px;'>P{$result['pid']}</div>";
        
        // Container cho các segments
        echo "<div style='margin-left: 50px;'>";
        
        $currentTime = $result['start_time'];
        
        // Nếu có I/O
        if ($result['io_start'] > 0) {
            // Segment trước I/O
            $preIoWidth = ($result['io_start'] - $result['start_time']) * $scale;
            echo "<div class='gantt-bar' style='width: {$preIoWidth}px; background-color: #1a73e8;'>";
            echo "CPU";
            echo "</div>";
            
            // I/O segment
            $ioWidth = ($result['io_end'] - $result['io_start']) * $scale;
            echo "<div class='gantt-bar' style='width: {$ioWidth}px; background-color: #fbbc04;'>";
            echo "I/O";
            echo "</div>";
            
            // Segment sau I/O
            $postIoWidth = ($result['completion_time'] - $result['io_end']) * $scale;
            echo "<div class='gantt-bar' style='width: {$postIoWidth}px; background-color: #1a73e8;'>";
            echo "CPU";
            echo "</div>";
        } else {
            // Process không có I/O
            $width = ($result['completion_time'] - $result['start_time']) * $scale;
            echo "<div class='gantt-bar' style='width: {$width}px; background-color: #1a73e8;'>";
            echo "CPU";
            echo "</div>";
        }
        
        echo "</div>"; // End segments container
        echo "</div>"; // End process timeline
    }
    
    // Thêm chú thích
    echo "<div class='legend' style='margin-top: 20px;'>";
    echo "<div style='display: inline-block; margin-right: 20px;'>";
    echo "<div style='width: 20px; height: 20px; background-color: #1a73e8; display: inline-block; margin-right: 5px;'></div>";
    echo "<span>CPU Execution</span>";
    echo "</div>";
    echo "<div style='display: inline-block;'>";
    echo "<div style='width: 20px; height: 20px; background-color: #fbbc04; display: inline-block; margin-right: 5px;'></div>";
    echo "<span>I/O Operation</span>";
    echo "</div>";
    echo "</div>";
    
    echo "</div>"; // End gantt-chart
    echo "</div>"; // End results
}

        ?>

        <form method="POST" class="process-form">
            <div class="form-group">
                <label for="num_processes">Số lượng tiến trình:</label>
                <input type="number" id="num_processes" name="num_processes" min="1" max="10" required>
            </div>

            <div class="form-group">
                <label for="algorithm">Thuật toán:</label>
                <select id="algorithm" name="algorithm" required>
                    <option value="fcfs">First Come First Serve (FCFS)</option>
                    <option value="sjf">Shortest Job First (SJF)</option>
                    <option value="srtf">Shortest Remaining Time First (SRTF)</option>
                    <option value="rr">Round Robin (RR)</option>
                    </select>
            </div>

            <div class="form-group quantum-time" style="display: none;">
                <label for="quantum">Quantum Time (for Round Robin):</label>
                <input type="number" id="quantum" name="quantum" min="1" value="1">
            </div>

            <div id="process_inputs" class="process-list"></div>

            <button type="submit" class="btn">Tính toán</button>
        </form>

        <script>
            document.getElementById('num_processes').addEventListener('change', function() {
                const numProcesses = parseInt(this.value);
                const container = document.getElementById('process_inputs');
                container.innerHTML = '';

                for (let i = 0; i < numProcesses; i++) {
                    const processDiv = document.createElement('div');
                    processDiv.className = 'process-container';
                    processDiv.innerHTML = `
                        <h3>Tiến trình ${i + 1}</h3>
                        <div class="form-group">
                            <label for="arrival_time_${i}">Thời gian vào:</label>
                            <input type="number" id="arrival_time_${i}" name="arrival_time_${i}" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="burst_time_${i}">Thời gian xử lý (CPU):</label>
                            <input type="number" id="burst_time_${i}" name="burst_time_${i}" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="io_time_${i}">Thời điểm I/O (tùy chọn):</label>
                            <input type="number" id="io_time_${i}" name="io_time_${i}" min="0">
                        </div>
                        <div class="form-group">
                            <label for="io_burst_time_${i}">Thời gian I/O (tùy chọn):</label>
                            <input type="number" id="io_burst_time_${i}" name="io_burst_time_${i}" min="0">
                        </div>
                    `;
                    container.appendChild(processDiv);
                }
            });

            document.getElementById('algorithm').addEventListener('change', function() {
                const quantumGroup = document.querySelector('.quantum-time');
                if (this.value === 'rr') {
                    quantumGroup.style.display = 'block';
                } else {
                    quantumGroup.style.display = 'none';
                }
            });

            // Form validation
            document.querySelector('form').addEventListener('submit', function(e) {
                const numProcesses = parseInt(document.getElementById('num_processes').value);
                let isValid = true;
                let errorMessage = '';

                for (let i = 0; i < numProcesses; i++) {
                    const arrivalTime = parseInt(document.getElementById(`arrival_time_${i}`).value);
                    const burstTime = parseInt(document.getElementById(`burst_time_${i}`).value);
                    const ioTime = parseInt(document.getElementById(`io_time_${i}`).value || '0');
                    const ioBurstTime = parseInt(document.getElementById(`io_burst_time_${i}`).value || '0');

                    if (ioTime > 0 && ioTime >= burstTime) {
                        isValid = false;
                        errorMessage = `Tiến trình ${i + 1}: Thời điểm I/O phải nhỏ hơn thời gian xử lý`;
                        break;
                    }

                    if ((ioTime > 0 && ioBurstTime === 0) || (ioTime === 0 && ioBurstTime > 0)) {
                        isValid = false;
                        errorMessage = `Tiến trình ${i + 1}: Vui lòng điền đầy đủ thông tin I/O`;
                        break;
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                    alert(errorMessage);
                }
            });
        </script>
    </div>
</body>
</html>