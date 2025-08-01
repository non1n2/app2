// lib/Controller/barcode_controller.dart
import 'dart:convert';
import 'package:get/get.dart';
import 'package:http/http.dart' as http;
import '../model/baracode_model.dart'; // Your Barcode model

class BarcodeController extends GetxController {
  // Observables
  var isLoading = true.obs;
  var barcodeList = <Barcode>[].obs;
  var errorMessage = ''.obs; // To store any error messages

  // API Configuration (Adjust as needed)
  final String _baseUrl = 'http://10.0.2.2:8000/api'; // Your Laravel API base URL

  @override
  void onInit() {
    super.onInit();
    fetchBarcodes(); // Fetch barcodes when the controller is initialized
  }

  // --- Method to fetch barcodes from the API ---
  Future<void> fetchBarcodes() async {
    try {
      isLoading(true);
      errorMessage(''); // Clear previous error

      // --- OPTIONAL: Get Auth Token if your endpoint is protected ---
      // final prefs = await SharedPreferences.getInstance();
      // final String? token = prefs.getString('auth_token'); // Assuming you save token with this key
      //
      // if (token == null) {
      //   errorMessage('Authentication token not found. Please login again.');
      //   isLoading(false);
      //   // Optionally, navigate to login: Get.offAll(() => LoginPage());
      //   return;
      // }
      // --- End Optional Auth Token ---

      final response = await http.get(
        Uri.parse('$_baseUrl/barcodes'), // Assuming your endpoint is /api/barcodes
        headers: {
          'Accept': 'application/json',
          // 'Authorization': 'Bearer $token', // Uncomment if endpoint is protected
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final List<dynamic> jsonData = json.decode(response.body);
        // Assuming the API returns a list of barcode objects directly
        // If it's nested under a key like 'data', adjust accordingly:
        // final List<dynamic> jsonData = json.decode(response.body)['data'];

        barcodeList.assignAll(
            jsonData.map((data) => Barcode.fromJson(data)).toList()
        );
      } else {
        // Handle API errors (e.g., 401, 404, 500)
        final errorData = json.decode(response.body);
        errorMessage(errorData['message'] ?? 'Failed to load barcodes. Status code: ${response.statusCode}');
        print("API Error: ${response.statusCode} - ${response.body}");
      }
    } catch (e) {
      // Handle network errors, timeouts, parsing errors
      errorMessage('An error occurred: ${e.toString()}');
      print("Fetch Barcodes Exception: $e");
    } finally {
      isLoading(false);
    }
  }

  // --- Method to refresh barcodes (e.g., for pull-to-refresh) ---
  Future<void> refreshBarcodes() async {
    await fetchBarcodes();
  }

// Optional: Method to add a new barcode (if ESP doesn't send directly)
// Future<void> addBarcode(Map<String, dynamic> barcodeData) async { ... }

// Optional: Method to delete a barcode
// Future<void> deleteBarcode(int barcodeId) async { ... }
}
