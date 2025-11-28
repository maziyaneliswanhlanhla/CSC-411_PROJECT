<?php
// socket_server.php (Producer with Socket)

require_once 'ITStudent.php';

class ProducerServer {
    private $host = '127.0.0.1';
    private $port = 8888;
    private $socket;
    
    private $firstNames = ['Thabo', 'Sipho', 'Nomsa', 'Lindiwe', 'Bongani', 'Zanele', 'Mandla', 'Precious', 'Themba', 'Nandi'];
    private $lastNames = ['Dlamini', 'Nkosi', 'Simelane', 'Mamba', 'Khumalo', 'Zwane', 'Maseko', 'Magagula', 'Shongwe', 'Fakudze'];
    private $programmes = ['BSc Computer Science', 'BSc Information Technology', 'BSc Software Engineering', 'BSc Data Science'];
    private $courses = [
        'CSC411 Integrative Programming',
        'CSC301 Data Structures',
        'CSC302 Algorithms',
        'CSC401 Database Systems',
        'CSC402 Web Development',
        'CSC403 Mobile Computing'
    ];
    
    public function __construct($port = 8888) {
        $this->port = $port;
    }
    
    private function generateRandomStudent() {
        $student = new ITStudent();
        
        $firstName = $this->firstNames[array_rand($this->firstNames)];
        $lastName = $this->lastNames[array_rand($this->lastNames)];
        $student->setStudentName($firstName . ' ' . $lastName);
        
        $student->setStudentID(sprintf('%08d', rand(10000000, 99999999)));
        $student->setProgramme($this->programmes[array_rand($this->programmes)]);
        
        $numCourses = rand(4, 6);
        $selectedCourses = array_rand(array_flip($this->courses), $numCourses);
        $coursesWithMarks = [];
        
        foreach ($selectedCourses as $course) {
            $coursesWithMarks[$course] = rand(35, 100);
        }
        
        $student->setCourses($coursesWithMarks);
        
        return $student;
    }
    
    public function start($numStudents = 10) {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "PRODUCER SERVER STARTED\n";
        echo str_repeat("=", 60) . "\n";
        echo "Listening on {$this->host}:{$this->port}\n";
        echo "Waiting for consumer connection...\n\n";
        
        // Create socket
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        if ($this->socket === false) {
            die("Failed to create socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        // Set socket options
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        
        // Bind socket
        if (!socket_bind($this->socket, $this->host, $this->port)) {
            die("Failed to bind socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        // Listen for connections
        if (!socket_listen($this->socket, 5)) {
            die("Failed to listen on socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        // Accept connection
        $clientSocket = socket_accept($this->socket);
        
        if ($clientSocket === false) {
            die("Failed to accept connection: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        echo "Consumer connected!\n\n";
        
        // Send number of students to expect
        socket_write($clientSocket, "$numStudents\n", strlen("$numStudents\n"));
        
        // Produce and send students
        for ($i = 1; $i <= $numStudents; $i++) {
            $student = $this->generateRandomStudent();
            $xmlData = $student->toXML();
            
            // Send XML data with delimiter
            $message = $xmlData . "|||END|||\n";
            socket_write($clientSocket, $message, strlen($message));
            
            echo "[PRODUCER] Sent student $i: " . $student->getStudentName() . "\n";
            
            // Wait for acknowledgment
            $ack = socket_read($clientSocket, 1024);
            if (trim($ack) === "ACK") {
                echo "[PRODUCER] Received acknowledgment for student $i\n\n";
            }
            
            sleep(1);
        }
        
        // Send completion signal
        socket_write($clientSocket, "DONE\n", 5);
        
        echo "\n[PRODUCER] All students sent. Closing connection.\n";
        
        socket_close($clientSocket);
        socket_close($this->socket);
        
        echo str_repeat("=", 60) . "\n";
        echo "PRODUCER SERVER COMPLETED\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// Run if called directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $port = isset($argv[1]) ? (int)$argv[1] : 8888;
    $numStudents = isset($argv[2]) ? (int)$argv[2] : 10;
    
    $server = new ProducerServer($port);
    $server->start($numStudents);
}