<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\DB;
use App\Models\Attraction;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class TicketController extends Controller
{
    /**
     * Display a listing of the tickets.
     */
    public function index()
    {
        // if (!hasPermission('view ticket')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }

        $auth_user = Auth::user();
        $tickets = [];

        if($auth_user->role_id == 1 || $auth_user->role_id == 20){
            // Admin and Virtual DMC can see all tickets
            $tickets = Ticket::all();
        }else if($auth_user->role_id == 11){
            // Regular DMC sees only their tickets
            $tickets = Ticket::where('dmc_id', $auth_user->userId)->get();
        }else if($auth_user->role_id == 35 || in_array($auth_user->role_id, [130, 132, 133, 135, 136, 137, 138, 139, 140])){
            // Sub-users see tickets of their parent DMC
            $dmc_id = $auth_user->created_by;
            $tickets = Ticket::where('dmc_id', $dmc_id)->get();
        }else if($auth_user->role_id == 78){
            // Sales executive sees tickets of their DMC
            $sales_head = User::where('userId', $auth_user->created_by)->first();
            $dmc_id = $sales_head->created_by;
            $tickets = Ticket::where('dmc_id', $dmc_id)->get();
        }else if($auth_user->role_id == 120){
            // Sales manager sees tickets of their DMC
            $sales_manager = User::where('userId', $auth_user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
            $tickets = Ticket::where('dmc_id', $dmc_id)->get();
        }else{
            // For other roles, show only their own tickets (fallback)
            $tickets = Ticket::where('dmc_id', $auth_user->userId)->get();
        }

        return view('tickets.tickets', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        // if (!hasPermission('create ticket')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        
        return view('tickets.create-ticket');
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        // if (!hasPermission('create ticket')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        
        // Validate request data
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'child_price' => 'nullable|numeric|min:0',
            'adult_price' => 'required|numeric|min:0',
            'senior_adult_price' => 'nullable|numeric|min:0',
            'child_cost_price' => 'nullable|numeric|min:0',
            'adult_cost_price' => 'nullable|numeric|min:0',
            'senior_adult_cost_price' => 'nullable|numeric|min:0',
            'child_price_nri' => 'nullable|numeric|min:0',
            'adult_price_nri' => 'nullable|numeric|min:0',
            'senior_adult_price_nri' => 'nullable|numeric|min:0',
            'child_cost_price_nri' => 'nullable|numeric|min:0',
            'adult_cost_price_nri' => 'nullable|numeric|min:0',
            'senior_adult_cost_price_nri' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:0,1',
        ]);

        $auth_user = Auth::user();
        if($auth_user->role_id == 1 || $auth_user->role_id == 20){
            $dmc_id = $request->input('dmc_id');
        }else if($auth_user->role_id == 11){
            $dmc_id = $auth_user->userId;
        }else if($auth_user->role_id == 35 || in_array($auth_user->role_id, [130, 132, 133, 135, 136, 137, 138])){
            $dmc_id = $auth_user->created_by;
        }else if($auth_user->role_id == 78 || $auth_user->role_id == 139){
            $sales_head = User::where('userId', $auth_user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($auth_user->role_id == 120 || $auth_user->role_id == 140){
            $sales_manager = User::where('userId', $auth_user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        $attraction_id = $request->input('attraction_id');
        if(!$attraction_id){
            return redirect()->back()->with('error', 'Attraction not found.');
        }

        // Generate a unique 8-digit ticket ID
        $lastTicket = Ticket::withTrashed()->orderBy('ticket_id', 'desc')->first();
        $ticketMaxId = $lastTicket ? $lastTicket->ticket_id : 10000000;
        $ticketMaxId = max($ticketMaxId, 10000000) + 1;
        
        // Ensure it's at least 8 digits
        DB::beginTransaction();

        try{
            do {
                // Lock the latest ticket row to avoid race condition
                $lastTicket = Ticket::withTrashed()
                    ->orderBy('ticket_id', 'desc')
                    ->lockForUpdate()
                    ->first();

                // Start from 10000000 if no ticket exists
                $ticketMaxId = $lastTicket ? $lastTicket->ticket_id : 10000000;
                $ticketMaxId = max($ticketMaxId, 10000000) + 1;

            } while (Ticket::withTrashed()->where('ticket_id', $ticketMaxId)->exists());
            
            // Create a new ticket
            $ticket = new Ticket();
            $ticket->ticket_id = $ticketMaxId;
            $ticket->name = $request->name;
            $ticket->description = $request->description;
            $ticket->remarks = $request->remarks;
            $ticket->terms_conditions = $request->terms_conditions;
            $ticket->child_price = $request->child_price;
            $ticket->child_cost_price = $request->child_cost_price;
            $ticket->adult_price = $request->adult_price;
            $ticket->adult_cost_price = $request->adult_cost_price;
            $ticket->senior_adult_price = $request->senior_adult_price;
            $ticket->senior_adult_cost_price = $request->senior_adult_cost_price;
            $ticket->child_price_nri = $request->child_price_nri;
            $ticket->child_cost_price_nri = $request->child_cost_price_nri;
            $ticket->adult_price_nri = $request->adult_price_nri;
            $ticket->adult_cost_price_nri = $request->adult_cost_price_nri;
            $ticket->senior_adult_price_nri = $request->senior_adult_price_nri;
            $ticket->senior_adult_cost_price_nri = $request->senior_adult_cost_price_nri;
            $ticket->status = $request->status ? 1 : 0;
            $ticket->created_by = Auth::user()->userId ?? null;
            $ticket->dmc_id = $dmc_id;
            $ticket->attraction_id = $attraction_id;
            $ticket->save();

            DB::commit();
            return redirect()->route('tickets.add_ticket', Crypt::encrypt($attraction_id))->with('success', 'Ticket created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create ticket.');
        }
    }

    /**
     * Display the specified ticket.
     */
    public function show($ticket_id)
    {
        // if (!hasPermission('view ticket')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $ticket_id = Crypt::decrypt($ticket_id);
        $ticket = Ticket::where('ticket_id', $ticket_id)->first();
        if(!$ticket){
            return redirect()->back()->with('error', 'Ticket not found.');
        }
        
        return view('tickets.show-ticket', compact('ticket'));
    }

    public function add_ticket($attraction_id)
    {
        $auth_user = Auth::user();
        $dmcUsers = collect();
        
        // Decrypt the attraction ID and get the current attraction
        $attraction_id = Crypt::decrypt($attraction_id);
        $attraction = Attraction::where('attraction_id', $attraction_id)->first();
        
        if ($auth_user->role_id == 1 || $auth_user->role_id == 20) {
            // Get the attraction's DMC IDs
            $attractionDmcIds = $attraction ? $attraction->getSelectedDmcIds() : [];
            
            if (!empty($attractionDmcIds)) {
                // Only show DMCs that are present in the attraction's dmc_id array
                $dmcUsers = User::where('role_id', 11)
                    ->where('user_type', 2)
                    ->whereIn('userId', $attractionDmcIds)
                    ->select('userId', 'name', 'company_name')
                    ->orderBy('company_name', 'asc')
                    ->get();
            }
            // If attraction's dmc_id is null/empty, $dmcUsers remains empty collection
        }
        
        if($auth_user->role_id == 1 || $auth_user->role_id == 20){
        $tickets = Ticket::where('status', 1)
            ->where('attraction_id', $attraction_id)
            ->get();
        }else if($auth_user->role_id == 11){
            $tickets = Ticket::where('status', 1)
            ->where('attraction_id', $attraction_id)
            ->where('dmc_id', $auth_user->userId)
            ->get();
        }else if($auth_user->role_id == 35 || in_array($auth_user->role_id, [130, 132, 133, 135, 136, 137, 138])){
            $userdmc = User::where('userId', $auth_user->created_by)->first();
            $tickets = Ticket::where('status', 1)
            ->where('attraction_id', $attraction_id)
            ->where('dmc_id', $userdmc->userId)
            ->get();
        }else if($auth_user->role_id == 74 || $auth_user->role_id == 139){
            $user_product_head = User::where('userId', $auth_user->created_by)->first();
            $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
            $tickets = Ticket::where('status', 1)
            ->where('attraction_id', $attraction_id)
            ->where('dmc_id', $user_product_head_dmc->userId)
            ->get();
        }else if($auth_user->role_id == 93 || $auth_user->role_id == 140){
            $user_product_manager = User::where('userId', $auth_user->created_by)->first();
            $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
            $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
            $tickets = Ticket::where('status', 1)
            ->where('attraction_id', $attraction_id)
            ->where('dmc_id', $user_product_head_dmc->userId)
            ->get();
        }else{
            $tickets = Ticket::where('status', 1)
            ->where('attraction_id', $attraction_id)
            ->where('dmc_id', $auth_user->userId)
            ->get();
        }
        return view('tickets.add-ticket', compact('attraction', 'tickets', 'auth_user', 'dmcUsers'));
    }

    /**
     * Show the form for editing the specified ticket.
     */
    public function edit($ticket_id)
    {
        // if (!hasPermission('edit ticket')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $ticket_id = Crypt::decrypt($ticket_id);
        $ticket = Ticket::where('ticket_id', $ticket_id)->first();
        if(!$ticket){
            return redirect()->back()->with('error', 'Ticket not found.');
        }
        return view('tickets.edit-ticket', compact('ticket'));
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, $ticket_id)
    {
        // if (!hasPermission('edit ticket')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        
        // Validate request data
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'child_price' => 'nullable|numeric|min:0',
            'adult_price' => 'required|numeric|min:0',
            'senior_adult_price' => 'nullable|numeric|min:0',
            'child_cost_price' => 'nullable|numeric|min:0',
            'adult_cost_price' => 'nullable|numeric|min:0',
            'senior_adult_cost_price' => 'nullable|numeric|min:0',
            'child_price_nri' => 'nullable|numeric|min:0',
            'adult_price_nri' => 'nullable|numeric|min:0',
            'senior_adult_price_nri' => 'nullable|numeric|min:0',
            'child_cost_price_nri' => 'nullable|numeric|min:0',
            'adult_cost_price_nri' => 'nullable|numeric|min:0',
            'senior_adult_cost_price_nri' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:0,1',
        ]);
        $ticket_id = Crypt::decrypt($ticket_id);
        $ticket = Ticket::where('ticket_id', $ticket_id)->first();
        if(!$ticket){
            return redirect()->back()->with('error', 'Ticket not found.');
        }

        // Update ticket
        $ticket->name = $request->name;
        $ticket->description = $request->description;
        $ticket->remarks = $request->remarks;
        $ticket->terms_conditions = $request->terms_conditions;
        $ticket->child_price = $request->child_price;
        $ticket->child_cost_price = $request->child_cost_price;
        $ticket->adult_price = $request->adult_price;
        $ticket->adult_cost_price = $request->adult_cost_price;
        $ticket->senior_adult_price = $request->senior_adult_price;
        $ticket->senior_adult_cost_price = $request->senior_adult_cost_price;
        $ticket->child_price_nri = $request->child_price_nri;
        $ticket->child_cost_price_nri = $request->child_cost_price_nri;
        $ticket->adult_price_nri = $request->adult_price_nri;
        $ticket->adult_cost_price_nri = $request->adult_cost_price_nri;
        $ticket->senior_adult_price_nri = $request->senior_adult_price_nri;
        $ticket->senior_adult_cost_price_nri = $request->senior_adult_cost_price_nri;
        $ticket->status = $request->has('status') ? 1 : 0;
        $ticket->updated_by = Auth::user()->userId ?? null;
        
        $ticket->save();

        return redirect()->route('tickets.add_ticket', Crypt::encrypt($ticket->attraction_id))->with('success', 'Ticket updated successfully.');
    }

    /**
     * Remove the specified ticket from storage.
     */
    public function destroy($ticket_id)
    {
        // if (!hasPermission('delete ticket')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $ticket_id = Crypt::decrypt($ticket_id);
        $ticket = Ticket::where('ticket_id', $ticket_id)->first();
        if(!$ticket){
            return redirect()->back()->with('error', 'Ticket not found.');
        }
        $ticket->delete();
        
        return redirect()->route('tickets.add_ticket', Crypt::encrypt($ticket->attraction_id))->with('success', 'Ticket deleted successfully.');
    }
}
