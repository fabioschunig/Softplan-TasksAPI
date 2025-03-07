<?php

require_once 'vendor/autoload.php';

$pdoConnection = \SoftplanTasksApi\Infrastructure\Persistence\PdoConnectionCreator::createConnection(
    '127.0.0.1',
    'task_rest_api',
);

$status = $pdoConnection->getAttribute(PDO::ATTR_CONNECTION_STATUS);
var_dump($status);
