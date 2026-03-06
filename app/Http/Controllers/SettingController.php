<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::firstOrCreate(['id' => 1], [
            'hospital_name' => 'Mi Hospital',
            'currency' => 'PEN'
        ]);
        return view('admin.settings.index', compact('setting'));
    }

    public function update(Request $request, Setting $setting)
    {
        $data = $request->validate([
            'hospital_name' => 'required|string|max:255',
            'ruc_nit' => 'required|string|max:20',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'currency' => 'required|string|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $setting->update($data);

        return back()->with('success', 'Configuración actualizada correctamente.');
    }
}
