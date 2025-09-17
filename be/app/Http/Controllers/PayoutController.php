<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Domains\Fees;

class PayoutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Log::debug(__METHOD__ . ' bof');
        $payments = Payment::select(
            'payments.amount',
            'payments.*',
            'payments.id as payment_id',
            'users.name',
            'users.email',
        )
            ->join('consultations', 'consultations.id', '=', 'payments.consultation_id')
            ->join('users', 'users.id', '=', 'consultations.doctor_id')
            ->where('payments.status', '=', 'paid')
            ->get();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "payouts retrieved successfully.",
            "data" => $payments
        ], 201);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPayouts()
    {
        Log::debug(__METHOD__ . ' bof');
        $payouts = Payout::select(
            'payouts.*',
        )
        ->orderBy("payouts.id", "desc")
        ->get();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "payouts retrieved successfully.",
            "data" => $payouts
        ], 201);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');

        $payoutID = array();
        $amountGross = 0;
        foreach ($request['payouts'] as $value) {
            $payoutID[] = $value['id'];
            $amountGross += $value['amount'];
        }

        $fees = new Fees();
        $calcFees = $fees->GenerateFees($amountGross);

        $ids = implode(',', $payoutID);
        $payout = Payout::create([
            'user_id' => 1,
            'payout_batch' => $ids,
            'amount_gross' => $calcFees['amount_gross'],
            'fees' => $calcFees['fees'],
            'amount_net' => $calcFees['amount_net'],
            'status' => 'paid'
        ]);
        if (!empty($payoutID)) {
            DB::update("UPDATE payments SET `status` = 'payout', `payout_id` = $payout->id WHERE `id` in ({$ids})");
        }
        //Send email notifing doctors of payouts

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "payout saved successfully.",
            "data" => $payout
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        Log::debug(__METHOD__ . ' bof');
        $payments = Payout::selectRaw(
            'sum(payouts.amount_net) as sum_amount_net,
            sum(payouts.amount_gross) as sum_amount_gross,
            sum(payouts.fees) as sum_fees,
            users.id,
            users.name,
            users.email'
        )
            ->join('payments', 'payments.payout_id', '=', 'payouts.id')
            ->join('consultations', 'consultations.id', '=', 'payments.consultation_id')
            ->join('users', 'users.id', '=', 'consultations.doctor_id')
            ->where('payouts.id', '=', $id)
            ->groupby('users.id')
            ->get();
        // ->sum("payouts.amount_net");
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "payouts retrieved successfully.",
            "data" => $payments
        ], 201);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
