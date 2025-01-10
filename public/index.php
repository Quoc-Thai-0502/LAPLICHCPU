<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPU Scheduling Simulator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand mb-0 h1">CPU Scheduling Simulator</span>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Add Process</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="pid" class="form-label">Process ID</label>
                                <input type="number" class="form-control" id="pid" name="pid" required>
                            </div>
                            <div class="mb-3">
                                <label for="arrival_time" class="form-label">Arrival Time</label>
                                <input type="number" class="form-control" id="arrival_time" name="arrival_time" required>
                            </div>
                            <div class="mb-3">
                                <label for="burst_time" class="form-label">Burst Time</label>
                                <input type="number" class="form-control" id="burst_time" name="burst_time" required>
                            </div>
                            <div class="mb-3">
                                <label for="io_time" class="form-label">I/O Time (optional)</label>
                                <input type="number" class="form-control" id="io_time" name="io_time">
                            </div>
                            <div class="mb-3">
                                <label for="io_burst_time" class="form-label">I/O Burst Time (optional)</label>
                                <input type="number" class="form-control" id="io_burst_time" name="io_burst_time">
                            </div>
                            <button type="submit" class="btn btn-primary">Add Process</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Results</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        include 'laplich.php';
                        if ($_POST) {
                            $processes[] = $_POST;
                            $result = calculateRR($processes, 2);
                            echo '<h6>Timeline:</h6>';
                            echo '<div class="timeline-container">';
                            foreach ($result['timeline'] as $event) {
                                echo '<div class="timeline-event" style="left: ' . 
                                     ($event['start'] * 50) . 'px; width: ' . 
                                     (($event['end'] - $event['start']) * 50) . 'px;">';
                                echo "P{$event['pid']} ({$event['type']})";
                                echo '</div>';
                            }
                            echo '</div>';
                            
                            echo '<h6 class="mt-4">Process Details:</h6>';
                            echo '<table class="table">';
                            echo '<thead><tr><th>PID</th><th>Completion Time</th><th>Turnaround Time</th><th>Waiting Time</th><th>Response Time</th></tr></thead>';
                            echo '<tbody>';
                            foreach ($result['processes'] as $process) {
                                echo "<tr>";
                                echo "<td>{$process['pid']}</td>";
                                echo "<td>{$process['completion_time']}</td>";
                                echo "<td>{$process['turnaround_time']}</td>";
                                echo "<td>{$process['waiting_time']}</td>";
                                echo "<td>{$process['response_time']}</td>";
                                echo "</tr>";
                            }
                            echo '</tbody></table>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>