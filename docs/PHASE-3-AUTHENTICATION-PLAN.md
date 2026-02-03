# 🔐 PHASE 3 : AUTHENTIFICATION - PLAN DE DÉVELOPPEMENT

**Date :** 3 Février 2026  
**Version :** 1.0.0  
**Statut :** 📋 Planification

---

## 🎯 OBJECTIF DE LA PHASE

Développer le **système d'authentification complet** de l'application mobile TeranguEST, permettant aux utilisateurs de :
1. Se connecter avec email + mot de passe
2. Stocker le token de manière sécurisée
3. S'auto-connecter au lancement
4. Voir et modifier leur profil
5. Changer leur mot de passe
6. Se déconnecter

---

## 📋 FONCTIONNALITÉS À DÉVELOPPER

### 1. Splash Screen (⏱️ 4h)

**Écran :** `lib/screens/auth/splash_screen.dart`

**Fonctionnalités :**
- Logo TeranguEST animé (fade-in + scale)
- Texte "Bienvenue" avec animation
- Vérification du token stocké
- Navigation automatique :
  - Si token valide → Dashboard
  - Si pas de token → Login

**Design :**
```
┌─────────────────────┐
│                     │
│                     │
│      [LOGO]         │  ← Animation fade-in
│    TERANGUEST       │
│                     │
│   "Bienvenue..."    │  ← Animation text
│                     │
│                     │
└─────────────────────┘
```

**Packages :**
- `animated_text_kit` (optionnel)
- `lottie` (pour animations)

**Fichiers :**
- `lib/screens/auth/splash_screen.dart`
- `lib/widgets/animated_logo.dart` (optionnel)

---

### 2. Login Screen (⏱️ 6h)

**Écran :** `lib/screens/auth/login_screen.dart`

**Fonctionnalités :**
- Logo de l'hôtel en haut
- Formulaire :
  - Champ Email (validation)
  - Champ Mot de passe (masqué, toggle visibility)
  - Checkbox "Se souvenir de moi"
- Bouton "Se connecter" (gradient or)
- Loader pendant connexion
- Messages d'erreur élégants
- Gestion des erreurs API

**Design :**
```
┌─────────────────────┐
│                     │
│      [LOGO]         │
│   King Fahd Palace  │
│                     │
│   ┌─────────────┐   │
│   │ Email       │   │
│   └─────────────┘   │
│                     │
│   ┌─────────────┐   │
│   │ Password 👁  │   │
│   └─────────────┘   │
│                     │
│   ☐ Se souvenir     │
│                     │
│  [Se connecter]     │  ← Bouton gold
│                     │
└─────────────────────┘
```

**Validation :**
- Email : format valide
- Password : non vide (min 6 caractères)

**API :**
```
POST /api/auth/login
Body: {
  "email": "guest@teranga.com",
  "password": "passer123"
}
Response: {
  "success": true,
  "data": {
    "user": {...},
    "token": "1|abc...",
    "token_type": "Bearer"
  }
}
```

**Fichiers :**
- `lib/screens/auth/login_screen.dart`
- `lib/services/auth_service.dart`
- `lib/providers/auth_provider.dart`

---

### 3. Service d'Authentification (⏱️ 4h)

**Service :** `lib/services/auth_service.dart`

**Méthodes :**

```dart
class AuthService {
  // Login
  Future<Map<String, dynamic>> login(String email, String password);
  
  // Logout
  Future<void> logout();
  
  // Get current user
  Future<User> getCurrentUser();
  
  // Change password
  Future<void> changePassword(String current, String newPassword);
  
  // Check if logged in
  Future<bool> isLoggedIn();
  
  // Get token
  Future<String?> getToken();
  
  // Save token
  Future<void> saveToken(String token);
  
  // Delete token
  Future<void> deleteToken();
}
```

**Provider :** `lib/providers/auth_provider.dart`

```dart
class AuthProvider extends ChangeNotifier {
  User? _user;
  bool _isAuthenticated = false;
  bool _isLoading = true;
  
  // Getters
  User? get user;
  bool get isAuthenticated;
  bool get isLoading;
  
  // Methods
  Future<void> login(String email, String password);
  Future<void> logout();
  Future<void> loadUser();
  Future<void> changePassword(String current, String newPassword);
}
```

**Fichiers :**
- `lib/services/auth_service.dart`
- `lib/providers/auth_provider.dart`
- `lib/models/user.dart`

---

### 4. Stockage Sécurisé (⏱️ 2h)

**Package :** `flutter_secure_storage` (déjà installé)

**Configuration :**

```dart
class SecureStorage {
  final FlutterSecureStorage _storage = FlutterSecureStorage();
  
  // Token
  Future<void> saveToken(String token);
  Future<String?> getToken();
  Future<void> deleteToken();
  
  // User data
  Future<void> saveUser(User user);
  Future<User?> getUser();
  Future<void> deleteUser();
  
  // Remember me
  Future<void> setRememberMe(bool value);
  Future<bool> getRememberMe();
}
```

**Fichiers :**
- `lib/services/secure_storage.dart`

---

### 5. Auto-Login (⏱️ 3h)

**Logique :**

1. App démarre → SplashScreen
2. Vérifier si token existe
3. Si oui :
   - Définir le token dans ApiService
   - Récupérer user depuis API
   - Naviguer vers Dashboard
4. Si non :
   - Naviguer vers LoginScreen

**Implémentation :**

```dart
// Dans SplashScreen
@override
void initState() {
  super.initState();
  _checkAuth();
}

Future<void> _checkAuth() async {
  await Future.delayed(Duration(seconds: 2)); // Splash duration
  
  final token = await SecureStorage().getToken();
  
  if (token != null) {
    // Auto-login
    ApiService().setAuthToken(token);
    try {
      final user = await AuthService().getCurrentUser();
      // Success → Dashboard
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => DashboardScreen()),
      );
    } catch (e) {
      // Token invalide → Login
      await SecureStorage().deleteToken();
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => LoginScreen()),
      );
    }
  } else {
    // Pas de token → Login
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => LoginScreen()),
    );
  }
}
```

---

### 6. Écran Profil (⏱️ 6h)

**Écran :** `lib/screens/profile/profile_screen.dart`

**Sections :**

**a) Informations utilisateur**
- Photo de profil (placeholder/avatar)
- Nom complet
- Email
- Rôle (Guest, Staff, Admin)
- Hôtel actuel
- Numéro de chambre

**b) Actions**
- Bouton "Modifier le profil" (optionnel)
- Bouton "Changer mot de passe"
- Bouton "Paramètres" (à venir)
- Bouton "Déconnexion" (rouge)

**Design :**
```
┌─────────────────────┐
│                     │
│      [Avatar]       │
│   John Doe          │
│   guest@hotel.com   │
│   Chambre 101       │
│                     │
│  ┌───────────────┐  │
│  │ 👤 Profil     │  │
│  └───────────────┘  │
│  ┌───────────────┐  │
│  │ 🔒 Mot passe  │  │
│  └───────────────┘  │
│  ┌───────────────┐  │
│  │ ⚙️ Paramètres │  │
│  └───────────────┘  │
│                     │
│  [Déconnexion]      │  ← Rouge
│                     │
└─────────────────────┘
```

**API :**
```
GET /api/user
```

**Fichiers :**
- `lib/screens/profile/profile_screen.dart`
- `lib/widgets/profile_tile.dart`

---

### 7. Changer Mot de Passe (⏱️ 4h)

**Écran :** `lib/screens/profile/change_password_screen.dart`

**Formulaire :**
- Champ "Mot de passe actuel"
- Champ "Nouveau mot de passe"
- Champ "Confirmer nouveau mot de passe"
- Indicateur de force du mot de passe
- Validation en temps réel

**Validation :**
- Current password : non vide
- New password : min 8 caractères, 1 majuscule, 1 chiffre
- Confirmation : match avec new password

**API :**
```
POST /api/auth/change-password
Body: {
  "current_password": "passer123",
  "password": "NewPass123",
  "password_confirmation": "NewPass123"
}
```

**Fichiers :**
- `lib/screens/profile/change_password_screen.dart`
- `lib/widgets/password_strength_indicator.dart`

---

### 8. Déconnexion (⏱️ 2h)

**Fonctionnalités :**
- Dialog de confirmation
- Appel API logout (optionnel)
- Suppression du token local
- Suppression des données user
- Navigation vers LoginScreen
- Vidage du panier (optionnel)

**Implémentation :**

```dart
Future<void> logout() async {
  // Confirmation
  final confirm = await showDialog<bool>(
    context: context,
    builder: (_) => ConfirmDialog(
      title: 'Déconnexion',
      message: 'Êtes-vous sûr de vouloir vous déconnecter ?',
    ),
  );
  
  if (confirm == true) {
    // Logout
    await AuthService().logout();
    await SecureStorage().deleteToken();
    await SecureStorage().deleteUser();
    
    // Clear provider
    Provider.of<AuthProvider>(context, listen: false).clear();
    
    // Navigate to login
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => LoginScreen()),
      (route) => false,
    );
  }
}
```

---

## 🔒 SÉCURITÉ

### Token Storage

**flutter_secure_storage :**
- Chiffrement AES-256
- Keychain (iOS) / Keystore (Android)
- Impossible d'accéder sans déverrouillage device

### API

**Headers :**
```
Authorization: Bearer {token}
```

**Intercepteur Dio :**
```dart
dio.interceptors.add(InterceptorsWrapper(
  onRequest: (options, handler) async {
    final token = await SecureStorage().getToken();
    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    return handler.next(options);
  },
));
```

### Expiration Token

**Gestion :**
- Si 401 Unauthorized → Logout automatique
- Redirect vers LoginScreen
- Message "Session expirée"

---

## 📁 STRUCTURE DES FICHIERS

```
terangaguest_app/
├── lib/
│   ├── models/
│   │   └── user.dart                      ✅ À CRÉER
│   │
│   ├── services/
│   │   ├── auth_service.dart              ✅ À CRÉER
│   │   └── secure_storage.dart            ✅ À CRÉER
│   │
│   ├── providers/
│   │   └── auth_provider.dart             ✅ À CRÉER
│   │
│   ├── screens/
│   │   ├── auth/
│   │   │   ├── splash_screen.dart         ✅ À CRÉER
│   │   │   └── login_screen.dart          ✅ À CRÉER
│   │   │
│   │   └── profile/
│   │       ├── profile_screen.dart        ✅ À CRÉER
│   │       └── change_password_screen.dart ✅ À CRÉER
│   │
│   ├── widgets/
│   │   ├── password_strength_indicator.dart ✅ À CRÉER
│   │   └── profile_tile.dart              ✅ À CRÉER
│   │
│   └── main.dart                          ⚡ MODIFIÉ (AuthProvider)
```

**Total à créer :**
- 10 fichiers
- ~1500 lignes de code

---

## 🎨 DESIGN GUIDELINES

### Couleurs

**Login Screen :**
- Background : Gradient bleu marine
- Input fields : Fond transparent, bordure or
- Bouton : Gradient or
- Texte : Blanc / Gris

**Profile Screen :**
- Background : Gradient bleu marine
- Cards : Fond semi-transparent
- Icons : Or
- Bouton déconnexion : Rouge

### Animations

**Splash Screen :**
- Logo : Fade-in (500ms) + Scale (300ms)
- Texte : Fade-in avec delay (200ms)

**Login Screen :**
- Form fields : Slide-in from bottom
- Bouton : Pulse effect (optionnel)

---

## 🧪 TESTS

### Scénarios à tester

1. **Login Success**
   - Entrer identifiants valides
   - Vérifier navigation vers Dashboard
   - Vérifier token stocké

2. **Login Failure**
   - Entrer identifiants invalides
   - Vérifier message d'erreur
   - Rester sur LoginScreen

3. **Auto-Login**
   - Fermer l'app
   - Relancer
   - Vérifier auto-login

4. **Logout**
   - Se déconnecter
   - Vérifier token supprimé
   - Vérifier navigation vers Login

5. **Change Password**
   - Changer mot de passe
   - Vérifier message succès
   - Se déconnecter et reconnecter

---

## ⏱️ ESTIMATION TEMPS

| Tâche | Durée |
|-------|-------|
| 1. Splash Screen | 4h |
| 2. Login Screen | 6h |
| 3. Auth Service | 4h |
| 4. Secure Storage | 2h |
| 5. Auto-Login | 3h |
| 6. Profile Screen | 6h |
| 7. Change Password | 4h |
| 8. Logout | 2h |
| **TOTAL** | **31h** |

Avec tests et debug : **~35h**

---

## 🎯 PRIORITÉS

### Must Have (MVP)
1. ✅ Login Screen
2. ✅ Auto-Login
3. ✅ Token Storage
4. ✅ Profile Screen
5. ✅ Logout

### Should Have
6. ✅ Splash Screen
7. ✅ Change Password
8. ⏳ Remember Me

### Nice to Have
9. ⏳ Password strength indicator
10. ⏳ Biometric authentication
11. ⏳ Forgot password

---

## 📚 PACKAGES NÉCESSAIRES

```yaml
dependencies:
  # Déjà installés
  flutter_secure_storage: ^9.0.0  ✅
  shared_preferences: ^2.2.2      ✅
  dio: ^5.4.0                     ✅
  provider: ^6.1.1                ✅
  
  # À ajouter (optionnel)
  animated_text_kit: ^4.2.2       ⏳ Animations texte
  lottie: ^3.0.0                  ⏳ Animations Lottie
```

---

## ✅ CHECKLIST DÉVELOPPEMENT

### Setup
- [ ] Créer modèle User
- [ ] Créer AuthService
- [ ] Créer SecureStorage
- [ ] Créer AuthProvider
- [ ] Intégrer Provider dans main.dart

### Écrans
- [ ] SplashScreen avec animations
- [ ] LoginScreen avec formulaire
- [ ] ProfileScreen avec infos user
- [ ] ChangePasswordScreen

### Fonctionnalités
- [ ] Login API
- [ ] Stockage token sécurisé
- [ ] Auto-login au démarrage
- [ ] Logout complet
- [ ] Change password API
- [ ] Intercepteur token Dio
- [ ] Gestion 401 Unauthorized

### Tests
- [ ] Login valide
- [ ] Login invalide
- [ ] Auto-login
- [ ] Logout
- [ ] Change password
- [ ] Token expiration

---

## 🚀 COMMANDE POUR DÉMARRER

```bash
cd terangaguest_app
flutter pub get
flutter run
```

---

**📋 PHASE 3 : AUTHENTIFICATION - PRÊTE À ÊTRE DÉVELOPPÉE ! 🔐**

**Temps estimé total :** ~35h  
**Priorité :** Haute (nécessaire pour tester le Room Service avec utilisateurs réels)
