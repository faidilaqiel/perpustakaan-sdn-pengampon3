<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profil.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|max:100',
            'password' => 'nullable|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = ['name' => $request->name];

        // Jika ganti password
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Jika ganti foto
        if ($request->hasFile('photo')) {
            if ($user->photo && file_exists(public_path('photos/' . $user->photo))) {
                unlink(public_path('photos/' . $user->photo));
            }
            $photoName = time() . '_' . $request->file('photo')->getClientOriginalName();
            $request->file('photo')->move(public_path('photos'), $photoName);
            $data['photo'] = $photoName;
        }

        $user->update($data);

        return back()->with('success', '✅ Profil berhasil diperbarui!');
    }
}