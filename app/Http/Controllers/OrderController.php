<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Jobs\SendOrderToThirdParty;

class OrderController extends Controller
{
    public function store(Request $request)
    {

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'order_value' => 'required|numeric|min:0',
        ]);

        try {
            // Create the order
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'order_value' => $validated['order_value'],
            ]);

            $order->save();

            // Dispatch the job
            SendOrderToThirdParty::dispatch($order);

            return response()->json([
                'order_id' => $order->id,
                'process_id' => rand(1, 10),
                'status' => 'Processing',
            ]);
        } catch (\Exception $e) {
            // Log the error
            //\Log::error('Order creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Order creation failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
