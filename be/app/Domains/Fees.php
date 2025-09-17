<?php

namespace App\Domains;

class Fees
{
    private $fee = 0.35;
    public function GenerateFees($amountGross)
    {
        $fees = $amountGross * $this->fee;
        $amountNet = $amountGross - $fees;

        return [
            'amount_gross' => $amountGross,
            'fees' => $fees,
            'amount_net' => $amountNet
        ];
    }

    public function convertAmount($amount, $type)
    {
        if ($type == 'toDecimal') {
            return $amount / 100;
        } else {
            return $amount * 100;
        }
    }
}
