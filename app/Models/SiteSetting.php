<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
        'is_public'
    ];

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        // Try to get from cache first
        $cachedValue = Cache::get('setting_' . $key);
        if ($cachedValue !== null) {
            return $cachedValue;
        }

        // If not in cache, get from database
        $setting = self::where('key', $key)->first();
        
        $value = $setting ? $setting->value : $default;
        
        // Cache the value for future use (1 hour)
        Cache::put('setting_' . $key, $value, 3600);
        
        return $value;
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @param string $description
     * @param bool $isPublic
     * @return SiteSetting
     */
    public static function set($key, $value, $group = 'general', $description = null, $isPublic = false)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'description' => $description,
                'is_public' => $isPublic
            ]
        );
        
        // Update the cache
        Cache::put('setting_' . $key, $value, 3600);
        
        return $setting;
    }

    /**
     * Get all settings in a group
     *
     * @param string $group
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getGroup($group)
    {
        return self::where('group', $group)->get();
    }

    /**
     * Get all rating related settings as an array
     *
     * @return array
     */
    public static function getRatingSettings()
    {
        $settings = self::where('group', 'rating')->get();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }
        
        // Set defaults if not found
        if (!isset($result['rating_scale'])) $result['rating_scale'] = 10;
        if (!isset($result['rating_threshold_high'])) $result['rating_threshold_high'] = 7;
        if (!isset($result['rating_threshold_medium'])) $result['rating_threshold_medium'] = 4;
        if (!isset($result['rating_display_decimal_places'])) $result['rating_display_decimal_places'] = 1;
        
        return $result;
    }
}
