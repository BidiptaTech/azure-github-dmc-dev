<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Agent;
use App\Models\Driver;
use App\Models\Guest;
use App\Models\Guide;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmartNotificationController extends Controller
{
    private const ALLOWED_ROLE_IDS = [1, 21, 11, 34, 128, 131, 132, 134, 135, 137, 138];

    private const OPERATION_ROLE_IDS = [34, 128, 131, 132, 134, 135, 137, 138];

    private function authorizeUser(): void
    {
        $user = Auth::user();
        if (!$user || !in_array((int) $user->role_id, self::ALLOWED_ROLE_IDS, true)) {
            abort(403);
        }
    }

    public function index()
    {
        $this->authorizeUser();

        $roleId = (int) Auth::user()->role_id;
        $isAdminOrAgentRole = in_array($roleId, [1, 21], true);

        return view('smart-notification.index', compact('isAdminOrAgentRole', 'roleId'));
    }

    public function recipients(Request $request)
    {
        $this->authorizeUser();

        $type = strtolower(trim((string) $request->query('type', '')));
        $roleId = (int) Auth::user()->role_id;
        $isAdminOrAgentRole = in_array($roleId, [1, 21], true);

        $recipients = match (true) {
            $isAdminOrAgentRole && $type === 'dmc' => $this->getDmcUsers(),
            $isAdminOrAgentRole && $type === 'agents' => $this->getAllAgents(),
            $isAdminOrAgentRole && $type === 'operations' => $this->getOperationUsers(),
            !$isAdminOrAgentRole && $type === 'agents' => $this->getAgentsForDmc($this->resolveDmcId()),
            !$isAdminOrAgentRole && $type === 'guests' => $this->getGuestsForDmc($this->resolveDmcId()),
            !$isAdminOrAgentRole && $type === 'drivers' => $this->getDriversForDmc($this->resolveDmcId()),
            !$isAdminOrAgentRole && $type === 'guides' => $this->getGuidesForDmc($this->resolveDmcId()),
            default => [],
        };

        return response()->json([
            'success' => true,
            'recipients' => $recipients,
        ]);
    }

    private function resolveDmcId(): ?int
    {
        $dmcId = CommonHelper::getDmcId(Auth::user());

        return $dmcId ? (int) $dmcId : null;
    }

    private function formatRecipient(string|int $id, ?string $name, ?string $fallbackName = null): array
    {
        $label = trim((string) ($name ?: $fallbackName ?: ''));

        return [
            'id' => (string) $id,
            'name' => $label !== '' ? $label : 'Unknown',
        ];
    }

    private function getDmcUsers(): array
    {
        return User::query()
            ->where('role_id', 11)
            ->orderBy('name')
            ->get(['userId', 'name', 'company_name'])
            ->map(fn (User $user) => $this->formatRecipient(
                $user->userId,
                $user->name,
                $user->company_name
            ))
            ->values()
            ->all();
    }

    private function getOperationUsers(): array
    {
        return User::query()
            ->whereIn('role_id', self::OPERATION_ROLE_IDS)
            ->orderBy('name')
            ->get(['userId', 'name', 'company_name'])
            ->map(fn (User $user) => $this->formatRecipient(
                $user->userId,
                $user->name,
                $user->company_name
            ))
            ->values()
            ->all();
    }

    private function getAllAgents(): array
    {
        return Agent::query()
            ->orderBy('name')
            ->get(['agent_id', 'name'])
            ->map(fn (Agent $agent) => $this->formatRecipient($agent->agent_id, $agent->name))
            ->values()
            ->all();
    }

    private function getAgentsForDmc(?int $dmcId): array
    {
        if (!$dmcId) {
            return [];
        }

        return Agent::query()
            ->whereJsonContains('dmc_id', $dmcId)
            ->orderBy('name')
            ->get(['agent_id', 'name'])
            ->map(fn (Agent $agent) => $this->formatRecipient($agent->agent_id, $agent->name))
            ->values()
            ->all();
    }

    private function getGuestsForDmc(?int $dmcId): array
    {
        if (!$dmcId) {
            return [];
        }

        $tourIds = Tour::query()
            ->where('dmc_id', $dmcId)
            ->pluck('tour_id')
            ->filter()
            ->unique()
            ->values();

        if ($tourIds->isEmpty()) {
            return [];
        }

        $guests = Guest::query()
            ->where(function ($query) use ($tourIds) {
                foreach ($tourIds as $tourId) {
                    $query->orWhereJsonContains('tour_id', (int) $tourId);
                }
            })
            ->orderBy('guest_name')
            ->get(['guest_id', 'guest_name']);

        return $guests
            ->map(fn (Guest $guest) => $this->formatRecipient($guest->guest_id, $guest->guest_name))
            ->values()
            ->all();
    }

    private function getDriversForDmc(?int $dmcId): array
    {
        if (!$dmcId) {
            return [];
        }

        return Driver::query()
            ->where('dmc_id', $dmcId)
            ->orderBy('name')
            ->get(['driver_id', 'name'])
            ->map(fn (Driver $driver) => $this->formatRecipient($driver->driver_id, $driver->name))
            ->values()
            ->all();
    }

    private function getGuidesForDmc(?int $dmcId): array
    {
        if (!$dmcId) {
            return [];
        }

        return Guide::query()
            ->where('dmc_id', $dmcId)
            ->orderBy('name')
            ->get(['guide_id', 'name'])
            ->map(fn (Guide $guide) => $this->formatRecipient($guide->guide_id, $guide->name))
            ->values()
            ->all();
    }
}
