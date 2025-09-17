<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\UserRole;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
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
            'unit_number' => 'required',
            //'file' => 'required|mimes:jpg,jpeg,png,csv,txt,xlx,xls,pdf|max:2048',
            // 'fullname' => 'required|max:191',
            'street_name' => 'required',
            'city' => 'required|alpha_num',
            'mobile_no' => 'required|numeric',
        ]);

         $fileUpload = new Upload;

         if ($request->file()) {
             $file_name = time().'_'.$request->file->getClientOriginalName();
             $file_path = $request->file('file')->storeAs('uploads', $file_name, 'public');

             $fileUpload->user_id = $request['user_id'];
             $fileUpload->upload_name = time().'_'.$request->file->getClientOriginalName();
             $fileUpload->description = 'profile';
             $fileUpload->upload = '/storage/' . $file_path;
             $fileUpload->save();

         }

        $contact = Contact::updateOrCreate([
            'user_id' => $request['user_id'],
            'unit_number' => $request['unit_number'],
            'street_name' => $request['street_name'],
            'suburb' => $request['surburb'],
            'city' => $request['city'],
            'mobile_no' => $request['mobile_no'],
            'gps' => 1111,
        ]);
            // Email for contact
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Data saved successfully.",
            "data" => $contact
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

        $contact = Contact::Join('users', 'users.id', '=', 'contacts.user_id')
        ->where('users.id','=',$id)->first();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Contact retrieved successfully.",
            "data" => $contact
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
            'unit_number' => 'required',
            'street_name' => 'required',
            'city' => 'required|alpha_num',
            'mobile_no' => 'required|numeric',
        ]);

        if (Contact::where('id', $id)->exists()) {
            $contact = Contact::find($id);
            $contact->user_id = is_null($request->user_id) ? $contact->user_id : $request->user_id;
            $contact->unit_number = is_null($request->unit_number) ? $contact->unit_number : $request->unit_number;
            $contact->street_name = is_null($request->street_name) ? $contact->street_name : $request->street_name;
            $contact->city = is_null($request->city) ? $contact->city : $request->city;
            $contact->mobile_no = is_null($request->mobile_no) ? $contact->mobile_no : $request->mobile_no;
            $contact->gps = 1111;
            $contact->save();

            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "Contact updated successfully.",
                "data" => $contact
            ], 201);
            } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "Contact not found.",
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

        if(Contact::where('id', $id)->exists()) {
            $contact = Contact::find($id);
            $contact->delete();

            Log::debug(__METHOD__ . ' eof');
            return response()->json([
              "message" => "records deleted"
            ], 202);
          } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
              "message" => "Student not found"
            ], 404);
          }
    }

}
