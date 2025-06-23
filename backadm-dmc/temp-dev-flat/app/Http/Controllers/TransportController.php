<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Facility;
use App\Models\Role;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Room;
use App\Models\Transport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;

class TransportController extends Controller
{
    /*
    * Display a listing of the Category.
    * Date 18-01-2025
    */
    public function index(Request $request)
    {
        if (!hasPermission('view transport')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $transports = Transport::all();
        return view('transports.transport', compact('transports'));
    }

    /*
    * Show the form for creating a new category.
    * Date 18-01-2025
    */
    public function create()
    {
        if (!hasPermission('create transport')) {
            abort(403, 'You do not have permission to access this page.');
        }
        return view('transports.create-transport');
    }

    /*
    * Store a newly created role.
    * Date 18-01-2025
    */
    public function store(Request $request)
    {
        
        // Validate the incoming request data
        $validatedData = $request->validate([
            'vehicle_name' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'contact_no' => 'required|numeric|digits_between:10,15',
            'license_no' => 'required|string|max:255',
            'expiry_date' => 'required|date|after_or_equal:today',
            'vehicle_reg_no' => 'required|string|max:255',
            'vehicle_no' => 'required|string|max:255',
            'transport_status' => 'nullable|integer',
        ]);

        $lastTransport = Transport::withTrashed()->orderBy('created_at', 'desc')->first();
        $transport_max_id = $lastTransport->transport_id ?? 0;
        $transportId = CommonHelper::createId($transport_max_id);
        while (Transport::where('transport_id', $transportId)->exists()) {
            $transportId = CommonHelper::createId($transportId);
        }

        // Save transport data or any other logic
        $transport = new Transport();
        $transport->transport_id = $transportId;
        $transport->vehicle_name = $validatedData['vehicle_name'];
        $transport->driver_name = $validatedData['driver_name'];
        $transport->contact_no = $validatedData['contact_no'];
        $transport->license_no = $validatedData['license_no'];
        $transport->license_expiry_date = $validatedData['expiry_date'];
        $transport->vehicle_registration_no = $validatedData['vehicle_reg_no'];
        $transport->vehicle_no = $validatedData['vehicle_no'];
        $transport->is_active = $request->transport_status;

        if ($transport->save()) {
            return redirect()->route('transport.index')->with('success', 'Transport details added successfully!');
        } else {
            return redirect()->route('transport.index')->with('error', 'Failed to add transport details.');
        }
        
    }

    /*
    * Show the form fors editing the specified role.
    * Date 18-01-2025
    */
    public function edit($id)
    {
        if (!hasPermission('edit transport')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $transport = Transport::where('transport_id', $id)->first(); 
        return view('transports.edit-transport', compact('transport'));
    }
    /*
    * Update the specified role.
    * Date 18-01-2025
    */
    public function update(Request $request, $id)
    {
        $request->validate([
            'vehicle_name' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'contact_no' => 'required|numeric|digits_between:10,15',
            'license_no' => 'required|string|max:255',
            'expiry_date' => 'required|date|after_or_equal:today',
            'vehicle_reg_no' => 'required|string|max:255',
            'vehicle_no' => 'required|string|max:255',
            'transport_status' => 'nullable|integer',
        ]);

        $transport = Transport::where('transport_id',$id)->first();
        $transport->vehicle_name = $request->input('vehicle_name');
        $transport->driver_name = $request->input('driver_name');
        $transport->contact_no = $request->input('contact_no');
        $transport->license_no = $request->input('license_no');
        $transport->license_expiry_date = $request->input('expiry_date');
        $transport->vehicle_registration_no = $request->input('vehicle_reg_no');
        $transport->vehicle_no = $request->input('vehicle_no');
        $transport->is_active = $request->input('transport_status') == 1 ? 1 : 0;

        if ($transport->save()) {
            return redirect()->route('transport.index')->with('success', 'Transport details updated successfully!');
        } else {
            return redirect()->route('transport.index')->with('error', 'Failed to update transport details.');
        }
    }

    /*
    * Soft Delete attraction.
    * Date 18-01-2025
    */
    public function destroy($id)
    {
        if (!hasPermission('delete transport')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $delete = Transport::where('transport_id', $id)->delete();
        if($delete){
            return redirect()->route('transport.index')
        ->with('error', 'Transport deleted successfully');
        }
        else{
            return redirect()->route('transport.index')
        ->with('error', 'Unable to delete!');
        }
        
    }
}
