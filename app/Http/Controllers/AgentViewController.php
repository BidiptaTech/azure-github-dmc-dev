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
        // Query OTP verifications table instead of agents
        $search = $request->input('search');
        $query = OtpVerification::where('is_verified', true);
        
        // Apply search filter if present
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereJsonContains('registration_data->name', $search)
                  ->orWhereJsonContains('registration_data->email', $search)
                  ->orWhereJsonContains('registration_data->company_name', $search)
                  ->orWhereJsonContains('registration_data->user_country', $search)
                  ->orWhereJsonContains('registration_data->city', $search);
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
        $verification = OtpVerification::findOrFail($id);
        
        if (!$verification->is_verified) {
            return redirect()->route('registered-agents.index')
                ->with('error', 'Registration verification not found.');
        }

        // Get the agent data from the registration_data JSON field
        $agentData = $verification->registration_data;
        
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
            
            // Find the OTP verification record
            $verification = OtpVerification::findOrFail($request->id);
            
            // Find the agent by email
            $agent = Agent::where('email', $request->email)->first();
            
            if (!$agent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agent not found with this email address.'
                ], 404);
            }
            
            // Update the agent status
            $agent->status = 1;
            $agent->save();
            
            // Log the action
            Log::info('Agent verified', [
                'agent_id' => $agent->agent_id,
                'email' => $agent->email,
                'verified_by' => Auth::id() ?? 'System'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Agent has been successfully verified.',
                'agent' => [
                    'id' => $agent->agent_id,
                    'email' => $agent->email,
                    'status' => $agent->is_active
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
