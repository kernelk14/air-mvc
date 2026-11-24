<?php

declare(strict_types=1);

namespace Air\Database\Migrations;

interface Migration
{
    public function up(): void;
    public function down(): void;
}
