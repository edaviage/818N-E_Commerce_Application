<?php
// Include the database connection
include_once __DIR__ . '/connect.php';

if (!class_exists('SessionHandlerDB')) {
    class SessionHandlerDB implements SessionHandlerInterface
    {
        private $db;

        public function open($save_path, $session_name)
        {
            // Use the existing database connection
            $this->db = $GLOBALS['con'];
            if ($this->db) {
                return true;
            } else {
                error_log("Session open failed: No database connection.");
                return false;
            }
        }

        public function close()
        {
            // Optionally close the database connection
            return true;
        }

        public function read($id)
        {
            $stmt = $this->db->prepare("SELECT data FROM user_sessions WHERE session_id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $id);
                $stmt->execute();
                $stmt->bind_result($data);
                if ($stmt->fetch()) {
                    $stmt->close();
                    return $data;
                } else {
                    $stmt->close();
                    return '';
                }
            } else {
                error_log("Session read failed: " . $this->db->error);
                return '';
            }
        }

        public function write($id, $data)
        {
            $access = time();
            $stmt = $this->db->prepare("REPLACE INTO user_sessions (session_id, access, data) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('sis', $id, $access, $data);
                $result = $stmt->execute();
                if (!$result) {
                    error_log("Session write failed: " . $stmt->error);
                }
                $stmt->close();
                return $result;
            } else {
                error_log("Session write failed: " . $this->db->error);
                return false;
            }
        }

        public function destroy($id)
        {
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE session_id = ?");
            if ($stmt) {
                $stmt->bind_param('s', $id);
                $result = $stmt->execute();
                if (!$result) {
                    error_log("Session destroy failed: " . $stmt->error);
                }
                $stmt->close();
                return $result;
            } else {
                error_log("Session destroy failed: " . $this->db->error);
                return false;
            }
        }

        public function gc($max_lifetime)
        {
            $old = time() - $max_lifetime;
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE access < ?");
            if ($stmt) {
                $stmt->bind_param('i', $old);
                $result = $stmt->execute();
                if (!$result) {
                    error_log("Session garbage collection failed: " . $stmt->error);
                }
                $stmt->close();
                return $result;
            } else {
                error_log("Session garbage collection failed: " . $this->db->error);
                return false;
            }
        }
    }
}

// Set the custom session handler
$handler = new SessionHandlerDB();
session_set_save_handler($handler, true);
session_start();

// Error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Do not display errors to users
ini_set('log_errors', '1');
// Errors will be logged to the web server's error log (e.g., /var/log/httpd/error_log)
?>
