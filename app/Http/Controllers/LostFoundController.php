<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\LostFound;
use App\Models\Tour;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LostFoundController extends Controller
{
    private const OPERATION_ROLE_IDS = [34, 128, 131, 132, 134, 135, 137, 138];

    private function authorizeOperationUser(): void
    {
        $user = Auth::user();
        // Match Trip Logs (TodaysBookingsController): role_id may be string in DB
        if (!$user || !in_array((int) $user->role_id, self::OPERATION_ROLE_IDS, true)) {
            abort(403);
        }
    }

    private function resolveDmcId()
    {
        return CommonHelper::getDmcId(Auth::user());
    }

    /**
     * Lost & Found and Incident Management listing.
     */
    public function index()
    {
        $this->authorizeOperationUser();

        $dmcId = $this->resolveDmcId();
        $reports = collect();

        if ($dmcId) {
            $reports = LostFound::query()
                ->where('dmc_id', $dmcId)
                ->orderByDesc('id')
                ->get();

            $tourDisplayIds = Tour::query()
                ->whereIn('tour_id', $reports->pluck('tour_id')->filter()->unique())
                ->pluck('display_id', 'tour_id');

            $reports->each(function (LostFound $report) use ($tourDisplayIds) {
                $report->tour_display_id = $tourDisplayIds[$report->tour_id] ?? $report->tour_id;
            });
        }

        return view('lost-found.index', compact('reports', 'dmcId'));
    }

    /**
     * Store staff response: comment → lost_found.comments, images → Azure via CommonHelper::image_path.
     */
    public function storeResponse(Request $request, $id)
    {
        $this->authorizeOperationUser();

        $request->validate([
            'comment' => 'nullable|string|max:5000',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:5120',
        ]);

        $dmcId = $this->resolveDmcId();
        if (!$dmcId) {
            return response()->json([
                'success' => false,
                'message' => 'DMC not found for your account.',
            ], 422);
        }

        // Target row by primary key id (scoped to this DMC's tour)
        $report = LostFound::query()
            ->where('id', $id)
            ->where('dmc_id', $dmcId)
            ->first();

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Report not found.',
            ], 404);
        }

        $comment = trim((string) $request->input('comment', ''));
        $hasImages = $request->hasFile('images') && count($request->file('images')) > 0;

        if ($comment === '' && !$hasImages) {
            return response()->json([
                'success' => false,
                'message' => 'Please add a comment or upload at least one image.',
            ], 422);
        }

        $azureImageUrls = [];
        if ($hasImages) {
            foreach ($request->file('images') as $image) {
                try {
                    // Uses file_storage setting → Azure blob when configured (see CommonHelper::image_path)
                    $uploadResult = CommonHelper::image_path('file_storage', $image);
                    if (!empty($uploadResult['master_value'])) {
                        $azureImageUrls[] = $uploadResult['master_value'];
                    }
                } catch (\Throwable $e) {
                    Log::warning('Lost & found Azure image upload failed', [
                        'lost_found_id' => $report->id,
                        'tour_id' => $report->tour_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($comment === '' && empty($azureImageUrls)) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to upload images to storage. Please try again.',
            ], 422);
        }

        if ($comment !== '') {
            $report->comments = $comment;
        }

        if (!empty($azureImageUrls)) {
            if (!Schema::hasColumn('lost_found', 'images')) {
                Log::error('lost_found.images column missing after Azure upload', [
                    'lost_found_id' => $report->id,
                    'urls' => $azureImageUrls,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Images uploaded but could not be saved. Add an `images` JSON column to lost_found (run migration).',
                ], 422);
            }

            $existing = is_array($report->images) ? $report->images : [];
            $report->images = array_values(array_merge($existing, $azureImageUrls));
        }

        $report->save();

        return response()->json([
            'success' => true,
            'message' => 'Response sent successfully.',
            'data' => [
                'id' => $report->id,
                'tour_id' => $report->tour_id,
                'comments' => $report->comments,
                'images' => $report->images,
            ],
        ]);
    }
}
