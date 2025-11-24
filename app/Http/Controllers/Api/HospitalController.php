<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HospitalController extends Controller
{

    public function registerHospital(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'hospitalName' => 'required',
            'hospitalCode' => 'required|integer',
            'licenseId' => 'required|integer',
            'location' => 'required',
            'type' => 'required',
            'phoneNumber' => 'required',
            'departments' => 'required',
            'workingHours' => 'required|integer',
            'services' => 'required',
            'numberOfBeds' => 'required|integer',
            'description' => 'required',
            'email' => 'required|email|unique:hospital',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $emailError = $errors->has('email') ? $errors->get('email') : null;

            return response()->json([
                "message" => "The given data was invalid.",
                "errors"  => $errors,
                "email_error" => $emailError
            ], 200);
        }

        $hospital = Hospital::create([
            'hospitalName' => $req->hospitalName,
            'hospitalCode' => $req->hospitalCode,
            'licenseId' => $req->licenseId,
            'location' => $req->location,
            'type' => $req->type,
            'phoneNumber' => $req->phoneNumber,
            'departments' => json_encode($req->departments),
            'workingHours' => $req->workingHours,
            'services' => json_encode($req->services),
            'numberOfBeds' => $req->numberOfBeds,
            'description' => $req->description,
            'role' => "hospital",
            'email' => $req->email,
            'password' => Hash::make($req->password),
        ]);
        $token = $hospital->createToken('api-token')->plainTextToken;
        return response()->json([
            'code' => 200,
            'message' => "Hospital Registered Successfully",
            'hospital' => $hospital,
            'token' => $token,
        ], 200);
    }

    public function logHospitalIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => "required|min:6",

        ]);

        $validated = $validator->validated();

        $hospital = Hospital::where('email', $validated["email"])->first();
        if (!$hospital || !Hash::check($validated["password"], $hospital->password)) {
            return response()->json([
                'code' => 401,
                'error' => "Wrong Credentials",
            ], 200);
        }


        $token = $hospital->createToken('api-token')->plainTextToken;
        $hospital->remember_token = $token;
        $hospital->save();

        return response()->json([
            'code' => 200,
            'message' => "User logged in Successfully",
            'hospital' => $hospital,
            'token' => $token,
        ], 200);
    }



    public function editHospitalProfile(Request $req)
    {
        $hospital = Hospital::where('email', $req->email)->first();

        if (!$hospital) {
            return response()->json([
                'code' => 401,
                'error' => "not authorized"
            ], 401);
        }

        $validator = Validator::make($req->all(), [
            'hospitalName' => 'nullable|string|max:255',
            'hospitalCode' => 'nullable|integer|min:0',
            'licenseId' => 'nullable',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'type' => 'nullable|string|max:255',
            'phoneNumber' => 'nullable|string|max:255',
            'workingHours' => 'nullable|integer|min:0',
            'numberOfBeds' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors(),
            ], 422);
        }
        try {
            $validated = $validator->validated();

            if ($req->hasFile('image')) {
                if ($hospital->image) {
                    $relativePath = str_replace(url('storage') . '/', '', $hospital->image);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }

                $path = $req->file('image')->store('hospital_images', 'public');
                $validated['image'] = url('storage/' . $path);
            }

            $hospital->update($validated);

            return response()->json([
                'code' => 200,
                'message' => 'Profile updated successfully',
                'hospital' => $hospital,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllHospitals()
    {
        try {
            $hospitals = Hospital::all();

            if (!$hospitals) {
                return response()->json([
                    'message' => "No hospital"
                ]);
            }
            return response()->json([
                'code' => 200,
                'hospitals' => $hospitals,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getAllDoctors()
    {
        try {
            $hospital = Auth::user();

            if (!$hospital) {
                return response()->json([
                    'message' => "No hospital"
                ]);
            }
            $doctors = DB::table("doctor")->where("hospitalId", $hospital->id)->get();
            if (!$doctors) {
                return response()->json([
                    'message' => "No doctors"
                ]);
            }

            return response()->json([
                'code' => 200,
                'doctors' => $doctors,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

     public function logHospitalOut(Request $req)
    {
        $req->user()->currentAccessToken()->delete();
        return response()->json(['code' => 200, 'message' => 'Logged out']);
    }
}
