<?php
// consumer.php - Standalone Consumer Script

require_once 'ITStudent.php';
require_once 'SharedBuffer.php';

class Consumer {
    private $buffer;
    private $sharedDir;
    
    public function __construct($sharedDir = 'shared') {
        $this->buffer = new SharedBuffer();
        $this->sharedDir = $sharedDir;
    }
    
    // Consume a single student
    public function consumeStudent() {
        // Wait if buffer is empty
        while ($this->buffer->isEmpty()) {
            echo "[CONSUMER] Buffer is empty. Waiting...\n";
            sleep(2);
        }
        
        // Get item from buffer
        $studentNumber = $this->buffer->consume();
        
        if ($studentNumber === null) {
            return false;
        }
        
        // Read XML file
        $filename = "student$studentNumber.xml";
        $filepath = $this->sharedDir . '/' . $filename;
        
        if (!file_exists($filepath)) {
            echo "[CONSUMER] Error: File $filename not found\n";
            return false;
        }
        
        $xmlContent = file_get_contents($filepath);
        
        // Unwrap XML to ITStudent object
        $student = ITStudent::fromXML($xmlContent);
        
        // Display student information
        echo "[CONSUMER] Processing $filename\n";
        $student->display();
        
        // Delete the file
        unlink($filepath);
        echo "[CONSUMER] Deleted $filename\n\n";
        
        return true;
    }
    
    // Run consumer continuously
    public function run($numStudents = 10) {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "CONSUMER STARTED\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $consumed = 0;
        
        while ($consumed < $numStudents) {
            if ($this->consumeStudent()) {
                $consumed++;
            }
            sleep(1); // Simulate processing time
        }
        
        echo "\n[CONSUMER] Finished consuming $consumed students\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// Run consumer if called directly
if (php_sapi_name() === 'cli') {
    $numStudents = isset($argv[1]) ? (int)$argv[1] : 10;
    
    echo "\n╔════════════════════════════════════════════════════════════╗\n";
    echo "║         PRODUCER-CONSUMER PROBLEM - CONSUMER ONLY          ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
    echo "\nConsuming $numStudents students...\n";
    
    $consumer = new Consumer();
    $consumer->run($numStudents);
    
    echo "\n✓ Consumption complete!\n";
    echo "  - $numStudents students processed\n";
    echo "  - All XML files deleted\n";
    echo "  - Buffer cleared\n\n";
}