<?php

namespace App\Policies;

use App\Models\StationeryTransaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StationeryTransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Allow authenticated users to view stationery transactions
        return $user->is_active === 'Yes';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StationeryTransaction $stationeryTransaction): bool
    {
        // Allow authenticated users to view individual stationery transactions and their attachments
        return $user->is_active === 'Yes';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StationeryTransaction $stationeryTransaction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StationeryTransaction $stationeryTransaction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StationeryTransaction $stationeryTransaction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StationeryTransaction $stationeryTransaction): bool
    {
        return false;
    }
}
