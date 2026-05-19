<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseService
{
    protected Database $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));

        $this->database = $factory->createDatabase();
    }

    public function createChatRoom($tourId, $dmcId, array $tourDetails = [])
    {
        $reference = $this->getChatReference($tourId);

        // Check if already exists
        $snapshot = $reference->getSnapshot();

        if (!$snapshot->exists()) {
            $sanitizedDetails = $this->sanitizeForRealtimeDatabase($tourDetails);

            $reference->set([
                'tour_id' => $tourId,
                'dmc_id' => (int) $dmcId,
                'created_at' => now()->toDateTimeString(),
                'Messages' => [],
                'messages' => [],
                'ID' => [],
                'guestId' => null,
                'emails' => [],
                'Tour_Details' => $sanitizedDetails,
            ]);

            return [
                'success' => true,
                'message' => 'Chat room created',
                'data' => [
                    'tour_id' => (int) $tourId,
                    'Tour_Details' => $sanitizedDetails,
                ],
            ];
        }

        return [
            'success' => false,
            'message' => 'Chat room already exists',
        ];
    }

    /**
     * Ensure values are JSON/Firebase-friendly (Carbon, nested objects, etc.).
     */
    public function sanitizeForRealtimeDatabase($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTime::ATOM);
        }
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->sanitizeForRealtimeDatabase($v);
            }

            return $out;
        }
        if (is_object($value)) {
            if ($value instanceof \JsonSerializable) {
                return $this->sanitizeForRealtimeDatabase($value->jsonSerialize());
            }
            if (method_exists($value, 'toArray')) {
                return $this->sanitizeForRealtimeDatabase($value->toArray());
            }

            return $this->sanitizeForRealtimeDatabase((array) $value);
        }

        return $value;
    }

    public function upsertChatAssignment($tourId, $dmcId, $orderId, array $assignmentData)
    {
        $payload = array_filter([
            'driverId' => isset($assignmentData['driverId']) ? (int) $assignmentData['driverId'] : null,
            'guideId' => isset($assignmentData['guideId']) ? (int) $assignmentData['guideId'] : null,
        ], static fn ($value) => !is_null($value));

        if (empty($payload)) {
            return [
                'success' => false,
                'message' => 'No driver or guide ID provided for chat sync.',
            ];
        }

        $this->ensureChatRoomExists($tourId, $dmcId);

        $assignmentReference = $this->getChatReference($tourId)
            ->getChild('ID')
            ->getChild((string) $orderId);

        $existingAssignment = $assignmentReference->getValue();
        $existingAssignment = is_object($existingAssignment)
            ? (array) $existingAssignment
            : (is_array($existingAssignment) ? $existingAssignment : []);

        $assignmentReference->set(array_merge(
            $existingAssignment,
            $payload
        ));

        return [
            'success' => true,
            'message' => 'Chat assignment synced successfully.',
            'data' => [
                'tour_id' => (int) $tourId,
                'order_id' => (string) $orderId,
                'assignment' => array_merge(
                    $existingAssignment,
                    $payload
                ),
            ],
        ];
    }

    public function upsertChatGuest($tourId, $dmcId, $guestId, ?string $guestEmail = null)
    {
        $this->ensureChatRoomExists($tourId, $dmcId);

        $this->getChatReference($tourId)
            ->getChild('guestId')
            ->set((int) $guestId);

        $result = [
            'success' => true,
            'message' => 'Chat guest synced successfully.',
            'data' => [
                'tour_id' => (int) $tourId,
                'guest_id' => (int) $guestId,
                'guestId' => (int) $guestId,
            ],
        ];

        if ($guestEmail !== null && trim($guestEmail) !== '') {
            $emailSync = $this->mergeChatEmails($tourId, $dmcId, [$guestEmail]);
            $result['data']['emails'] = $emailSync['data']['emails'] ?? [];
        }

        return $result;
    }

    /**
     * Merge unique email addresses into chat/{tourId}/emails (tour-level list).
     */
    public function mergeChatEmails($tourId, $dmcId, array $emails): array
    {
        $incoming = $this->filterValidEmails($emails);

        if (empty($incoming)) {
            return [
                'success' => true,
                'message' => 'No valid emails to merge.',
                'data' => [
                    'tour_id' => (int) $tourId,
                    'emails' => [],
                ],
            ];
        }

        $this->ensureChatRoomExists($tourId, $dmcId);

        $emailsRef = $this->getChatReference($tourId)->getChild('emails');
        $existing = $this->filterValidEmails($emailsRef->getValue() ?? []);
        $merged = array_values(array_unique(array_merge($existing, $incoming)));

        $emailsRef->set($this->sanitizeForRealtimeDatabase($merged));

        return [
            'success' => true,
            'message' => 'Chat emails merged successfully.',
            'data' => [
                'tour_id' => (int) $tourId,
                'emails' => $merged,
            ],
        ];
    }

    /**
     * @param  mixed  $emails
     * @return list<string>
     */
    private function filterValidEmails($emails): array
    {
        if (!is_array($emails)) {
            return [];
        }

        $valid = [];
        foreach ($emails as $email) {
            if (!is_string($email)) {
                continue;
            }
            $email = trim($email);
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $valid[] = strtolower($email);
        }

        return array_values(array_unique($valid));
    }

    public function removeChatGuest($tourId, $guestId)
    {
        $this->getChatReference($tourId)
            ->getChild('guestId')
            ->set(null);

        return [
            'success' => true,
            'message' => 'Chat guest removed successfully.',
            'data' => [
                'tour_id' => (int) $tourId,
                'guest_id' => (int) $guestId,
            ],
        ];
    }

    protected function ensureChatRoomExists($tourId, $dmcId): void
    {
        $reference = $this->getChatReference($tourId);
        $snapshot = $reference->getSnapshot();

        if (!$snapshot->exists()) {
            $reference->set([
                'tour_id' => $tourId,
                'dmc_id' => (int) $dmcId,
                'created_at' => now()->toDateTimeString(),
                'Messages' => [],
                'messages' => [],
                'ID' => [],
                'guestId' => null,
                'emails' => [],
            ]);
        }
    }

    protected function getChatReference($tourId)
    {
        return $this->database->getReference('chat/' . $tourId);
    }
}