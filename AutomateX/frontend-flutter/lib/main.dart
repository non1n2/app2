// lib/main.dart
import 'package:dd/View/login-page.dart';
import 'package:dd/View/home-page.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:dd/Controller/login-controller.dart'; // Import your LoginController
// import 'package:get_storage/get_storage.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // await GetStorage.init(); // If you use GetStorage

  // Create an instance of LoginController
  final LoginController loginCheckerController = LoginController(); // Or Get.put(LoginController()) if you want this instance to be the one GetX manages globally from the start
  bool isLoggedInValue = await loginCheckerController.isLoggedIn(); // Call the instance method

  runApp(MyApp(isLoggedIn: isLoggedInValue));
}

class MyApp extends StatelessWidget {
  final bool isLoggedIn;

  const MyApp({super.key, required this.isLoggedIn});

  @override
  Widget build(BuildContext context) {
    // If you used 'final LoginController loginCheckerController = LoginController();' above,
    // and you want GetX to manage the LoginController for your pages,
    // you would typically put it here or use bindings.
    // If you used 'Get.put(LoginController())' in main, it's already available.
    // Get.put(LoginController()); // Ensure it's available for LoginPage/HomePage if not put in main()

    return GetMaterialApp(
      title: 'Automate X',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.deepPurple),
        useMaterial3: true,
      ),
      home: isLoggedIn ? const HomePage() : const LoginPage(),
    );
  }
}