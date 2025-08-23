<?php

namespace App\Policies;

use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PemesananPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Admin', 'Pegawai Gudang', 'Manajer']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pemesanan $pemesanan): bool
    {
        return $user->hasRole(['Admin', 'Pegawai Gudang', 'Manajer']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Admin', 'Pegawai Gudang']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pemesanan $pemesanan): bool
    {
        // Manajer bisa mengupdate status (approve/reject)
        // Admin dan Pegawai Gudang hanya bisa mengupdate jika status masih pending
        if ($user->hasRole('Manajer')) {
            return true;
        }
        
        if ($user->hasRole(['Admin', 'Pegawai Gudang'])) {
            return $pemesanan->status === 'pending';
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pemesanan $pemesanan): bool
    {
        return $user->hasRole('Admin') && $pemesanan->status === 'pending';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pemesanan $pemesanan): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pemesanan $pemesanan): bool
    {
        return $user->hasRole('Admin');
    }
}
