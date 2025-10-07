<?php

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
        Schema::create('motivations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // quien envía
            $table->foreignId('sent_to')->constrained('users')->onDelete('cascade'); // quien recibe
            $table->text('message'); // mensaje de motivación
            $table->string('image_path')->nullable(); // imagen adjunta
            $table->boolean('receiver_closed')->default(false); // si el receptor ha cerrado la motivación
            $table->text('receiver_message')->nullable(); // mensaje de respuesta del receptor
            $table->boolean('sender_closed_response')->default(false); // si el emisor ha cerrado la respuesta
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motivations');
    }
};
