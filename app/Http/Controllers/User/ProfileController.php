<?php

namespace App\Http\Controllers\User;

use App\Models\City;
use App\Models\Province;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
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

        return view('profile.edit', [
            'user' => $request->user(),
            'provinces' => $provinces,
            'cities' => $cities,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'account-deleted');
    }
}
