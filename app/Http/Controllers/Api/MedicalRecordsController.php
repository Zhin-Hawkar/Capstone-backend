<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecords;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;



class MedicalRecordsController extends Controller
{
    public function uploadMedicalRecord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'medicalRecord' => "required|file|mimes:pdf,jpeg|max:20480"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ]);
        }
        $validated = $validator->validated();
        $user = Auth::user();
        if ($request->hasFile('medicalRecord')) {
            $path = $request->file('medicalRecord')->store('user_medical_records', 'public');
            $validated['medicalRecord'] = url('storage/' . $path);
        }

        MedicalRecords::create([
            'userId' => $user->id,
            'fileName' => $request['fileName'],
            'medicalRecord' => $validated['medicalRecord']

        ]);

        $record = DB::table('users')
            ->join('medical_records', 'users.id', '=', 'medical_records.userId')
            ->select('users.email', 'medical_records.fileName', 'medical_records.medicalRecord')
            ->where('users.id', $user->id)
            ->get();

        return response()->json([
            'code' => 200,
            'record' => $record,
        ], 200);
    }

    public function showMedicalRecords()
    {
        $user = Auth::user();
        $record = DB::table('users')
            ->join('medical_records', 'users.id', '=', 'medical_records.userId')
            ->select('users.email', 'medical_records.fileName', 'medical_records.medicalRecord')
            ->where('users.id', $user->id)
            ->get();
        return response()->json([
            'code' => 200,
            'record' => $record,
        ], 200);
    }
}
