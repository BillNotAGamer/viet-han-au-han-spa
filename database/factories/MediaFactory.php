<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $ext = $this->faker->randomElement(['webp', 'jpg', 'png']);
        $filename = $this->faker->unique()->slug(2).'.'.$ext;

        return [
            'disk' => 'public',
            'path' => 'uploads/'.date('Y/m').'/'.$filename,
            'file_name' => $filename,
            'mime_type' => 'image/'.($ext === 'jpg' ? 'jpeg' : $ext),
            'extension' => $ext,
            'size_bytes' => $this->faker->numberBetween(50000, 2500000),
            'width' => 1920,
            'height' => 1080,
            'uploaded_by' => User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
