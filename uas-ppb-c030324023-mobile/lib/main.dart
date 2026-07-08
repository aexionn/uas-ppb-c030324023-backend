import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'providers/auth_provider.dart';
import 'providers/application_provider.dart';
import 'providers/admin_application_provider.dart';
import 'screens/home_screen.dart';
import 'screens/admin_home_screen.dart';
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

  final applicationService = ApplicationService(dio);
  final applicationProvider = ApplicationProvider(applicationService);
  final adminApplicationProvider = AdminApplicationProvider(applicationService);

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider<AuthProvider>.value(value: authProvider),
        ChangeNotifierProvider<ApplicationProvider>.value(
            value: applicationProvider),
        ChangeNotifierProvider<AdminApplicationProvider>.value(
            value: adminApplicationProvider),
      ],
      child: const App(),
    ),
  );
}

class App extends StatelessWidget {
  const App({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    
    Widget homeWidget;
    if (auth.isLoggedIn) {
      if (auth.account?.role == 'admin') {
        homeWidget = const AdminHomeScreen();
      } else {
        homeWidget = const HomeScreen();
      }
    } else {
      homeWidget = const LoginScreen();
    }

    return MaterialApp(
      title: 'Pendaftaran Kampus',
      theme: ThemeData(useMaterial3: true),
      home: homeWidget,
    );
  }
}
