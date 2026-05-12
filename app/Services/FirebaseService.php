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

    public function createChatRoom($tourId, $dmcId)
    {
        $reference = $this->getChatReference($tourId);

        // Check if already exists
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
            ]);

            return [
                'success' => true,
                'message' => 'Chat room created'
            ];
        }

        return [
            'success' => false,
            'message' => 'Chat room already exists'
        ];
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

    public function upsertChatGuest($tourId, $dmcId, $guestId)
    {
        $this->ensureChatRoomExists($tourId, $dmcId);

        $this->getChatReference($tourId)
            ->getChild('guestId')
            ->set((int) $guestId);

        return [
            'success' => true,
            'message' => 'Chat guest synced successfully.',
            'data' => [
                'tour_id' => (int) $tourId,
                'guest_id' => (int) $guestId,
                'guestId' => (int) $guestId,
            ],
        ];
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
            ]);
        }
    }

    protected function getChatReference($tourId)
    {
        return $this->database->getReference('chat/' . $tourId);
    }
}