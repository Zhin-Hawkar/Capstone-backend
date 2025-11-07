<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
            'licenseId' => 'nullable|integer|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'type' => 'nullable|string|max:255',
            'phoneNumber' => 'nullable|string|max:255',
            'departments' => 'nullable',
            'workingHours' => 'nullable|integer|min:0',
            'numberOfBeds' => 'nullable|integer|min:0',
            'services' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors(),
            ], 422);
        }

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
    }
}
