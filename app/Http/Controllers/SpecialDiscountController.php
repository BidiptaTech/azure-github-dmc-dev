<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpecialDiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $discounts = [];
        $discounts = Discount::all();
        return view('special-discount.index', compact('discounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $assignedAgentIds = Discount::pluck('agent_id')->toArray(); // Get already assigned agent IDs
        $agents = Agent::whereNotIn('agent_id', $assignedAgentIds)->get(); // Exclude them

        // $agent = Agent::where('agent_id', $agentId)->first();
        // $salesManagerId = $agent->sales_manager_dmc;
        // $dmc_id = User::where('userId', $salesManagerId)->value('dmcId');

        return view('special-discount.create', compact('agents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
    $request->validate([
        'discount_amount' => 'required|numeric',
        'discount_type' => 'required|in:percentage,fixed',
        'expiry' => 'required|date|after_or_equal:today',
        'agent_id' => 'required|exists:agents,id',
    ]);

    // Generate a 16-digit unique ID
    $special_discount_id = $this->generateUniqueId();
    $dmcId = auth()->user()->userId;

    // Create the discount
    Discount::create([
        'special_discount_id' => $special_discount_id,
        'discount_amount' => $request->discount_amount,
        'discount_type' => $request->discount_type,
        'expiry' => $request->expiry,
        'agent_id' => $request->agent_id,
        'dmc_id' => $dmcId
    ]);

    return redirect()->route('discount.index')->with('success', 'Special Discount Added Successfully!');

    }

    private function generateUniqueId()
    {
        do {
            $hexPart = substr(md5(uniqid(mt_rand(), true)), 0, 7); // Generate a random hex-like string
            $decimalPart = substr(str_replace('0.', '', strval(mt_rand() / mt_getrandmax())), 0, 8); // Generate an 8-digit random decimal
            $id = "{$hexPart}.{$decimalPart}"; // Combine both parts
        } while (Discount::where('special_discount_id', $id)->exists()); // Ensure uniqueness

        return $id;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
