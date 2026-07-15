<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan target_archetypes ke adaptive_modules
        Schema::table('adaptive_modules', function (Blueprint $table) {
            $table->json('target_archetypes')->nullable()->after('course_id');
        });

        // Pindahkan data archetype_name lama ke target_archetypes (sebagai array)
        $modules = DB::table('adaptive_modules')->get();
        foreach ($modules as $module) {
            if (!empty($module->archetype_name)) {
                DB::table('adaptive_modules')
                    ->where('id', $module->id)
                    ->update(['target_archetypes' => json_encode([$module->archetype_name])]);
            }
        }

        // Drop kolom archetype_name dan index lama (sementara hapus FK dulu)
        Schema::table('adaptive_modules', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropIndex(['course_id', 'archetype_name']);
            $table->dropColumn('archetype_name');
            
            // Re-add foreign key constraint
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });

        // 2. ai_generation_jobs: drop archetype_name, add module_id
        Schema::table('ai_generation_jobs', function (Blueprint $table) {
            $table->foreignId('module_id')->nullable()->constrained('adaptive_modules')->nullOnDelete()->after('course_id');
            $table->string('archetype_name')->nullable()->change();
        });

        // 3. ai_references: hapus archetype_name
        Schema::table('ai_references', function (Blueprint $table) {
            $table->dropColumn('archetype_name');
        });
    }

    public function down(): void
    {
        Schema::table('adaptive_modules', function (Blueprint $table) {
            $table->string('archetype_name', 100)->nullable();
            
            // Drop FK to add composite index
            $table->dropForeign(['course_id']);
            $table->index(['course_id', 'archetype_name']);
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });

        // Kembalikan archetype_name pertama dari array target_archetypes (jika ada)
        $modules = DB::table('adaptive_modules')->get();
        foreach ($modules as $module) {
            if (!empty($module->target_archetypes)) {
                $archetypes = json_decode($module->target_archetypes, true);
                if (is_array($archetypes) && count($archetypes) > 0) {
                    DB::table('adaptive_modules')
                        ->where('id', $module->id)
                        ->update(['archetype_name' => $archetypes[0]]);
                }
            }
        }

        Schema::table('adaptive_modules', function (Blueprint $table) {
            $table->dropColumn('target_archetypes');
        });

        Schema::table('ai_generation_jobs', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn('module_id');
            $table->string('archetype_name')->nullable(false)->change();
        });

        Schema::table('ai_references', function (Blueprint $table) {
            $table->string('archetype_name', 100)->nullable();
        });
    }
};
