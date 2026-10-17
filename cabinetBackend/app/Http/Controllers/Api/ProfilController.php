<?php
// À copier dans : app/Http/Controllers/Api/ProfilController.php
// Ces routes sont accessibles à TOUS les utilisateurs connectés (admin, médecin, secrétaire),
// mais chacun ne peut modifier que SON propre compte.

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfilController extends Controller
{

    private function donnees($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'photo' => $user->photo,
        ];
    }

    /**
     * Modifier son nom et son email
     */
    public function modifierProfil(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès.',
            'user' => $this->donnees($user),
        ]);
    }

    /**
     * Changer son mot de passe
     */
    public function changerMotDePasse(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        $user = $request->user();

        // Vérifier le mot de passe actuel
        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        // Déconnecter les autres appareils (on garde la session actuelle)
        $tokenActuel = $request->user()->currentAccessToken();
        $user->tokens()->where('id', '!=', $tokenActuel->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe modifié avec succès.',
        ]);
    }

    /**
     * Ajouter ou remplacer sa photo de profil
     */
    public function changerPhoto(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // 2 Mo
        ]);

        $user = $request->user();

        // Supprimer l'ancienne photo
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        // Enregistrer la nouvelle dans storage/app/public/photos
        $user->photo = $request->file('photo')->store('photos', 'public');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Photo de profil mise à jour.',
            'user' => $this->donnees($user),
        ]);
    }

    /**
     * Supprimer sa photo de profil
     */
    public function supprimerPhoto(Request $request)
    {
        $user = $request->user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->photo = null;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Photo de profil supprimée.',
            'user' => $this->donnees($user),
        ]);
    }
}
