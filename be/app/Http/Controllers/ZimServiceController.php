<?php

namespace App\Http\Controllers;

use App\Models\ZimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZimServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Log::debug(__METHOD__ . ' bof');
       
        $zimService = ZimService::get();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Service retrieved successfully.",
            "data" => $zimService
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'name' => 'required|max:191',
            'address' => 'required',
            'mobile_no' => 'required',
            'landline' => 'landline',
            'additional_contacts' => 'required',
            'facebook' => 'required',
            'twitter' => 'required',
            'instagram' => 'required',
            'type' => 'required'
        ]);

        $zimService = ZimService::create([
            'name' => $request['name'],
            'address' => $request['address'],
            'mobile_no' => $request['mobile_no'],
            'landline' => $request['landline'],
            'additional_contacts' => $request['additional_contacts'],
            'city' => $request['city'],
            'mobile_no' => $request['mobile_no'],
            'facebook' => $request['facebook'],
            'twitter' => $request['twitter'],
            'instagram' => $request['instagram'],
            'type' => $request['type']
        ]);

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Service saved successfully.",
            "data" => $zimService
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
        //
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
        Log::debug(__METHOD__ . ' bof');

        $this->validate($request, [
            'name' => 'required|max:191',
            'address' => 'required',
            'mobile_no' => 'required',
            'landline' => 'landline',
            'additional_contacts' => 'required',
            'facebook' => 'required',
            'twitter' => 'required',
            'instagram' => 'required',
            'gps' => 'required'
        ]);


        if (ZimService::where('id', $id)->exists()) {
            $zimService = ZimService::find($id);
            $zimService->name = is_null($request->name) ? $zimService->name : $request->name;
            $zimService->address = is_null($request->address) ? $zimService->address : $request->address;
            $zimService->mobile_no = is_null($request->mobile_no) ? $zimService->mobile_no : $request->mobile_no;
            $zimService->landline = is_null($request->landline) ? $zimService->landline : $request->landline;
            $zimService->additional_contacts = is_null($request->additional_contacts) ? $zimService->additional_contacts : $request->additional_contacts;
            $zimService->facebook = is_null($request->facebook) ? $zimService->facebook : $request->facebook;
            $zimService->twitter = is_null($request->twitter) ? $zimService->twitter : $request->twitter;
            $zimService->type = is_null($request->type) ? $zimService->type : $request->type;
            $zimService->instagram = is_null($request->instagram) ? $zimService->instagram : $request->instagram;
            $zimService->save();

            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "zimService updated successfully.",
                "data" => $zimService
            ], 201);
        } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "zimService not found.",
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
        //
    }
}
