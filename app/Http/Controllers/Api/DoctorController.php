<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;




class DoctorController extends Controller
{
    public function registerDoctor(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'specialization' => 'required',
            'qualification' => 'required',
            'licenseId' => 'required',
            'department' => 'required',
            'hospital' => 'required',
            'email' => 'required|email|unique:doctor',
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

        $doctor = Doctor::create([
            'firstName' => $req->firstName,
            'lastName' => $req->lastName,
            'specialization' => $req->specialization,
            'qualification' => json_encode($req->qualification),
            'licenseId' => $req->licenseId,
            'department' => $req->department,
            'hospital' => $req->hospital,
            'role' => "doctor",
            'email' => $req->email,
            'password' => Hash::make($req->password),
        ]);
        $token = $doctor->createToken('api-token')->plainTextToken;
        return response()->json([
            'code' => 200,
            'message' => "Doctor Registered Successfully",
            'doctor' => $doctor,
            'token' => $token,
        ], 200);
    }



    public function editDoctorProfile(Request $req)
    {
        try {
            $doctor = DB::table("doctor")->where("email", $req->email)->first();

            if (!$doctor) {
                return response()->json([
                    'code' => 401,
                    'error' => "not authorized"
                ], 401);
            }

            $validator = Validator::make($req->all(), [
                'firstName' => 'nullable|string|max:255',
                'lastName' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'age' => 'nullable|integer|min:0',
                'description' => 'nullable|string|max:1000',
                'doctorImage' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'specialization' => 'nullable|string|max:255',
                'qualification' => 'nullable|string|max:255',
                'licenseId' => 'nullable|integer|min:0',
                'yearsofexperience' => 'nullable|integer|min:0',
                'department' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            if ($req->hasFile('doctorImage')) {
                if ($doctor->image) {
                    $relativePath = str_replace(url('storage') . '/', '', $doctor->image);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }

                $path = $req->file('doctorImage')->store('doctor_images', 'public');
                $validated['doctorImage'] = url('storage/' . $path);
            }

            $doctor->update($validated);

            return response()->json([
                'code' => 200,
                'message' => 'Profile updated successfully',
                'doctor' => $doctor,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "code" => 500,
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function deleteDoctor(Request $request)
    {
        $record = Doctor::find($request->id);
        if ($record) {
            $record->delete();
            return response()->json([
                "code" => 200,
                "msg" => "doctor deleted",
            ]);
        }
    }
}
