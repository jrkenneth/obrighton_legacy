<?php
/**
 * Database Helper Class - Secure Query Wrapper
 * 
 * This class wraps mysqli functions with prepared statements to prevent SQL injection
 * All database operations should use this class instead of raw mysqli_query()
 * 
 * Usage:
 *   $db = new DatabaseHelper($mysqli_connection);
 *   $result = $db->select('users', ['id' => 5]);
 *   $result = $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
 *   $result = $db->update('users', ['name' => 'Jane'], ['id' => 5]);
 *   $result = $db->delete('users', ['id' => 5]);
 */

class DatabaseHelper {
    private $conn;
    private $audit_log_enabled = true;
    
    public function __construct($mysqli_connection) {
        $this->conn = $mysqli_connection;
    }

    /**
     * Get raw mysqli connection
     *
     * @return mysqli
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * SELECT Query with Prepared Statements
     * 
     * @param string $table Table name
     * @param array $where Associative array of WHERE conditions ['column' => value]
     * @param array $columns Columns to select (default: all)
     * @param string $order ORDER BY clause
     * @return mysqli_result or false
     */
    public function select($table, $where = [], $columns = ['*'], $order = '') {
        if (empty($columns)) $columns = ['*'];
        
        $columns_str = implode(', ', $columns);
        $sql = "SELECT {$columns_str} FROM {$table}";
        $types = '';
        $params = [];
        
        if (!empty($where)) {
            $where_clauses = [];
            foreach ($where as $column => $value) {
                $where_clauses[] = "{$column} = ?";
                $params[] = $value;
                $types .= $this->getParamType($value);
            }
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        if (!empty($order)) {
            $sql .= " ORDER BY {$order}";
        }
        
        return $this->execute($sql, $types, $params);
    }
    
    /**
     * INSERT Query with Prepared Statements
     * 
     * @param string $table Table name
     * @param array $data Associative array of data to insert
     * @param string $user_id Current user ID (for audit logging)
     * @return int Inserted ID or 0 on failure
     */
    public function insert($table, $data, $user_id = null) {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($data), '?');
        $values = array_values($data);
        
        $columns_str = implode(', ', $columns);
        $placeholders_str = implode(', ', $placeholders);
        
        $sql = "INSERT INTO {$table} ({$columns_str}) VALUES ({$placeholders_str})";
        
        $types = '';
        foreach ($values as $value) {
            $types .= $this->getParamType($value);
        }
        
        $result = $this->execute($sql, $types, $values);
        
        if ($result) {
            $inserted_id = $this->conn->insert_id;
            
            // Log the insertion
            if ($this->audit_log_enabled && $user_id) {
                $this->logAudit('INSERT', $table, $inserted_id, null, $data, $user_id);
            }
            
            return $inserted_id;
        }
        
        return 0;
    }
    
    /**
     * UPDATE Query with Prepared Statements
     * 
     * IMPORTANT: Must have WHERE clause to prevent updating all records
     * 
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $where WHERE conditions (REQUIRED)
     * @param string $user_id Current user ID (for audit logging)
     * @return bool Success or failure
     */
    public function update($table, $data, $where = [], $user_id = null) {
        if (empty($where)) {
            trigger_error("UPDATE without WHERE clause is not allowed", E_USER_ERROR);
            return false;
        }
        
        $set_parts = [];
        $types = '';
        $params = [];
        
        // Get before data for audit logging
        $before_data = [];
        if ($this->audit_log_enabled && $user_id) {
            $id_key = key($where);
            $before_result = $this->select($table, $where);
            if ($before_result && $row = $before_result->fetch_assoc()) {
                $before_data = $row;
            }
        }
        
        foreach ($data as $column => $value) {
            $set_parts[] = "{$column} = ?";
            $params[] = $value;
            $types .= $this->getParamType($value);
        }
        
        $where_clauses = [];
        foreach ($where as $column => $value) {
            $where_clauses[] = "{$column} = ?";
            $params[] = $value;
            $types .= $this->getParamType($value);
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $set_parts);
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
        
        $result = $this->execute($sql, $types, $params);
        
        // Log the update
        if ($result && $this->audit_log_enabled && $user_id) {
            $record_id = reset($where);
            $this->logAudit('UPDATE', $table, $record_id, $before_data, $data, $user_id);
        }
        
        return $result !== false;
    }
    
    /**
     * DELETE Query with Prepared Statements
     * 
     * CRITICAL: Must have WHERE clause to prevent deleting all records
     * 
     * @param string $table Table name
     * @param array $where WHERE conditions (REQUIRED)
     * @param string $user_id Current user ID (for audit logging)
     * @return bool Success or failure
     */
    public function delete($table, $where = [], $user_id = null) {
        if (empty($where)) {
            trigger_error("DELETE without WHERE clause is not allowed", E_USER_ERROR);
            return false;
        }
        
        // Get the record before deletion for audit trail
        $before_data = [];
        if ($this->audit_log_enabled && $user_id) {
            $delete_result = $this->select($table, $where);
            if ($delete_result && $row = $delete_result->fetch_assoc()) {
                $before_data = $row;
            }
        }
        
        $where_clauses = [];
        $types = '';
        $params = [];
        
        foreach ($where as $column => $value) {
            $where_clauses[] = "{$column} = ?";
            $params[] = $value;
            $types .= $this->getParamType($value);
        }
        
        $sql = "DELETE FROM {$table} WHERE " . implode(" AND ", $where_clauses);
        
        $result = $this->execute($sql, $types, $params);
        
        // Log the deletion
        if ($result && $this->audit_log_enabled && $user_id) {
            $record_id = reset($where);
            $this->logAudit('DELETE', $table, $record_id, $before_data, null, $user_id);
        }
        
        return $result !== false;
    }
    
    /**
     * Execute prepared statement
     * 
     * @param string $sql SQL query with placeholders
     * @param string $types Parameter types (s, i, d, b)
     * @param array $params Parameter values
     * @return mysqli_result or false
     */
    private function execute($sql, $types, $params) {
        try {
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return false;
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
            $result = $stmt->get_result();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Determine mysqli parameter type
     * 
     * @param mixed $value
     * @return string Type: 's' (string), 'i' (int), 'd' (double), 'b' (blob)
     */
    private function getParamType($value) {
        if (is_int($value)) {
            return 'i';
        } elseif (is_float($value)) {
            return 'd';
        } elseif (is_bool($value)) {
            return 'i';
        } else {
            return 's';
        }
    }
    
    /**
     * Log database operations to audit table
     * 
     * @param string $action INSERT, UPDATE, DELETE
     * @param string $table Table name
     * @param mixed $record_id Record ID
     * @param array $before_data Before data (for update/delete)
     * @param array $after_data After data (for insert/update)
     * @param string $user_id User performing action
     */
    private function logAudit($action, $table, $record_id, $before_data, $after_data, $user_id) {
        try {
            $timestamp = date('Y-m-d H:i:s');
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
            $before_json = json_encode($before_data);
            $after_json = json_encode($after_data);
            
            $sql = "INSERT INTO audit_logs (action, table_name, record_id, before_data, after_data, user_id, ip_address, timestamp) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('ssisssi', $action, $table, $record_id, $before_json, $after_json, $user_id, $ip_address, $timestamp);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Exception $e) {
            error_log("Audit logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        $this->conn->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->conn->rollback();
    }
    
    /**
     * Get last error
     */
    public function getError() {
        return $this->conn->error;
    }
}

?>
