<?php

namespace SoftplanTasksApi\Application\Config;

use SoftplanTasksApi\Domain\Config\ConfigApp;

class ConfigAppEnvFile implements ConfigApp
{
    private string $envFile = (__DIR__ . '/../../../config/.env');

    private string $host = '';
    private string $dbname = '';
    private string $username = '';
    private string $password = '';

    public function loadEnv()
    {
        if (! file_exists($this->envFile)) {
            throw new \Exception("Error: $this->envFile file not found");
        }

        $lines = file($this->envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $env = [];
        foreach ($lines as $line) {
            // ignore comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // key=value
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            $env[$key] = $value;
        }

        $this->host = $env['DB_HOST'] ?? '';
        $this->dbname = $env['DB_DBNAME'] ?? '';
        $this->username = $env['DB_USERNAME'] ?? '';
        $this->password = $env['DB_PASSWORD'] ?? '';
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getDBName(): string
    {
        return $this->dbname;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
