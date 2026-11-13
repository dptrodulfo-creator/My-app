<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_tasks', function (Blueprint $table) {
            $table->id();

            // Item information
            $table->string('item_name');
            $table->unsignedSmallInteger('item_quantity')->default(1);
            $table->text('item_notes')->nullable();

            // Destination
            $table->string('recipient_name');
            $table->string('destination');
            $table->string('zone')->index();

            // Which driver carries it, and where it stands: "sent" until the driver confirms, then "received".
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('sent');
            $table->timestamp('received_at')->nullable();

            $table->timestamps(); // created_at is the moment the dispatcher sent the task
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_tasks');
    }
};
