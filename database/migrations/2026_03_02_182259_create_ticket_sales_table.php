<?php

use App\Models\Accounts;
use App\Models\Companies;
use App\Models\Customers;
use App\Models\Reference;
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
        Schema::create('ticket_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Reference::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(TicketPurchases::class, 'purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Customers::class, 'customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Accounts::class, 'account_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('sell_price', 10, 2)->default(0);
            $table->decimal('paid', 10, 2)->default(0);
            $table->decimal('due', 10, 2)->default(0);
            $table->date('issue_date')->nullable();
            $table->timestamps();
            $table->foreignIdFor(Companies::class, 'company_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_sales');
    }
};
