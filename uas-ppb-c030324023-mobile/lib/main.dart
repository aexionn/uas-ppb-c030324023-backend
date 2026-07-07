import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'providers/auth_provider.dart';
import 'providers/application_provider.dart';
import 'screens/home_screen.dart';
import 'screens/login_screen.dart';
import 'services/auth_service.dart';
import 'services/application_service.dart';
import 'services/dio_client.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final prefs = await SharedPreferences.getInstance();

  // onUnauthorized fires from the Dio 401 interceptor
  late final AuthProvider authProvider;

  final dio = buildDio(
    prefs: prefs,
    onUnauthorized: () => authProvider.logout(),
  );

  authProvider = AuthProvider(AuthService(dio), prefs);
  await authProvider.restore();

  final applicationProvider = ApplicationProvider(ApplicationService(dio));

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider<AuthProvider>.value(value: authProvider),
        ChangeNotifierProvider<ApplicationProvider>.value(
            value: applicationProvider),
      ],
      child: const App(),
    ),
  );
}

class App extends StatelessWidget {
  const App({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Pendaftaran Kampus',
      theme: ThemeData(useMaterial3: true),
      home: context.watch<AuthProvider>().isLoggedIn
          ? const HomeScreen()
          : const LoginScreen(),
    );
  }
}
