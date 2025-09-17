<?php

namespace App\Http\Controllers;

use App\Models\Qualification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QualificationController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');

        $this->validate($request,[
            'user_id' => 'required|numeric|max:191',
            'institution_name' => 'required',
            'qualification_name' => 'required',
            'year_completed' => 'required|numeric',
            'upload' => 'required'
        ]);

        $qualification = Qualification::create([
            'user_id' => $request['user_id'],
            'institution_name' => $request['institution_name'],
            'qualification_name' => $request['qualification_name'],
            'year_completed' => $request['year_completed'],
            'upload' => $request['upload']
        ]);
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
        "success" => true,
        "message" => "Qualification created successfully.",
        "data" => $qualification
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

        $qualification = Qualification::Join('users', 'users.id', '=', 'qualifications.user_id')
        ->where('qualifications.id','=',$id)->first();

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "contact retrieved successfully.",
            "data" => $qualification
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
            'institution_name' => 'required',
            'qualification_name' => 'required',
            'year_completed' => 'required|numeric',
            'upload' => 'required'
        ]);

        if (Qualification::where('id', $id)->exists()) {
            $qualification = Qualification::find($id);
            $qualification->user_id = is_null($request->user_id) ? $qualification->user_id : $request->user_id;
            $qualification->institution_name = is_null($request->institution_name) ? $qualification->institution_name : $request->unit_number;
            $qualification->qualification_name = is_null($request->qualification_name) ? $qualification->qualification_name : $request->qualification_name;
            $qualification->year_completed = is_null($request->year_completed) ? $qualification->year_completed : $request->year_completed;
            $qualification->upload = is_null($request->upload) ? $qualification->upload : $request->upload;
            $qualification->save();

            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "qualification updated successfully.",
                "data" => $qualification
            ], 201);
            } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "qualification not found.",
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

        if(Qualification::where('id', $id)->exists()) {
            $qualification = Qualification::find($id);
            $qualification->delete();

            Log::debug(__METHOD__ . ' eof');
            return response()->json([
              "message" => "records deleted"
            ], 202);
          } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
              "message" => "Qualification not found"
            ], 404);
          }

    }
}
