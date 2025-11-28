<?php
// socket_client.php (Consumer with Socket)

require_once 'ITStudent.php';

class ConsumerClient {
    private $host = '127.0.0.1';
    private $port = 8888;
    private $socket;
    
    public function __construct($port = 8888) {
        $this->port = $port;
    }
    
    public function start() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "CONSUMER CLIENT STARTED\n";
        echo str_repeat("=", 60) . "\n";
        echo "Connecting to producer at {$this->host}:{$this->port}...\n\n";
        
        // Create socket
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        if ($this->socket === false) {
            die("Failed to create socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        // Connect to server
        $maxAttempts = 5;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            if (socket_connect($this->socket, $this->host, $this->port)) {
                break;
            }
            
            $attempt++;
            echo "Connection attempt $attempt/$maxAttempts failed. Retrying in 2 seconds...\n";
            sleep(2);
        }
        
        if ($attempt >= $maxAttempts) {
            die("Failed to connect to server after $maxAttempts attempts\n");
        }
        
        echo "Connected to producer!\n\n";
        
        // Receive number of students to expect
        $numStudentsStr = socket_read($this->socket, 1024);
        $numStudents = (int)trim($numStudentsStr);
        
        echo "Expecting $numStudents students\n\n";
        
        $studentsProcessed = 0;
        $buffer = '';
        
        // Receive and process students
        while ($studentsProcessed < $numStudents) {
            $data = socket_read($this->socket, 4096);
            
            if ($data === false || $data === '') {
                break;
            }
            
            $buffer .= $data;
            
            // Check if we have a complete message
            if (strpos($buffer, '|||END|||') !== false) {
                $parts = explode('|||END|||', $buffer, 2);
                $xmlData = $parts[0];
                $buffer = isset($parts[1]) ? $parts[1] : '';
                
                if (trim($xmlData) === 'DONE') {
                    break;
                }
                
                // Process the student
                try {
                    $student = ITStudent::fromXML($xmlData);
                    
                    $studentsProcessed++;
                    echo "[CONSUMER] Received student $studentsProcessed\n";
                    $student->display();
                    
                    // Send acknowledgment
                    socket_write($this->socket, "ACK\n", 4);
                    
                } catch (Exception $e) {
                    echo "[CONSUMER] Error processing student: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "\n[CONSUMER] Processed $studentsProcessed students. Closing connection.\n";
        
        socket_close($this->socket);
        
        echo str_repeat("=", 60) . "\n";
        echo "CONSUMER CLIENT COMPLETED\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// Run if called directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $port = isset($argv[1]) ? (int)$argv[1] : 8888;
    
    $client = new ConsumerClient($port);
    $client->start();
}