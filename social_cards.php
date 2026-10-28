<?php
/**
 * The cards on the Social page (social.php): segments in page order, each
 * card with its built-in title, description and image. page_cards.php shows
 * them and applies what an admin changed (Social Page Images in the
 * dashboard). A card's 'key' is permanent -- renaming one orphans whatever
 * was saved for it.
 */

/** Segments in page order; each card's 'img' is its built-in image, null for the logo. */
function social_cards(): array
{
    $logoCards = static function (string $prefix, int $count, string $title, string $alt): array {
        $cards = [];
        for ($i = 1; $i <= $count; $i++) {
            $cards[] = ['key' => "{$prefix}-{$i}", 'img' => null, 'alt' => $alt, 'title' => $title, 'desc' => ''];
        }
        return $cards;
    };

    return [
        [
            'icon'  => 'fa-solid fa-hand-holding-heart',
            'title' => 'Empowering Women',
            'cards' => [
                ['key' => 'women-1', 'img' => 'COW/cow_pic1.jpg', 'alt' => 'Empowering Women',
                 'title' => 'Empowering Women',
                 'desc'  => 'ATMABISWAS supports women by providing livestock for sustainable livelihoods.'],
                ['key' => 'women-2', 'img' => 'fish&redy/ABS_04.jpg', 'alt' => 'Culinary Training',
                 'title' => 'Empowering Women',
                 'desc'  => 'Women receive culinary training to enhance their skills and achieve financial independence.'],
                ['key' => 'women-3', 'img' => 'women_empowerment/women.jpg', 'alt' => 'Female Farmers',
                 'title' => 'Empowering Women',
                 'desc'  => 'ATMABISWAS empowers female farmers with free fertilizer and resources for better agricultural productivity.'],
            ],
        ],
        [
            'icon'  => 'fa-solid fa-chalkboard-user',
            'title' => 'ATMABISWAS Training Center',
            'cards' => [
                ['key' => 'training-1', 'img' => 'training/automachinic.jpeg', 'alt' => 'Auto Mechanic Training',
                 'title' => 'ATMABISWAS Auto Mechanic Training Center',
                 'desc'  => 'ATMABISWAS Auto Mechanic Training Center equips individuals with practical skills for better job opportunities and improved livelihoods.'],
                ['key' => 'training-2', 'img' => 'training/bevarag.jpeg', 'alt' => 'Beverage Training Center',
                 'title' => 'ATMABISWAS Food & Beverage Training Center',
                 'desc'  => 'ATMABISWAS provides hands-on food and beverage training for skill development and employment.'],
                ['key' => 'training-3', 'img' => 'training/cooking.jpeg', 'alt' => 'ATMABISWAS Cooking Training Center',
                 'title' => 'ATMABISWAS Cooking Training Center',
                 'desc'  => 'ATMABISWAS Cooking Training Center empowers individuals with culinary skills for self-reliance and employment.'],
                ['key' => 'training-4', 'img' => 'training/fashion.jpeg', 'alt' => 'ATMABISWAS Fashion Training Center',
                 'title' => 'ATMABISWAS Fashion Training Center',
                 'desc'  => 'ATMABISWAS Fashion Training Center offers skill-based training in tailoring and design for self-employment.'],
                ['key' => 'training-5', 'img' => 'training/pksf_fashion.jpeg', 'alt' => 'PKSF Visit Fashion Center',
                 'title' => 'PKSF Team Visited ATMABISWAS Fashion Training Center',
                 'desc'  => 'PKSF team visited ATMABISWAS Fashion Training Center to observe and encourage skill development initiatives.'],
                ['key' => 'training-6', 'img' => 'training/pksfvisitcooking.jpeg', 'alt' => 'PKSF Visit Cooking Center',
                 'title' => 'PKSF Team Visited ATMABISWAS Cooking Training Center',
                 'desc'  => 'PKSF team visited ATMABISWAS Cooking Training Center to inspire and support skill development initiatives.'],
            ],
        ],
        [
            'icon'  => 'fa-solid fa-baby',
            'title' => 'ATMABISWAS Daycare',
            'cards' => [
                ['key' => 'daycare-1', 'img' => 'daycare/daycare.jpeg', 'alt' => 'ATMABISWAS Daycare',
                 'title' => 'ATMABISWAS Daycare',
                 'desc'  => 'ATMABISWAS Daycare provides a safe and nurturing environment for children while their parents work.'],
                ['key' => 'daycare-2', 'img' => 'daycare/daycare2.png', 'alt' => 'ATMABISWAS Daycare',
                 'title' => 'ATMABISWAS Daycare',
                 'desc'  => 'ATMABISWAS Daycare fosters early childhood development in a caring environment.'],
                ['key' => 'daycare-3', 'img' => null, 'alt' => 'ATMABISWAS Daycare',
                 'title' => 'ATMABISWAS Daycare', 'desc' => ''],
            ],
        ],
        [
            'icon'  => 'fa-solid fa-book-open',
            'title' => 'ATMABISWAS Pathshala',
            'cards' => $logoCards('pathshala', 3, 'ATMABISWAS Pathshala', 'ATMABISWAS Pathshala'),
        ],
        [
            'icon'  => 'fa-solid fa-heart-pulse',
            'title' => 'Compassionate Care & Support',
            'cards' => $logoCards('care', 5, 'Compassionate Care & Support', 'Compassionate Care'),
        ],
        [
            'icon'  => 'fa-solid fa-circle-plus',
            'title' => 'Other Program',
            'cards' => $logoCards('other', 5, 'Supporting Disabilities', 'Supporting Disabilities'),
        ],
    ];
}
