<?php

use App\Models\Part;
use App\Models\Table; // Assuming you have a Table model
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('qrs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('value')->unique();

            // For tracking the current table the part is on
            $table->foreignIdFor(Table::class)
                  ->nullable()             // Make the column nullable
                  ->default(null)          // Set the default value to NULL
                  ->constrained()
                  ->cascadeOnDelete()      // Or consider ->nullOnDelete() if you want to set to NULL when parent is deleted
                  ->cascadeOnUpdate();

            // For tracking the qr assigned to which part
            $table->foreignIdFor(Part::class)
                  ->nullable()             // Make the column nullable
                  ->default(null)          // Set the default value to NULL
                  ->constrained()
                  ->cascadeOnDelete()      // Or consider ->nullOnDelete()
                  ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // It's good practice to drop foreign keys before dropping the table if they were explicitly named
        // However, Schema::dropIfExists handles this for simple cases.
        // If you had custom foreign key names, you might do:
        // Schema::table('qrs', function (Blueprint $table) {
        //     $table->dropForeign(['table_id']); // or the generated name like 'qrs_table_id_foreign'
        //     $table->dropForeign(['part_id']);  // or the generated name like 'qrs_part_id_foreign'
        // });
        Schema::dropIfExists('qrs');
    }
};