// lib/Controller/login-controller.dart
import 'package:flutter/material.dart'; // For GlobalKey and potentially other UI elements
import 'package:get/get.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http; // Import http package
import 'dart:convert'; // For jsonDecode if you need to parse the response body

import 'package:dd/View/login-page.dart'; // Assuming this is your login page
import 'package:dd/View/home-page.dart'; // Assuming this is your home page

class LoginController extends GetxController {
  // --- For SharedPreferences ---
  static const String _loggedInKey = 'isLoggedIn';
  static const String _usernameKey = 'username'; // Optional: if you want to save username

  // --- Observables for UI State & Form Fields ---
  var isLoading = false.obs; // To show a loading indicator during login
  var obscureText = true.obs; // For password visibility

  // Form fields - If you use TextEditingControllers in your LoginPage,
  // you might bind them directly or update these Rx variables.
  // For simplicity, let's assume your LoginPage updates these.
  var email = ''.obs; // Changed from username to email to match API
  var password = ''.obs;

  // GlobalKey for Form validation (optional, but good practice)
  final GlobalKey<FormState> loginFormKey = GlobalKey<FormState>();

  // --- Lifecycle Methods ---
  @override
  void onInit() {
    super.onInit();
    // No need to auto-login here if checkLoginStatus is called from main.dart
    // or by the initial route logic.
  }

  // --- Methods ---

  void togglePasswordVisibility() {
    obscureText.value = !obscureText.value;
  }

  Future<void> _saveLoginStatus(bool isLoggedIn, {String? userEmail}) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_loggedInKey, isLoggedIn);
    if (isLoggedIn && userEmail != null) {
      await prefs.setString(_usernameKey, userEmail); // Save email if login successful
    } else if (!isLoggedIn) {
      await prefs.remove(_usernameKey); // Remove email on logout
    }
  }

  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(_loggedInKey) ?? false;
  }

  // --- API Login Logic ---
  Future<void> loginUser() async {
    // Optional: Validate form if using loginFormKey
    // if (!loginFormKey.currentState!.validate()) {
    //   return;
    // }
    // loginFormKey.currentState!.save(); // If using onSaved in TextFormFields

    isLoading.value = true; // Start loading

    try {
      final response = await http.post(
        Uri.parse('http://10.0.2.2:8000/api/login'), // Your API endpoint
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'Accept': 'application/json', // Good practice to accept JSON
        },
        body: {
          'email': email.value, // Use the email Rx variable
          'password': password.value, // Use the password Rx variable
        },
      ).timeout(const Duration(seconds: 10)); // Add a timeout

      if (response.statusCode == 200) {
        // Login successful
        print("Login API call successful: true");
        // Optional: Parse response if it contains user data or token
        // final responseData = jsonDecode(response.body);
        // String? token = responseData['token']; // Example
        // String? userNameFromApi = responseData['user']['name']; // Example

        await _saveLoginStatus(true, userEmail: email.value);
        Get.offAll(() => const HomePage()); // Navigate to HomePage
      } else {
        // Login failed - handle different error status codes
        print("Login API call failed: false");
        print("Status Code: ${response.statusCode}");
        print("Response Body: ${response.body}");

        String errorMessage = "Login failed. Please check your credentials.";
        if (response.statusCode == 401) {
          errorMessage = "Invalid email or password.";
        } else if (response.statusCode == 422) {
          // Try to parse validation errors if your API returns them
          try {
            final errors = jsonDecode(response.body)['errors'];
            if (errors != null && errors is Map) {
              // You could extract specific error messages here
              errorMessage = errors.entries.map((e) => e.value.join(', ')).join('\n');
            }
          } catch (e) {
            // Fallback if error parsing fails
            errorMessage = "Invalid input. Please check your details.";
          }
        } else {
          errorMessage = "An error occurred (Code: ${response.statusCode}). Please try again.";
        }
        Get.snackbar(
          "Login Error",
          errorMessage,
          snackPosition: SnackPosition.BOTTOM,
          backgroundColor: Colors.red,
          colorText: Colors.white,
        );
        await _saveLoginStatus(false); // Ensure login status is false
      }
    } catch (e) {
      // Handle network errors, timeouts, etc.
      print("Login Exception: $e");
      Get.snackbar(
        "Network Error",
        "Could not connect to the server. Please check your internet connection and try again.",
        snackPosition: SnackPosition.BOTTOM,
        backgroundColor: Colors.red,
        colorText: Colors.white,
      );
      await _saveLoginStatus(false); // Ensure login status is false
    } finally {
      isLoading.value = false; // Stop loading
    }
  }

  // --- Logout Logic ---
  Future<void> logout() async {
    // Clear local sensitive data
    email.value = '';
    password.value = '';
    // No need to call API for logout unless your backend requires it
    // (e.g., to invalidate a token on the server-side)

    await _saveLoginStatus(false); // Update SharedPreferences
    print('Logout Successful from LoginController');
    // Navigate back to LoginPage
    Get.offAll(() => const LoginPage()); // Ensures no back navigation to HomePage
  }

  // To be called from main.dart or initial routing logic
  Future<void> checkLoginStatusAndNavigate() async {
    final loggedIn = await isLoggedIn();
    if (loggedIn) {
      // Optional: Load username if saved
      // final prefs = await SharedPreferences.getInstance();
      // String? savedUsername = prefs.getString(_usernameKey);
      // if (savedUsername != null) {
      //   email.value = savedUsername; // Or a specific username Rx variable for display
      // }
      Get.offAll(() => const HomePage());
    } else {
      Get.offAll(() => const LoginPage());
    }
  }
}
