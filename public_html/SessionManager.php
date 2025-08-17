<?php
// Advanced Session Manager für IcebreakerNews
class SessionManager {
    private $sessionPath;
    private $fallbackMethods = [];
    
    public function __construct() {
        $this->sessionPath = __DIR__ . '/sessions';
        $this->initializeFallbackMethods();
    }
    
    private function initializeFallbackMethods() {
        $this->fallbackMethods = [
            'local_sessions' => [$this, 'useLocalSessions'],
            'cookie_fallback' => [$this, 'useCookieFallback'],
            'file_based' => [$this, 'useFileBased']
        ];
    }
    
    public function start() {
        // Methode 1: Lokale Sessions
        if ($this->useLocalSessions()) {
            return true;
        }
        
        // Methode 2: Cookie-Fallback
        if ($this->useCookieFallback()) {
            return true;
        }
        
        // Methode 3: File-based
        return $this->useFileBased();
    }
    
    private function useLocalSessions() {
        try {
            if (!is_dir($this->sessionPath)) {
                mkdir($this->sessionPath, 0755, true);
            }
            
            if (is_writable($this->sessionPath)) {
                session_save_path($this->sessionPath);
                session_start();
                return true;
            }
        } catch (Exception $e) {
            error_log("Local sessions failed: " . $e->getMessage());
        }
        
        return false;
    }
    
    private function useCookieFallback() {
        try {
            // Verwende Standard-Session aber mit Cookie-Backup
            session_start();
            
            // Wenn Session leer ist, versuche von Cookie zu laden
            if (empty($_SESSION) && isset($_COOKIE['admin_backup'])) {
                $data = json_decode(base64_decode($_COOKIE['admin_backup']), true);
                if ($data && $this->validateCookieData($data)) {
                    $_SESSION = $data;
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Cookie fallback failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function useFileBased() {
        try {
            // Verwende dateibasierte Session als letzter Ausweg
            $file = $this->sessionPath . '/admin_session.json';
            
            if (file_exists($file)) {
                $data = json_decode(file_get_contents($file), true);
                if ($data && $this->validateSessionData($data)) {
                    $_SESSION = $data;
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("File-based sessions failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function setAdmin($loginTime = null) {
        $_SESSION['admin'] = true;
        $_SESSION['login_time'] = $loginTime ?? time();
        
        // Backup in Cookie
        $this->backupToCookie();
        
        // Backup in Datei
        $this->backupToFile();
        
        return true;
    }
    
    private function backupToCookie() {
        try {
            $data = base64_encode(json_encode($_SESSION));
            setcookie('admin_backup', $data, time() + 3600, '/', '', false, true);
        } catch (Exception $e) {
            error_log("Cookie backup failed: " . $e->getMessage());
        }
    }
    
    private function backupToFile() {
        try {
            $file = $this->sessionPath . '/admin_session.json';
            file_put_contents($file, json_encode($_SESSION));
        } catch (Exception $e) {
            error_log("File backup failed: " . $e->getMessage());
        }
    }
    
    private function validateCookieData($data) {
        return isset($data['admin']) && $data['admin'] === true && 
               isset($data['login_time']) && 
               (time() - $data['login_time']) < 3600; // 1 Stunde
    }
    
    private function validateSessionData($data) {
        return $this->validateCookieData($data);
    }
    
    public function destroy() {
        session_destroy();
        setcookie('admin_backup', '', time() - 3600, '/');
        
        $file = $this->sessionPath . '/admin_session.json';
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public function getDebugInfo() {
        return [
            'session_id' => session_id(),
            'session_status' => session_status(),
            'session_save_path' => session_save_path(),
            'session_writable' => is_writable(session_save_path()),
            'local_path' => $this->sessionPath,
            'local_writable' => is_writable($this->sessionPath),
            'cookie_exists' => isset($_COOKIE['admin_backup']),
            'file_exists' => file_exists($this->sessionPath . '/admin_session.json'),
            'session_data' => $_SESSION
        ];
    }
}
?>
