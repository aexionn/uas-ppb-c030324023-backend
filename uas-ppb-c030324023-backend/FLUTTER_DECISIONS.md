# Flutter Mobile App — Design Decisions

Resolved during grilling session. All decisions agreed by stakeholder.

## D1: State Management → Provider

Provider only. No Riverpod, no BLoC. Sufficient for this app's complexity (login state, one application form, account page). `ChangeNotifier` + `Consumer` covers it.

## D2: Navigation → Plain Navigator.push

No GoRouter, no auto_route. App has ~5 screens with no deep linking requirement. `Navigator.push(context, MaterialPageRoute(...))` everywhere.

## D3: HTTP Client → Dio

Dio over http package. Interceptors for JWT attachment (`Authorization: Bearer $token`) and 401 handling (auto-logout). Single `dio` instance shared via Provider.

## D4: Token Storage → shared_preferences

JWT stored in `shared_preferences`. No flutter_secure_storage — this is a campus admission app, not a banking app. Token read on app start to restore session.

## D5: Base URL → const String

```dart
const String baseUrl = 'http://10.0.2.2:8000/api/v1'; // Android emulator → host
```

No `.env`, no flavor system. Change the const when deploying. `// ponytail: .env/flavors when multi-environment needed`

## D6: Photo Capture → image_picker

`image_picker` for camera + gallery. No cropping library. Server accepts the photo as-is (JPEG/PNG, validated server-side).

## D7: Edit Window Countdown → Timer.periodic

`Timer.periodic(Duration(seconds: 1), ...)` in the Provider. Counts down from `editable_until` (server-provided ISO timestamp). When hits zero, disable submit button and show "Waktu edit habis". Timer disposed in Provider's `dispose()`.

## D8: Error Handling → Server Message Passthrough

No client-side error mapping. Server returns validation errors as JSON → display `response.data['message']` or field-level errors directly. Generic catch for network errors: "Gagal terhubung ke server".

## D9: Project Structure → Layer-First

```
lib/
  models/        # Data classes (Account, Application)
  services/      # Dio calls (AuthService, ApplicationService)
  providers/     # ChangeNotifiers (AuthProvider, ApplicationProvider)
  screens/       # Full-page widgets
  widgets/       # Reusable components (form fields, photo picker)
  main.dart
```

Not feature-first. App is small enough that layer-first keeps things findable without cross-feature duplication.

## D10: Slice Breakdown → M1 through M4

| Slice | Scope |
|-------|-------|
| M1 | Project scaffold, Dio client, AuthProvider, Login + Register screens |
| M2 | Application form submit (create), photo picker, success feedback |
| M3 | Application detail view, edit with countdown timer, lock state display |
| M4 | Account page (view NISN/username/email, change password), logout |

## D11: Dependencies → 4 Packages Only

```yaml
dependencies:
  provider: ^6.0.0
  dio: ^5.0.0
  shared_preferences: ^2.0.0
  image_picker: ^1.0.0
```

No additional packages. Material icons built-in. No intl (dates formatted server-side or with simple string ops).

## D12: Theming → Default Material 3

`ThemeData(useMaterial3: true)` with no color customization. Default purple seed. No dark mode. Looks fine out of the box for a campus app.

## D13: Client-Side Validation → Minimal (Non-Empty Only)

Form fields use `validator: (v) => v == null || v.isEmpty ? 'Wajib diisi' : null`. No regex, no length checks, no format validation. Server handles real validation and returns specific error messages. Avoids client/server rule drift.
