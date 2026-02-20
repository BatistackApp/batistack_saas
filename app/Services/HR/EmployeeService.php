<?php

namespace App\Services\HR;

use App\Enums\Tiers\TierType;
use App\Models\HR\Employee;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Notifications\HR\EmployeeCreateUserNotification;
use App\Services\Tiers\TierCodeGenerator;

class EmployeeService
{
    public function create(array $data, string $email, string $tiers_type): Employee
    {
        $data['tenants_id'] = auth()->user()->tenants_id;
        $employee = Employee::create($data);
        $passwordCase = \Str::random(10);

        $user = $this->createUser($employee, $passwordCase, $email, $tiers_type);
        $user->notify(new EmployeeCreateUserNotification($employee, $passwordCase));

        return $employee;
    }

    private function createUser(Employee $employee, string $passwordCase, string $email, string $tiers_type): User
    {
        $user = User::create([
            'name' => $employee->full_name,
            'email' => $email,
            'password' => \Hash::make($passwordCase),
            'tenants_id' => auth()->user()->tenants_id,
        ]);

        $tiers = Tiers::create([
            'type_entite' => TierType::Employee,
            'nom' => $employee->first_name,
            'prenom' => $employee->last_name,
            'email' => $user->email,
            'tenants_id' => $user->tenants_id,
            'code_tiers' => app(TierCodeGenerator::class)->generate(TierType::Employee),
        ]);

        $user->update(['tiers_id' => $tiers->id]);

        $tiers->types()->create([
            'tiers_id' => $tiers->id,
            'type' => $tiers_type,
        ]);

        return $user;
    }
}
