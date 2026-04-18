<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\City;
use App\Models\Province;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $provinces = Cache::rememberForever('nepal_provinces', function () {
            return Province::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);
        });

        $selectedProvinceId = old('province_id', $request->user()->province_id);
        $cities = collect();

        if ($selectedProvinceId) {
            $cities = City::query()
                ->where('province_id', $selectedProvinceId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'province_id']);
        }

        return view('admin.profile.edit', [
            'user' => $request->user(),
            'provinces' => $provinces,
            'cities' => $cities,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('admin.profile.edit')->with('status', 'profile-updated');
    }
}
