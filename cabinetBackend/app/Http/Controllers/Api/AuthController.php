<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    // Inscription
    public function register(Request $request)
    {
        return response()->json([
            'message' => 'Les inscriptions publiques sont désactivées. Contactez l’administrateur.',
        ], 403);
    }

    /**
     * Créer un compte médecin.
     * Réservé à l'administrateur par les routes API.
     */
    public function creerMedecin(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'medecin',
        ]);

        return response()->json([
            'message' => 'Compte médecin créé avec succès.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    /**
     * Créer un compte secrétaire.
     * Réservé à l'administrateur par les routes API.
     */
    public function creerSecretaire(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);


        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'secretaire',
        ]);

        return response()->json([
            'message' => 'Compte secrétaire créé avec succès.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    // Connexion
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {

            throw ValidationException::withMessages(['email' => ['Les identifiants sont incorrects.'],]);
        }

        $token = $user->createToken('cabinet-medical')->plainTextToken;

        return response()->json(['message' => 'Connexion réussie.','user' => $user,'token' => $token,]);
    }

    // Déconnexion

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie.',]);
    }

    // Utilisateur connecté
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
