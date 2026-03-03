<?php

use App\Models\Accounts;
use App\Models\Companies;
use App\Models\TicketPurchases;
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
        Schema::create('ticket_purchase_payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TicketPurchases::class, 'ticket_purchase_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Accounts::class, 'account_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('paid', 10, 2)->default(0);
            $table->decimal('due', 10, 2)->default(0);
            $table->timestamps();
            $table->foreignIdFor(Companies::class, 'company_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_purchase_payment_histories');
    }
};
