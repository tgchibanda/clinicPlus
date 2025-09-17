<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Options;
use App\Models\Payment as ModelsPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** Paypal Details classes **/

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use App\Domains\Emails;
use App\Domains\Patient;
use PayPal\Exception\PayPalConnectionException;
use Exception;
use App\Models\User;

class PaymentController extends Controller
{
    private $api_context;

    public function __construct()
    {
        $this->api_context = new ApiContext(new OAuthTokenCredential(config('paypal.client_id'), config('paypal.secret')));
        $this->api_context->setConfig(config('paypal.settings'));
    }

    /** This method sets up the paypal payment.
     *
     */
    public function payWithpaypal(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');

        $consultationFee = Options::where('name', '=', 'consultation_fee')->first();
        $consultationAmount = (float)$consultationFee->value;

        //Setup Payer
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        //Setup Amount
        $amount = new Amount();
        $amount->setCurrency('USD');
        $amount->setTotal($consultationAmount);
        //Setup Transaction
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('Consultation Fee');
        //List redirect URLS
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($request->return_url);
        $redirectUrls->setCancelUrl($request->return_url);
        //And finally set all the prerequisites and create the
        $payment = new Payment();

        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setRedirectUrls($redirectUrls);
        $payment->setTransactions(array(
            $transaction
        ));

        try {
            $response = $payment->create($this->api_context);
            ModelsPayment::create([
                'user_id' => $request['user_id'],
                'patient_id' => $request['patient_id'],
                'consultation_id' => $request['consultation_id'],
                'amount' => $consultationAmount,
                'order_number' => $response->id,
                'status' => 'order_created',
            ]);
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            Log::error(__METHOD__ . ' PayPalConnectionException' . print_r($ex, true));
            $sendEmail = new Emails();
            $sendEmail->sendEmail(env('ADMIN_EMAIL', 'admin@clinicPluszimbabwe.com'), $ex, 'clinicPlus Payment Error');

            return response()->json([
                "success" => true,
                "message" => "Payment failed.",
                "data" => $ex
            ], 400);
        }

        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }
        $data = ['id' => $response->id, 'url' => $redirect_url];
        //Return our payment info to the user
        Log::debug(__METHOD__ . ' bof');

        return $data;
    }

    public function getPaymentStatus(Request $request)
    {
        Log::debug(__METHOD__ . ' eof');
        /** Get the payment ID before session clear **/
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('payerID');
        $sendEmail = new Emails();
        $patient = new Patient();

        $payment = Payment::get($paymentId, $this->api_context);
        $paymentExecution = new PaymentExecution();
        $paymentExecution->setPayerId($payerId);

        try {
            $executePayment = $payment->execute($paymentExecution, $this->api_context);
            if ($executePayment->getState() == 'approved') {
                if ($executePayment->transactions[0]->related_resources[0]->sale->state == 'completed') {
                    $payments = ModelsPayment::where('order_number', $paymentId)->update(['status' => 'paid']);
                    $consultation = Consultation::join('payments', 'payments.consultation_id', '=', 'consultations.id')
                        ->where('payments.order_number', $paymentId)->update(['consultations.status' => 2]);

                    $userRole = User::select('*')
                        ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
                        ->where('role', '=', 'doctor')
                        ->get();

                    $paymentData = Consultation::select('amount', 'fullname', 'payments.user_id', 'email')
                        ->join('patient_details', 'consultations.patient_id', '=', 'patient_details.id')
                        ->join('payments', 'payments.consultation_id', '=', 'consultations.id')
                        ->where('payments.order_number', $paymentId)
                        ->get();

                    //Send email to
                    $emailMsg = "Thank you so much for your payment of $ {$paymentData[0]->amount} for
                    {$paymentData[0]->fullname}. We will notify you once a doctor is ready
                    to see the patient";

                    $userEmail = $sendEmail->getEmail($paymentData[0]->user_id, 'user');
                    $sendEmail->sendEmail($userEmail, $emailMsg, 'clinicPlus Consultation Confirmation');
                    $sendEmail->sendEmail($paymentData[0]->email, $emailMsg, 'clinicPlus Consultation Confirmation');
                    $sendEmail->sendEmail(env('ADMIN_EMAIL', 'admin@clinicPluszimbabwe.com'), $emailMsg, 'clinicPlus Consultation Confirmation');

                    Log::debug(__METHOD__ . ' Sending emails to all doctors');
                    foreach ($userRole as $value) {
                        $emailMsg = "A new consultation was created  for
                        {$paymentData[0]->fullname} in {$paymentData[0]->city}";

                        $sendEmail->sendEmail($value->email, $emailMsg, 'clinicPlus Consultation Created');
                    }

                    Log::debug(__METHOD__ . ' bof');
                    return response()->json([
                        "success" => true,
                        "message" => "Payment successfully.",
                        "data" => $consultation
                    ], 200);
                }
            }
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            Log::error(__METHOD__ . ' PayPalConnectionException' . print_r($ex, true));
            $sendEmail = new Emails();
            $sendEmail->sendEmail(env('ADMIN_EMAIL', 'admin@clinicPluszimbabwe.com'), $ex, 'clinicPlus Payment Error');

            return response()->json([
                "success" => true,
                "message" => "Payment failed.",
                "data" => $ex
            ], 400);
        }
        Log::debug(__METHOD__ . ' bof');
        return response()->json([
            "success" => true,
            "message" => "Payment failed.",
            "data" => "Failed"
        ], 400);
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
        $payments = [];
        if ($id != 'admin') {
            $payments = ModelsPayment::select(
                'payments.order_number',
                'payments.status',
                'payments.amount',
                'patient_details.fullname',
                'patient_details.email',
                'patient_details.mobile_no',
                'payments.created_at',
                'payments.id as payment_id'
            )
                ->join('patient_details', 'patient_details.id', '=', 'payments.patient_id')
                ->where('payments.user_id', '=', $id)
                ->get();
        }
        if ($id == 'admin') {
            $payments = ModelsPayment::select(
                'payments.order_number',
                'payments.status',
                'payments.amount',
                'patient_details.fullname',
                'patient_details.email',
                'patient_details.mobile_no',
                'payments.created_at',
                'payments.id as payment_id'
            )
                ->join('patient_details', 'patient_details.id', '=', 'payments.patient_id')
                ->orderBy("payments.created_at", "desc")
                ->get();
        }

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "payments retrieved successfully.",
            "data" => $payments
        ], 200);
    }

    /**
     * getDoctorPayments
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getDoctorPayments($id)
    {
        Log::debug(__METHOD__ . ' bof');

        $payments = ModelsPayment::select(
            'payments.order_number',
            'payments.status',
            'payments.amount',
            'patient_details.fullname',
            'patient_details.email',
            'patient_details.mobile_no',
            'payments.created_at',
            'payments.id as payment_id'
        )
            ->join('consultations', 'consultations.id', '=', 'payments.consultation_id')
            ->join('patient_details', 'patient_details.id', '=', 'payments.patient_id')
            ->where('consultations.doctor_id', '=', $id)
            ->get();

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "doctor payments retrieved successfully.",
            "data" => $payments
        ], 200);
    }
}
