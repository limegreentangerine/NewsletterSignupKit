<?php

namespace NewsletterSignupKit\Config;

/**
 * Reads environment variables, falling back to the package's .env file when
 * the variable isn't in the process environment (e.g. when the automated
 * task runs from the CLI and nothing has loaded .env).
 *
 * Extend this per provider, expose typed getters, and implement isConfigured().
 */
abstract class Env
{
    /**
     * Setting => [env variable, label, secret?, required?]. Override per provider.
     */
    public const SETTINGS = [];

    protected static ?array $fileValues = null;

    protected static function get(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return static::fileValues()[$key] ?? $default;
    }

    protected static function envPath(): string
    {
        return dirname(__DIR__, 2) . '/.env';
    }

    protected static function fileValues(): array
    {
        if (static::$fileValues !== null) {
            return static::$fileValues;
        }

        static::$fileValues = [];
        $lines = is_readable(static::envPath()) ? file(static::envPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : false;

        foreach ($lines ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim(preg_replace('/^export\s+/', '', trim($name)));
            $value = trim($value);

            if (strlen($value) >= 2 && in_array($value[0], ['"', "'"], true) && $value[-1] === $value[0]) {
                $value = substr($value, 1, -1);
            }

            static::$fileValues[$name] = $value;
        }

        return static::$fileValues;
    }

    /**
     * Each setting's status for display. Secret values are masked, never returned.
     *
     * @return array<string, array{env: string, label: string, required: bool, populated: bool, display: string}>
     */
    public function describeSettings(): array
    {
        $settings = [];

        foreach (static::SETTINGS as $key => [$envName, $label, $secret, $required]) {
            $value = static::get($envName);

            $settings[$key] = [
                'env' => $envName,
                'label' => $label,
                'required' => $required,
                'populated' => $value !== '',
                'display' => $secret && $value !== '' ? str_repeat('•', 8) : $value,
            ];
        }

        return $settings;
    }

    /**
     * Whether every value this provider needs is present.
     */
    abstract public function isConfigured(): bool;
}
