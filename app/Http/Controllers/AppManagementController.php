<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AppManagement;
use App\Helpers\CommonHelper;
use Illuminate\Http\Request;

class AppManagementController extends Controller
{
    /**
     * Display app management settings form
     */
    public function index()
    {
        $appManagement = AppManagement::first();

        return view('app.settings', [
            'pastImage' => $appManagement ? $appManagement->past_image : null,
            'upcomingImage' => $appManagement ? $appManagement->upcoming_image : null,
            'ongoingImage' => $appManagement ? $appManagement->ongoing_image : null,
        ]);
    }

    /**
     * Update app management settings
     */
    public function update(Request $request)
    {
        // Validate the request - only images
        $request->validate([
            'past_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'upcoming_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'ongoing_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        $appManagement = AppManagement::first();
        if (!$appManagement) {
            $appManagement = new AppManagement();
        }

        // Handle past image upload
        if ($request->hasFile('past_image')) {
            if ($appManagement->past_image) {
                CommonHelper::deleteAzureImage($appManagement->past_image);
            }
            $uploadResult = CommonHelper::image_path('file_storage', $request->file('past_image'), 'uploads');
            $appManagement->past_image = $uploadResult['master_value'] ?? null;
        }

        // Handle past image removal
        if ($request->has('remove_past_image') && $request->remove_past_image == '1') {
            if ($appManagement->past_image) {
                CommonHelper::deleteAzureImage($appManagement->past_image);
            }
            $appManagement->past_image = null;
        }

        // Handle upcoming image upload
        if ($request->hasFile('upcoming_image')) {
            if ($appManagement->upcoming_image) {
                CommonHelper::deleteAzureImage($appManagement->upcoming_image);
            }
            $uploadResult = CommonHelper::image_path('file_storage', $request->file('upcoming_image'), 'uploads');
            $appManagement->upcoming_image = $uploadResult['master_value'] ?? null;
        }

        // Handle upcoming image removal
        if ($request->has('remove_upcoming_image') && $request->remove_upcoming_image == '1') {
            if ($appManagement->upcoming_image) {
                CommonHelper::deleteAzureImage($appManagement->upcoming_image);
            }
            $appManagement->upcoming_image = null;
        }

        // Handle ongoing image upload
        if ($request->hasFile('ongoing_image')) {
            if ($appManagement->ongoing_image) {
                CommonHelper::deleteAzureImage($appManagement->ongoing_image);
            }
            $uploadResult = CommonHelper::image_path('file_storage', $request->file('ongoing_image'), 'uploads');
            $appManagement->ongoing_image = $uploadResult['master_value'] ?? null;
        }

        // Handle ongoing image removal
        if ($request->has('remove_ongoing_image') && $request->remove_ongoing_image == '1') {
            if ($appManagement->ongoing_image) {
                CommonHelper::deleteAzureImage($appManagement->ongoing_image);
            }
            $appManagement->ongoing_image = null;
        }

        $appManagement->save();

        return redirect()->route('app-management.index')
            ->with('success', 'App management settings updated successfully!');
    }
}
