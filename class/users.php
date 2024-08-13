<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php'; // รวมไฟล์คลาส Encryption

class Users
{
    private $db;
    private $connection;
    private $encryption;

    public function __construct()
    {
        $this->db = new DBConnect();
        $this->connection = $this->db->getConnection();
        $this->encryption = new Encryption(); // สร้างออบเจกต์ Encryption
    }

    public function getUserAll()
    {
        $query = 'SELECT user_id, username, firstname, lastname, role, statusflag FROM cm_sap.tb_users WHERE is_deleted = false';
        $result = pg_prepare($this->connection, "get_all_users", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for fetching all users.');
        }
        $result = pg_execute($this->connection, "get_all_users", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for fetching all users.');
        }

        $users = pg_fetch_all($result);
        if ($users === false) {
            return [];
        }

        foreach ($users as &$user) {
            $user['user_id'] = $this->encryption->encrypt($user['user_id']);
        }

        return $users;
    }

    public function getUserById($encryptedUserId)
    {
        $userId = $this->encryption->decrypt($encryptedUserId);

        $query = 'SELECT user_id, username, firstname, lastname, role, statusflag FROM cm_sap.tb_users WHERE user_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "get_user_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for fetching user by ID.');
        }
        $result = pg_execute($this->connection, "get_user_by_id", array($userId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for fetching user by ID.');
        }
        return pg_fetch_assoc($result);
    }

    public function createUser($username, $userPassword, $firstname, $lastname, $role)
    {
        $query = 'INSERT INTO cm_sap.tb_users (username, user_password, firstname, lastname, role, created_at, updated_at, statusflag, is_deleted) 
                  VALUES ($1, $2, $3, $4, $5, NOW(), NOW(), true, false) RETURNING user_id';
        $result = pg_prepare($this->connection, "create_user", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating user.');
        }
        $result = pg_execute($this->connection, "create_user", array($username, $userPassword, $firstname, $lastname, $role));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating user.');
        }
        $userId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($userId);
    }

    public function updateUser($encryptedUserId, $firstname, $lastname, $role, $statusflag)
    {
        $userId = $this->encryption->decrypt($encryptedUserId);

        $query = 'UPDATE cm_sap.tb_users 
                  SET firstname = $2, lastname = $3, role = $4, statusflag = $5, updated_at = NOW() 
                  WHERE user_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_user", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating user.');
        }
        $result = pg_execute($this->connection, "update_user", array($userId, $firstname, $lastname, $role, $statusflag));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating user.');
        }
        return pg_affected_rows($result);
    }

    public function updateUserPassword($encryptedUserId, $newPassword)
    {
        $userId = $this->encryption->decrypt($encryptedUserId);

        $query = 'UPDATE cm_sap.tb_users SET user_password = $2, updated_at = NOW() 
                  WHERE user_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_user_password", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating user password.');
        }
        $result = pg_execute($this->connection, "update_user_password", array($userId, $newPassword));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating user password.');
        }
        return pg_affected_rows($result);
    }

    public function deleteUser($encryptedUserId)
    {
        $userId = $this->encryption->decrypt($encryptedUserId);

        $query = 'UPDATE cm_sap.tb_users SET is_deleted = true, updated_at = NOW() 
                  WHERE user_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "delete_user", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting user.');
        }
        $result = pg_execute($this->connection, "delete_user", array($userId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting user.');
        }
        return pg_affected_rows($result);
    }

    public function login($username, $password)
    {
        $query = 'SELECT user_id, username, user_password, statusflag, is_deleted, role FROM cm_sap.tb_users WHERE username = $1';
        $result = pg_prepare($this->connection, "login_user", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for user login.');
        }
        $result = pg_execute($this->connection, "login_user", array($username));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for user login.');
        }
        $user = pg_fetch_assoc($result);
    
        if (!$user) {
            // Username does not exist
            return array('status' => 'error', 'message' => 'Username does not exist.');
        }
    
        if ($user['is_deleted'] === 't') {
            // User account is deleted
            return array('status' => 'error', 'message' => 'Account is deactivated.');
        }
    
        if ($user['statusflag'] === 'f') {
            // User account is not active
            return array('status' => 'error', 'message' => 'Account is inactive.');
        }
    
        if (!password_verify($password, $user['user_password'])) {
            // Incorrect password
            return array('status' => 'error', 'message' => 'Incorrect password.');
        }
    
        // Successful login
        return array(
            'status' => 'success',
            'user_id' => $this->encryption->encrypt($user['user_id']),
            'username' => $user['username'],
            'role' => $user['role']
        );
    }
}
?>
