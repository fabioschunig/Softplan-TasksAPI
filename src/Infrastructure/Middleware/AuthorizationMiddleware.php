<?php

namespace SoftplanTasksApi\Infrastructure\Middleware;

use SoftplanTasksApi\Domain\Model\User;

class AuthorizationMiddleware
{
    public static function requireAdmin(User $user): bool
    {
        return $user->isAdmin();
    }

    public static function requireUser(User $user): bool
    {
        return $user->isUser() || $user->isAdmin();
    }

    public static function canManageUsers(User $user): bool
    {
        return $user->isAdmin();
    }

    public static function canCreateProject(User $user): bool
    {
        return $user->isAdmin();
    }

    public static function canEditProject(User $user): bool
    {
        return $user->isAdmin();
    }

    public static function canDeleteProject(User $user): bool
    {
        return $user->isAdmin();
    }

    public static function canCreateTask(User $user): bool
    {
        return $user->isAdmin();
    }

    public static function canEditTask(User $user): bool
    {
        return $user->isAdmin();
    }

    public static function canDeleteTask(User $user): bool
    {
        return $user->isAdmin();
    }

    public static function canViewReports(User $user): bool
    {
        return true; // Both admin and user can view reports
    }

    public static function canViewProjects(User $user): bool
    {
        return true; // Both admin and user can view projects
    }

    public static function canViewTasks(User $user): bool
    {
        return true; // Both admin and user can view tasks
    }
}
