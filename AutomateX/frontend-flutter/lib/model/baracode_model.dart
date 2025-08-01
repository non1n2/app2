// lib/Models/barcode_model.dart

class Barcode {
  final int id;
  final String barcodeValue;
  final String? productName;
  final String? description;
  final double? price;
  final int? quantity;
  final DateTime createdAt;
  final DateTime updatedAt;

  Barcode({
    required this.id,
    required this.barcodeValue,
    this.productName,
    this.description,
    this.price,
    this.quantity,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Barcode.fromJson(Map<String, dynamic> json) {
    return Barcode(
      id: json['id'],
      barcodeValue: json['barcode_value'],
      productName: json['product_name'],
      description: json['description'],
      price: json['price'] != null ? double.tryParse(json['price'].toString()) : null,
      quantity: json['quantity'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  // Optional: toJson method if you ever need to send this model back to the server
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'barcode_value': barcodeValue,
      'product_name': productName,
      'description': description,
      'price': price,
      'quantity': quantity,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
}
