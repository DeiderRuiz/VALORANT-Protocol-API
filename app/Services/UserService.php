<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function editEmail(array $data){
        $user = auth()->user();

        $user->update([
            'email' => $data['email'],
        ]);

        return $user;
    }

    public function editPassword(array $data){
        $user = auth()->user();
        
        $user->update([
            'password' => $data['password'],
        ]);

        return $user;
    }
}