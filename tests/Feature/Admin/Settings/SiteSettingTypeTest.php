<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Settings;

use App\Enums\SiteSettingType;
use App\Services\Settings\SiteSettings;
use App\Services\Settings\SiteSettingWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SiteSettingTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_enum_backing_values(): void
    {
        $cases = SiteSettingType::cases();
        $this->assertCount(4, $cases);

        $values = array_map(fn (SiteSettingType $type) => $type->value, $cases);
        $this->assertSame(['string', 'text', 'boolean', 'json'], $values);
    }

    public function test_typed_round_trip_serialization(): void
    {
        $writer = app(SiteSettingWriter::class);
        $reader = app(SiteSettings::class);

        // 1. String
        $writer->create([
            'key' => 'test.string',
            'value' => 'Việt Hàn Spa',
            'type' => SiteSettingType::STRING,
        ]);
        $this->assertSame('Việt Hàn Spa', $reader->get('test.string'));

        // 2. Text
        $multiLine = "Dòng 1\nDòng 2\nDòng 3";
        $writer->create([
            'key' => 'test.text',
            'value' => $multiLine,
            'type' => SiteSettingType::TEXT,
        ]);
        $this->assertSame($multiLine, $reader->get('test.text'));

        // 3. Boolean True
        $writer->create([
            'key' => 'test.bool_true',
            'value' => true,
            'type' => SiteSettingType::BOOLEAN,
        ]);
        $this->assertTrue($reader->get('test.bool_true'));

        // 4. Boolean False
        $writer->create([
            'key' => 'test.bool_false',
            'value' => false,
            'type' => SiteSettingType::BOOLEAN,
        ]);
        $this->assertFalse($reader->get('test.bool_false'));

        // 5. JSON
        $jsonData = ['opening' => '08:30', 'closing' => '20:30', 'branches' => ['HN', 'HCM']];
        $writer->create([
            'key' => 'test.json',
            'value' => json_encode($jsonData),
            'type' => SiteSettingType::JSON,
        ]);
        $this->assertSame($jsonData, $reader->get('test.json'));
    }

    public function test_invalid_json_is_rejected(): void
    {
        $writer = app(SiteSettingWriter::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON provided');

        $writer->create([
            'key' => 'test.invalid_json',
            'value' => '{invalid_json: true, missing_quotes}',
            'type' => SiteSettingType::JSON,
        ]);
    }

    public function test_type_change_safety(): void
    {
        $writer = app(SiteSettingWriter::class);
        $reader = app(SiteSettings::class);

        // 1. String to Text succeeds
        $setting = $writer->create([
            'key' => 'convertible.setting',
            'value' => 'Ban đầu là string',
            'type' => SiteSettingType::STRING,
        ]);

        $updated = $writer->update($setting, [
            'type' => SiteSettingType::TEXT,
            'value' => "Đã chuyển sang text\nThêm dòng mới",
        ]);
        $this->assertSame(SiteSettingType::TEXT, $updated->type);

        // 2. Incompatible conversion to JSON fails without corrupting existing record
        try {
            $writer->update($updated, [
                'type' => SiteSettingType::JSON,
                'value' => 'Not a valid JSON string',
            ]);
            $this->fail('Expected InvalidArgumentException for invalid JSON conversion');
        } catch (InvalidArgumentException $e) {
            // Expected
        }

        $this->assertSame(SiteSettingType::TEXT, $setting->fresh()->type);
        $this->assertStringContainsString('Đã chuyển sang text', $setting->fresh()->value);
    }
}
