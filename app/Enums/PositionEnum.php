<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PositionEnum: string implements HasLabel
{
    // Top Management
    case DIRECTOR = 'Director';
    case VICE_DIRECTOR = 'Vice Director';
    case GENERAL_MANAGER = 'General Manager';

    // Middle Management
    case MANAGER = 'Manager';
    case ASSISTANT_MANAGER = 'Assistant Manager';
    case SUPERVISOR = 'Supervisor';
    case SECTION_HEAD = 'Section Head';
    case TEAM_LEADER = 'Team Leader';

    // Staff Level
    case SENIOR_STAFF = 'Senior Staff';
    case STAFF = 'Staff';
    case JUNIOR_STAFF = 'Junior Staff';
    case INTERN = 'Intern';

    // Specialized Positions
    case IT_MANAGER = 'IT Manager';
    case FINANCE_MANAGER = 'Finance Manager';
    case HR_MANAGER = 'HR Manager';
    case PROCUREMENT_MANAGER = 'Procurement Manager';
    case OPERATIONS_MANAGER = 'Operations Manager';
    case MARKETING_MANAGER = 'Marketing Manager';

    // Finance Specific
    case CHIEF_ACCOUNTANT = 'Chief Accountant';
    case ACCOUNTANT = 'Accountant';
    case FINANCE_ADMIN = 'Finance Admin';
    case TREASURER = 'Treasurer';
    case BUDGET_ANALYST = 'Budget Analyst';

    // IT Specific
    case IT_SUPERVISOR = 'IT Supervisor';
    case SYSTEM_ADMINISTRATOR = 'System Administrator';
    case DEVELOPER = 'Developer';
    case IT_SUPPORT = 'IT Support';

    // Admin
    case ADMIN = 'Admin';
    case SECRETARY = 'Secretary';
    case RECEPTIONIST = 'Receptionist';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
