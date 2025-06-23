<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Storage;

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
        $settings = Setting::whereIn('name', ['name', 'commission', 'logo', 'favicon', 'file_storage','currency','tax_percentage'])->pluck('value', 'name');
        $existingLogo = $settings['logo'] ?? '';
        $existingFavicon = $settings['favicon'] ?? '';
        $name = $settings['name'] ?? '';
        $tax_percentage = $settings['tax_percentage'] ?? '';
        $currentCurrency  = $settings['currency'] ?? '';
        $file = $settings['file_storage'] ?? 'local';
        $Commission = $settings['commission'] ?? '';
        return view('master-setting', compact('existingLogo', 'Commission', 'existingFavicon', 'name','file','currentCurrency','tax_percentage'));
    }


    /*
    * Update Master Settings.
    * Date 18-10-2024
    */
    public function store(Request $request)
    {
        $get_filestorage = Setting::where('name', 'file_storage')->first()->value ?? 'local'; // default to 'local'
        foreach ($request->except(['_token', 'logo', 'favicon']) as $key => $value) {
            Setting::updateOrCreate(
                ['name' => $key], 
                ['value' => $value] 
            );
        }
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoName = 'logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
            if ($get_filestorage == 'local') {
                // For local storage
                $destinationPath = public_path('build/images');
                $logoFile->move($destinationPath, $logoName);
                $logoPath = asset('build/images/' . $logoName); // Full URL for local storage
            } elseif ($get_filestorage == 's3') {
                // For S3 storage
                $logoPath = Storage::disk('s3')->url(Storage::disk('s3')->putFileAs('uploads', $logoFile, $logoName));
            } elseif ($get_filestorage == 'azure') {
                // For Azure storage
                $logoPath = Storage::disk('azure')->url(Storage::disk('azure')->putFileAs('uploads', $logoFile, $logoName));
            }
        
            // Save logo path in the settings
            Setting::updateOrCreate(
                ['name' => 'logo'],
                ['value' => $logoPath]
            );
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $iconFile = $request->file('favicon');
            $iconName = 'icon_' . time() . '.' . $iconFile->getClientOriginalExtension();
        
            if ($get_filestorage == 'local') {
                // For local storage
                $destinationPath = public_path('build/images');
                $iconFile->move($destinationPath, $iconName);
                $iconPath = asset('build/images/' . $iconName); // Full URL for local storage
            } elseif ($get_filestorage == 's3') {
                // For S3 storage
                $iconPath = Storage::disk('s3')->url(Storage::disk('s3')->putFileAs('uploads', $iconFile, $iconName));
            } elseif ($get_filestorage == 'azure') {
                // For Azure storage
                $iconPath = Storage::disk('azure')->url(Storage::disk('azure')->putFileAs('uploads', $iconFile, $iconName));
            }
        
            // Save favicon path in the settings
            Setting::updateOrCreate(
                ['name' => 'favicon'],
                ['value' => $iconPath]
            );
        }
        return redirect()->route('master-setting')
            ->with('success', 'Settings updated successfully.');
    }


}
