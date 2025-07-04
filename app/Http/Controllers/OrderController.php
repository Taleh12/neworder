<?php

namespace App\Http\Controllers;

use App\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:Team Lead|Department Head|Finance|Procurement|Warehouse'])->only(['approve', 'reject']);
    }

    /**
     * Display a listing of orders.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $orders = Order::with(['user', 'product', 'brand'])->get();
        return response()->json($orders);
    }

    /**
     * Store a new order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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
            'status' => OrderStatus::DRAFT->value, // Enum-un string dəyəri
        ]);

        if (!$order->canBeApprovedBy($user)) {
            return response()->json(['message' => 'Sifariş yaratmaq üçün icazəniz yoxdur'], 403);
        }

        return response()->json([
            'message' => 'Sifariş yaradıldı',
            'order' => $order->load(['user', 'product', 'brand']),
        ]);
    }

    /**
     * Display the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order)
    {
        return response()->json($order->load(['user', 'product', 'brand']));
    }

    /**
     * Approve the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(Order $order)
    {
        $user = Auth::user();

        if ($order->approve($user)) {
            return response()->json(['message' => 'Sifariş təsdiqləndi', 'status' => $order->status->value]);
        }

        return response()->json(['message' => 'Təsdiq icazəniz yoxdur'], 403);
    }

    /**
     * Reject the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Order $order)
    {
        $user = Auth::user();

        if (!$order->canBeApprovedBy($user)) {
            return response()->json(['message' => 'Rədd etmə icazəniz yoxdur'], 403);
        }

        $order->reject();

        return response()->json(['message' => 'Sifariş rədd edildi', 'status' => $order->status->value]);
    }

    /**
     * Show the form for editing the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Order $order)
    {
        // Implementasiya əlavə edilə bilər
        return response()->json(['message' => 'Edit funksiyası hələ tətbiq edilməyib']);
    }

    /**
     * Update the specified order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Order $order)
    {
        // Implementasiya əlavə edilə bilər
        return response()->json(['message' => 'Update funksiyası hələ tətbiq edilməyib']);
    }

    /**
     * Remove the specified order from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Order $order)
    {
        // Implementasiya əlavə edilə bilər
        return response()->json(['message' => 'Delete funksiyası hələ tətbiq edilməyib']);
    }
}
