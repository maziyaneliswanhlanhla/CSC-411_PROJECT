<?php
// SharedBuffer.php - Thread-Safe Bounded Buffer Implementation

class SharedBuffer {
    private $buffer = [];
    private $maxSize;
    private $bufferFile;
    private $lockFile;
    
    public function __construct($maxSize = 10, $bufferFile = 'buffer.json') {
        $this->maxSize = $maxSize;
        $this->bufferFile = $bufferFile;
        $this->lockFile = $bufferFile . '.lock';
        
        // Initialize buffer file if it doesn't exist
        if (!file_exists($this->bufferFile)) {
            file_put_contents($this->bufferFile, json_encode([]));
        }
    }
    
    // Acquire lock for mutual exclusion
    private function acquireLock() {
        $fp = fopen($this->lockFile, 'w');
        if (flock($fp, LOCK_EX)) {
            return $fp;
        }
        return false;
    }
    
    // Release lock
    private function releaseLock($fp) {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
    
    // Load buffer from file
    private function loadBuffer() {
        $content = file_get_contents($this->bufferFile);
        $this->buffer = json_decode($content, true) ?: [];
    }
    
    // Save buffer to file
    private function saveBuffer() {
        file_put_contents($this->bufferFile, json_encode($this->buffer));
    }
    
    // Check if buffer is full
    public function isFull() {
        $fp = $this->acquireLock();
        $this->loadBuffer();
        $full = count($this->buffer) >= $this->maxSize;
        $this->releaseLock($fp);
        return $full;
    }
    
    // Check if buffer is empty
    public function isEmpty() {
        $fp = $this->acquireLock();
        $this->loadBuffer();
        $empty = count($this->buffer) === 0;
        $this->releaseLock($fp);
        return $empty;
    }
    
    // Producer adds item to buffer
    public function produce($item) {
        $fp = $this->acquireLock();
        $this->loadBuffer();
        
        if (count($this->buffer) >= $this->maxSize) {
            $this->releaseLock($fp);
            return false; // Buffer full
        }
        
        $this->buffer[] = $item;
        $this->saveBuffer();
        $this->releaseLock($fp);
        
        echo "[BUFFER] Produced item: $item | Buffer size: " . count($this->buffer) . "/" . $this->maxSize . "\n";
        return true;
    }
    
    // Consumer removes item from buffer
    public function consume() {
        $fp = $this->acquireLock();
        $this->loadBuffer();
        
        if (count($this->buffer) === 0) {
            $this->releaseLock($fp);
            return null; // Buffer empty
        }
        
        $item = array_shift($this->buffer);
        $this->saveBuffer();
        $this->releaseLock($fp);
        
        echo "[BUFFER] Consumed item: $item | Buffer size: " . count($this->buffer) . "/" . $this->maxSize . "\n";
        return $item;
    }
    
    // Get current buffer size
    public function getSize() {
        $fp = $this->acquireLock();
        $this->loadBuffer();
        $size = count($this->buffer);
        $this->releaseLock($fp);
        return $size;
    }
    
    // Clear buffer (for testing)
    public function clear() {
        $fp = $this->acquireLock();
        $this->buffer = [];
        $this->saveBuffer();
        $this->releaseLock($fp);
    }
}