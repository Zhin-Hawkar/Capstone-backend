<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiChatLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AiChatLogController extends Controller
{
    public function talkToAi(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'prompt' => 'required',
            'email' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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
            'HTTP-Referer' => 'http://127.0.0.1:8000',
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

            return response()->json([
                'result' => [
                    'code'=>200,
                    'log' => [
                        'prompt' => $log->prompt,
                        'response' => $log->response,
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
}
