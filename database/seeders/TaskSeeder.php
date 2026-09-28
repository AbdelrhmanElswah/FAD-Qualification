<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Seed a small, predictable set of tasks for manual API exploration.
     */
    public function run(): void
    {
        Task::factory()->count(12)->pending()->create();
        Task::factory()->count(5)->completed()->create();
    }
}
