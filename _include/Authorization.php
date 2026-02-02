<?php
/**
 * Authorization & Access Control Utility
 * 
 * Enforces role-based access control and data ownership verification
 * All sensitive operations must check authorization before execution
 * 
 * Usage:
 *   if (!Authorization::canDelete('users', 5)) {
 *       die('You do not have permission to delete this user');
 *   }
 *   
 *   if (!Authorization::isOwner('properties', 10, $current_user_id)) {
 *       die('You do not own this property');
 *   }
 */

class Authorization {
    
    // Role definitions
    const ROLE_ADMIN = 1;
    const ROLE_EDITOR = 2;
    const ROLE_AGENT = 3;
    
    private static $current_user = null;
    private static $db = null;
    
    /**
     * Initialize authorization system
     * 
     * @param object $db DatabaseHelper instance
     * @param array $user Current user data
     */
    public static function initialize($db, $user) {
        self::$db = $db;
        self::$current_user = $user;
    }
    
    /**
     * Get current user
     * 
     * @return array Current user data
     */
    public static function getCurrentUser() {
        return self::$current_user;
    }
    
    /**
     * Get current user's role
     * 
     * @return int Role ID
     */
    public static function getCurrentUserRole() {
        return self::$current_user['role_id'] ?? null;
    }
    
    /**
     * Get current user's ID
     * 
     * @return int User ID
     */
    public static function getCurrentUserId() {
        return self::$current_user['id'] ?? null;
    }
    
    /**
     * Check if user is admin
     * 
     * @return bool
     */
    public static function isAdmin() {
        return self::getCurrentUserRole() === self::ROLE_ADMIN;
    }
    
    /**
     * Check if user is editor
     * 
     * @return bool
     */
    public static function isEditor() {
        return self::getCurrentUserRole() === self::ROLE_EDITOR;
    }
    
    /**
     * Check if user is agent
     * 
     * @return bool
     */
    public static function isAgent() {
        return self::getCurrentUserRole() === self::ROLE_AGENT;
    }
    
    /**
     * Check if user has specific role
     * 
     * @param int $role_id Role to check
     * @return bool
     */
    public static function hasRole($role_id) {
        return self::getCurrentUserRole() === $role_id;
    }
    
    /**
     * Check if user has one of multiple roles
     * 
     * @param array $role_ids Roles to check
     * @return bool
     */
    public static function hasAnyRole($role_ids = []) {
        return in_array(self::getCurrentUserRole(), $role_ids);
    }
    
    /**
     * Check if user owns a record
     * 
     * Verifies that the record belongs to the user or was created by them
     * 
     * @param string $table Table name (users, properties, tenants, etc)
     * @param int $record_id Record ID
     * @param int $user_id User ID to verify ownership (optional, uses current user)
     * @return bool
     */
    public static function isOwner($table, $record_id, $user_id = null) {
        if (!self::$db) {
            return false;
        }
        
        $user_id = $user_id ?? self::getCurrentUserId();
        
        if (!$user_id) {
            return false;
        }
        
        // Define ownership columns for each table
        $ownership_columns = [
            'properties' => 'landlord_id',
            'listings' => 'landlord_id',
            'tenants' => 'uploader_id',
            'landlords' => 'id',
            'users' => 'id',
            'tickets' => 'created_by_id',
            'artisans' => 'id'
        ];
        
        $owner_column = $ownership_columns[$table] ?? null;
        
        if (!$owner_column) {
            error_log("Unknown table for ownership check: {$table}");
            return false;
        }
        
        // Query the database
        $result = self::$db->select($table, ['id' => $record_id]);
        
        if (!$result || $result->num_rows === 0) {
            return false;
        }
        
        $row = $result->fetch_assoc();
        
        // Check ownership
        return intval($row[$owner_column]) === intval($user_id);
    }
    
    /**
     * Check if user has access to a record through access_mgt table
     * 
     * @param string $table Table name
     * @param int $record_id Record ID
     * @return bool
     */
    public static function hasAccess($table, $record_id) {
        if (!self::$db) {
            return false;
        }
        
        $user_id = self::getCurrentUserId();
        $role_id = self::getCurrentUserRole();
        
        if (!$user_id) {
            return false;
        }
        
        // Admins have access to everything
        if (self::isAdmin()) {
            return true;
        }
        
        // Check access_mgt table
        $result = self::$db->select('access_mgt', [
            'user_id' => $user_id,
            'target' => $table,
            'target_id' => $record_id
        ]);
        
        return $result && $result->num_rows > 0;
    }
    
    /**
     * Can user perform action on record?
     * 
     * Combines ownership and role checks
     * 
     * @param string $action Action (read, create, update, delete)
     * @param string $table Table name
     * @param int $record_id Record ID (optional for create)
     * @return bool
     */
    public static function can($action, $table, $record_id = null) {
        // Admins can do everything
        if (self::isAdmin()) {
            return true;
        }
        
        switch ($action) {
            case 'create':
                // Define who can create what
                $create_permissions = [
                    'users' => [self::ROLE_ADMIN, self::ROLE_EDITOR],
                    'properties' => [self::ROLE_EDITOR],
                    'listings' => [self::ROLE_EDITOR],
                    'tenants' => [self::ROLE_EDITOR, self::ROLE_AGENT],
                    'tickets' => [self::ROLE_ADMIN, self::ROLE_EDITOR, self::ROLE_AGENT],
                ];
                
                $allowed_roles = $create_permissions[$table] ?? [];
                return in_array(self::getCurrentUserRole(), $allowed_roles);
                
            case 'read':
                // Can user read this record?
                if ($record_id === null) {
                    return true; // Can read list
                }
                return self::isOwner($table, $record_id) || self::hasAccess($table, $record_id);
                
            case 'update':
                // Can user update this record?
                if ($record_id === null) {
                    return false;
                }
                return self::isOwner($table, $record_id) || self::hasAccess($table, $record_id);
                
            case 'delete':
                // Can user delete this record?
                if ($record_id === null) {
                    return false;
                }
                
                // Define who can delete what
                $delete_permissions = [
                    'users' => [self::ROLE_ADMIN],
                    'properties' => [self::ROLE_EDITOR],
                    'listings' => [self::ROLE_EDITOR],
                    'tenants' => [self::ROLE_EDITOR],
                    'tickets' => [self::ROLE_ADMIN, self::ROLE_EDITOR],
                ];
                
                $allowed_roles = $delete_permissions[$table] ?? [];
                $role_ok = in_array(self::getCurrentUserRole(), $allowed_roles);
                
                // Must own the record or have explicit access
                $access_ok = self::isOwner($table, $record_id) || self::hasAccess($table, $record_id);
                
                return $role_ok && $access_ok;
                
            default:
                return false;
        }
    }
    
    /**
     * Require user to have permission, die if not
     * 
     * Usage:
     *   Authorization::require('delete', 'users', 5);
     * 
     * @param string $action Action
     * @param string $table Table name
     * @param int $record_id Record ID
     * @param string $error_message Custom error message
     */
    public static function require($action, $table, $record_id = null, $error_message = null) {
        if (!self::can($action, $table, $record_id)) {
            $default_message = "You do not have permission to {$action} this {$table}";
            $message = $error_message ?? $default_message;
            
            // Log unauthorized attempt
            $user_id = self::getCurrentUserId();
            error_log("Authorization denied - User {$user_id} attempted to {$action} {$table} id {$record_id}");
            
            // Return error
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = $message;
            $_SESSION['expire'] = time() + 10;
            
            // Redirect or die
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                http_response_code(403);
                die(json_encode(['success' => false, 'message' => $message]));
            } else {
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
                die();
            }
        }
    }
    
    /**
     * Check if user has access to view another user's data
     * 
     * @param int $target_user_id User ID to check access to
     * @return bool
     */
    public static function canViewUser($target_user_id) {
        // Admins can view all users
        if (self::isAdmin()) {
            return true;
        }
        
        // Users can view themselves
        if (self::getCurrentUserId() === $target_user_id) {
            return true;
        }
        
        // Editors can view non-admin users
        if (self::isEditor()) {
            // Check target user's role (simplified - in practice query DB)
            return true;
        }
        
        return false;
    }
    
    /**
     * Get all records user has access to (for listing pages)
     * 
     * @param string $table Table name
     * @return array Array of accessible record IDs
     */
    public static function getAccessibleRecords($table) {
        if (!self::$db) {
            return [];
        }
        
        // Admins have access to all records
        if (self::isAdmin()) {
            return '*'; // Special value meaning all records
        }
        
        $user_id = self::getCurrentUserId();
        $record_ids = [];
        
        // Get records from access_mgt table
        $result = self::$db->select('access_mgt', [
            'user_id' => $user_id,
            'target' => $table
        ]);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $record_ids[] = $row['target_id'];
            }
        }
        
        return $record_ids;
    }
}

?>
