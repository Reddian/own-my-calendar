<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_connected')->default(false);
            $table->timestamp('last_sync')->nullable();
            $table->boolean('quick_add_enabled')->default(false);
            $table->boolean('notifications_enabled')->default(false);
            $table->boolean('auto_sync_enabled')->default(false);
            $table->integer('sync_interval')->default(15);
            $table->integer('notification_time')->default(15);
            $table->integer('quick_adds_count')->default(0);
            $table->integer('notifications_count')->default(0);
            $table->integer('syncs_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('extensions');
    }
}; 