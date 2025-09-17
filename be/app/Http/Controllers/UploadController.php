<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
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
            'upload_name' => 'required',
            'description' => 'required',
            'upload' => 'required',
        ]);

        $upload = Upload::create([
            'user_id' => $request['user_id'],
            'upload_name' => $request['upload_name'],
            'description' => $request['description'],
            'upload' => $request['upload']
        ]);
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Upload created successfully.",
            "data" => $upload
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

        $upload = Upload::Join('users', 'users.id', '=', 'special_areas.user_id')
        ->where('special_areas.id','=',$id)->first();

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "specialarea retrieved successfully.",
            "data" => $upload
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
            'upload_name' => 'required',
            'description' => 'required'
        ]);

        if (Upload::where('id', $id)->exists()) {
            $upload = Upload::find($id);
            $upload->user_id = is_null($request->user_id) ? $upload->user_id : $request->user_id;
            $upload->upload_name = is_null($request->upload_name) ? $upload->upload_name : $request->unit_number;
            $upload->description = is_null($request->description) ? $upload->description : $request->description;
            $upload->upload = is_null($request->upload) ? $upload->upload : $request->upload;
            $upload->save();

            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "upload updated successfully.",
                "data" => $upload
            ], 201);
            } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "upload not found.",
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

        if(Upload::where('id', $id)->exists()) {
            $upload = Upload::find($id);
            $upload->delete();

            Log::debug(__METHOD__ . ' eof');
            return response()->json([
              "message" => "records deleted"
            ], 202);
          } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
              "message" => "Upload not found"
            ], 404);
          }

    }}
