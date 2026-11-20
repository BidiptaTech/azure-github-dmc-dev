<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MasterSettingController extends Controller
{
    /*
    * Display Master Settings.
    * Date 18-10-2024
    */
    public function index()
    {
        if (!hasPermission('edit settings')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $settings = Setting::whereIn('name', [
            'name',
            'commission',
            'logo',
            'favicon',
            'file_storage',
            'currency',
            'tax_percentage',
            'conversion',
            'guide_default_pic',
            'vehicle_default_pic',
            'driver_default_pic',
        ])->pluck('value', 'name');

        $existingLogo = $settings['logo'] ?? '';
        $existingFavicon = $settings['favicon'] ?? '';
        $existingGuideDefaultPic = $settings['guide_default_pic'] ?? '';
        $existingVehicleDefaultPic = $settings['vehicle_default_pic'] ?? '';
        $existingDriverDefaultPic = $settings['driver_default_pic'] ?? '';
        $name = $settings['name'] ?? '';
        $tax_percentage = $settings['tax_percentage'] ?? '';
        $conversion = $settings['conversion'] ?? '';
        $currentCurrency  = $settings['currency'] ?? '';
        $file = $settings['file_storage'] ?? 'local';
        $Commission = $settings['commission'] ?? '';

        return view('master-setting', compact(
            'existingLogo',
            'Commission',
            'existingFavicon',
            'existingGuideDefaultPic',
            'existingVehicleDefaultPic',
            'existingDriverDefaultPic',
            'name',
            'file',
            'currentCurrency',
            'tax_percentage',
            'conversion'
        ));
    }


    /*
    * Update Master Settings.
    * Date 18-10-2024
    */
    public function store(Request $request)
    {
        $request->validate([
            'conversion' => 'nullable|numeric',
        ]);

        $get_filestorage = Setting::where('name', 'file_storage')->first()->value ?? 'local';

        foreach ($request->except([
            '_token',
            'logo',
            'favicon',
            'guide_default_pic',
            'vehicle_default_pic',
            'driver_default_pic',
        ]) as $key => $value) {
            Setting::updateOrCreate(
                ['name' => $key],
                ['value' => $value]
            );
        }

        // Logo / favicon — original upload handling (unchanged)
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoName = 'logo_' . time() . '.' . $logoFile->getClientOriginalExtension();

            if ($get_filestorage == 'local') {
                $destinationPath = public_path('build/images');
                $logoFile->move($destinationPath, $logoName);
                $logoPath = asset('build/images/' . $logoName);
            } elseif ($get_filestorage == 's3') {
                $filePath = $logoFile->storeAs('uploads', $logoName, 's3');
                $logoPath = Storage::disk('s3')->url($filePath);
            } elseif ($get_filestorage == 'azure') {
                $filePath = $logoName;
                $stream = fopen($logoFile->getRealPath(), 'r');
                Storage::disk('azure')->writeStream($filePath, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }

                $azureEndpoint = config('filesystems.disks.azure.endpoint');
                $azureContainer = config('filesystems.disks.azure.container');
                $logoPath = rtrim($azureEndpoint, '/') . '/' . $azureContainer . '/' . $filePath;
            }

            Setting::updateOrCreate(
                ['name' => 'logo'],
                ['value' => $logoPath]
            );
        }

        if ($request->hasFile('favicon')) {
            $iconFile = $request->file('favicon');
            $iconName = 'icon_' . time() . '.' . $iconFile->getClientOriginalExtension();

            if ($get_filestorage == 'local') {
                $destinationPath = public_path('build/images');
                $iconFile->move($destinationPath, $iconName);
                $iconPath = asset('build/images/' . $iconName);
            } elseif ($get_filestorage == 's3') {
                $filePath = $iconFile->storeAs('uploads', $iconName, 's3');
                $iconPath = Storage::disk('s3')->url($filePath);
            } elseif ($get_filestorage == 'azure') {
                $filePath = $iconName;
                $stream = fopen($iconFile->getRealPath(), 'r');
                Storage::disk('azure')->writeStream($filePath, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }

                $azureEndpoint = config('filesystems.disks.azure.endpoint');
                $azureContainer = config('filesystems.disks.azure.container');
                $iconPath = rtrim($azureEndpoint, '/') . '/' . $azureContainer . '/' . $filePath;
            }

            Setting::updateOrCreate(
                ['name' => 'favicon'],
                ['value' => $iconPath]
            );
        }

        // Only these 3 new default pics use CommonHelper (same as guide images)
        $this->storeDefaultPicViaCommonHelper($request, 'guide_default_pic');
        $this->storeDefaultPicViaCommonHelper($request, 'vehicle_default_pic');
        $this->storeDefaultPicViaCommonHelper($request, 'driver_default_pic');

        return redirect()->route('master-setting')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Upload guide/vehicle/driver default pic via CommonHelper and save Azure URL in settings.
     */
    private function storeDefaultPicViaCommonHelper(Request $request, string $fieldName): void
    {
        if (!$request->hasFile($fieldName)) {
            return;
        }

        try {
            $uploadResult = CommonHelper::image_path('file_storage', $request->file($fieldName));
            if (empty($uploadResult['master_value'])) {
                Log::warning("Default pic upload returned empty result [{$fieldName}]");
                return;
            }

            Setting::updateOrCreate(
                ['name' => $fieldName],
                ['value' => $uploadResult['master_value']]
            );

            Log::info("Default pic uploaded successfully [{$fieldName}]", [
                'path' => $uploadResult['master_value'],
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to upload default pic [{$fieldName}]: " . $e->getMessage());
        }
    }
}
