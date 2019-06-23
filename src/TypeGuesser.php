<?php

namespace Naoray\LaravelFactoryPrefill;

use Illuminate\Support\Str;
use Doctrine\DBAL\Types\Type;
use Faker\Generator as Faker;

class TypeGuesser
{
    /**
     * @var \Faker\Generator
     */
    protected $generator;

    /**
     * Create a new TypeGuesser instance.
     *
     * @param \Faker\Generator $generator
     */
    public function __construct(Faker $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param string                   $name
     * @param Doctrine\DBAL\Types\Type $type
     * @param int|null                 $size Length of field, if known
     *
     * @return string
     */
    public function guess($name, Type $type, $size = null)
    {
        $name = Str::lower($name);

        if ('word' !== $typeNameGuess = $this->guessBasedOnName($name, $size)) {
            return $typeNameGuess;
        }

        return $this->guessBasedOnType($type, $size);
    }

    /**
     * Get type guess.
     *
     * @param string   $name
     * @param int|null $size
     *
     * @return string
     */
    private function guessBasedOnName($name, $size = null)
    {
        switch (str_replace('_', '', $name)) {
            case 'name':
                return 'name';
            case 'firstname':
                return 'firstName';
            case 'lastname':
                return 'lastName';
            case 'username':
            case 'login':
                return 'userName';
            case 'email':
            case 'emailaddress':
                return 'email';
            case 'phonenumber':
            case 'phone':
            case 'telephone':
            case 'telnumber':
                return 'phoneNumber';
            case 'address':
                return 'address';
            case 'city':
            case 'town':
                return 'city';
            case 'streetaddress':
                return 'streetAddress';
            case 'postcode':
            case 'zipcode':
                return 'postcode';
            case 'state':
                return 'state';
            case 'county':
                return $this->predictCountyType();
            case 'country':
                return $this->predictCountryType($size);
            case 'locale':
                return 'locale';
            case 'currency':
            case 'currencycode':
                return 'currencyCode';
            case 'url':
            case 'website':
                return 'url';
            case 'company':
            case 'companyname':
            case 'employer':
                return 'company';
            case 'title':
                return $this->predictTitleType($size);
            case 'password':
                return "bcrypt(\$faker->word($size))";
            default:
                return 'word';
        }
    }

    /**
     * Try to guess the right faker method for the given type.
     *
     * @param Type     $type
     * @param int|null $size
     *
     * @return string
     */
    protected function guessBasedOnType(Type $type, $size)
    {
        $typeName = $type->getName();

        switch ($typeName) {
            case Type::BOOLEAN:
                return 'boolean';
            case Type::BIGINT:
            case Type::INTEGER:
            case Type::SMALLINT:
                return 'randomNumber' . ($size ? "($size)" : '');
            case Type::DATE:
                return 'date';
            case Type::DATETIME:
                return 'dateTime';
            case Type::DECIMAL:
            case Type::FLOAT:
                return 'randomFloat' . ($size ? "($size)" : '');
            case Type::GUID:
            case Type::STRING:
                return 'word';
            case Type::TEXT:
                return 'text';
            case Type::TIME:
                return 'time';
            default:
                return 'word';
        }
    }

    /**
     * Predicts county type by locale.
     */
    protected function predictCountyType()
    {
        if ('en_US' == $this->generator->locale) {
            return "sprintf('%s County', \$faker->city)";
        }

        return 'state';
    }

    /**
     * Predicts country code based on $size.
     *
     * @param int $size
     */
    protected function predictCountryType($size)
    {
        switch ($size) {
            case 2:
                return 'countryCode';
            case 3:
                return 'countryISOAlpha3';
            case 5:
            case 6:
                return 'locale';
        }

        return 'country';
    }

    /**
     * Predicts type of title by $size.
     *
     * @param int $size
     */
    protected function predictTitleType($size)
    {
        if (null === $size || $size <= 10) {
            return 'title';
        }

        return 'sentence';
    }
}
