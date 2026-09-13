<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\SendReport;

class FirebaseService
{
    protected Database $database;

    protected Factory $factory;

    public function __construct()
    {
        $this->factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));

        $this->database = $this->factory->createDatabase();
    }

    /**
     * Resolve FCM device tokens from user_tokens/{base64(email)}/{device_id}/token.
     *
     * @param  list<string>  $emails
     * @return array{tokens: list<string>, token_to_email: array<string, string>}
     */
    public function getDeviceTokensByEmails(array $emails): array
    {
        $tokens = [];
        $tokenToEmail = [];

        foreach ($this->filterValidEmails($emails) as $email) {
            $emailKey = base64_encode($email);
            $devices = $this->database->getReference('user_tokens/' . $emailKey)->getValue();

            if (!is_array($devices)) {
                continue;
            }

            foreach ($devices as $device) {
                if (!is_array($device)) {
                    continue;
                }

                $token = isset($device['token']) ? trim((string) $device['token']) : '';
                if ($token !== '') {
                    $tokens[] = $token;
                    $tokenToEmail[$token] = $email;
                }
            }
        }

        return [
            'tokens' => array_values(array_unique($tokens)),
            'token_to_email' => $tokenToEmail,
        ];
    }

    /**
     * @param  list<string>  $tokens
     */
    public function sendPushNotifications(array $tokens, string $title, string $body, array $data = []): array
    {
        $tokens = array_values(array_unique(array_filter(array_map(
            static fn ($token) => trim((string) $token),
            $tokens
        ))));

        if (empty($tokens)) {
            return [
                'success' => false,
                'message' => 'No device tokens found for the selected recipients.',
                'data' => [
                    'success_count' => 0,
                    'failure_count' => 0,
                    'total_tokens' => 0,
                ],
            ];
        }

        $messaging = $this->factory->createMessaging();
        $notification = Notification::create($title, $body);
        $message = CloudMessage::new()->withNotification($notification);

        if (!empty($data)) {
            $message = $message->withData($data);
        }

        $report = $messaging->sendMulticast($message, $tokens);
        $successCount = $report->successes()->count();
        $failureCount = $report->failures()->count();

        // Provide detailed failure reasons for debugging.
        // Kreait reports per-token errors; we return a small set of unique messages.
        $invalidTokenCount = count($report->invalidTokens());
        $unknownTokenCount = count($report->unknownTokens());

        $failureReasons = array_values(array_unique(array_filter(
            $report->failures()->map(static function (SendReport $item) {
                $error = $item->error();
                return $error ? $error->getMessage() : null;
            }),
            static fn ($msg) => !empty($msg)
        )));

        $unknownTokens = $report->unknownTokens();
        $successfulTokens = $report->validTokens();

        $message = $successCount > 0
            ? sprintf('Notification sent to %d of %d device(s).', $successCount, count($tokens))
            : $this->buildFailureMessage($invalidTokenCount, $unknownTokenCount, count($tokens));

        return [
            'success' => $successCount > 0,
            'message' => $message,
            'data' => [
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'total_tokens' => count($tokens),
                'invalid_token_count' => $invalidTokenCount,
                'unknown_token_count' => $unknownTokenCount,
                'failure_reasons' => array_slice($failureReasons, 0, 10),
                'unknown_tokens' => $unknownTokens,
                'successful_tokens' => $successfulTokens,
            ],
        ];
    }

    /**
     * Remove device entries whose FCM tokens are no longer registered.
     *
     * @param  list<string>  $emails
     * @param  list<string>  $unknownTokens
     */
    public function removeStaleTokensForEmails(array $emails, array $unknownTokens): int
    {
        if (empty($unknownTokens)) {
            return 0;
        }

        $unknownLookup = array_fill_keys($unknownTokens, true);
        $removed = 0;

        foreach ($this->filterValidEmails($emails) as $email) {
            $emailKey = base64_encode($email);
            $reference = $this->database->getReference('user_tokens/' . $emailKey);
            $devices = $reference->getValue();

            if (!is_array($devices)) {
                continue;
            }

            foreach ($devices as $deviceKey => $device) {
                if (!is_array($device)) {
                    continue;
                }

                $token = isset($device['token']) ? trim((string) $device['token']) : '';
                if ($token !== '' && isset($unknownLookup[$token])) {
                    $reference->getChild((string) $deviceKey)->remove();
                    $removed++;
                }
            }
        }

        return $removed;
    }

    private function buildFailureMessage(int $invalidTokenCount, int $unknownTokenCount, int $totalTokens): string
    {
        if ($unknownTokenCount > 0 && $unknownTokenCount === $totalTokens) {
            return 'All device token(s) are unregistered in Firebase Cloud Messaging. '
                . 'Ask the recipient to open the mobile app and log in again to register a fresh device token.';
        }

        if ($invalidTokenCount > 0 && $invalidTokenCount === $totalTokens) {
            return 'All device token(s) are invalid. Ask the recipient to open the mobile app and log in again.';
        }

        if ($unknownTokenCount > 0 || $invalidTokenCount > 0) {
            return sprintf(
                'Failed to send notification. %d unregistered and %d invalid device token(s) were found.',
                $unknownTokenCount,
                $invalidTokenCount
            );
        }

        return 'Failed to send notification to any device.';
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