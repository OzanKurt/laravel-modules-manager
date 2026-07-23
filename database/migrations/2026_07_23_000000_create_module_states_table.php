<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_states', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type')->nullable();
            $table->string('scope_id')->nullable();
            $table->string('module');
            $table->string('kind');            // state | feature | setting
            $table->string('key')->nullable(); // null for kind=state
            $table->json('value');
            $table->timestamps();

            $table->unique(['scope_type', 'scope_id', 'module', 'kind', 'key'], 'module_states_identity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_states');
    }
};
