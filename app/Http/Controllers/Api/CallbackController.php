<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CallbackRequest;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CallbackController extends Controller
{
    public function __invoke(CallbackRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {

            $order = Order::whereKey($data['order_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $this->toMinorUnits($order->amount) !==
                $this->toMinorUnits($data['amount']) ||
                strtoupper($order->currency) !== strtoupper($data['currency'])
            ) {
                throw ValidationException::withMessages([
                    'amount' => ['Order amount or currency mismatch.'],
                ]);
            }

            $merchant = DB::table('merchants')
                ->where('id', $order->merchant_id)
                ->lockForUpdate()
                ->first();

            if (!$merchant) {
                throw ValidationException::withMessages([
                    'merchant_id' => ['Merchant not found.'],
                ]);
            }

            $inserted = DB::table('callback_events')->insertOrIgnore([
                'event_id' => $data['event_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!$inserted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event already processed.',
                    'order_id' => $order->id,
                    'status' => $order->status,
                ]);
            }

            $ledgerAmount = null;

            switch ($data['status']) {
                case 'paid':

                    if ($order->status === 'pending' || $order->status === 'failed') {
                        $ledgerAmount = $order->amount;
                        $order->status = 'paid';
                    }

                    break;

                case 'failed':

                    if ($order->status === 'pending') {
                        $order->status = 'failed';
                    }

                    break;

                case 'refunded':

                    if ($order->status !== 'paid') {
                        throw ValidationException::withMessages([
                            'status' => ['Only paid orders can be refunded.'],
                        ]);
                    }

                    $balance = DB::table('merchant_ledger')
                        ->where('merchant_id', $order->merchant_id)
                        ->where('currency', $order->currency)
                        ->sum('amount');

                    if (
                        $this->toMinorUnits($balance) <
                        $this->toMinorUnits($order->amount)
                    ) {
                        throw ValidationException::withMessages([
                            'amount' => ['Insufficient merchant balance.'],
                        ]);
                    }

                    $ledgerAmount = -$this->toMinorUnits($order->amount) / 100;
                    $order->status = 'refunded';

                    break;
            }

            $order->save();

            if ($ledgerAmount !== null) {
                DB::table('merchant_ledger')->insert([
                    'merchant_id' => $order->merchant_id,
                    'order_id' => $order->id,
                    'event_id' => $data['event_id'],
                    'type' => $data['status'],
                    'amount' => $ledgerAmount,
                    'currency' => strtoupper($order->currency),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'status' => $order->status,
            ]);
        });
    }

    private function toMinorUnits($amount): int
    {
        $amount = number_format((float) $amount, 2, '.', '');

        [$whole, $fraction] = explode('.', $amount);

        return ((int) $whole * 100) + (int) $fraction;
    }
}
