<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $roles = DB::table('roles')->pluck('id', 'name');

        DB::table('users')->upsert([
            [
                'email' => 'admin@evolyx.local',
                'email_verified_at' => $now,
                'pseudo' => 'admin',
                'password' => Hash::make('password'),
                'first_name' => 'Admin',
                'sex' => 'other',
                'height' => 175,
                'activity_level' => 'active',
                'birth_date' => '1990-01-15',
                'role_id' => $roles['admin'],
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'email' => 'demo@evolyx.local',
                'email_verified_at' => $now,
                'pseudo' => 'lina',
                'password' => Hash::make('password'),
                'first_name' => 'Lina',
                'sex' => 'female',
                'height' => 168,
                'activity_level' => 'moderate',
                'birth_date' => '1996-05-22',
                'role_id' => $roles['user'],
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ], ['email'], [
            'pseudo',
            'email_verified_at',
            'password',
            'first_name',
            'sex',
            'height',
            'activity_level',
            'birth_date',
            'role_id',
            'updated_at',
            'deleted_at',
        ]);
    }
}
