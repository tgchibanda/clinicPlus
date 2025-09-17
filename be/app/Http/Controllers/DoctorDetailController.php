<?php

namespace App\Http\Controllers;

use App\Models\DoctorDetail;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DoctorDetailController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showFiles($id)
    {
        Log::debug(__METHOD__ . ' bof');

        $uploads = Upload::select(
            'uploads.*'
        )->Join('users', 'users.id', '=', 'uploads.user_id')
        ->where('users.id','=',$id)
        ->where('uploads.description','<>','profile')
        ->get();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Files retrieved successfully.",
            "data" => $uploads
            ], 200);

    }
    /**
     * Download the specified files.
     *
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function downloadFiles($id)
    {
        Log::debug(__METHOD__ . ' bof');
        $uploads = Upload::select('*')->where('id','=',$id)->first();

        $filename = 'uploads/'.$uploads->upload_name;
        $file=Storage::disk('public')->get($filename);

		return (new Response($file, 200))
              ->header('Content-Type', 'image/jpeg');

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeFiles(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');

        $this->validate($request,[
            'user_id' => 'required|numeric|max:191',
            'name' => 'required|max:191',
            'file' => 'required|mimes:jpg,jpeg,png,csv,txt,xlx,xls,pdf|max:2048',
        ]);

         $fileUpload = new Upload;

         if ($request->file()) {
             $file_name = time().'_'.$request->file->getClientOriginalName();
             $file_path = $request->file('file')->storeAs('uploads', $file_name, 'public');

             $fileUpload->user_id = $request['user_id'];
             $fileUpload->upload_name = time().'_'.$request->file->getClientOriginalName();
             $fileUpload->description = $request['name'];;
             $fileUpload->upload = '/storage/' . $file_path;
             $fileUpload->save();

         }

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Data saved successfully.",
            "data" => $fileUpload
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

        $this->validate($request,[
            'user_id' => 'required|numeric|max:191',
            'reg_number' => 'required|max:191',
        ]);

        $doc_details = DoctorDetail::updateOrCreate([
            'user_id' => $request['user_id'],
            'reg_number' => $request['reg_number'],
        ]);
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Data saved successfully.",
            "data" => $doc_details
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
        $doc_details = DoctorDetail::select(
            'doctor_details.*'
        )
        ->Join('users', 'users.id', '=', 'doctor_details.user_id')
        ->where('users.id','=',$id)->first();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "DoctorDetail retrieved successfully.",
            "data" => $doc_details
            ], 200);


    }


}
