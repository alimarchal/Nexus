<?php

namespace App\Policies;

use App\Models\FileManagementSystem;
use App\Models\User;

/**
 * Who may do what with a File Management record: the module's permissions, limited to the records the user's office
 * holds (a super admin sees every office) and to the stage the record is in. The controllers apply the same rules.
 */
class FileManagementSystemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view file management systems');
    }

    public function view(User $user, FileManagementSystem $fileManagementSystem): bool
    {
        return $user->can('view file management systems') && $this->canSee($user, $fileManagementSystem);
    }

    public function create(User $user): bool
    {
        return $user->can('create file management systems');
    }

    public function update(User $user, FileManagementSystem $fileManagementSystem): bool
    {
        return $user->can('edit file management systems') && $this->canSee($user, $fileManagementSystem) && $fileManagementSystem->isEditable();
    }

    public function delete(User $user, FileManagementSystem $fileManagementSystem): bool
    {
        return $user->can('delete file management systems') && $this->canSee($user, $fileManagementSystem) && $fileManagementSystem->isEditable();
    }

    /**
     * Send the record to another office: an approved record that is not in a box.
     */
    public function transfer(User $user, FileManagementSystem $fileManagementSystem): bool
    {
        return $user->can('transfer file management systems')
            && $this->canSee($user, $fileManagementSystem)
            && $fileManagementSystem->isReadyToMove()
            && ! $fileManagementSystem->isArchived();
    }

    /**
     * Put the record into, or take it out of, a box: only the office that holds it (a super admin for any office).
     */
    public function archive(User $user, FileManagementSystem $fileManagementSystem): bool
    {
        return $user->can('archive file management systems')
            && $this->canSee($user, $fileManagementSystem)
            && ($fileManagementSystem->isHeldBy($user) || FileManagementSystem::officeUnitOf($user) === null)
            && ($fileManagementSystem->isArchived() || $fileManagementSystem->isReadyToMove());
    }

    /**
     * Restoring and permanently deleting records are not offered in the module; only a super admin may.
     */
    public function restore(User $user, FileManagementSystem $fileManagementSystem): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function forceDelete(User $user, FileManagementSystem $fileManagementSystem): bool
    {
        return $this->isSuperAdmin($user);
    }

    private function canSee(User $user, FileManagementSystem $fileManagementSystem): bool
    {
        return FileManagementSystem::visibleTo($user)->whereKey($fileManagementSystem->getKey())->exists();
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->is_super_admin === 'Yes' || $user->hasRole('super-admin');
    }
}
