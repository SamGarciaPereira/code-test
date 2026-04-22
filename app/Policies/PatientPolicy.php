<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PatientPolicy
{
    use HandlesAuthorization;

    // veterinários conseguem editar pacientes
    public function before(User $user, $ability) {
        if ($user->type === 'VET' && $ability !== 'delete') {
            return true;
        }
    }

    // o usuário só consegue editar se o ID dele bater com o dono do cachorro
    public function update(User $user, Patient $patient) {
        return $user->id === $patient->user_id;
    }

    // o usuário só exclui se for o dono
    public function delete(User $user, Patient $patient) {
        return $user->id === $patient->user_id;
    }
}
