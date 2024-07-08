<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendOrderToThirdParty implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        try {
            $response = Http::post('https://wibip.free.beeceptor.com/order', [
                'Order_ID' => $this->order->id,
                'Customer_Name' => $this->order->customer_name,
                'Order_Value' => $this->order->order_value,
                'Order_Date' => now()->toDateTimeString(),
                'Order_Status' => 'Processing',
                'Process_ID' => rand(1, 10),
            ]);

            // Optionally log the response or handle it
            //Log::info('Third-party API response: ', ['response' => $response->body()]);
            print("Success. $response");
        } catch (\Exception $e) {
            print("Error");

            // Log the error
            //Log::error('Failed to send order to third-party API: ' . $e->getMessage());
        }
    }
}
