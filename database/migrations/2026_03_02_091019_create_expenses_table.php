<?php

use App\Models\Accounts;
use App\Models\Companies;
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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class, 'user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('account_id')
                ->constrained('accounts')
                ->restrictOnDelete(); // better for expense history

            $table->decimal('amount', 14, 2);
            $table->string('category', 100)->nullable();
            $table->dateTime('spent_at')->index();
            $table->text('note')->nullable();
            $table->string('attachment_path')->nullable();

            $table->timestamps();

            $table->foreignIdFor(Companies::class, 'company_id')->constrained('companies')->cascadeOnDelete();


            $table->index(['user_id', 'spent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
