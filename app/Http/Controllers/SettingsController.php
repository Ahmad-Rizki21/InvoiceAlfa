<?php

namespace App\Http\Controllers;

use App\Enums\SettingKey;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Models\DistributionCenter;
use App\Models\Settings;
use App\Models\Store;
use App\Rules\UniqueEmailRule;
use App\Rules\UniqueUsernameRule;
use App\Rules\ValidUsernameRule;
use App\Services\FileUpload\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $settings = Settings::config()->all();

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'settings' => $settings,
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->except([
            SettingKey::SignatureImage->value,
            SettingKey::StampImage->value,
        ]);

        foreach ($input as $key => $value) {
            Settings::setValue($key, $value, true);
        }

        if ($request->hasFile(SettingKey::SignatureImage->value)) {
            $uploadedFile = UploadedFile::from($request->file(SettingKey::SignatureImage->value));
            $path = $uploadedFile->disk('public')->store('settings-signature');

            $oldValue = Settings::data()->get(SettingKey::SignatureImage->value);

            if ($oldValue && Storage::disk('public')->exists($oldValue)) {
                Storage::disk('public')->delete($oldValue);
            }

            Settings::setValue(SettingKey::SignatureImage->value, $path, true);
        } else if (! $request->input(SettingKey::SignatureImage->value)){
            $oldValue = Settings::data()->get(SettingKey::SignatureImage->value);

            if ($oldValue && Storage::disk('public')->exists($oldValue)) {
                Storage::disk('public')->delete($oldValue);
            }

            Settings::setValue(SettingKey::SignatureImage->value, null, true);
        }

        if ($request->hasFile(SettingKey::StampImage->value)) {
            $uploadedFile = UploadedFile::from($request->file(SettingKey::StampImage->value));
            $path = $uploadedFile->disk('public')->store('settings-stamp');

            $oldValue = Settings::data()->get(SettingKey::StampImage->value);

            if ($oldValue && Storage::disk('public')->exists($oldValue)) {
                Storage::disk('public')->delete($oldValue);
            }

            Settings::setValue(SettingKey::StampImage->value, $path, true);
        } else if (! $request->input(SettingKey::StampImage->value)) {
            $oldValue = Settings::data()->get(SettingKey::StampImage->value);

            if ($oldValue && Storage::disk('public')->exists($oldValue)) {
                Storage::disk('public')->delete($oldValue);
            }

            Settings::setValue(SettingKey::StampImage->value, null, true);
        }

        $settings = Settings::config()->all();

        return $this->json([
            'status' => 'success',
            'message' => __(':entity successfully updated', ['entity' => __('Settings')]),
            'data' => [
                'settings' => $settings,
            ],
        ]);
    }
}
