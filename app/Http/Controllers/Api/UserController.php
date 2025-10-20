<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{

    public function register(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
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

        $user = User::create([
            'firstName' => $req->first_name,
            'lastName' => $req->last_name,
            'email' => $req->email,
            'password' => Hash::make($req->password),
        ]);
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'code' => 200,
            'message' => "User Registered Successfully",
            'first_name' => $user->firstName,
            'last_name' => $user->lastName,
            'token' => $token,
        ], 200);
    }


    public function login(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'email' => 'required',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 402,
                    'error' => $validator->errors(),
                ], 200);
            }

            $user = User::where('email', $req->email)->first();
            if (!$user || !Hash::check($req->password, $user->password)) {
                return response()->json([
                    'code' => 401,
                    'error' => "Wrong Credentials",
                ], 200);
            }
            $token = $user->createToken('api-token')->plainTextToken;
            $user->remember_token = $token;
            $user->save();

            return response()->json([
                'code' => 200,
                'message' => "User logged in Successfully",
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->firstName,
                    'last_name' => $user->lastName,
                    'email' => $user->email,
                    'age' => $user->age,
                    'location' => $user->location,
                    'description' => $user->description,
                    'image' => $user->image,
                ],
                'token' => $token,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $req)
    {
        $req->user()->currentAccessToken()->delete();
        return response()->json(['code' => 200, 'message' => 'Logged out']);
    }

    public function editProfile(Request $req)
    {
        $user = User::where('email', $req->email)->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'error' => "not authorized"
            ], 401);
        }

        $validator = Validator::make($req->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if ($req->hasFile('image')) {
            if ($user->image) {
                $relativePath = str_replace(url('storage') . '/', '', $user->image);
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            }

            $path = $req->file('image')->store('user_images', 'public');
            $validated['image'] = url('storage/' . $path);
        }

        $user->update($validated);

        return response()->json([
            'code' => 200,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->firstName,
                'last_name' => $user->lastName,
                'email' => $user->email,
                'age' => $user->age,
                'location' => $user->location,
                'description' => $user->description,
                'image' => $user->image ? $user->image : null,
            ],
        ], 200);
    }
}
