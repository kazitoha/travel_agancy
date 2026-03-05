<?php

use App\Models\Companies;
use App\Models\Reference;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('passport_number', 100)->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 30);
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->foreignIdFor(Companies::class, 'company_id')->constrained('companies')->cascadeOnDelete();
            $table->unique(['company_id', 'passport_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
