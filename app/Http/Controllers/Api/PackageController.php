<?php

namespace App\Http\Controllers\Api;
use App\Models\Package;
use App\Models\Agent;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Auth;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $city = $request->input('city');
        $country = $request->input('country');
        $today = Carbon::today();

        $dmc_id = $this->getDmcIdForCurrentUser();

        if (!$dmc_id) {
            return response()->json(['message' => 'DMC Not Found!'], 400);
        }

        $query = Package::where('status', 'active')
            // ->where('created_by', $dmc_id)
            // ->whereDate('start_date', '<=', $today)
            ->whereDate('expire_date', '>=', $today);
        if (!empty($city)) {
            $query->where('city', $city);
        }

        if (!empty($country)) {
            $query->where('destination', $country);
        }

        $packages = $query->get();

        return response()->json($packages);
    }

    private function getDmcIdForCurrentUser()
    {
        $user = Auth::user();

        if ($user->agent_id) {
            $agent = Agent::where('agent_id', $user->agent_id)->first();

            if (!$agent) {
                return null;
            }

            switch ($agent->role_id) {
                case 11: // DMC
                    return $agent->sales_manager_dmc;

                case 33: // Sales Head
                    return optional(User::find($agent->sales_manager_dmc))->created_by;

                case 12:
                case 37: // Sales Manager
                    $sm = User::find($agent->sales_manager_dmc);
                    return optional($sm && $sm->created_by ? User::find($sm->created_by) : null)->created_by;

                case 38: // Assistant Manager
                    $am = User::find($agent->sales_manager_dmc);
                    $sm = $am && $am->created_by ? User::find($am->created_by) : null;
                    $sh = $sm && $sm->created_by ? User::find($sm->created_by) : null;
                    return optional($sh)->created_by;
            }
        }

        // If the user is not an agent (e.g., directly SH, SM, AM)
        switch ($user->role_id) {
            case 33: // SH
                return $user->created_by;

            case 37: // SM
                return optional(User::find($user->created_by))->created_by;

            case 38: // AM
                $sm = User::find($user->created_by);
                $sh = $sm && $sm->created_by ? User::find($sm->created_by) : null;
                return optional($sh)->created_by;
        }

        return null;
    }
}
