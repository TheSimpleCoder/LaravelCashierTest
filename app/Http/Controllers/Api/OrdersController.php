<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetOrdersRequest;
use App\Models\Order;

class OrdersController extends Controller
{
    public function orders(GetOrdersRequest $request)
    {
        $order = Order::create([
            ...$request->validated(),
            'status' => 'pending',
        ]);

        return response()->json([
            'order_id' => $order->id,
        ], 201);
    }
}
