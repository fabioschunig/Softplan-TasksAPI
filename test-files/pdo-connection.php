<?php

use SoftplanTasksApi\Application\Config\ConfigAppEnvFile;

require_once 'vendor/autoload.php';

$config = new ConfigAppEnvFile;
$config->loadEnv();
var_dump($config);

$pdoConnection = \SoftplanTasksApi\Infrastructure\Persistence\PdoConnectionCreator::createConnection(
    $config->host,
    $config->dbname,
    $config->username,
    $config->password,
);

$status = $pdoConnection->getAttribute(PDO::ATTR_CONNECTION_STATUS);
var_dump($status);
