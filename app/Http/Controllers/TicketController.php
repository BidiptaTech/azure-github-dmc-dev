<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\DB;
use App\Models\Attraction;
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

        $tickets = Ticket::where('dmc_id', Auth::user()->userId)->get();
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
            'status' => 'nullable|in:0,1',
        ]);

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
            $ticket->adult_price = $request->adult_price;
            $ticket->senior_adult_price = $request->senior_adult_price;
            $ticket->child_price_nri = $request->child_price_nri;
            $ticket->adult_price_nri = $request->adult_price_nri;
            $ticket->senior_adult_price_nri = $request->senior_adult_price_nri;
            $ticket->status = $request->status ? 1 : 0;
            $ticket->created_by = Auth::user()->userId ?? null;
            $ticket->dmc_id = Auth::user()->userId ?? null;
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
        $attraction_id = Crypt::decrypt($attraction_id);
        $attraction = Attraction::where('attraction_id', $attraction_id)->first();
        $tickets = Ticket::where('status', 1)->where('attraction_id', $attraction_id)->get();
        return view('tickets.add-ticket', compact('attraction', 'tickets'));
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
        $ticket->adult_price = $request->adult_price;
        $ticket->senior_adult_price = $request->senior_adult_price;
        $ticket->child_price_nri = $request->child_price_nri;
        $ticket->adult_price_nri = $request->adult_price_nri;
        $ticket->senior_adult_price_nri = $request->senior_adult_price_nri;
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
