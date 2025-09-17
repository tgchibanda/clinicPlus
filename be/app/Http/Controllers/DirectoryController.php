<?php

namespace App\Http\Controllers;

use App\Models\ZimService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DirectoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Log::debug(__METHOD__ . ' bof');
        try {
            $directory = ZimService::select('*')->get();
            Log::debug(__METHOD__ . ' eof');
        } catch (\Exception $exception) {
            Log::warning(__METHOD__ . ': Exception caught error code : '
                . $exception->getCode() . ' : ' . $exception->getMessage());
            return response()->json(['errors' => [$exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
        return response()->json([
            "success" => true,
            "message" => "directory retrieved successfully.",
            "data" => $directory
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

        $this->validate($request,[
            'type' => 'required|max:191',
            'name' => 'required|max:191',
            'mobile_no' => 'required',
        ]);
        try {
            $directory = ZimService::updateOrCreate([
                'name' => $request['name'],
                'address' => $request['address'],
                'mobile_no' => $request['mobile_no'],
                'landline' => $request['landline'],
                'additional_contacts' => $request['additional_contacts'],
                'facebook' => $request['facebook'],
                'twitter' => $request['twitter'],
                'instagram' => $request['instagram'],
                'type' => $request['type'],
            ]);
        } catch (\Exception $exception) {
            Log::warning(__METHOD__ . ': Exception caught error code : '
                . $exception->getCode() . ' : ' . $exception->getMessage());
            return response()->json(['errors' => [$exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Data saved successfully.",
            "data" => $directory
            ], Response::HTTP_CREATED);
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
        ]);

        try {
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
                    "message" => "Data updated successfully.",
                    "data" => $zimService
                ], Response::HTTP_CREATED);
            } else {
                Log::debug(__METHOD__ . ' eof');
                return response()->json([
                    "success" => true,
                    "message" => "Data not found.",
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $exception) {
            Log::warning(__METHOD__ . ': Exception caught error code : '
                . $exception->getCode() . ' : ' . $exception->getMessage());
            return response()->json(['errors' => [$exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
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

        $directory = ZimService::select('*')->where('type', '=', $id)->get();

        return response()->json([
            "success" => true,
            "message" => "directory retrieved successfully.",
            "data" => $directory
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
            $ZimService = ZimService::findOrFail($id);
            $ZimService->delete();
        } catch (\Exception $exception) {
            Log::warning(__METHOD__ . ': Exception caught error code : '
                . $exception->getCode() . ' : ' . $exception->getMessage());
            return response()->json(['errors' => [$exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
        return response()->json([
            "success" => true,
            "message" => "Data delete successfully.",
            "data" => $ZimService
        ], Response::HTTP_NO_CONTENT);

        Log::debug(__METHOD__ . ' eof');
    }
}
