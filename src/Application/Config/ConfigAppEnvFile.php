<?php

namespace SoftplanTasksApi\Application\Config;

use SoftplanTasksApi\Domain\Config\ConfigApp;

class ConfigAppEnvFile implements ConfigApp
{
    private string $envFile = (__DIR__ . '/.env');

    public readonly string $host;
    public readonly string $dbname;
    public readonly string $username;
    public readonly string $password;

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
}
