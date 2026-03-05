<?php

namespace App\Domain\ValueObject;

enum Continent: string
{
    case Europe = 'Europe';
    case Asia = 'Asia';
    case Africa = 'Africa';
    case NorthAmerica = 'North America';
    case SouthAmerica = 'South America';
    case Oceania = 'Oceania';

    public function getTeams(): array
    {
        return match($this) {
            self::Europe => ['Spain', 'Germany', 'Italy', 'France', 'England'],
            self::Asia => ['Japan', 'South Korea', 'Iran', 'Saudi Arabia', 'Australia'],
            self::Africa => ['Nigeria', 'Egypt', 'Senegal', 'Ghana', 'Cameroon'],
            self::NorthAmerica => ['Mexico', 'Canada', 'USA'],
            self::SouthAmerica => ['Brazil', 'Argentina', 'Uruguay'],
            self::Oceania => ['New Zealand', 'Fiji'],
        };
    }

    public function containsTeam(Team $team): bool
    {
        return in_array($team->getName(), $this->getTeams(), true);
    }
}
