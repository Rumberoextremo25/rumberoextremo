<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('source', 20)->default('youtube');
            $table->string('video_url', 500)->nullable();
            $table->string('video_path', 500)->nullable();
            $table->string('thumbnail_path', 500)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // El video que ya estaba en la vista de demo pasa a ser el primero
        // del carrusel. Los demás se cargan desde el panel.
        DB::table('demo_videos')->insert([
            'title' => 'Demo de Rumbero Extremo',
            'description' => 'Cómo funciona la app: lugares, descuentos y beneficios para rumberos y aliados.',
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=v4j2Hxq7BoI',
            'display_order' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_videos');
    }
};
