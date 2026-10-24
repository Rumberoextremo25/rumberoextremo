<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_slides', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('image_path', 500);
            $table->string('target_url', 500)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Mantiene las 5 imágenes que ya tenía el Home; desde el panel se
        // pueden reemplazar o agregar más.
        $now = now();
        foreach ([
            ['Discoteca', 'Rumbero Extremo - Discoteca', 'assets/img/home/discoteca_5.jpg'],
            ['Cafetería', 'Rumbero Extremo - Cafetería', 'assets/img/home/cafeteria_4.webp'],
            ['Parque', 'Rumbero Extremo - Parque', 'assets/img/home/parque_3.jpg'],
            ['Posada', 'Rumbero Extremo - Posada', 'assets/img/home/posada_2.jpeg'],
            ['Fiesta', 'Rumbero Extremo - Fiesta', 'assets/img/home/fiesta_1.jpg'],
        ] as $index => [$title, $alt, $path]) {
            DB::table('home_slides')->insert([
                'title' => $title,
                'alt_text' => $alt,
                'image_path' => $path,
                'display_order' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_slides');
    }
};
