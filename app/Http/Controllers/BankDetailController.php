<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\BankDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BankDetailController extends Controller
{
    /**
     * Get DMC ID based on user role
     */
    private function getDmcIdByUserRole()
    {
        $user = Auth::user();
        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4  || $user->role_id == 19|| $user->role_id == 20){
            $dmc_id = null;
        }
        elseif($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 35 || in_array($user->role_id, [33, 128, 129, 130, 134, 135, 136, 138])){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 74 || $user->role_id == 37){
            $user_product_head = \App\Models\User::where('userId', $user->created_by)->first();
            if (!$user_product_head) {
                throw new \Exception('Product head user not found.');
            }
            $dmc_id = $user_product_head->created_by;
        }else if($user->role_id == 93 || $user->role_id == 38){
            $user_product_manager = \App\Models\User::where('userId', $user->created_by)->first();
            if (!$user_product_manager) {
                throw new \Exception('Product manager user not found.');
            }
            $user_product_head = \App\Models\User::where('userId', $user_product_manager->created_by)->first();
            if (!$user_product_head) {
                throw new \Exception('Product head user not found.');
            }
            $dmc_id = $user_product_head->created_by;
        }
        else{
            throw new \Exception('You do not have permission to access this page.');
        }
        return $dmc_id;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $dmc_id = $this->getDmcIdByUserRole();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $user = Auth::user();
        
        // Filter bank details based on DMC ID
        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4 || $user->role_id == 19 || $user->role_id == 20){
            $bankDetails = BankDetail::with(['creator', 'updater', 'dmc'])
                    ->orderBy('created_at', 'desc')
                    ->get();
        } else {
            $bankDetails = BankDetail::with(['creator', 'updater', 'dmc'])
                    ->where('dmc_id', $dmc_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
        }
                         
        return view('bank-details.index', compact('bankDetails'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bank-details.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'terms_and_conditions' => 'nullable|string',
            'payment_terms' => 'nullable|array',
            'payment_terms.*' => 'string',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'bank_address' => 'required|string',
            'bank_type' => 'nullable|string|max:100',
            'ifsc' => 'nullable|string|max:50',
            'swift_bic_iban' => 'nullable|string|max:100',
            'bank_code' => 'nullable|string|max:50',
            'branch_code' => 'nullable|string|max:50',
            'aba_routing' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            // India bank details (optional)
            'india_gst_number' => 'nullable|string|max:100',
            'india_pan_number' => 'nullable|string|max:100',
            'india_account_name' => 'nullable|string|max:255',
            'india_account_number' => 'nullable|string|max:100',
            'india_bank_name' => 'nullable|string|max:255',
            'india_bank_address' => 'nullable|string',
            'india_ifsc' => 'nullable|string|max:50',
            'india_bank_type' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $authUser = Auth::user();
            $dmc_id = null;
            
            if($authUser->role_id == 1 || $authUser->role_id == 2 || $authUser->role_id == 3 || $authUser->role_id == 4 || $authUser->role_id == 19 || $authUser->role_id == 20){
                $dmc_id = null;
            } else {
                $dmc_id = $this->getDmcIdByUserRole();
            }

            // Generate bank_detail_id
            // $lastBankDetail = BankDetail::withTrashed()->orderBy('created_at', 'desc')->first();
            // $bank_detail_max_id = $lastBankDetail->bank_detail_id ?? 0;
            // $bankDetailId = CommonHelper::createId($bank_detail_max_id);
            
            // while (BankDetail::where('bank_detail_id', $bankDetailId)->exists()) {
            //     $bankDetailId = CommonHelper::createId($bankDetailId);
            // }

            // Prepare payment terms array
            $paymentTerms = [];
            if ($request->has('payment_terms') && is_array($request->payment_terms)) {
                $paymentTerms = array_filter($request->payment_terms, function($term) {
                    return !empty(trim($term));
                });
            }

            // Prepare India bank details JSON (optional second bank)
            $indiaBank = [
                'bank_type' => $request->india_bank_type ?: 'INR Accounts',
                'gst_number' => $request->india_gst_number,
                'pan_number' => $request->india_pan_number,
                'account_name' => $request->india_account_name,
                'account_number' => $request->india_account_number,
                'bank_name' => $request->india_bank_name,
                'bank_address' => $request->india_bank_address,
                'ifsc' => $request->india_ifsc,
            ];
            $indiaBank = array_filter($indiaBank, function ($value) {
                return !is_null($value) && $value !== '';
            });

            $bankDetail = new BankDetail();
            // $bankDetail->bank_detail_id = $bankDetailId;
            $bankDetail->dmc_id = $dmc_id;
            $bankDetail->terms_and_conditions = $request->terms_and_conditions;
            $bankDetail->payment_terms = !empty($paymentTerms) ? $paymentTerms : null;
            $bankDetail->india_bank_details = !empty($indiaBank) ? $indiaBank : null;
            $bankDetail->bank_type = $request->bank_type ?: 'SGD Accounts';
            $bankDetail->account_name = $request->account_name;
            $bankDetail->account_number = $request->account_number;
            $bankDetail->bank_address = $request->bank_address;
            $bankDetail->ifsc = $request->ifsc;
            $bankDetail->swift_bic_iban = $request->swift_bic_iban;
            $bankDetail->bank_code = $request->bank_code;
            $bankDetail->branch_code = $request->branch_code;
            $bankDetail->aba_routing = $request->aba_routing;
            $bankDetail->is_active = $request->has('is_active') ? 1 : 0;
            $bankDetail->created_by = $authUser->userId;
            $isSaved = $bankDetail->save();
            $bankDetail->refresh();
            if ($isSaved) {
                return redirect()->route('bank-details.index')->with('success', 'Bank details created successfully!');
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to create bank details.');
            }

            DB::commit();

            return redirect()->route('bank-details.index')
                ->with('success', 'Bank details created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bank detail creation error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while creating bank details. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $bankDetail = BankDetail::with(['creator', 'updater', 'dmc'])
                ->where('bank_detail_id', Crypt::decrypt($id))
                ->firstOrFail();
                
            return view('bank-details.show', compact('bankDetail'));
        } catch (\Exception $e) {
            Log::error('Bank detail show error: ' . $e->getMessage());
            return redirect()->route('bank-details.index')
                ->with('error', 'Bank detail not found.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $bankDetail = BankDetail::where('bank_detail_id', Crypt::decrypt($id))->firstOrFail();
            return view('bank-details.edit', compact('bankDetail'));
        } catch (\Exception $e) {
            Log::error('Bank detail edit error: ' . $e->getMessage());
            return redirect()->route('bank-details.index')
                ->with('error', 'Bank detail not found.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'terms_and_conditions' => 'nullable|string',
            'payment_terms' => 'nullable|array',
            'payment_terms.*' => 'string',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'bank_address' => 'required|string',
            'bank_type' => 'nullable|string|max:100',
            'ifsc' => 'nullable|string|max:50',
            'swift_bic_iban' => 'nullable|string|max:100',
            'bank_code' => 'nullable|string|max:50',
            'branch_code' => 'nullable|string|max:50',
            'aba_routing' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            // India bank details (optional)
            'india_gst_number' => 'nullable|string|max:100',
            'india_pan_number' => 'nullable|string|max:100',
            'india_account_name' => 'nullable|string|max:255',
            'india_account_number' => 'nullable|string|max:100',
            'india_bank_name' => 'nullable|string|max:255',
            'india_bank_address' => 'nullable|string',
            'india_ifsc' => 'nullable|string|max:50',
            'india_bank_type' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $bankDetail = BankDetail::where('bank_detail_id', Crypt::decrypt($id))->firstOrFail();

            // Prepare payment terms array
            $paymentTerms = [];
            if ($request->has('payment_terms') && is_array($request->payment_terms)) {
                $paymentTerms = array_filter($request->payment_terms, function($term) {
                    return !empty(trim($term));
                });
            }

            // Prepare India bank details JSON (optional second bank)
            $indiaBank = [
                'bank_type' => $request->india_bank_type ?: ($bankDetail->india_bank_details['bank_type'] ?? 'INR Accounts'),
                'gst_number' => $request->india_gst_number,
                'pan_number' => $request->india_pan_number,
                'account_name' => $request->india_account_name,
                'account_number' => $request->india_account_number,
                'bank_name' => $request->india_bank_name,
                'bank_address' => $request->india_bank_address,
                'ifsc' => $request->india_ifsc,
            ];
            $indiaBank = array_filter($indiaBank, function ($value) {
                return !is_null($value) && $value !== '';
            });

            $bankDetail->terms_and_conditions = $request->terms_and_conditions;
            $bankDetail->payment_terms = !empty($paymentTerms) ? $paymentTerms : null;
            $bankDetail->india_bank_details = !empty($indiaBank) ? $indiaBank : null;
            $bankDetail->bank_type = $request->bank_type ?: ($bankDetail->bank_type ?? 'SGD Accounts');
            $bankDetail->account_name = $request->account_name;
            $bankDetail->account_number = $request->account_number;
            $bankDetail->bank_address = $request->bank_address;
            $bankDetail->ifsc = $request->ifsc;
            $bankDetail->swift_bic_iban = $request->swift_bic_iban;
            $bankDetail->bank_code = $request->bank_code;
            $bankDetail->branch_code = $request->branch_code;
            $bankDetail->aba_routing = $request->aba_routing;
            $bankDetail->is_active = $request->has('is_active') ? 1 : 0;
            $bankDetail->updated_by = Auth::user()->userId;
            $bankDetail->save();

            DB::commit();

            return redirect()->route('bank-details.index')
                ->with('success', 'Bank details updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bank detail update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while updating bank details. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $bankDetail = BankDetail::where('bank_detail_id', Crypt::decrypt($id))->firstOrFail();
            $bankDetail->delete();

            return redirect()->route('bank-details.index')
                ->with('success', 'Bank details deleted successfully!');
                
        } catch (\Exception $e) {
            Log::error('Bank detail delete error: ' . $e->getMessage());
            return redirect()->route('bank-details.index')
                ->with('error', 'An error occurred while deleting bank details. Please try again.');
        }
    }
}
