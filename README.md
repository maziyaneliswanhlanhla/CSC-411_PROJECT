# Producer-Consumer Problem Implementation
## CSC411 - Integrative Programming Technologies Mini Project

**Team Members:**
- [Neliswa Maziya] - [202203810]
- [Ayanda Mhlanga] - [202200841]


## Project Overview

This project implements the classic **Producer-Consumer Problem** using PHP, demonstrating concurrent programming concepts including:
- Process synchronization
- Mutual exclusion
- Bounded buffer management
- XML data serialization/deserialization
- Socket programming for distributed computing

---

## Features

### Core Producer-Consumer Implementation
**ITStudent Class**: Object representation with XML serialization
**Random Data Generation**: Student names, IDs, programmes, courses, and marks
**Producer**: Generates student data and wraps it in XML format
**Consumer**: Unwraps XML, calculates averages, determines pass/fail status
**Shared Buffer**: Thread-safe queue with capacity of 10 elements
**Synchronization**: File-based locking mechanism for mutual exclusion

###  Version Control 
 GitHub repository with complete source code
 Collaborative development workflow
 Comprehensive README documentation

### Socket Programming
 Network-based producer-consumer implementation
 TCP socket communication
 Client-server architecture

### Video Presentation 
 📹 Demonstration video 





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



## Contributors

- **[Neliswa Maziya 202203810]** - [https://github.com/maziyaneliswanhlanhla]
  - Implemented core producer logic
  - Implemented socket_server programming
  

  - Developed synchronization mechanisms
  
- **[Ayanda Mhlanga 202200841]** - [https://github.com/mhlangaayandatech404]
  - Implemented socket_client programming
  - Created documentation and testing



## Acknowledgments

- Dr. [Nxumalo] - Course Instructor
- University of Eswatini, Department of Computer Science

---
## Repository adress 
- https://github.com/maziyaneliswanhlanhla/CSC-411_PROJECT


**Submission Date:** December 5, 2025
**Course:** CSC411 - Integrative Programming Technologies
**Institution:** University of Eswatini