<?php

namespace App\Services\HR;

use App\Enums\Tiers\TierType;
use App\Models\HR\Employee;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Notifications\HR\EmployeeCreateUserNotification;

class EmployeeService
{
    public function create(array $data, string $email): Employee
    {
        $employee = Employee::create($data);
        $passwordCase = \Str::random(10);


        $user = $this->createUser($employee, $passwordCase, $email);
        $user->notify(new EmployeeCreateUserNotification($employee, $passwordCase));

        return $employee;
    }

    private function createUser(Employee $employee, string $passwordCase, string $email): User
    {
        $user = User::create([
            'name' => $employee->full_name,
            'email' => $email,
            'password' => \Hash::make($passwordCase),
        ]);

        $tiers = Tiers::create([
            'type_entite' => TierType::Employee,
            'nom' => $employee->first_name,
            'prenom' => $employee->last_name,
            'email' => $user->email,
        ]);

        $user->update(['tiers_id' => $tiers->id]);

        $tiers->types()->create([
            'tiers_id' => $tiers->id,
            'type' => 'employee',
        ]);

        return $user;
    }
}
