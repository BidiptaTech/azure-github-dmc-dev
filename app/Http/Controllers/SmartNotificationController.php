<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Agent;
use App\Models\Driver;
use App\Models\Guest;
use App\Models\Guide;
use App\Models\Tour;
use App\Models\SmartAppNtf;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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

    public function history()
    {
        $this->authorizeUser();

        $notificationHistory = $this->getNotificationHistoryForCurrentUser();

        return view('smart-notification.history', compact('notificationHistory'));
    }

    private function getNotificationHistoryForCurrentUser()
    {
        $dmcId = $this->resolveDmcId();

        if (!$dmcId) {
            return collect();
        }

        return SmartAppNtf::query()
            ->where('dmc_id', $dmcId)
            ->orderByDesc('id')
            ->limit(100)
            ->get();
    }

    public function recipients(Request $request)
    {
        $this->authorizeUser();

        $type = strtolower(trim((string) $request->query('type', '')));

        return response()->json([
            'success' => true,
            'recipients' => $this->getRecipientsForType($type),
        ]);
    }

    public function send(Request $request, FirebaseService $firebaseService)
    {
        $this->authorizeUser();

        $validated = $request->validate([
            'type' => 'required|string|in:dmc,agents,operations,guests,drivers,guides',
            'sending_to' => 'required|array|min:1',
            'sending_to.*' => 'string',
            'notification_title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $type = strtolower($validated['type']);
        $recipients = $this->getRecipientsForType($type);
        $selected = array_map('strval', $validated['sending_to']);

        if (in_array('all', $selected, true)) {
            $targetRecipients = $recipients;
        } else {
            $targetRecipients = array_values(array_filter(
                $recipients,
                static fn (array $recipient) => in_array((string) $recipient['id'], $selected, true)
            ));
        }

        $emails = array_values(array_unique(array_filter(array_map(
            static fn (array $recipient) => $recipient['email'] ?? null,
            $targetRecipients
        ))));

        if (empty($emails)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid recipient emails found for the selected options.',
            ], 422);
        }

        try {
            $tokenPayload = $firebaseService->getDeviceTokensByEmails($emails);
            $tokens = $tokenPayload['tokens'] ?? [];
            $tokenToEmail = $tokenPayload['token_to_email'] ?? [];

            if (empty($tokens)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No registered device tokens found for the selected recipient emails.',
                    'data' => [
                        'emails' => $emails,
                    ],
                ], 422);
            }

            $result = $firebaseService->sendPushNotifications(
                $tokens,
                $validated['notification_title'],
                $validated['message'],
                [
                    'type' => $type,
                    'source' => 'smart_notification',
                ]
            );

            $result['data']['emails'] = $emails;

            if (
                !$result['success']
                && !empty($result['data']['unknown_tokens'])
            ) {
                $removedCount = $firebaseService->removeStaleTokensForEmails(
                    $emails,
                    $result['data']['unknown_tokens']
                );
                $result['data']['removed_stale_tokens'] = $removedCount;

                if ($removedCount > 0) {
                    $result['message'] .= ' ' . sprintf(
                        '%d stale device token(s) were removed from Firebase. Please ask the user to open the app again and retry.',
                        $removedCount
                    );
                }
            }

            if ($result['success']) {
                $successfulReceivers = $this->resolveSuccessfulReceivers(
                    $targetRecipients,
                    $result['data']['successful_tokens'] ?? [],
                    $tokenToEmail
                );

                if (!empty($successfulReceivers)) {
                    SmartAppNtf::create([
                        'dmc_id' => $this->resolveDmcId(),
                        'sender_type' => $type,
                        'receiver' => $successfulReceivers,
                        'title' => $validated['notification_title'],
                        'message' => $validated['message'],
                    ]);
                }
            }

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Throwable $e) {
            Log::error('Smart notification send failed', [
                'error' => $e->getMessage(),
                'type' => $type,
                'emails' => $emails,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification. Please try again.',
            ], 500);
        }
    }

    private function resolveSuccessfulReceivers(array $targetRecipients, array $successfulTokens, array $tokenToEmail): array
    {
        $successfulEmails = [];

        foreach ($successfulTokens as $token) {
            $email = $tokenToEmail[$token] ?? null;
            if ($email) {
                $successfulEmails[$email] = true;
            }
        }

        if (empty($successfulEmails)) {
            return [];
        }

        $receivers = [];

        foreach ($targetRecipients as $recipient) {
            $email = $this->normalizeEmail($recipient['email'] ?? null);
            if (!$email || !isset($successfulEmails[$email])) {
                continue;
            }

            $receivers[] = [
                'name' => $recipient['name'] ?? 'Unknown',
                'email' => $email,
            ];
        }

        return $receivers;
    }

    private function getRecipientsForType(string $type): array
    {
        $roleId = (int) Auth::user()->role_id;
        $isAdminOrAgentRole = in_array($roleId, [1, 21], true);

        return match (true) {
            $isAdminOrAgentRole && $type === 'dmc' => $this->getDmcUsers(),
            $isAdminOrAgentRole && $type === 'agents' => $this->getAllAgents(),
            $isAdminOrAgentRole && $type === 'operations' => $this->getOperationUsers(),
            !$isAdminOrAgentRole && $type === 'agents' => $this->getAgentsForDmc($this->resolveDmcId()),
            !$isAdminOrAgentRole && $type === 'guests' => $this->getGuestsForDmc($this->resolveDmcId()),
            !$isAdminOrAgentRole && $type === 'drivers' => $this->getDriversForDmc($this->resolveDmcId()),
            !$isAdminOrAgentRole && $type === 'guides' => $this->getGuidesForDmc($this->resolveDmcId()),
            default => [],
        };
    }

    private function resolveDmcId(): ?int
    {
        $dmcId = CommonHelper::getDmcId(Auth::user());

        return $dmcId ? (int) $dmcId : null;
    }

    private function displayValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        if (is_array($value)) {
            $formatted = array_filter(array_map(
                static fn ($item) => trim((string) $item),
                $value
            ));

            return $formatted ? implode(', ', $formatted) : 'N/A';
        }

        return trim((string) $value);
    }

    private function formatUserPhone(?User $user): string
    {
        if (!$user) {
            return 'N/A';
        }

        $countryCode = trim((string) ($user->country_code ?? ''));
        $phone = trim((string) ($user->phone ?? ''));

        if ($countryCode !== '' && $phone !== '') {
            return $countryCode . ' ' . $phone;
        }

        return $phone !== '' ? $phone : ($countryCode !== '' ? $countryCode : 'N/A');
    }

    private function formatGuestContact(Guest $guest): string
    {
        $countryCode = trim((string) ($guest->country_code ?? ''));
        $contact = trim((string) ($guest->contact ?? ''));

        if ($countryCode !== '' && $contact !== '') {
            return $countryCode . ' ' . $contact;
        }

        return $contact !== '' ? $contact : ($countryCode !== '' ? $countryCode : 'N/A');
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = strtolower(trim((string) $email));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    private function buildRecipient(
        string|int $id,
        ?string $name,
        ?string $email = null,
        array $tooltip = [],
        ?string $fallbackName = null
    ): array {
        $label = trim((string) ($name ?: $fallbackName ?: ''));

        return [
            'id' => (string) $id,
            'name' => $label !== '' ? $label : 'Unknown',
            'email' => $this->normalizeEmail($email),
            'tooltip' => $tooltip,
        ];
    }

    private function formatUserRecipient(User $user): array
    {
        return $this->buildRecipient(
            $user->userId,
            $user->name,
            $user->email,
            [
                ['label' => 'Email', 'value' => $this->displayValue($user->email)],
                ['label' => 'Phone', 'value' => $this->formatUserPhone($user)],
                ['label' => 'Company Name', 'value' => $this->displayValue($user->company_name)],
                ['label' => 'Country', 'value' => $this->displayValue($user->country)],
            ],
            $user->company_name
        );
    }

    private function formatAgentRecipient(Agent $agent): array
    {
        $country = $agent->country ?? $agent->user_country ?? null;
        $state = Schema::hasColumn('agents', 'state') ? ($agent->state ?? null) : null;

        return $this->buildRecipient(
            $agent->agent_id,
            $agent->name,
            $agent->email,
            [
                ['label' => 'Email', 'value' => $this->displayValue($agent->email)],
                ['label' => 'Phone', 'value' => $this->displayValue($agent->phone)],
                ['label' => 'City', 'value' => $this->displayValue($agent->city)],
                ['label' => 'State', 'value' => $this->displayValue($state)],
                ['label' => 'Country', 'value' => $this->displayValue($country)],
            ]
        );
    }

    private function formatGuestRecipient(Guest $guest): array
    {
        return $this->buildRecipient(
            $guest->guest_id,
            $guest->guest_name,
            $guest->email,
            [
                ['label' => 'Email', 'value' => $this->displayValue($guest->email)],
                ['label' => 'Contact', 'value' => $this->formatGuestContact($guest)],
                ['label' => 'WhatsApp No', 'value' => $this->displayValue($guest->whatsapp_no)],
                ['label' => 'Tour ID', 'value' => $this->displayValue($guest->tour_id)],
            ]
        );
    }

    private function formatDriverRecipient(Driver $driver): array
    {
        return $this->buildRecipient(
            $driver->driver_id,
            $driver->name,
            $driver->email,
            [
                ['label' => 'Email', 'value' => $this->displayValue($driver->email)],
                ['label' => 'Phone', 'value' => $this->displayValue($driver->phone ?? $driver->mobile_number ?? null)],
                ['label' => 'City', 'value' => $this->displayValue($driver->city)],
                ['label' => 'State', 'value' => $this->displayValue($driver->state)],
                ['label' => 'Country', 'value' => $this->displayValue($driver->country)],
                ['label' => 'Gender', 'value' => $this->displayValue($driver->driver_gender)],
                ['label' => 'Age', 'value' => $this->displayValue($driver->driver_age)],
            ]
        );
    }

    private function formatGuideRecipient(Guide $guide): array
    {
        return $this->buildRecipient(
            $guide->guide_id,
            $guide->name,
            $guide->email,
            [
                ['label' => 'Email', 'value' => $this->displayValue($guide->email)],
                ['label' => 'Contact No', 'value' => $this->displayValue($guide->contact_no)],
                ['label' => 'City', 'value' => $this->displayValue($guide->city)],
                ['label' => 'Country', 'value' => $this->displayValue($guide->country)],
                ['label' => 'Gender', 'value' => $this->displayValue($guide->guide_gender)],
                ['label' => 'Age', 'value' => $this->displayValue($guide->guide_age)],
                ['label' => 'Experience Years', 'value' => $this->displayValue($guide->experience_years)],
            ]
        );
    }

    private function getDmcUsers(): array
    {
        return User::query()
            ->where('role_id', 11)
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => $this->formatUserRecipient($user))
            ->values()
            ->all();
    }

    private function getOperationUsers(): array
    {
        return User::query()
            ->whereIn('role_id', self::OPERATION_ROLE_IDS)
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => $this->formatUserRecipient($user))
            ->values()
            ->all();
    }

    private function getAllAgents(): array
    {
        return Agent::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Agent $agent) => $this->formatAgentRecipient($agent))
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
            ->get()
            ->map(fn (Agent $agent) => $this->formatAgentRecipient($agent))
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
            ->get();

        return $guests
            ->map(fn (Guest $guest) => $this->formatGuestRecipient($guest))
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
            ->get()
            ->map(fn (Driver $driver) => $this->formatDriverRecipient($driver))
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
            ->get()
            ->map(fn (Guide $guide) => $this->formatGuideRecipient($guide))
            ->values()
            ->all();
    }
}
