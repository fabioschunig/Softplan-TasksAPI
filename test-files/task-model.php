<?php

require_once 'vendor/autoload.php';

$task = new \SoftplanTasksApi\Domain\Model\Task(
    1,
    'Test Task 1',
    new \DateTime(),
    new \DateTime('tomorrow'),
    new \DateTime('yesterday'),
    'Obs',
    'Issue 123',
);
var_dump($task);
