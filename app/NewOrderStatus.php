<?php

namespace App;

enum NewOrderStatus: string
{
    case Draft = 'Draft';
    case TeamLeadApproval = 'TeamLeadApproval';
    case DepartmentApproval = 'DepartmentApproval';
    case FinanceApproval = 'FinanceApproval';
    case ProcurementApproval = 'ProcurementApproval';
    case WarehouseApproval = 'WarehouseApproval';
    case Closed = 'Closed';
    case Rejected = 'Rejected';

    public function level(): int
    {
        return match($this) {
            self::Draft => 1,
            self::TeamLeadApproval => 2,
            self::DepartmentApproval => 3,
            self::FinanceApproval => 4,
            self::ProcurementApproval => 5,
            self::WarehouseApproval => 6,
            self::Closed => 7,
            self::Rejected => 0,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Draft',
            self::TeamLeadApproval => 'Team Lead Approval',
            self::DepartmentApproval => 'Department Approval',
            self::FinanceApproval => 'Finance Approval',
            self::ProcurementApproval => 'Procurement Approval',
            self::WarehouseApproval => 'Warehouse Approval',
            self::Closed => 'Closed',
            self::Rejected => 'Rejected',
        };
    }
}
