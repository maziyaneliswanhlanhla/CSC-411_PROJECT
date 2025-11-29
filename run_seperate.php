<?php
// run_separate.php - Helper script to test producer and consumer separately

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║  PRODUCER-CONSUMER PROBLEM - SEPARATE FILES TEST RUNNER      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "This script helps you test the producer and consumer separately.\n\n";

echo "Available options:\n";
echo "  1. Run Producer only\n";
echo "  2. Run Consumer only\n";
echo "  3. Run Both (Producer first, then Consumer)\n";
echo "  4. Run Both Concurrently (separate processes)\n";
echo "  5. Clean up (delete all data)\n";
echo "  6. Exit\n\n";

echo "Enter your choice (1-6): ";
$choice = trim(fgets(STDIN));

echo "\nHow many students? (default 10): ";
$numStudents = trim(fgets(STDIN));
$numStudents = empty($numStudents) ? 10 : (int)$numStudents;

echo "\n";

switch ($choice) {
    case '1':
        echo "Running Producer only...\n";
        system("php producer.php $numStudents");
        break;
        
    case '2':
        echo "Running Consumer only...\n";
        system("php consumer.php $numStudents");
        break;
        
    case '3':
        echo "Running Producer first...\n\n";
        system("php producer.php $numStudents");
        
        echo "\n\nPress Enter to run Consumer...";
        fgets(STDIN);
        
        echo "\nRunning Consumer...\n\n";
        system("php consumer.php $numStudents");
        break;
        
    case '4':
        echo "Running both processes concurrently...\n";
        echo "Check the terminal output carefully.\n\n";
        
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        if ($isWindows) {
            // Windows
            pclose(popen("start /B php producer.php $numStudents", 'r'));
            sleep(1);
            pclose(popen("start /B php consumer.php $numStudents", 'r'));
        } else {
            // Unix/Linux/Mac
            pclose(popen("php producer.php $numStudents > producer_output.log 2>&1 &", 'r'));
            sleep(1);
            pclose(popen("php consumer.php $numStudents > consumer_output.log 2>&1 &", 'r'));
            
            echo "Processes started in background.\n";
            echo "Check producer_output.log and consumer_output.log for details.\n";
        }
        
        echo "\nMonitoring buffer (press Ctrl+C to stop)...\n\n";
        
        require_once 'SharedBuffer.php';
        $buffer = new SharedBuffer();
        
        for ($i = 0; $i < 30; $i++) {
            $size = $buffer->getSize();
            echo "\r[MONITOR] Buffer size: $size/10 | Time: {$i}s     ";
            
            if ($size == 0 && $i > 5) {
                echo "\n\nAll students processed!\n";
                break;
            }
            
            sleep(1);
        }
        
        echo "\n\nCheck logs for complete output.\n";
        break;
        
    case '5':
        echo "Cleaning up...\n";
        
        // Delete buffer files
        if (file_exists('buffer.json')) {
            unlink('buffer.json');
            echo "✓ Deleted buffer.json\n";
        }
        if (file_exists('buffer.json.lock')) {
            unlink('buffer.json.lock');
            echo "✓ Deleted buffer.json.lock\n";
        }
        
        // Delete shared directory and contents
        if (file_exists('shared')) {
            $files = glob('shared/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir('shared');
            echo "✓ Deleted shared directory\n";
        }
        
        // Delete log files
        if (file_exists('producer_output.log')) {
            unlink('producer_output.log');
            echo "✓ Deleted producer_output.log\n";
        }
        if (file_exists('consumer_output.log')) {
            unlink('consumer_output.log');
            echo "✓ Deleted consumer_output.log\n";
        }
        
        echo "\nCleanup complete!\n";
        break;
        
    case '6':
        echo "Goodbye!\n\n";
        exit(0);
        
    default:
        echo "Invalid choice. Please run the script again.\n\n";
        break;
}

echo "\n";