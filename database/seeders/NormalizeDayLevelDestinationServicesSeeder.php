<?php

namespace Database\Seeders;

use App\Models\DayLevel;
use Illuminate\Database\Seeder;

class NormalizeDayLevelDestinationServicesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = DayLevel::query()
            ->whereNull('deleted_at')
            ->get();

        $updatedCount = 0;

        foreach ($rows as $row) {
            $changed = false;

            $interCity = is_array($row->inter_city) ? $row->inter_city : [];
            $activities = is_array($row->activities) ? $row->activities : [];

            $interCity = $this->normalizeInterCityPayload($interCity, $changed);
            $activities = $this->normalizeActivitiesPayload($activities, $changed);

            if (! $changed) {
                continue;
            }

            $row->forceFill([
                'inter_city' => $interCity,
                'activities' => $activities,
            ])->save();

            $updatedCount++;
        }

        $this->command?->info("Normalized destination services in {$updatedCount} day_levels row(s).");
    }

    private function normalizeInterCityPayload(array $payload, bool &$changed): array
    {
        if (isset($payload['destinations']) && is_array($payload['destinations'])) {
            $payload['destinations'] = $this->normalizeDestinations($payload['destinations'], $changed);
            return $payload;
        }

        if (isset($payload['Master_DMC']) && is_array($payload['Master_DMC'])) {
            foreach ($payload['Master_DMC'] as $i => $masterNode) {
                if (! is_array($masterNode)) {
                    continue;
                }
                $destinations = is_array($masterNode['destinations'] ?? null) ? $masterNode['destinations'] : [];
                $payload['Master_DMC'][$i]['destinations'] = $this->normalizeDestinations($destinations, $changed);
            }
        }

        return $payload;
    }

    private function normalizeActivitiesPayload(array $payload, bool &$changed): array
    {
        // activities currently stores destinations list in this flow.
        if (! array_is_list($payload)) {
            return $payload;
        }

        return $this->normalizeDestinations($payload, $changed);
    }

    private function normalizeDestinations(array $destinations, bool &$changed): array
    {
        foreach ($destinations as $i => $destination) {
            if (! is_array($destination)) {
                continue;
            }

            if (array_key_exists('services', $destination)) {
                if (! array_key_exists('service_meta', $destination) && is_array($destination['services'])) {
                    $destination['service_meta'] = $destination['services'];
                }
                unset($destination['services']);
                $changed = true;
            }

            $destinations[$i] = $destination;
        }

        return $destinations;
    }
}

