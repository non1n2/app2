import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:dd/Controller/login-controller.dart'; // Make sure this path is correct
// import 'package:dd/View/home-page.dart'; // Make sure this path is correct

class LoginPage extends StatelessWidget {
  const LoginPage({super.key});

  @override
  Widget build(BuildContext context) {
    final LoginController loginController = Get.put(LoginController());
    var sizeBetween = Get.height * 0.03;

    return SafeArea(
      child: Scaffold(
        resizeToAvoidBottomInset: true,
        appBar: AppBar(
          shadowColor: Colors.blueAccent,
          centerTitle: true,
          title: const Text("Automate X"),
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.start,
            children: [
              Container(
                width: Get.width,
                height: Get.height * 0.4,
                decoration: const BoxDecoration(
                    image: DecorationImage(
                      image: AssetImage('images/AutomateX.png'), // Ensure this path is correct
                      fit: BoxFit.contain,
                    )
                ),
              ),
              SizedBox(
                height: sizeBetween,
              ),
              Container(
                decoration: BoxDecoration(
                    color: Colors.black12,
                    shape: BoxShape.rectangle,
                    borderRadius: BorderRadius.all(Radius.circular(5))),
                width: Get.width * 0.8,
                child: TextField(
                  onChanged: (value) => loginController.email.value = value,
                  decoration: const InputDecoration(
                      contentPadding:
                      EdgeInsets.symmetric(vertical: 10, horizontal: 10),
                      labelText: "User Name",
                      hintText: "Enter your username",
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.all(
                            Radius.circular(5),
                          ))),
                ),
              ),
              SizedBox(
                height: sizeBetween,
              ),
              Container(
                decoration: BoxDecoration(
                    backgroundBlendMode: BlendMode.darken,
                    color: Colors.black12,
                    borderRadius: BorderRadius.all(Radius.circular(5))),
                width: Get.width * 0.8,
                child: Obx(() => TextField(
                  onChanged: (value) => loginController.password.value = value,
                  obscureText: loginController.obscureText.value,
                  decoration: InputDecoration(
                      contentPadding: const EdgeInsets.symmetric(
                          vertical: 10, horizontal: 10),
                      labelText: "Password",
                      hintText: "******",
                      border: const OutlineInputBorder(
                          borderSide: BorderSide(),
                          borderRadius: BorderRadius.all(
                            Radius.circular(5),
                          )),
                      suffixIcon: IconButton(
                        iconSize: 18,
                        icon: Icon(
                          color: Colors.black,
                          loginController.obscureText.value
                              ? Icons.visibility_off
                              : Icons.visibility,
                        ),
                        onPressed: loginController.togglePasswordVisibility,
                      )),
                )),
              ),
              SizedBox(
                height: sizeBetween * 1.5,
              ),
              Obx(() => ElevatedButton(
                onPressed: loginController.isLoading.value ? null : () { // Disable button while loading
                  loginController.loginUser();
                },
                child: loginController.isLoading.value
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white))
                    : const Text('Login'),
              )),
              // GestureDetector(
              //   onTap: () async {
              //     if (await loginController.login()) { // login() in controller now saves status
              //       Get.offAll(() => const HomePage()); // Navigate to HomePage
              //     } else {
              //       Get.snackbar(
              //         'Login Error',
              //         'Invalid username or password',
              //         snackPosition: SnackPosition.BOTTOM,
              //         backgroundColor: Colors.red,
              //         colorText: Colors.white,
              //       );
              //     }
              //   },
              //   child: Container(
              //     width: Get.width * 0.5,
              //     height: Get.height * 0.06,
              //     decoration: BoxDecoration(
              //       shape: BoxShape.rectangle,
              //       borderRadius: BorderRadius.all(Radius.circular(10)),
              //       color: Colors.blueAccent,
              //     ),
              //     child: const Center(
              //         child: Text(
              //           "Login",
              //           style: TextStyle(
              //             color: Colors.white,
              //             fontSize: 16,
              //           ),
              //         )),
              //   ),
              // ),
            ],
          ),
        ),
      ),
    );
  }
}
