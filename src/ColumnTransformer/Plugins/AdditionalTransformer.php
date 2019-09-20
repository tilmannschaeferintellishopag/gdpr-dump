<?php


namespace machbarmacher\GdprDump\ColumnTransformer\Plugins;


use Faker\Factory;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;

class AdditionalTransformer extends ColumnTransformer
{
    private $formatter = [];

    public function __construct()
    {
        $this->formatter = [
            'exact' => static function ($expression) {
                if (isset($expression['value'])) {
                    return (string)$expression['value'];
                }
                return null;
            },
            'bcryptPassword' => static function ($expression) {
                $password = Factory::create()->password;
                if (function_exists('password_hash')) {
                    return password_hash($password, PASSWORD_BCRYPT);
                }
                return $password;
            },
            'defaultPassword' => static function ($expression) {
                $algo = isset($expression['algo']) ? $expression['algo'] : 'bcrypt';
                $defaultPassword = isset($expression['default']) ? $expression['default'] : 'password';
                switch ($algo) {
                    default:
                    case 'bcrypt':
                        return password_hash($defaultPassword, PASSWORD_BCRYPT);
                    case 'md5':
                        return md5($defaultPassword);
                    case 'plain':
                        return $defaultPassword;
                }
                return null;
            }
        ];
    }

    public function getValue($expression)
    {
        if (isset($this->formatter[$expression['formatter']])) {
            return $this->formatter[$expression['formatter']]($expression);
        }
        return null;
    }

    protected function getSupportedFormatters()
    {
        return array_keys($this->formatter);
    }
}