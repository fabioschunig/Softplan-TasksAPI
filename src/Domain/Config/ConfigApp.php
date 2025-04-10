<?php

namespace SoftplanTasksApi\Domain\Config;

interface ConfigApp
{
    public function loadEnv();

    public function getHost(): string;
    public function getDBName(): string;
    public function getUsername(): string;
    public function getPassword(): string;

    public function getProjeto(): int;
}
