<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\OrderStatus;
use Illuminate\Support\Facades\Auth;
use App\Models\ApprovalLog;
use Spatie\Permission\Traits\HasRoles;

class Order extends Model
{


     protected $fillable = [
        'user_id',
        'product_id',
        'brand_id',
        'quantity',
        'total_price',
        'notes',
        'expected_delivery_date',
        'attachment',
        'status',
    ];


    // Statusu enum olaraq cast et
    protected $casts = [
        'status' => OrderStatus::class,
    ];

    // Status-rol mapping (hardcoded nümunə, lazım olsa config-ə köçür)
    protected array $statusRoleMap = [
        OrderStatus::Draft->value => 'Worker',
        OrderStatus::TeamLeadApproval->value => 'Team Lead',
        OrderStatus::DepartmentApproval->value => 'Department Head',
        OrderStatus::FinanceApproval->value => 'Finance',
        OrderStatus::ProcurementApproval->value => 'Procurement',
        OrderStatus::WarehouseApproval->value => 'Warehouse',
    ];

    // Cari mərhələnin rolunu qaytarır
    public function getCurrentStageRole(): ?string
    {
        return $this->statusRoleMap[$this->status->value] ?? null;
    }

    // İstifadəçinin təsdiq vermək icazəsi var mı?
    public function canApprove(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        $requiredRole = $this->getCurrentStageRole();

        if (!$requiredRole) {
            return false;
        }

        return $user->hasRole($requiredRole);
    }

    // Təsdiqləmə əməliyyatı
    public function approve(): bool
    {
        if (!$this->canApprove()) {
            abort(403, 'Sənin təsdiq vermək icazən yoxdur.');
        }

        if (in_array($this->status, [OrderStatus::Closed, OrderStatus::Rejected])) {
            return false; // artıq bitmiş və ya rədd edilmiş
        }

        $nextStatus = $this->getNextStatus();

        if ($nextStatus === null) {
            $this->status = OrderStatus::Closed;
        } else {
            $this->status = $nextStatus;
        }

        $this->save();



        return true;
    }

    // Geri qaytarma əməliyyatı
    public function return(string $comment): bool
    {
        if (!$this->canApprove()) {
            abort(403, 'Sənin geri qaytarmaq icazən yoxdur.');
        }

        if ($this->status === OrderStatus::Draft) {
            return false; // Draft mərhələsindən geri qaytarmaq olmaz
        }

        $previousStatus = $this->getPreviousStatus();

        if ($previousStatus === null) {
            return false;
        }

        $oldStatus = $this->status;
        $this->status = $previousStatus;
        $this->save();



        return true;
    }

    // Rədd etmə əməliyyatı
    public function reject(string $comment): bool
    {
        if (!$this->canApprove()) {
            abort(403, 'Sənin rədd etmək icazən yoxdur.');
        }

        if (empty(trim($comment))) {
            abort(400, 'Rədd etmə üçün qeyd vacibdir.');
        }

        $oldStatus = $this->status;
        $this->status = OrderStatus::Rejected;
        $this->save();



        return true;
    }

    // Növbəti mərhələni tapır
    protected function getNextStatus(): ?OrderStatus
    {
        $all = [
            OrderStatus::Draft,
            OrderStatus::TeamLeadApproval,
            OrderStatus::DepartmentApproval,
            OrderStatus::FinanceApproval,
            OrderStatus::ProcurementApproval,
            OrderStatus::WarehouseApproval,
            OrderStatus::Closed,
        ];

        $pos = array_search($this->status, $all);

        return ($pos !== false && isset($all[$pos + 1])) ? $all[$pos + 1] : null;
    }

    // Əvvəlki mərhələni tapır
    protected function getPreviousStatus(): ?OrderStatus
    {
        $all = [
            OrderStatus::Draft,
            OrderStatus::TeamLeadApproval,
            OrderStatus::DepartmentApproval,
            OrderStatus::FinanceApproval,
            OrderStatus::ProcurementApproval,
            OrderStatus::WarehouseApproval,
            OrderStatus::Closed,
        ];

        $pos = array_search($this->status, $all);

        return ($pos !== false && $pos > 0) ? $all[$pos - 1] : null;
    }

   public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

     public function brand()
    {
        return $this->belongsTo(Product::class);
    }
}