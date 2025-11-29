<?php
// ITStudent.php

class ITStudent {
    private $studentName;
    private $studentID;
    private $programme;
    private $courses; // Array of course name => mark
    
    public function __construct($studentName = "", $studentID = "", $programme = "", $courses = []) {
        $this->studentName = $studentName;
        $this->studentID = $studentID;
        $this->programme = $programme;
        $this->courses = $courses;
    }
    
    // Getters
    public function getStudentName() {
        return $this->studentName;
    }
    
    public function getStudentID() {
        return $this->studentID;
    }
    
    public function getProgramme() {
        return $this->programme;
    }
    
    public function getCourses() {
        return $this->courses;
    }
    
    // Setters
    public function setStudentName($name) {
        $this->studentName = $name;
    }
    
    public function setStudentID($id) {
        $this->studentID = $id;
    }
    
    public function setProgramme($programme) {
        $this->programme = $programme;
    }
    
    public function setCourses($courses) {
        $this->courses = $courses;
    }
    
    // Calculate average mark
    public function calculateAverage() {
        if (empty($this->courses)) {
            return 0;
        }
        
        $total = array_sum($this->courses);
        return $total / count($this->courses);
    }
    
    // Determine pass/fail status
    public function getPassFailStatus() {
        $average = $this->calculateAverage();
        return $average >= 50 ? "PASS" : "FAIL";
    }
    
    // Convert to XML
    public function toXML() {
        $xml = new SimpleXMLElement('<student></student>');
        $xml->addChild('name', htmlspecialchars($this->studentName));
        $xml->addChild('studentID', $this->studentID);
        $xml->addChild('programme', htmlspecialchars($this->programme));
        
        $coursesNode = $xml->addChild('courses');
        foreach ($this->courses as $courseName => $mark) {
            $courseNode = $coursesNode->addChild('course');
            $courseNode->addChild('name', htmlspecialchars($courseName));
            $courseNode->addChild('mark', $mark);
        }
        
        return $xml->asXML();
    }
    
    // Create from XML
    public static function fromXML($xmlString) {
        $xml = simplexml_load_string($xmlString);
        
        $student = new ITStudent();
        $student->setStudentName((string)$xml->name);
        $student->setStudentID((string)$xml->studentID);
        $student->setProgramme((string)$xml->programme);
        
        $courses = [];
        if (isset($xml->courses->course)) {
            foreach ($xml->courses->course as $course) {
                $courseName = (string)$course->name;
                $mark = (int)$course->mark;
                $courses[$courseName] = $mark;
            }
        }
        $student->setCourses($courses);
        
        return $student;
    }
    
    // Display student information
    public function display() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "STUDENT INFORMATION\n";
        echo str_repeat("=", 60) . "\n";
        echo "Name: " . $this->studentName . "\n";
        echo "Student ID: " . $this->studentID . "\n";
        echo "Programme: " . $this->programme . "\n";
        echo "\nCourses and Marks:\n";
        echo str_repeat("-", 60) . "\n";
        
        foreach ($this->courses as $courseName => $mark) {
            echo sprintf("  %-40s %3d%%\n", $courseName, $mark);
        }
        
        echo str_repeat("-", 60) . "\n";
        echo sprintf("Average Mark: %.2f%%\n", $this->calculateAverage());
        echo "Status: " . $this->getPassFailStatus() . "\n";
        echo str_repeat("=", 60) . "\n\n";
    }
}