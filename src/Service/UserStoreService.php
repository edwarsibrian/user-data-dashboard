<?php

namespace App\Service;

class UserStoreService
{
    private string $filePath;

    public function __construct()
    {
        $this->filePath = __DIR__ . '/../../var/users.json';
    }

    public function createUser(string $email, string $password): void
    {
        $users = $this->getUsers();

        if (isset($users[$email])) {
            throw new \RuntimeException('User already exists.');
        }

        $users[$email] = [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'createdAt' => date('c'),
        ];

        $this->saveUsers($users);
    }

    public function validateCredentials(string $email, string $password): bool
    {
        $users = $this->getUsers();

        if (!isset($users[$email])) {
            return false;
        }

        return password_verify($password, $users[$email]['password']);
    }

    private function getUsers(): array
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $content = file_get_contents($this->filePath);

        if (!$content) {
            return [];
        }

        return json_decode($content, true) ?? [];
    }

    private function saveUsers(array $users): void
    {
        $directory = dirname($this->filePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT));
    }
}