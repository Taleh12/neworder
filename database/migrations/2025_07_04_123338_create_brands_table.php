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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable(); // Optional logo field
            $table->string('website')->nullable(); // Optional website field
            $table->string('contact_email')->nullable(); // Optional contact email field
            $table->string('contact_phone')->nullable(); // Optional contact phone field
            $table->string('address')->nullable(); // Optional address field
            $table->string('social_media')->nullable(); // Optional social media field
            $table->boolean('is_active')->default(true); // Field to indicate if the brand
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};