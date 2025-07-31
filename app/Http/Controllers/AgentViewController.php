<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use App\Models\OtpVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AgentViewController extends Controller
{
    /**
     * Display a listing of registered agents
     */
    public function index(Request $request)
    {
        // Query agents with status 2 (pending verification)
        $search = $request->input('search');
        $query = Agent::where('status', 2);
        
        // Apply search filter if present
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('user_country', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }
        
        // Fetch paginated results
        $otpVerifications = $query->latest()->paginate(10);
        
        return view('agent-view.index', compact('otpVerifications', 'search'));
    }

    /**
     * Display the details of a specific agent registration
     */
    public function show($id)
    {
        // Find the agent with the given id and status 2
        $verification = Agent::where('id', $id)
                        ->where('status', 2)
                        ->firstOrFail();
        
        // For compatibility with existing view, create an agentData array with the agent fields
        $agentData = [
            'company_name' => $verification->company_name,
            'salutation' => $verification->salutation,
            'name' => $verification->name,
            'email' => $verification->email,
            'user_country' => $verification->user_country,
            'city' => $verification->city,
            'agent_address' => $verification->agent_address,
            'code' => $verification->code,
            'phone' => $verification->phone,
            'id_card' => $verification->id_card,
            'card_number' => $verification->card_number,
            'country' => is_string($verification->country) ? [$verification->country] : json_decode($verification->country, true),
            'image' => $verification->image,
            'agent_image' => $verification->agent_image
        ];
        
        return view('agent-view.show', compact('verification', 'agentData'));
    }
    
    /**
     * Verify an agent by setting their status to 1 in the Agents table
     */
    public function verifyAgent(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'id' => 'required',
                'email' => 'required|email'
            ]);
            
            // Find the agent by id and email
            $agent = Agent::where('id', $request->id)
                       ->where('email', $request->email)
                       ->where('status', 2)
                       ->first();
            
            if (!$agent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agent not found or already verified.'
                ], 404);
            }
            
            // Update the agent status
            $agent->status = 1;
            $agent->save();
            
            // Log the action
            Log::info('Agent verified', [
                'agent_id' => $agent->id,
                'email' => $agent->email,
                'verified_by' => Auth::id() ?? 'System'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Agent has been successfully verified.',
                'agent' => [
                    'id' => $agent->id,
                    'email' => $agent->email,
                    'status' => $agent->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Agent verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify agent: ' . $e->getMessage()
            ], 500);
        }
    }
}
