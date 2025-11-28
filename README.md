# Producer-Consumer Problem Implementation
## CSC411 - Integrative Programming Technologies Mini Project

**Team Members:**
- [Student Name 1] - [Student ID]
- [Student Name 2] - [Student ID]

---

## Table of Contents
1. [Project Overview](#project-overview)
2. [Features](#features)
3. [System Requirements](#system-requirements)
4. [Installation](#installation)
5. [Project Structure](#project-structure)
6. [How to Run](#how-to-run)
7. [Implementation Details](#implementation-details)
8. [Socket Programming](#socket-programming)
9. [Testing](#testing)
10. [GitHub Repository](#github-repository)

---

## Project Overview

This project implements the classic **Producer-Consumer Problem** using PHP, demonstrating concurrent programming concepts including:
- Process synchronization
- Mutual exclusion
- Bounded buffer management
- XML data serialization/deserialization
- Socket programming for distributed computing

---

## Features

### Part 1: Core Producer-Consumer Implementation (70 marks)
- ✅ **ITStudent Class**: Object representation with XML serialization
- ✅ **Random Data Generation**: Student names, IDs, programmes, courses, and marks
- ✅ **Producer**: Generates student data and wraps it in XML format
- ✅ **Consumer**: Unwraps XML, calculates averages, determines pass/fail status
- ✅ **Shared Buffer**: Thread-safe queue with capacity of 10 elements
- ✅ **Synchronization**: File-based locking mechanism for mutual exclusion

### Part 2: Version Control (10 marks)
- ✅ GitHub repository with complete source code
- ✅ Collaborative development workflow
- ✅ Comprehensive README documentation

### Part 3: Socket Programming (20 marks)
- ✅ Network-based producer-consumer implementation
- ✅ TCP socket communication
- ✅ Client-server architecture

### Part 4: Video Presentation (20 marks)
- 📹 Demonstration video (5-10 minutes)
- 📝 Video script included

---

## System Requirements

- **PHP**: Version 7.4 or higher
- **PHP Extensions**: 
  - SimpleXML (usually enabled by default)
  - Sockets (for socket programming)
- **Operating System**: Windows, Linux, or macOS
- **Command Line Interface**: Terminal or Command Prompt

### Checking PHP Installation

```bash
php --version
php -m | grep -i simplexml
php -m | grep -i sockets
```

---

## Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/producer-consumer-project.git
cd producer-consumer-project
```

### Step 2: Verify File Structure

Ensure all files are present:
```
producer-consumer-project/
├── ITStudent.php
├── SharedBuffer.php
├── producer.php
├── consumer.php
├── main.php
├── socket_server.php
├── socket_client.php
├── README.md
└── shared/ (created automatically)
```

### Step 3: Set Permissions (Linux/Mac)

```bash
chmod +x *.php
```

---

## Project Structure

### Core Classes

#### 1. **ITStudent.php**
Represents student data with the following attributes:
- Student Name
- Student ID (8 digits)
- Programme
- Courses and marks (associative array)

**Methods:**
- `toXML()` - Serialize to XML format
- `fromXML()` - Deserialize from XML
- `calculateAverage()` - Calculate average mark
- `getPassFailStatus()` - Determine if student passed (≥50%)
- `display()` - Print formatted student information

#### 2. **SharedBuffer.php**
Thread-safe bounded buffer implementation:
- Maximum capacity: 10 elements
- File-based locking for mutual exclusion
- FIFO queue operations

**Methods:**
- `produce($item)` - Add item to buffer (blocks if full)
- `consume()` - Remove item from buffer (blocks if empty)
- `isFull()` - Check if buffer is at capacity
- `isEmpty()` - Check if buffer is empty

#### 3. **Producer.php**
Generates random student data:
- Creates ITStudent objects with random attributes
- Wraps data in XML format
- Stores XML files in shared directory
- Adds file references to buffer

#### 4. **Consumer.php**
Processes student data:
- Reads XML files from shared directory
- Unwraps XML to ITStudent objects
- Calculates averages and pass/fail status
- Displays formatted output
- Removes processed files

---

## How to Run

### Option 1: Concurrent Execution (Recommended)

Run producer and consumer simultaneously:

```bash
php main.php concurrent 10
```

This will:
1. Start both producer and consumer as separate processes
2. Process 10 students concurrently
3. Display real-time buffer monitoring

### Option 2: Sequential Execution

Run producer first, then consumer:

```bash
php main.php sequential 10
```

### Option 3: Manual Execution

**Terminal 1 (Producer):**
```bash
php producer.php 10
```

**Terminal 2 (Consumer):**
```bash
php consumer.php 10
```

### Socket Programming

**Terminal 1 (Server/Producer):**
```bash
php socket_server.php 8888 10
```

**Terminal 2 (Client/Consumer):**
```bash
php socket_client.php 8888
```

---

## Implementation Details

### Synchronization Mechanism

The project uses **file-based locking** to ensure mutual exclusion:

```php
private function acquireLock() {
    $fp = fopen($this->lockFile, 'w');
    if (flock($fp, LOCK_EX)) {
        return $fp;
    }
    return false;
}
```

This prevents:
- Race conditions
- Concurrent buffer modifications
- Data corruption

### Buffer Rules Enforcement

1. **Producer waits when buffer is full:**
```php
while ($this->buffer->isFull()) {
    echo "[PRODUCER] Buffer is full. Waiting...\n";
    sleep(2);
}
```

2. **Consumer waits when buffer is empty:**
```php
while ($this->buffer->isEmpty()) {
    echo "[CONSUMER] Buffer is empty. Waiting...\n";
    sleep(2);
}
```

3. **Mutual exclusion through file locking**

### XML Format Example

```xml
<?xml version="1.0"?>
<student>
    <name>Thabo Dlamini</name>
    <studentID>12345678</studentID>
    <programme>BSc Computer Science</programme>
    <courses>
        <course>
            <name>CSC411 Integrative Programming</name>
            <mark>85</mark>
        </course>
        <course>
            <name>CSC301 Data Structures</name>
            <mark>72</mark>
        </course>
    </courses>
</student>
```

---

## Socket Programming

### Architecture

- **Server (Producer)**: Listens on port 8888, generates student data
- **Client (Consumer)**: Connects to server, receives and processes data

### Communication Protocol

1. Server sends number of students
2. For each student:
   - Server sends XML data with delimiter `|||END|||`
   - Client processes data
   - Client sends `ACK` acknowledgment
3. Server sends `DONE` signal
4. Both close connections

### Advantages

- Distributed processing
- Network transparency
- Scalability
- Real-world application simulation

---

## Testing

### Test Scenarios

1. **Basic Functionality**
   - Generate 5 students
   - Verify XML creation
   - Verify consumption and deletion

2. **Buffer Capacity**
   - Generate 15 students (exceeds buffer size)
   - Verify producer waits when buffer is full

3. **Synchronization**
   - Run concurrent processes
   - Verify no race conditions
   - Verify data integrity

4. **Socket Communication**
   - Test network transmission
   - Verify data integrity over sockets
   - Test connection handling

### Sample Output

```
============================================================
STUDENT INFORMATION
============================================================
Name: Nomsa Simelane
Student ID: 45678912
Programme: BSc Information Technology

Courses and Marks:
------------------------------------------------------------
  CSC411 Integrative Programming                      78%
  CSC301 Data Structures                               65%
  CSC402 Web Development                               82%
  CSC403 Mobile Computing                              71%
------------------------------------------------------------
Average Mark: 74.00%
Status: PASS
============================================================
```

---

## GitHub Repository

### Repository Structure
```
producer-consumer-project/
├── src/                    # Source code
├── docs/                   # Documentation
├── tests/                  # Test files
├── demo-video/            # Presentation video
├── .gitignore
└── README.md
```

### Commit Guidelines

Follow these commit message conventions:
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `refactor:` Code refactoring

Example:
```bash
git add ITStudent.php
git commit -m "feat: implement XML serialization in ITStudent class"
git push origin main
```

### Collaboration Workflow

1. Clone repository
2. Create feature branch: `git checkout -b feature-name`
3. Make changes and commit
4. Push to GitHub: `git push origin feature-name`
5. Create pull request
6. Review and merge

---

## Troubleshooting

### Common Issues

**Issue: "Failed to create socket"**
- Solution: Enable sockets extension in php.ini
- Uncomment: `extension=sockets`

**Issue: "Buffer file permission denied"**
- Solution (Linux/Mac): `chmod 777 .`
- Solution (Windows): Run as administrator

**Issue: "Port already in use"**
- Solution: Change port number or kill existing process
- `lsof -i :8888` (Mac/Linux)
- `netstat -ano | findstr :8888` (Windows)

**Issue: Concurrent execution not working**
- Solution: Ensure PHP CLI is in system PATH
- Use absolute paths to PHP executable

---

## Contributors

- **[Student 1 Name]** - [GitHub Profile URL]
  - Implemented core producer-consumer logic
  - Developed synchronization mechanisms
  
- **[Student 2 Name]** - [GitHub Profile URL]
  - Implemented socket programming
  - Created documentation and testing

---

## License

This project is submitted as part of CSC411 coursework at the University of Eswatini.

---

## Acknowledgments

- Dr. [Instructor Name] - Course Instructor
- University of Eswatini, Department of Computer Science
- PHP Documentation and Community

---

## Contact

For questions or issues, please contact:
- Email: [your-email@example.com]
- GitHub Issues: [Repository Issues URL]

---

**Submission Date:** November 23, 2025
**Course:** CSC411 - Integrative Programming Technologies
**Institution:** University of Eswatini