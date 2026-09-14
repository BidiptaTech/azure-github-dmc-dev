<?php

namespace Database\Seeders;

use App\Models\Attraction;
use App\Models\Hotel;
use App\Models\Restaurant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ensures PT Millenium Tours & Travel Indonesia (typical userId 18) can load Day Level picks:
 * links existing hotels / restaurants / attractions in Batam, Bandung, Jakarta and adds sample tickets.
 */
class MillenniumIndonesiaServicesSeeder extends Seeder
{
    private function likeOp(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    private function resolveMillenniumDmcUser(): ?User
    {
        $like = $this->likeOp();

        $q = User::query()->whereNull('deleted_at');

        if ($like === 'ilike') {
            $q->where(function ($sub) {
                $sub->where('company_name', 'ilike', '%Millenium%')
                    ->orWhere('company_name', 'ilike', '%Millennium%');
            })
                ->where('company_name', 'ilike', '%Indonesia%');
        } else {
            $q->whereRaw('LOWER(company_name) LIKE ?', ['%millenium%'])
                ->whereRaw('LOWER(company_name) LIKE ?', ['%indonesia%']);
        }

        $user = $q->first();

        if (!$user) {
            $user = User::query()->where('userId', 18)->whereNull('deleted_at')->first();
        }

        return $user;
    }

    private function linkHotels(int $dmcId, array $cityPatterns): int
    {
        if (!Schema::hasTable('hotels')) {
            return 0;
        }

        $like = $this->likeOp();
        $n = 0;

        Hotel::query()
            ->whereNull('deleted_at')
            ->where(function ($q) use ($cityPatterns, $like) {
                foreach ($cityPatterns as $city) {
                    $q->orWhere('city', $like, '%' . $city . '%');
                }
            })
            ->orderBy('id')
            ->limit(80)
            ->get()
            ->each(function (Hotel $h) use ($dmcId, &$n) {
                try {
                    $h->addDmcId($dmcId);
                    $n++;
                } catch (\Throwable $e) {
                    // ignore row-level errors (legacy data)
                }
            });

        return $n;
    }

    private function linkRestaurants(int $dmcId, array $cityPatterns): int
    {
        if (!Schema::hasTable('restaurants') || !Schema::hasColumn('restaurants', 'city')) {
            return 0;
        }

        $like = $this->likeOp();
        $n = 0;
        $rq = Restaurant::query();
        if (Schema::hasColumn('restaurants', 'deleted_at')) {
            $rq->whereNull('deleted_at');
        }
        $rq->where(function ($q) use ($cityPatterns, $like) {
                foreach ($cityPatterns as $city) {
                    $q->orWhere('city', $like, '%' . $city . '%');
                }
            })
            ->orderBy('id')
            ->limit(80)
            ->get()
            ->each(function (Restaurant $r) use ($dmcId, &$n) {
                try {
                    if (method_exists($r, 'addDmcId')) {
                        $r->addDmcId($dmcId);
                        $n++;
                    }
                } catch (\Throwable $e) {
                }
            });

        return $n;
    }

    private function linkAttractions(int $dmcId, array $locationPatterns): int
    {
        if (!Schema::hasTable('attractions') || !Schema::hasColumn('attractions', 'location')) {
            return 0;
        }

        $like = $this->likeOp();
        $n = 0;
        Attraction::query()
            ->whereNull('deleted_at')
            ->where(function ($q) use ($locationPatterns, $like) {
                foreach ($locationPatterns as $loc) {
                    $q->orWhere('location', $like, '%' . $loc . '%');
                }
            })
            ->orderBy('attraction_id')
            ->limit(80)
            ->get()
            ->each(function (Attraction $a) use ($dmcId, &$n) {
                try {
                    $a->addDmcId($dmcId);
                    $n++;
                } catch (\Throwable $e) {
                }
            });

        return $n;
    }

    private function ensureSampleTickets(int $dmcId): void
    {
        if (!Schema::hasTable('tickets')) {
            return;
        }

        $samples = [
            ['attraction_id' => '41', 'ticket_id' => '10000092', 'name' => 'Go Kart'],
            ['attraction_id' => '38', 'ticket_id' => '10000067', 'name' => '1 Day Trip Nirup Island'],
            ['attraction_id' => '43', 'ticket_id' => '10000071', 'name' => 'Galilia Sunset Cruise Dinner'],
        ];

        foreach ($samples as $row) {
            $attr = Attraction::query()
                ->whereNull('deleted_at')
                ->where('attraction_id', $row['attraction_id'])
                ->first();
            if (!$attr) {
                continue;
            }

            // tickets.ticket_id is globally unique in schema — only insert if absent.
            if (Ticket::query()->where('ticket_id', $row['ticket_id'])->exists()) {
                continue;
            }

            Ticket::query()->create([
                'ticket_id' => $row['ticket_id'],
                'name' => $row['name'],
                'attraction_id' => $row['attraction_id'],
                'dmc_id' => $dmcId,
                'status' => 1,
                'adult_price' => 0,
                'child_price' => 0,
            ]);
        }
    }

    public function run(): void
    {
        $user = $this->resolveMillenniumDmcUser();
        if (!$user) {
            $this->command?->warn('MillenniumIndonesiaServicesSeeder: could not find PT Millenium Tours & Travel Indonesia (or userId 18). Skipped.');

            return;
        }

        $dmcId = (int) $user->userId;

        $hc = $this->linkHotels($dmcId, ['Batam', 'Bandung', 'Jakarta']);
        $rc = $this->linkRestaurants($dmcId, ['Batam', 'Bandung']);
        $ac = $this->linkAttractions($dmcId, ['Batam', 'Bandung']);
        $this->ensureSampleTickets($dmcId);

        $this->command?->info(sprintf(
            'MillenniumIndonesiaServicesSeeder: DMC userId=%d (%s). Linked hotels: %d, restaurants: %d, attractions: %d. Sample tickets ensured where attractions exist.',
            $dmcId,
            $user->company_name ?? '',
            $hc,
            $rc,
            $ac
        ));
    }
}
