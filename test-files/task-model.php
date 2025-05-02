<?php

require_once 'vendor/autoload.php';

$task = new \SoftplanTasksApi\Domain\Model\Task(
    1,
    'Test Task 1',
    'to-do,urgent',
    1,
    new \DateTime(),
    new \DateTime('tomorrow'),
    0,
    new \DateTime('yesterday'),
    new \DateTime('yesterday-3days'),
);
var_dump($task);
