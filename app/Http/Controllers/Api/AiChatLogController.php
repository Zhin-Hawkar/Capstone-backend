<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiChatLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;
use App\Models\LegalDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Storage;




class AiChatLogController extends Controller
{
    public function talkToAi(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'prompt' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        $OPEN_ROUTER_ENDPOINT = env('OPENROUTER_ENDPOINT');
        $OPENROUTER_API_KEY = env('OPENROUTER_API_KEY');
        $prompt = $request->input('prompt');

        $systemMessage = [
            "role" => "system",
            "content" => "You are a professional assistant specialized in helping patients understand the visa application process for healthcare-related travel (e.g., traveling abroad for medical treatment or checkups). 

- Your main focus is to provide clear, concise, and accurate guidance on healthcare visa steps, required documents, travel planning, and related questions.  
- You can also engage in general conversation, answer everyday questions like greetings, weather, or small talk.  
- If a question is very specific to another professional domain (e.g., computers, IT, software development, finance, engineering), politely respond: 
  'I'm sorry, but I can only provide guidance on healthcare visa and travel-related questions.  
- If you do not understand a question, respond: 
  'I'm not sure I understood that. Could you please rephrase your question?
- Always keep responses clear, helpful, and professional.
"
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $OPENROUTER_API_KEY,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => 'https://capstone-backend-inbqo.sevalla.app',
            'X-Title' => 'Healthcare Visa Assistant'
        ])->post($OPEN_ROUTER_ENDPOINT, [
            "model" => "openai/gpt-3.5-turbo",
            "messages" => [
                $systemMessage,
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "max_tokens" => 500,
            "temperature" => 0.4
        ]);

        $aiResponse = $response->json();

        $aiContent = $aiResponse['choices'][0]['message']['content'] ?? null;

        if (!$aiContent) {
            return response()->json(['error' => 'No response from AI'], 500);
        }

        try {
            if (!$user) {
                return response()->json([
                    'result' => [
                        'code' => 200,
                        'log' => [
                            'prompt' => $prompt,
                            'response' => $aiContent,
                            'created_at' => now(),
                        ]
                    ]
                ], 200);
            }
            $chat = AiChatLog::create([
                'id' => $user->id,
                'email' => $user->email,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'prompt' => $prompt,
                'response' => $aiContent,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            $log = AiChatLog::where('id', $chat->id)->first();
            $logs = AiChatLog::where('email', $chat->email)->get();
            return response()->json([
                'result' => [
                    'code' => 200,
                    'log' => [
                        'prompt' => $log->prompt,
                        'response' => $log->prompt,
                        'all_logs' => $logs,
                        'created_at' => $log->created_at,
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function analyzeMedicalData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'medical_record' => 'nullable|file|mimes:pdf,txt,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors(),
            ], 422);
        }

        $text = $request->input('text');
        $fileContent = '';

        if ($request->hasFile('medical_record')) {
            $file = $request->file('medical_record');
            $extension = $file->getClientOriginalExtension();

            if (in_array($extension, ['txt'])) {
                $fileContent = file_get_contents($file->getRealPath());
            } elseif ($extension === 'pdf') {
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($file->getRealPath());
                    $fileContent = $pdf->getText();
                } catch (\Exception $e) {
                    $fileContent = '[Unable to extract text from PDF]';
                }
            } else {
                $fileContent = '[File type not directly readable: ' . $extension . ']';
            }
        }

        $prompt = "Patient text: " . $text;
        if (!empty($fileContent)) {
            $prompt .= "\n\nAttached medical record content:\n" . $fileContent;
        }

        $OPEN_ROUTER_ENDPOINT = env('OPENROUTER_ENDPOINT');
        $OPENROUTER_API_KEY = env('OPENROUTER_API_KEY');

        $systemMessage = [
            "role" => "system",
            "content" => "You are a medical data analyst AI. Analyze patient text and uploaded medical record content, summarize findings, identify potential health issues, and suggest what type of doctor might be appropriate to consult. Keep your tone professional and clear."
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $OPENROUTER_API_KEY,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => 'https://capstone-backend-inbqo.sevalla.app',
            'X-Title' => 'Healthcare medical analyzer'
        ])->post($OPEN_ROUTER_ENDPOINT, [
            "model" => "openai/gpt-3.5-turbo",
            "messages" => [
                $systemMessage,
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "max_tokens" => 800,
            "temperature" => 0.4
        ]);

        $aiResponse = $response->json();
        $aiContent = $aiResponse['choices'][0]['message']['content'] ?? null;

        if (!$aiContent) {
            return response()->json(['error' => 'No response from AI'], 500);
        }



        return response()->json([
            'code' => 200,
            'ai_analysis' => $aiContent,
        ]);
    }


    public static function generateLegalDocument()
    {
        $user = Auth::user();
        $acceptedAppointment = DB::table("accepted_appointment")
            ->where("patientId", $user->id)
            ->first();

        try {
            $systemMessage = [
                "role" => "system",
                "content" => "
You generate a fully structured legal agreement between a patient and a hospital.
Add the current date and time, and remove the signature in the end.

The user will send:
patient_name: {value}
doctor_name: {value}
hospital_name: {value}

Create a legal contract including:
- Patient name
- Doctor name
- Hospital name
- Responsibilities
- Payment terms
- Medical service description
- Clause: if patient is not satisfied with the results,
  the hospital refunds all money spent from travel until arrival.
- Signature section

Return only the final formatted legal document.
"
            ];

            $userPrompt = "
patient_name: {$acceptedAppointment->firstName}
doctor_name: {$acceptedAppointment->doctorFirstName}
hospital_name: {$acceptedAppointment->hospitalName}
";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env("OPENROUTER_API_KEY"),
                'Content-Type' => 'application/json',
            ])->post(env("OPENROUTER_ENDPOINT"), [
                "model" => "openai/gpt-4o-mini",
                "messages" => [
                    $systemMessage,
                    ["role" => "user", "content" => $userPrompt]
                ],
                "temperature" => 0.3,
                "max_tokens" => 1200
            ]);

            $aiResponse = $response->json();
            $documentText = $aiResponse['choices'][0]['message']['content'] ?? null;

            if (!$documentText) {
                return response()->json(["error" => "AI did not return a document"], 500);
            }

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 14px;
        line-height: 1.7;
        color: #333;
        margin: 40px;
    }

    .doc-container {
        border: 1px solid #bbb;
        padding: 40px;
        border-radius: 8px;
        background: #fafafa;
    }

    h1 {
        text-align: center;
        font-size: 22px;
        margin-bottom: 25px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    h2 {
        font-size: 18px;
        margin-top: 30px;
        margin-bottom: 10px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 4px;
    }

    .section {
        margin-bottom: 20px;
    }

    .header-info {
        margin-bottom: 25px;
        padding: 15px;
        background: #f0f0f0;
        border-left: 4px solid #4a90e2;
    }

    .header-info p {
        margin: 5px 0;
        font-size: 14px;
    }

    .signature-box {
        margin-top: 50px;
        display: flex;
        justify-content: space-between;
    }

    .signature-block {
        width: 45%;
        text-align: center;
        margin-top: 30px;
    }

    .signature-line {
        border-top: 1px solid #000;
        margin-top: 50px;
        padding-top: 5px;
        font-size: 13px;
    }
</style>
</head>
<body>

<div class="doc-container">

    <h1>Legal Medical Service Agreement</h1>

    <div class="header-info">
        <p><strong>Patient Name:</strong> ' . e($acceptedAppointment->firstName ?? "") . '</p>
        <p><strong>Doctor Name:</strong> ' . e($acceptedAppointment->doctorFirstName ?? "") . '</p>
        <p><strong>Hospital Name:</strong> ' . e($acceptedAppointment->hospitalName ?? "") . '</p>
        <p><strong>Date Issued:</strong> ' . date("Y-m-d") . '</p>
    </div>

    <div class="section">
        ' . nl2br(e($documentText)) . '
    </div>

    <div class="signature-box">
        <div class="signature-block">
            <div class="signature-line">Patient Signature</div>
        </div>
        <div class="signature-block">
            <div class="signature-line">Hospital / Doctor Signature</div>
        </div>
    </div>

</div>

</body>
</html>
';


            $pdf = PDF::loadHTML($html);

            $fileName = "legal_doc_" . time() . ".pdf";
            $filePath = "legal_docs/" . $fileName;

            Storage::disk('public')->put($filePath, $pdf->output());

            $recordId = DB::table("legal_document")->insertGetId([
                "userId" => $acceptedAppointment->patientId,
                "doctorId" => $acceptedAppointment->doctorId,
                "hospitalId" => $acceptedAppointment->hospitalId,
                "fileName" => $fileName,
                "legalDocument" => $filePath,
                "url" => asset("storage/" . $filePath),
                "created_at" => now(),
                "updated_at" => now()
            ]);

            $record = DB::table("legal_document")->where("id", $recordId)->first();

            return response()->json([
                "success" => true,
                "message" => "Legal document generated successfully",
                "document" => $record->legalDocument,
                "document_id" => $record->id,
                "pdf_url" => asset("storage/" . $filePath)
            ]);
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()]);
        }
    }
}
