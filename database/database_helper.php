<?php
require_once __DIR__ . '/db_config.php';

use Kreait\Firebase\Database;

class FirebaseService
{
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function hashPassword(string $password): string
    {
        return hash('sha256', $password);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return hash('sha256', $password) === $hash;
    }

    public function generateAdminId(): string
    {
        return 'admin_' . time() . '_' . bin2hex(random_bytes(4));
    }

    public function generateUserId(): string
    {
        return 'user_' . time() . '_' . bin2hex(random_bytes(4));
    }
}

class AuthService
{
    private $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function login(string $email, string $password): array
    {
        $database = $this->firebaseService->getDatabase();
        $admins = $database->getReference('admins')->getValue() ?? [];

        foreach ($admins as $adminId => $admin) {
            if ($admin['email'] === $email &&
                $this->firebaseService->verifyPassword($password, $admin['password']) &&
                $admin['status'] === 'active') {

                // Update last login
                $database->getReference('admins/' . $adminId)->update([
                    'lastLogin' => date('c')
                ]);

                // Start session
                session_start();
                $_SESSION['admin'] = [
                    'id' => $adminId,
                    'email' => $admin['email'],
                    'name' => $admin['name'],
                    'role' => $admin['role'] ?? 'super_admin',
                    'loginTime' => date('c')
                ];

                return ['success' => true, 'admin' => $_SESSION['admin']];
            }
        }

        return ['success' => false, 'message' => 'Invalid credentials or inactive account'];
    }

    public function logout(): void
    {
        session_start();
        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        session_start();
        return isset($_SESSION['admin']);
    }

    public function getCurrentAdmin(): ?array
    {
       
        return $_SESSION['admin'] ?? null;
    }

    public function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: index.php');
            exit;
        }
    }
}
