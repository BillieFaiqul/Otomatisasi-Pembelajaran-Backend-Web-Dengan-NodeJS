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
        Schema::create('submission_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['file', 'url']);
            $table->string('path')->comment('The path to the file or the url');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed']);
            $table->json('results')->nullable()->comment('The results of the submission');
            $table->integer('attempts');
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            $table->integer('port')->nullable()->comment('The port number of the submission');
            $table->text('description')->nullable()->comment('The description of the submission');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_histories');
    }
};
