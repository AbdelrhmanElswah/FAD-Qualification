<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property bool $is_completed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['title', 'description', 'is_completed'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /** @var list<string> */
    public const array SEARCHABLE = ['title', 'description'];

    /** @var list<string> */
    public const array SORTABLE = ['id', 'title', 'is_completed', 'created_at', 'updated_at'];

    /** @var array<string, mixed> */
    protected $attributes = [
        'is_completed' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
        ];
    }
}
