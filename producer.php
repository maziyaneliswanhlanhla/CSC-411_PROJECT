<?php
// producer.php - Standalone Producer Script

require_once 'ITStudent.php';
require_once 'SharedBuffer.php';

class Producer {
    private $buffer;
    private $sharedDir;
    
    // Sample data for random generation
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
    
    public function __construct($sharedDir = 'shared') {
        $this->buffer = new SharedBuffer();
        $this->sharedDir = $sharedDir;
        
        // Create shared directory if it doesn't exist
        if (!file_exists($this->sharedDir)) {
            mkdir($this->sharedDir, 0777, true);
        }
    }
    
    // Generate random student
    private function generateRandomStudent() {
        $student = new ITStudent();
        
        // Random name
        $firstName = $this->firstNames[array_rand($this->firstNames)];
        $lastName = $this->lastNames[array_rand($this->lastNames)];
        $student->setStudentName($firstName . ' ' . $lastName);
        
        // Random 8-digit student ID
        $student->setStudentID(sprintf('%08d', rand(10000000, 99999999)));
        
        // Random programme
        $student->setProgramme($this->programmes[array_rand($this->programmes)]);
        
        // Random courses (4-6 courses)
        $numCourses = rand(4, 6);
        $selectedCourses = array_rand(array_flip($this->courses), $numCourses);
        $coursesWithMarks = [];
        
        foreach ($selectedCourses as $course) {
            $coursesWithMarks[$course] = rand(35, 100); // Marks between 35 and 100
        }
        
        $student->setCourses($coursesWithMarks);
        
        return $student;
    }
    
    // Produce a single student
    public function produceStudent($studentNumber) {
        // Wait if buffer is full
        while ($this->buffer->isFull()) {
            echo "[PRODUCER] Buffer is full. Waiting...\n";
            sleep(2);
        }
        
        // Generate random student
        $student = $this->generateRandomStudent();
        
        // Create XML file
        $filename = "student$studentNumber.xml";
        $filepath = $this->sharedDir . '/' . $filename;
        
        $xmlContent = $student->toXML();
        file_put_contents($filepath, $xmlContent);
        
        // Add to buffer
        if ($this->buffer->produce($studentNumber)) {
            echo "[PRODUCER] Produced $filename - " . $student->getStudentName() . "\n";
            return true;
        }
        
        return false;
    }
    
    // Run producer for specified number of students
    public function run($numStudents = 10) {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "PRODUCER STARTED\n";
        echo str_repeat("=", 60) . "\n\n";
        
        for ($i = 1; $i <= $numStudents; $i++) {
            $this->produceStudent($i);
            sleep(1); // Simulate production time
        }
        
        echo "\n[PRODUCER] Finished producing $numStudents students\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// Run producer if called directly
if (php_sapi_name() === 'cli') {
    $numStudents = isset($argv[1]) ? (int)$argv[1] : 10;
    
    echo "\n╔════════════════════════════════════════════════════════════╗\n";
    echo "║         PRODUCER-CONSUMER PROBLEM - PRODUCER ONLY          ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
    echo "\nProducing $numStudents students...\n";
    
    $producer = new Producer();
    $producer->run($numStudents);
    
    echo "\n✓ Production complete!\n";
    echo "  - $numStudents XML files created in 'shared/' directory\n";
    echo "  - $numStudents references added to buffer\n";
    echo "\nRun consumer.php to process these students.\n\n";
}