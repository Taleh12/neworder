<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\NewOrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class neworderController extends Controller
{
    // Təsdiq əməliyyatı
    public function approve(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        try {
            $order->approve();

            return redirect()->back()->with('success', 'Sifariş təsdiqləndi.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Geri qaytarma əməliyyatı
    public function return(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $comment = $request->input('comment');

        if (empty(trim($comment))) {
            return redirect()->back()->with('error', 'Geri qaytarma üçün qeyd yazmalısınız.');
        }

        try {
            $order->return($comment);

            return redirect()->back()->with('success', 'Sifariş geri qaytarıldı.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Rədd etmə əməliyyatı
    public function reject(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $comment = $request->input('comment');

        if (empty(trim($comment))) {
            return redirect()->back()->with('error', 'Rədd etmək üçün qeyd yazmalısınız.');
        }

        try {
            $order->reject($comment);

            return redirect()->back()->with('success', 'Sifariş rədd edildi.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'brand_id' => 'required|exists:brands,id',
            'quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
            'expected_delivery_date' => 'required|date|after_or_equal:today',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        $attachmentPath = $request->hasFile('attachment')
            ? $request->file('attachment')->store('attachments', 'public')
            : null;

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'brand_id' => $request->brand_id,
            'quantity' => $request->quantity,
            'total_price' => $request->total_price,
            'notes' => $request->notes,
            'expected_delivery_date' => $request->expected_delivery_date,
            'attachment' => $attachmentPath,
            'status' => NewOrderStatus::Draft // Enum-un string dəyəri
        ]);

        if (!$order->canBeApprovedBy($user)) {
            return response()->json(['message' => 'Sifariş yaratmaq üçün icazəniz yoxdur'], 403);
        }

        return response()->json([
            'message' => 'Sifariş yaradıldı',
            'order' => $order->load(['user', 'product', 'brand']),
        ]);
    }

}
