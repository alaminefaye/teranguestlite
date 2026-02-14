# 🔥 Configuration Firebase - Teranga Guest

## ✅ Configuration Terminée

Firebase est maintenant **100% configuré** pour les notifications push vers l'application mobile !

---

## 📋 CE QUI A ÉTÉ CONFIGURÉ

### 1. **Firebase Admin SDK** ✅
- ✅ Package `kreait/firebase-php` installé
- ✅ Credentials Firebase stockés dans `storage/app/firebase/credentials.json`
- ✅ Service Provider créé et enregistré
- ✅ Singleton Firebase disponible dans toute l'application

### 2. **Base de données** ✅
- ✅ Champ `fcm_token` ajouté à la table `users`
- ✅ Champ `fcm_token_updated_at` pour tracking
- ✅ Migration exécutée avec succès

### 3. **Service de Notifications** ✅
- ✅ `FirebaseNotificationService` créé
- ✅ Méthodes prêtes pour tous types de notifications
- ✅ Support Android & iOS
- ✅ Logging intégré

### 4. **API Endpoints** ✅
- ✅ Routes API créées dans `routes/api.php`
- ✅ Contrôleur `FcmTokenController` pour gérer les tokens
- ✅ Protection par authentification Sanctum

---

## 🚀 UTILISATION

### **1. Enregistrer un FCM Token (depuis l'app mobile)**

```http
POST /api/fcm-token
Content-Type: application/json
Authorization: Bearer {token}

{
  "fcm_token": "device_fcm_token_here"
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "FCM token enregistré avec succès"
}
```

---

### **2. Supprimer un FCM Token (déconnexion)**

```http
DELETE /api/fcm-token
Authorization: Bearer {token}
```

---

### **3. Envoyer des Notifications Push (Backend)**

#### **A. Notification à un utilisateur spécifique**

```php
use App\Services\FirebaseNotificationService;

$firebaseService = app(FirebaseNotificationService::class);

$firebaseService->sendToUser(
    $user,
    'Titre de la notification',
    'Corps de la notification',
    ['data' => 'optionnelle']
);
```

#### **B. Notification de nouvelle commande**

```php
$firebaseService = app(FirebaseNotificationService::class);
$firebaseService->sendNewOrderNotification($user, $order);
```

#### **C. Notification de changement de statut**

```php
$firebaseService = app(FirebaseNotificationService::class);
$firebaseService->sendOrderStatusNotification($user, $order);
```

#### **D. Notification de confirmation de réservation**

```php
$firebaseService = app(FirebaseNotificationService::class);
$firebaseService->sendReservationConfirmation($user, $reservation);
```

#### **E. Notification à plusieurs utilisateurs**

```php
$users = User::where('enterprise_id', $enterpriseId)->get();
$firebaseService = app(FirebaseNotificationService::class);

$firebaseService->sendToMultipleUsers(
    $users->toArray(),
    'Titre',
    'Corps'
);
```

#### **F. Notification à tous les utilisateurs d'une entreprise**

```php
$firebaseService = app(FirebaseNotificationService::class);
$firebaseService->sendToEnterprise(
    $enterpriseId,
    'Titre',
    'Message'
);
```

#### **G. Notification au staff uniquement**

```php
$firebaseService = app(FirebaseNotificationService::class);
$firebaseService->sendToStaff(
    $enterpriseId,
    'Nouvelle commande',
    'Une nouvelle commande vient d\'arriver'
);
```

---

## 📱 INTÉGRATION DANS L'APPLICATION MOBILE

### **1. Flutter - Configuration FCM**

```yaml
# pubspec.yaml
dependencies:
  firebase_core: ^latest
  firebase_messaging: ^latest
```

### **2. Initialiser Firebase**

```dart
// main.dart
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();
  
  // Demander la permission
  FirebaseMessaging messaging = FirebaseMessaging.instance;
  await messaging.requestPermission();
  
  runApp(MyApp());
}
```

### **3. Obtenir et enregistrer le FCM Token**

```dart
Future<void> registerFcmToken() async {
  FirebaseMessaging messaging = FirebaseMessaging.instance;
  
  // Obtenir le token
  String? token = await messaging.getToken();
  
  if (token != null) {
    // Envoyer au backend
    final response = await http.post(
      Uri.parse('${apiUrl}/api/fcm-token'),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $authToken',
      },
      body: jsonEncode({'fcm_token': token}),
    );
  }
  
  // Écouter les rafraîchissements de token
  messaging.onTokenRefresh.listen((newToken) {
    // Mettre à jour le backend
    updateFcmToken(newToken);
  });
}
```

### **4. Écouter les notifications**

```dart
// Quand l'app est en arrière-plan
FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

// Quand l'app est au premier plan
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  print('Notification reçue: ${message.notification?.title}');
  
  // Afficher une notification locale ou une snackbar
  if (message.notification != null) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(message.notification!.title ?? ''),
        content: Text(message.notification!.body ?? ''),
      ),
    );
  }
  
  // Traiter les données
  if (message.data.isNotEmpty) {
    String type = message.data['type'];
    String screen = message.data['screen'];
    
    // Navigation selon le type
    if (type == 'order' && screen == 'OrderDetails') {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => OrderDetailsScreen(
            orderId: message.data['order_id'],
          ),
        ),
      );
    }
  }
});

// Quand on clique sur une notification
FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
  // Naviguer vers l'écran approprié
  handleNotificationClick(message);
});
```

### **5. Supprimer le token à la déconnexion**

```dart
Future<void> logout() async {
  // Supprimer le FCM token du backend
  await http.delete(
    Uri.parse('${apiUrl}/api/fcm-token'),
    headers: {
      'Authorization': 'Bearer $authToken',
    },
  );
  
  // Supprimer le token local
  await FirebaseMessaging.instance.deleteToken();
  
  // Reste de la logique de déconnexion...
}
```

---

## 🔍 TYPES DE NOTIFICATIONS DISPONIBLES

### **1. Nouvelle Commande**
```json
{
  "type": "order",
  "order_id": "123",
  "order_number": "CMD-20260202-001",
  "screen": "OrderDetails"
}
```

### **2. Changement de Statut Commande**
```json
{
  "type": "order_status",
  "order_id": "123",
  "order_number": "CMD-20260202-001",
  "status": "confirmed",
  "screen": "OrderDetails"
}
```

### **3. Confirmation de Réservation**
```json
{
  "type": "reservation",
  "reservation_id": "456",
  "reservation_number": "RES-20260202-001",
  "screen": "ReservationDetails"
}
```

---

## 🎨 EXEMPLE D'INTÉGRATION DANS UN CONTRÔLEUR

### **Notification lors de la création d'une commande**

```php
// app/Http/Controllers/Guest/RoomServiceController.php

public function checkout(Request $request)
{
    // ... validation et création de la commande ...
    
    $order = Order::create([
        'user_id' => $user->id,
        'enterprise_id' => $user->enterprise_id,
        'room_id' => $user->room_number,
        'order_number' => $this->generateOrderNumber(),
        'items' => $validatedItems,
        'total_amount' => $total,
        'status' => 'pending',
    ]);
    
    // 🔥 ENVOYER LA NOTIFICATION PUSH
    $firebaseService = app(FirebaseNotificationService::class);
    $firebaseService->sendNewOrderNotification($user, $order);
    
    // 🔥 NOTIFIER LE STAFF
    $firebaseService->sendToStaff(
        $user->enterprise_id,
        'Nouvelle commande',
        "Nouvelle commande #{$order->order_number} de la chambre {$user->room_number}"
    );
    
    return redirect()->route('guest.orders.index')
        ->with('success', 'Commande passée avec succès !');
}
```

### **Notification lors du changement de statut**

```php
// app/Http/Controllers/Dashboard/OrderController.php

public function update(Request $request, Order $order)
{
    $validated = $request->validate([
        'status' => 'required|in:confirmed,preparing,ready,delivering,delivered,cancelled',
    ]);
    
    $order->update($validated);
    
    // 🔥 NOTIFIER LE CLIENT
    $firebaseService = app(FirebaseNotificationService::class);
    $firebaseService->sendOrderStatusNotification($order->user, $order);
    
    return redirect()->route('dashboard.orders.show', $order)
        ->with('success', 'Statut mis à jour avec succès !');
}
```

---

## 🛠️ VARIABLES D'ENVIRONNEMENT

```env
# Firebase Configuration
FIREBASE_CREDENTIALS=storage/app/firebase/credentials.json
FIREBASE_PROJECT_ID=terangaguest
```

---

## 📁 STRUCTURE DES FICHIERS

```
/storage/app/firebase/
  └── credentials.json          # Credentials Firebase (NE PAS COMMIT)
  
/app/Services/
  └── FirebaseNotificationService.php    # Service de notifications
  
/app/Http/Controllers/Api/
  └── FcmTokenController.php    # Gestion des FCM tokens
  
/app/Providers/
  └── FirebaseServiceProvider.php        # Service Provider Firebase
  
/routes/
  └── api.php                    # Routes API (FCM token)
```

---

## 🔒 SÉCURITÉ

### **Important :**
1. ✅ Le fichier `terangaguest-50ff77e0c82d.json` est dans `storage/app/firebase/`
2. ✅ Ce dossier est dans `.gitignore` (ne sera jamais commit)
3. ✅ Les routes API sont protégées par Sanctum
4. ✅ Seul l'utilisateur authentifié peut modifier son FCM token

### **.gitignore**
```gitignore
storage/app/firebase/
*.json
```

---

## 📊 LOGS

Les notifications Firebase sont loggées automatiquement :

```php
Log::info("Notification sent to user {$user->id}: {$title}");
Log::error("Failed to send notification: " . $e->getMessage());
```

Pour voir les logs :
```bash
tail -f storage/logs/laravel.log
```

---

## ✅ CHECKLIST MOBILE

Avant de déployer l'application mobile :

- [ ] Firebase Core initialisé
- [ ] Permission notifications demandée
- [ ] FCM Token récupéré
- [ ] FCM Token envoyé au backend via API
- [ ] Listeners de notifications configurés
- [ ] Navigation selon les types de notifications
- [ ] Suppression du token à la déconnexion
- [ ] Gestion du rafraîchissement du token

---

## 🧪 TESTS

### **Test 1 : Envoyer une notification manuelle**

```bash
php artisan tinker
```

```php
$user = User::find(1);
$firebaseService = app(App\Services\FirebaseNotificationService::class);
$firebaseService->sendToUser($user, 'Test', 'Message de test');
```

### **Test 2 : Vérifier les credentials**

```bash
php artisan tinker
```

```php
$firebase = app('firebase');
$messaging = $firebase->createMessaging();
echo "Firebase OK!";
```

---

## 📞 SUPPORT

**Project ID :** `terangaguest`  
**Service Account :** `firebase-adminsdk-fbsvc@terangaguest.iam.gserviceaccount.com`

---

## 🎉 RÉSUMÉ

✅ **Backend Laravel** : 100% configuré  
✅ **Service de notifications** : Prêt à l'emploi  
✅ **API FCM Token** : Fonctionnelle  
✅ **Sécurité** : Credentials protégés  
✅ **Documentation** : Complète  

**Firebase est maintenant prêt pour l'application mobile ! 🚀📱**
