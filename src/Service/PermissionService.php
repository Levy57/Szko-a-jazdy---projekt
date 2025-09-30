<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\Permission;
use Symfony\Bundle\SecurityBundle\Security;

class PermissionService
{
    public function __construct(
        private Security $security
    ) {
    }

    public function hasPermission(Permission $permission): bool
    {
        return $this->security->isGranted($permission->value);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    public function getUserPermissions(): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return [];
        }

        return $user->getRoles();
    }

    public function getAvailablePermissions(): array
    {
        return Permission::cases();
    }

    public function getPermissionsByCategory(): array
    {
        $permissionsByCategory = [];
        foreach (Permission::cases() as $permission) {
            $category = $permission->getCategory();
            $permissionsByCategory[$category][] = $permission;
        }
        return $permissionsByCategory;
    }
}
