<?php

namespace App\Http\Controllers;

use App\Enums\SettingKey;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Http\Resources\StoreResource;
use App\Models\DistributionCenter;
use App\Models\Settings;
use App\Rules\UniqueEmailRule;
use App\Rules\UniqueUsernameRule;
use App\Rules\ValidUsernameRule;
use Illuminate\Support\Facades\DB;
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
        foreach ($request->all() as $key => $value) {
            Settings::setValue($key, $value, true);
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
