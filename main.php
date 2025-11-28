<?php
// main.php - Run Producer and Consumer Concurrently

require_once 'ITStudent.php';
require_once 'SharedBuffer.php';
require_once 'producer.php';
require_once 'consumer.php';

class ProducerConsumerSystem {
    private $sharedDir = 'shared';
    
    public function __construct() {
        // Clear previous data
        $this->cleanup();
        
        // Create shared directory
        if (!file_exists($this->sharedDir)) {
            mkdir($this->sharedDir, 0777, true);
        }
        
        // Initialize buffer
        $buffer = new SharedBuffer();
        $buffer->clear();
    }
    
    private function cleanup() {
        // Clear buffer file
        if (file_exists('buffer.json')) {
            unlink('buffer.json');
        }
        if (file_exists('buffer.json.lock')) {
            unlink('buffer.json.lock');
        }
        
        // Clear shared directory
        if (file_exists($this->sharedDir)) {
            $files = glob($this->sharedDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
    
    public function runConcurrently($numStudents = 10) {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "PRODUCER-CONSUMER PROBLEM DEMONSTRATION\n";
        echo str_repeat("=", 80) . "\n";
        echo "Number of students to process: $numStudents\n";
        echo "Buffer capacity: 10\n";
        echo str_repeat("=", 80) . "\n\n";
        
        // Check if running on Windows or Unix-like system
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        if ($isWindows) {
            // Windows: Use start command
            $producerCmd = "start /B php producer.php $numStudents";
            $consumerCmd = "start /B php consumer.php $numStudents";
        } else {
            // Unix/Linux/Mac: Use & for background execution
            $producerCmd = "php producer.php $numStudents > producer.log 2>&1 &";
            $consumerCmd = "php consumer.php $numStudents > consumer.log 2>&1 &";
        }
        
        echo "Starting Producer and Consumer processes...\n\n";
        
        // Start producer process
        pclose(popen($producerCmd, 'r'));
        
        // Small delay
        sleep(1);
        
        // Start consumer process
        pclose(popen($consumerCmd, 'r'));
        
        echo "Both processes started!\n";
        echo "Producer and Consumer are running concurrently.\n";
        echo "Check producer.log and consumer.log for details.\n";
        echo "\nMonitoring buffer status (press Ctrl+C to stop):\n\n";
        
        // Monitor buffer status
        $buffer = new SharedBuffer();
        $startTime = time();
        $timeout = $numStudents * 3; // Timeout after 3 seconds per student
        
        while (true) {
            $size = $buffer->getSize();
            $elapsed = time() - $startTime;
            
            echo "\r[MONITOR] Buffer size: $size/10 | Elapsed: {$elapsed}s     ";
            
            // Check if we should stop monitoring
            if ($elapsed > $timeout) {
                echo "\n\n[MONITOR] Timeout reached. Processing complete.\n";
                break;
            }
            
            // Check if all files are processed
            $xmlFiles = glob($this->sharedDir . '/student*.xml');
            if (empty($xmlFiles) && $size == 0 && $elapsed > 5) {
                echo "\n\n[MONITOR] All files processed successfully!\n";
                break;
            }
            
            sleep(1);
        }
        
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "EXECUTION COMPLETED\n";
        echo str_repeat("=", 80) . "\n";
    }
    
    public function runSequential($numStudents = 10) {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "SEQUENTIAL PRODUCER-CONSUMER DEMONSTRATION\n";
        echo str_repeat("=", 80) . "\n\n";
        
        $producer = new Producer($this->sharedDir);
        $consumer = new Consumer($this->sharedDir);
        
        // Produce some students
        echo "Phase 1: Producing students...\n";
        for ($i = 1; $i <= min(5, $numStudents); $i++) {
            $producer->produceStudent($i);
            sleep(1);
        }
        
        echo "\nPhase 2: Consuming students...\n";
        for ($i = 1; $i <= min(5, $numStudents); $i++) {
            $consumer->consumeStudent();
            sleep(1);
        }
        
        // Produce remaining
        if ($numStudents > 5) {
            echo "\nPhase 3: Producing remaining students...\n";
            for ($i = 6; $i <= $numStudents; $i++) {
                $producer->produceStudent($i);
                sleep(1);
            }
            
            echo "\nPhase 4: Consuming remaining students...\n";
            for ($i = 6; $i <= $numStudents; $i++) {
                $consumer->consumeStudent();
                sleep(1);
            }
        }
        
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "SEQUENTIAL EXECUTION COMPLETED\n";
        echo str_repeat("=", 80) . "\n";
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $system = new ProducerConsumerSystem();
    
    $mode = isset($argv[1]) ? $argv[1] : 'concurrent';
    $numStudents = isset($argv[2]) ? (int)$argv[2] : 10;
    
    if ($mode === 'sequential') {
        $system->runSequential($numStudents);
    } else {
        $system->runConcurrently($numStudents);
    }
}