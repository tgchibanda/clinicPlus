<?php

namespace App\Http\Controllers;

use App\Models\MonthlyCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class MonthlyConditionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Log::debug(__METHOD__ . ' bof');

        $monthlyCondition = MonthlyCondition::orderBy("id", "desc")->get();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "all monthlyCondition retrieved successfully.",
            "data" => $monthlyCondition
        ], 200);
    }

    /**
     * Get getMonthlyCondition.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMonthlyCondition()
    {
        Log::debug(__METHOD__ . ' bof');

        $monthlyCondition = MonthlyCondition::latest()->first();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "monthlyCondition retrieved successfully.",
            "data" => $monthlyCondition
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
        Log::debug(__METHOD__ . ' bof' );

        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
        ]);

        $monthlyCondition = MonthlyCondition::create([
            'title' => $request['title'],
            'description' => $request['description'],
        ]);
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "MonthlyCondition saved successfully.",
            "data" => $monthlyCondition
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getCondition()
    {
        Log::debug(__METHOD__ . ' bof');

        $monthlyCondition = MonthlyCondition::first();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "monthlyCondition retrieved successfully.",
            "data" => $monthlyCondition
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        Log::debug(__METHOD__ . ' bof');
        try {
            $monthlyCondition = MonthlyCondition::findOrFail($id);
            $monthlyCondition->delete();
        } catch (\Exception $exception) {
            Log::warning(__METHOD__ . ': Exception caught error code : '
                . $exception->getCode() . ' : ' . $exception->getMessage());
            return response()->json(['errors' => [$exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
        return response()->json([
            "success" => true,
            "message" => "Data delete successfully.",
            "data" => $monthlyCondition
        ], Response::HTTP_NO_CONTENT);

        Log::debug(__METHOD__ . ' eof');
    }
}
