# CPU Scheduling Algorithms

This project implements various CPU scheduling algorithms including:
- Round Robin (RR) with I/O handling
- First Come First Serve (FCFS)
- Shortest Job First (SJF)
- Priority Scheduling

## Features
- Process scheduling with I/O handling
- Timeline visualization
- Calculation of metrics:
  - Completion Time
  - Turnaround Time  
  - Waiting Time
  - Response Time

## Usage
Example usage for Round Robin:
```php
$processes = [
    [
        'pid' => 0,
        'arrival_time' => 0,
        'burst_time' => 6,
        'io_time' => 2,
        'io_burst_time' => 3
    ],
    [
        'pid' => 1,
        'arrival_time' => 0,
        'burst_time' => 4
    ]
];

$quantum = 2;
$result = calculateRR($processes, $quantum);
```

