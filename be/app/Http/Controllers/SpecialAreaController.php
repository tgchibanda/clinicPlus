<?php

namespace App\Http\Controllers;

use App\Models\SpecialArea;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SpecialAreaController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');

        $this->validate($request,[
            'user_id' => 'required|numeric|max:191',
            'area_name' => 'required',
            'description' => 'required'
        ]);

        $specialarea = SpecialArea::create([
            'user_id' => $request['user_id'],
            'area_name' => $request['area_name'],
            'description' => $request['description']
        ]);
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
        "success" => true,
        "message" => "SpecialArea created successfully.",
        "data" => $specialarea
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

        $specialarea = SpecialArea::Join('users', 'users.id', '=', 'special_areas.user_id')
        ->where('special_areas.id','=',$id)->first();

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "specialarea retrieved successfully.",
            "data" => $specialarea
        ], 200);
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
        Log::debug(__METHOD__ . ' bof');

        $this->validate($request,[
            'user_id' => 'required|numeric|max:191',
            'area_name' => 'required',
            'description' => 'required'
        ]);

        if (SpecialArea::where('id', $id)->exists()) {
            $specialarea = SpecialArea::find($id);
            $specialarea->user_id = is_null($request->user_id) ? $specialarea->user_id : $request->user_id;
            $specialarea->area_name = is_null($request->area_name) ? $specialarea->area_name : $request->unit_number;
            $specialarea->description = is_null($request->description) ? $specialarea->description : $request->description;
            $specialarea->save();

            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "SpecialArea updated successfully.",
                "data" => $specialarea
            ], 201);
            } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "specialarea not found.",
            ], 404);
        }
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

        if(SpecialArea::where('id', $id)->exists()) {
            $qualification = SpecialArea::find($id);
            $qualification->delete();

            Log::debug(__METHOD__ . ' eof');
            return response()->json([
              "message" => "records deleted"
            ], 202);
          } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
              "message" => "SpecialArea not found"
            ], 404);
          }

    }
}
