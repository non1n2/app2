// lib/View/home-page.dart
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../Controller/baracode_controller.dart';
import '../Controller/login-controller.dart'; // For logout
import '../model/baracode_model.dart'; // Import Barcode model

// Assuming LoginController is already put, e.g. in main.dart or by a previous route
// final LoginController loginController = Get.find<LoginController>(); // Or Get.put if necessary

class HomePage extends StatelessWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context) {
    // Put BarcodeController here so it's initialized when HomePage is built
    // lazy:false ensures onInit (and thus fetchBarcodes) is called immediately.
    final BarcodeController barcodeController = Get.put(BarcodeController(), permanent: false);
    final LoginController loginController = Get.put(LoginController()); // Assuming it's already put

    return Scaffold(
        drawer: Drawer(
        child: ListView( // Using ListView for better structure and scrollability if needed
          padding: EdgeInsets.zero, // Remove default padding for ListView in Drawer
          children: <Widget>[
            // Optional: DrawerHeader for a nicer look
            const DrawerHeader(
              decoration: BoxDecoration(
                color: Colors.blue, // Or your app's primary color
              ),
              child: Text(
                'Menu',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 24,
                ),
              ),
            ),
            ListTile(
              leading: const Icon(Icons.settings),
              title: const Text('Settings'),
              onTap: () {
                // Navigate to Settings page or perform action
                Navigator.pop(context); // Close the drawer
                // Get.to(() => SettingsPage()); // Example navigation
                print('Settings tapped');
              },
            ),
            ListTile(
              leading: const Icon(Icons.person_add),
              title: const Text('Add Admin'),
              onTap: () {
                // Navigate to Add Admin page or perform action
                Navigator.pop(context); // Close the drawer
                // Get.to(() => AddAdminPage()); // Example navigation
                print('Add Admin tapped');
              },
            ),
            ListTile(
              leading: const Icon(Icons.perm_device_information_sharp),
              title: const Text('About Us'),
              onTap: () {
                // Navigate to About Us page or perform action
                Navigator.pop(context); // Close the drawer
                // Get.to(() => AboutUsPage()); // Example navigation
                print('About Us tapped');
              },
            ),
            const Divider(), // Optional: Adds a visual separator
            ListTile(
              leading: const Icon(Icons.logout),
              title: const Text('Logout'),
              onTap: () {
                // It's good practice to close the drawer before showing a dialog
                Navigator.pop(context);
                Get.defaultDialog(
                  title: "Logout",
                  middleText: "Are you sure you want to logout?",
                  textConfirm: "Logout",
                  textCancel: "Cancel",
                  onConfirm: () {
                    loginController.logout();
                    // Get.offAll is already handled in your loginController.logout()
                  },
                );
              },
            ),
          ],
        ),
      ),
      appBar: AppBar(
        title: const Text('Barcode Scanner Data'), // Updated title
        centerTitle: true,
        actions: [
          // Optional: Refresh button
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              barcodeController.refreshBarcodes();
            },
          ),
        ],
      ),
      body: Obx(() { // Use Obx to listen to changes in BarcodeController
        if (barcodeController.isLoading.value) {
          return const Center(child: CircularProgressIndicator());
        }

        if (barcodeController.errorMessage.value.isNotEmpty) {
          return Center(
            child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    barcodeController.errorMessage.value,
                    style: const TextStyle(color: Colors.red, fontSize: 16),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 20),
                  ElevatedButton(
                    onPressed: () => barcodeController.fetchBarcodes(),
                    child: const Text('Retry'),
                  )
                ],
              ),
            ),
          );
        }

        if (barcodeController.barcodeList.isEmpty) {
          return const Center(
            child: Text(
              'No barcode data found.',
              style: TextStyle(fontSize: 18),
            ),
          );
        }

        // --- Displaying data using DataTable ---
        return SingleChildScrollView( // DataTable often needs to be scrollable
          scrollDirection: Axis.vertical,
          child: SingleChildScrollView( // For horizontal scrolling if table is wide
            scrollDirection: Axis.horizontal,
            child: DataTable(
              columnSpacing: 20.0, // Adjust spacing as needed
              columns: const <DataColumn>[
                DataColumn(label: Text('Barcode', style: TextStyle(fontWeight: FontWeight.bold))),
                DataColumn(label: Text('Product Name', style: TextStyle(fontWeight: FontWeight.bold))),
                DataColumn(label: Text('Price', style: TextStyle(fontWeight: FontWeight.bold))),
                DataColumn(label: Text('Quantity', style: TextStyle(fontWeight: FontWeight.bold))),
                DataColumn(label: Text('Scanned At', style: TextStyle(fontWeight: FontWeight.bold))),
                // Add more columns as needed (e.g., Description)
              ],
              rows: barcodeController.barcodeList.map((Barcode barcode) {
                return DataRow(
                  cells: <DataCell>[
                    DataCell(Text(barcode.barcodeValue)),
                    DataCell(Text(barcode.productName ?? 'N/A')),
                    DataCell(Text(barcode.price != null ? '\$${barcode.price!.toStringAsFixed(2)}' : 'N/A')),
                    DataCell(Text(barcode.quantity?.toString() ?? 'N/A')),
                    DataCell(Text(
                      // Format DateTime to a more readable string
                      "${barcode.createdAt.toLocal().year}-${barcode.createdAt.toLocal().month.toString().padLeft(2, '0')}-${barcode.createdAt.toLocal().day.toString().padLeft(2, '0')} ${barcode.createdAt.toLocal().hour.toString().padLeft(2, '0')}:${barcode.createdAt.toLocal().minute.toString().padLeft(2, '0')}",
                    )),
                    // Add more cells corresponding to your columns
                  ],
                );
              }).toList(),
            ),
          ),
        );
      }),
    );
  }
}
































// // C:/Users/NoN/Desktop/automatex/frontend-flutter/lib/View/home-page.dart
// import 'package:flutter/material.dart';
// import 'package:get/get.dart';
// import '../Controller/login-controller.dart';
//
// final LoginController loginController = Get.put(LoginController());
//
// class HomePage extends StatelessWidget {
//   const HomePage({super.key}); // Added const constructor
//
//   @override
//   Widget build(BuildContext context) {
//     return Scaffold(
//       drawer: Drawer(
//         child: ListView( // Using ListView for better structure and scrollability if needed
//           padding: EdgeInsets.zero, // Remove default padding for ListView in Drawer
//           children: <Widget>[
//             // Optional: DrawerHeader for a nicer look
//             const DrawerHeader(
//               decoration: BoxDecoration(
//                 color: Colors.blue, // Or your app's primary color
//               ),
//               child: Text(
//                 'Menu',
//                 style: TextStyle(
//                   color: Colors.white,
//                   fontSize: 24,
//                 ),
//               ),
//             ),
//             ListTile(
//               leading: const Icon(Icons.settings),
//               title: const Text('Settings'),
//               onTap: () {
//                 // Navigate to Settings page or perform action
//                 Navigator.pop(context); // Close the drawer
//                 // Get.to(() => SettingsPage()); // Example navigation
//                 print('Settings tapped');
//               },
//             ),
//             ListTile(
//               leading: const Icon(Icons.person_add),
//               title: const Text('Add Admin'),
//               onTap: () {
//                 // Navigate to Add Admin page or perform action
//                 Navigator.pop(context); // Close the drawer
//                 // Get.to(() => AddAdminPage()); // Example navigation
//                 print('Add Admin tapped');
//               },
//             ),
//             ListTile(
//               leading: const Icon(Icons.perm_device_information_sharp),
//               title: const Text('About Us'),
//               onTap: () {
//                 // Navigate to About Us page or perform action
//                 Navigator.pop(context); // Close the drawer
//                 // Get.to(() => AboutUsPage()); // Example navigation
//                 print('About Us tapped');
//               },
//             ),
//             const Divider(), // Optional: Adds a visual separator
//             ListTile(
//               leading: const Icon(Icons.logout),
//               title: const Text('Logout'),
//               onTap: () {
//                 // It's good practice to close the drawer before showing a dialog
//                 Navigator.pop(context);
//                 Get.defaultDialog(
//                   title: "Logout",
//                   middleText: "Are you sure you want to logout?",
//                   textConfirm: "Logout",
//                   textCancel: "Cancel",
//                   onConfirm: () {
//                     loginController.logout();
//                     // Get.offAll is already handled in your loginController.logout()
//                   },
//                 );
//               },
//             ),
//           ],
//         ),
//       ),
//       appBar: AppBar(
//         title: const Text('Home Page'),
//         centerTitle: true,
//       ),
//       body: GridView.builder(
//         gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
//           crossAxisCount: 2, // Number of columns
//           crossAxisSpacing: 10.0,
//           mainAxisSpacing: 10.0,
//         ),
//         itemBuilder: (context, index) {
//           // Ensure your images are in 'assets/images/' directory
//           // and you have declared this folder in pubspec.yaml
//           // Create a placeholder list of image paths or generate dynamically
//           final imagePaths = List.generate(10, (i) => 'assets/images/image_$i.png');
//           if (index < imagePaths.length) {
//             return Image.asset(
//               imagePaths[index],
//               errorBuilder: (context, error, stackTrace) {
//                 print('Error loading image ${imagePaths[index]}: $error');
//                 return Card( // Use a Card for better visual feedback on error
//                   color: Colors.grey[300],
//                   child: const Center(
//                     child: Column(
//                       mainAxisAlignment: MainAxisAlignment.center,
//                       children: [
//                         Icon(Icons.broken_image, size: 40, color: Colors.black54),
//                         SizedBox(height: 8),
//                         Text('Image not found', textAlign: TextAlign.center, style: TextStyle(color: Colors.black54)),
//                       ],
//                     ),
//                   ),
//                 );
//               },
//             );
//           }
//           return const SizedBox.shrink(); // Should not happen if itemCount matches imagePaths.length
//         },
//         itemCount: 10, // Adjust according to the number of images
//         padding: const EdgeInsets.all(10.0),
//       ),
//     );
//   }
// }
