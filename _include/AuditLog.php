<?php
/**
 * Audit Logging System
 * 
 * Tracks all database modifications (INSERT, UPDATE, DELETE) for security and compliance
 * Provides complete audit trail of who changed what, when, and from which IP
 * 
 * Usage:
 *   AuditLog::log('DELETE', 'users', 5, $old_data, null, $user_id);
 *   $logs = AuditLog::getRecordHistory('users', 5);
 *   $logs = AuditLog::getUserActivity($user_id, 'DELETE');
 */

class AuditLog {
    
    private static $db = null;
    
    /**
     * Initialize audit logging system
     * 
     * @param object $db DatabaseHelper instance
     */
    public static function initialize($db) {
        self::$db = $db;
        
        // Create audit_logs table if it doesn't exist
        self::createAuditTable();
    }
    
    /**
     * Create audit_logs table if it doesn't exist
     */
    private static function createAuditTable() {
        if (!self::$db) {
            return;
        }
        
        try {
            $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                action VARCHAR(50) NOT NULL COMMENT 'INSERT, UPDATE, DELETE',
                table_name VARCHAR(100) NOT NULL,
                record_id INT,
                before_data LONGTEXT COMMENT 'JSON data before change',
                after_data LONGTEXT COMMENT 'JSON data after change',
                user_id INT,
                user_ip VARCHAR(45),
                user_agent VARCHAR(500),
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_table_record (table_name, record_id),
                INDEX idx_user_time (user_id, timestamp),
                INDEX idx_action (action),
                INDEX idx_timestamp (timestamp)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            self::$db->getConnection()->query($sql);
        } catch (Exception $e) {
            error_log("Failed to create audit_logs table: " . $e->getMessage());
        }
    }
    
    /**
     * Log a database operation
     * 
     * @param string $action INSERT, UPDATE, or DELETE
     * @param string $table Table name
     * @param int $record_id Record ID
     * @param array $before_data Data before change (for UPDATE/DELETE)
     * @param array $after_data Data after change (for INSERT/UPDATE)
     * @param int $user_id User performing action
     * @param string $user_ip IP address (optional, uses $_SERVER['REMOTE_ADDR'])
     */
    public static function log($action, $table, $record_id, $before_data = null, $after_data = null, $user_id = null, $user_ip = null) {
        if (!self::$db) {
            error_log("AuditLog not initialized");
            return;
        }
        
        try {
            $action = strtoupper($action);
            $user_ip = $user_ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'CLI');
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            // Encode data as JSON
            $before_json = $before_data ? json_encode($before_data) : null;
            $after_json = $after_data ? json_encode($after_data) : null;
            
            // Insert audit log
            $log_data = [
                'action' => $action,
                'table_name' => $table,
                'record_id' => $record_id,
                'before_data' => $before_json,
                'after_data' => $after_json,
                'user_id' => $user_id,
                'user_ip' => $user_ip,
                'user_agent' => substr($user_agent, 0, 500),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $conn = self::$db->getConnection();
            $columns = implode(',', array_keys($log_data));
            $values = array_values($log_data);
            $placeholders = str_repeat('?,', count($values) - 1) . '?';
            
            $sql = "INSERT INTO audit_logs ({$columns}) VALUES ({$placeholders})";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $types = '';
                foreach ($values as $value) {
                    $types .= is_int($value) ? 'i' : 's';
                }
                $stmt->bind_param($types, ...$values);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Exception $e) {
            error_log("Audit logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Get all changes to a specific record
     * 
     * @param string $table Table name
     * @param int $record_id Record ID
     * @param int $limit Number of records to return
     * @return array Array of audit log entries
     */
    public static function getRecordHistory($table, $record_id, $limit = 100) {
        if (!self::$db) {
            return [];
        }
        
        try {
            $sql = "SELECT * FROM audit_logs 
                    WHERE table_name = ? AND record_id = ? 
                    ORDER BY timestamp DESC 
                    LIMIT ?";
            
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param('sii', $table, $record_id, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $logs = [];
            while ($row = $result->fetch_assoc()) {
                $row['before_data'] = $row['before_data'] ? json_decode($row['before_data'], true) : null;
                $row['after_data'] = $row['after_data'] ? json_decode($row['after_data'], true) : null;
                $logs[] = $row;
            }
            
            $stmt->close();
            return $logs;
        } catch (Exception $e) {
            error_log("Error retrieving record history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all activities by a specific user
     * 
     * @param int $user_id User ID
     * @param string $action Action filter (optional: INSERT, UPDATE, DELETE)
     * @param int $limit Number of records
     * @return array Array of audit log entries
     */
    public static function getUserActivity($user_id, $action = null, $limit = 100) {
        if (!self::$db) {
            return [];
        }
        
        try {
            $sql = "SELECT * FROM audit_logs WHERE user_id = ?";
            $types = 'i';
            $params = [$user_id];
            
            if ($action) {
                $sql .= " AND action = ?";
                $types .= 's';
                $params[] = strtoupper($action);
            }
            
            $sql .= " ORDER BY timestamp DESC LIMIT ?";
            $types .= 'i';
            $params[] = $limit;
            
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $logs = [];
            while ($row = $result->fetch_assoc()) {
                $row['before_data'] = $row['before_data'] ? json_decode($row['before_data'], true) : null;
                $row['after_data'] = $row['after_data'] ? json_decode($row['after_data'], true) : null;
                $logs[] = $row;
            }
            
            $stmt->close();
            return $logs;
        } catch (Exception $e) {
            error_log("Error retrieving user activity: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get activity for a table
     * 
     * @param string $table Table name
     * @param string $action Action filter (optional)
     * @param int $limit Number of records
     * @return array Array of audit log entries
     */
    public static function getTableActivity($table, $action = null, $limit = 100) {
        if (!self::$db) {
            return [];
        }
        
        try {
            $sql = "SELECT * FROM audit_logs WHERE table_name = ?";
            $types = 's';
            $params = [$table];
            
            if ($action) {
                $sql .= " AND action = ?";
                $types .= 's';
                $params[] = strtoupper($action);
            }
            
            $sql .= " ORDER BY timestamp DESC LIMIT ?";
            $types .= 'i';
            $params[] = $limit;
            
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $logs = [];
            while ($row = $result->fetch_assoc()) {
                $row['before_data'] = $row['before_data'] ? json_decode($row['before_data'], true) : null;
                $row['after_data'] = $row['after_data'] ? json_decode($row['after_data'], true) : null;
                $logs[] = $row;
            }
            
            $stmt->close();
            return $logs;
        } catch (Exception $e) {
            error_log("Error retrieving table activity: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all deletions (potential data loss investigation)
     * 
     * @param int $days Days to look back
     * @param int $limit Number of records
     * @return array Array of all deletions
     */
    public static function getRecentDeletions($days = 7, $limit = 500) {
        if (!self::$db) {
            return [];
        }
        
        try {
            $from_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            $sql = "SELECT * FROM audit_logs 
                    WHERE action = 'DELETE' AND timestamp >= ? 
                    ORDER BY timestamp DESC 
                    LIMIT ?";
            
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param('si', $from_date, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $logs = [];
            while ($row = $result->fetch_assoc()) {
                $row['before_data'] = $row['before_data'] ? json_decode($row['before_data'], true) : null;
                $logs[] = $row;
            }
            
            $stmt->close();
            return $logs;
        } catch (Exception $e) {
            error_log("Error retrieving recent deletions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get bulk activity (multiple changes in short period - potential attack indicator)
     * 
     * @param int $minutes Time window in minutes
     * @param int $change_threshold Minimum number of changes to flag
     * @return array Array of suspicious activity
     */
    public static function getBulkActivity($minutes = 5, $change_threshold = 10) {
        if (!self::$db) {
            return [];
        }
        
        try {
            $sql = "SELECT user_id, action, COUNT(*) as change_count, MAX(timestamp) as last_change
                    FROM audit_logs 
                    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
                    GROUP BY user_id, action
                    HAVING change_count >= ?
                    ORDER BY change_count DESC";
            
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param('ii', $minutes, $change_threshold);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $activity = [];
            while ($row = $result->fetch_assoc()) {
                $activity[] = $row;
            }
            
            $stmt->close();
            return $activity;
        } catch (Exception $e) {
            error_log("Error retrieving bulk activity: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Purge old audit logs (keep for compliance/retention)
     * 
     * @param int $days Keep logs for this many days
     * @return int Number of rows deleted
     */
    public static function purgeOldLogs($days = 90) {
        if (!self::$db) {
            return 0;
        }
        
        try {
            $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            $sql = "DELETE FROM audit_logs WHERE timestamp < ?";
            
            $conn = self::$db->getConnection();
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                return 0;
            }
            
            $stmt->bind_param('s', $cutoff_date);
            $stmt->execute();
            $deleted = $stmt->affected_rows;
            $stmt->close();
            
            return $deleted;
        } catch (Exception $e) {
            error_log("Error purging audit logs: " . $e->getMessage());
            return 0;
        }
    }
}

?>
