<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

    // ----------------------------
    // VALIDATION
    // ----------------------------
    $this->validate($request, [
        'fullname'          => 'required|string|max:191',
        'email'             => 'required|email|unique:users,email',
        'password'          => 'required|min:6|confirmed',
        'password_confirmation' => 'required|min:6',
        'user_account'              => 'required|string',
        'mobile_no'         => 'required|numeric',
        'unit_number'       => 'required|string',
        'street_name'       => 'required|string',
        'surburb'           => 'nullable|string',
        'city'              => 'required|string',
    ]);

    DB::beginTransaction();

    try {
        // ----------------------------
        // CREATE USER
        // ----------------------------
        $user = User::create([
            'name'      => $request->fullname,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'location_id' => $request->location,
        ]);

        // ----------------------------
        // CREATE ROLE RECORD
        // ----------------------------
        $role = UserRole::create([
            'user_id'   => $user->id,
            'role'      => $request->user_account,
            'status'    => 'active',    // default
        ]);

        // ----------------------------
        // CREATE CONTACT RECORD
        // ----------------------------
        $contact = Contact::create([
            'user_id'       => $user->id,
            'unit_number'   => $request->unit_number,
            'street_name'   => $request->street_name,
            'suburb'        => $request->surburb,
            'city'          => $request->city,
            'mobile_no'     => $request->mobile_no,
            'gps'           => 123,
        ]);

        DB::commit();

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "User created successfully.",
            "data" => [
                "user" => $user,
                "role" => $role,
                "contact" => $contact
            ]
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("ERROR: " . $e->getMessage());

        return response()->json([
            "success" => false,
            "message" => "Error creating user.",
            "error"   => $e->getMessage()
        ], 500);
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
