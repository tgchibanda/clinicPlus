<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Domains\Emails;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Log::debug(__METHOD__ . ' bof');

        $feedback = Feedback::select('feedback.*', 'users.name')
            ->join('users', 'users.id', '=', 'feedback.user_id')->get();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Feedback retrieved successfully.",
            "data" => $feedback
        ], 200);
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

        $this->validate($request, [
            'user_id' => 'required|numeric|max:191',
            'feedback' => 'required',
        ]);

        $feedback = Feedback::create([
            'user_id' => $request['user_id'],
            'feedback' => $request['feedback'],
        ]);

        $sendEmail = new Emails();
        $sendEmail->sendEmail(
            $sendEmail->getEmail($request['user_id'], 'user'),
            "Feedback {$request['feedback']}",
            'Feedback Created'
        );
        $sendEmail->sendEmail(
            env('ADMIN_EMAIL', 'admin@clinicPluszimbabwe.com'),
            "Feedback {$request['feedback']}",
            'Feedback Created'
        );

        return response()->json([
            "success" => true,
            "message" => "feedback saved successfully.",
            "data" => $feedback
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

        $feedback = Feedback::select('feedback.*', 'users.name')
            ->join('users', 'users.id', '=', 'patient_details.user_id')
            ->where('feeback.id', '=', $id)->get();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Feedback retrieved successfully.",
            "data" => $feedback
        ], 200);
    }
}
