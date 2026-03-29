<?php
class ZodiacHelper {
    public static function getZodiacSign($dob) {
        $date = new DateTime($dob);
        $month = (int)$date->format('m');
        $day = (int)$date->format('d');

        $zodiac = "";
        $element = "";

        if (($month == 3 && $day >= 21) || ($month == 4 && $day <= 19)) { $zodiac = "Aries"; $element = "Fire"; }
        elseif (($month == 4 && $day >= 20) || ($month == 5 && $day <= 20)) { $zodiac = "Taurus"; $element = "Earth"; }
        elseif (($month == 5 && $day >= 21) || ($month == 6 && $day <= 20)) { $zodiac = "Gemini"; $element = "Air"; }
        elseif (($month == 6 && $day >= 21) || ($month == 7 && $day <= 22)) { $zodiac = "Cancer"; $element = "Water"; }
        elseif (($month == 7 && $day >= 23) || ($month == 8 && $day <= 22)) { $zodiac = "Leo"; $element = "Fire"; }
        elseif (($month == 8 && $day >= 23) || ($month == 9 && $day <= 22)) { $zodiac = "Virgo"; $element = "Earth"; }
        elseif (($month == 9 && $day >= 23) || ($month == 10 && $day <= 22)) { $zodiac = "Libra"; $element = "Air"; }
        elseif (($month == 10 && $day >= 23) || ($month == 11 && $day <= 21)) { $zodiac = "Scorpio"; $element = "Water"; }
        elseif (($month == 11 && $day >= 22) || ($month == 12 && $day <= 21)) { $zodiac = "Sagittarius"; $element = "Fire"; }
        elseif (($month == 12 && $day >= 22) || ($month == 1 && $day <= 19)) { $zodiac = "Capricorn"; $element = "Earth"; }
        elseif (($month == 1 && $day >= 20) || ($month == 2 && $day <= 18)) { $zodiac = "Aquarius"; $element = "Air"; }
        elseif (($month == 2 && $day >= 19) || ($month == 3 && $day <= 20)) { $zodiac = "Pisces"; $element = "Water"; }

        return ["sign" => $zodiac, "element" => $element];
    }
}
?>