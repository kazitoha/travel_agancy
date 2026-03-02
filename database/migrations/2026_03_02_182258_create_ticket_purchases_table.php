<?php

use App\Models\Accounts;
use App\Models\Companies;
use App\Models\Customers;
use App\Models\Vendors;
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
        Schema::create('ticket_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Vendors::class, 'vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Customers::class, 'customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Accounts::class, 'account_id')->nullable()->constrained()->nullOnDelete();
            $table->date('flight_date');
            $table->string('sector');
            $table->string('carrier');
            $table->decimal('net_fare', 10, 2)->comment('Purchase price');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->date('issue_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreignIdFor(Companies::class, 'company_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_purchases');
    }
};
