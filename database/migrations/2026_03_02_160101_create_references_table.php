<?php

use App\Models\Companies;
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
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address', 255)->nullable();
            $table->timestamps();
            $table->foreignIdFor(Companies::class, 'company_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};
