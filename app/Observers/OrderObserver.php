<?php

namespace App\Observers;

use App\OrderStatus;
use App\Models\ApprovalLog;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function creating(Order $order): void
    {
        // Default draft
        $order->status = $order->status ?? OrderStatus::Draft->value;
    }

    public function created(Order $order): void
    {
        $order->status = $order->status ?? OrderStatus::Draft->value;

        ApprovalLog::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_status' => 'Draft',
            'to_status' => $order->status,
            'action' => 'created',
            'comment' => request()->input('comment', null),
        ]);

        Log::channel('autolog')->info('Order created', [
            'order_id' => $order->id,
            'status' => $order->status,
            'user_id' => Auth::id() ?? 'unknown',
        ]);
    }

    public function updating(Order $order): void
    {
        if ($order->isDirty('status')) {
            $oldStatusValue = $order->getOriginal('status');
            $newStatusValue = $order->status instanceof \BackedEnum
                ? $order->status->value
                : (string) $order->status;

            $oldEnum = OrderStatus::tryFrom($oldStatusValue);
            $newEnum = OrderStatus::tryFrom($newStatusValue);

            $action = match (true) {
                $newEnum === OrderStatus::Rejected => 'rejected',
                $oldEnum && $newEnum && $this->isBackStatus($oldEnum, $newEnum) => 'returned',
                default => 'approved',
            };

            ApprovalLog::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'from_status' => $oldStatusValue,
                'to_status' => $newStatusValue,
                'action' => $action,
                'comment' => request()->input('comment'),
            ]);

            Log::channel('autolog')->info('Order status changed', [
                'order_id' => $order->id,
                'from' => $oldStatusValue,
                'to' => $newStatusValue,
                'user_id' => Auth::id(),
                'action' => $action,
            ]);
        }
    }

    protected function isBackStatus(OrderStatus $from, OrderStatus $to): bool
    {
        // Back status = səviyyə azalmışdırsa
        return $to->level() < $from->level();
    }
}