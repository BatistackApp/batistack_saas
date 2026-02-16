<?php

namespace App\Observers\HR;

use App\Models\HR\EmployeeSkill;
use Storage;

class EmployeeSkillObserver
{
    public function updated(EmployeeSkill $employeeSkill): void
    {
        if ($employeeSkill->wasChanged('document_path')) {
            $oldPath = $employeeSkill->getOriginal('document_path');

            if ($oldPath && Storage::exists($oldPath)) {
                Storage::delete($oldPath);
            }
        }
    }

    public function deleted(EmployeeSkill $employeeSkill): void
    {
        if ($employeeSkill->document_path && Storage::exists($employeeSkill->document_path)) {
            Storage::delete($employeeSkill->document_path);
        }
    }

    public function forceDeleted(EmployeeSkill $employeeSkill): void
    {
        if ($employeeSkill->document_path && Storage::exists($employeeSkill->document_path)) {
            Storage::delete($employeeSkill->document_path);
        }
    }
}
