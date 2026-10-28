<?php
/**
 * The cards on the Events page (Events.php): segments in page order, each
 * card with its built-in title, description and image. page_cards.php shows
 * them and applies what an admin changed (Events Page Images in the
 * dashboard). A card's 'key' is permanent -- renaming one orphans whatever
 * was saved for it.
 */

/** Segments in page order, each with a permanent 'key' (cards added from the admin
 *  panel are stored against it); each card's 'img' is its built-in image. */
function events_cards(): array
{
    return [
        [
            'key'   => 'scholarship',
            'icon'  => 'fa-solid fa-graduation-cap',
            'title' => 'Scholarship Events',
            'cards' => [
                ['key' => 'scholarship-1', 'img' => 'Scholarship/pic1.jpg', 'alt' => 'Scholarship Event',
                 'title' => 'Scholarship Event',
                 'desc'  => 'PKSF was a partner in providing the scholarship.'],
                ['key' => 'scholarship-2', 'img' => 'Scholarship/pic2.jpg', 'alt' => 'Scholarship Event',
                 'title' => 'Scholarship Event',
                 'desc'  => 'Empowering students through education.'],
                ['key' => 'scholarship-3', 'img' => 'Scholarship/pic3.jpg', 'alt' => 'Scholarship Event',
                 'title' => 'Scholarship',
                 'desc'  => 'Providing opportunities for bright minds.'],
                ['key' => 'scholarship-4', 'img' => 'Scholarship/pic4.jpg', 'alt' => 'Scholarship Event',
                 'title' => 'Scholarship',
                 'desc'  => 'Providing opportunities for bright minds.'],
            ],
        ],
        [
            'key'   => 'women-rights',
            'icon'  => 'fa-solid fa-venus',
            'title' => 'Women Rights Events',
            'cards' => [
                ['key' => 'women-rights-1', 'img' => 'women/women_pic1.jpg', 'alt' => 'Women Rights Event',
                 'title' => 'Celebrate Women\'s Day!',
                 'desc'  => 'Advocating for equal rights.'],
                ['key' => 'women-rights-2', 'img' => 'women/women_pic2.jpg', 'alt' => 'Women Rights Event',
                 'title' => 'Equality Campaign',
                 'desc'  => 'We are campaigning in the village for gender equality between women and men.'],
                ['key' => 'women-rights-3', 'img' => 'women/women_pic3.jpg', 'alt' => 'Women Rights Event',
                 'title' => 'Equality Campaign',
                 'desc'  => 'We are campaigning in the city to promote gender equality between women and men.'],
            ],
        ],
        [
            'key'   => 'conference',
            'icon'  => 'fa-solid fa-users',
            'title' => 'Employee Conference',
            'cards' => [
                ['key' => 'conference-1', 'img' => 'meeting/meeting_pic2.jpg', 'alt' => 'Conference Event',
                 'title' => 'Best Employee Award',
                 'desc'  => 'We are proud to present the Best Employee Award, recognizing outstanding performance and dedication to excellence.'],
                ['key' => 'conference-2', 'img' => 'exam_hall/exam_hall_pic1.jpg', 'alt' => 'Conference Event',
                 'title' => 'Bi-Monthly Meetings with Managers for Guidance and Improvement',
                 'desc'  => 'We hold bi-monthly meetings with one or two managers to provide guidance on how we can support their branches and discuss strategies for improving their areas.'],
                ['key' => 'conference-3', 'img' => 'meeting/meeting_pic1.jpg', 'alt' => 'Conference Event',
                 'title' => 'Annual Conference with Managers at Head Office',
                 'desc'  => 'Our team conducts an annual conference at the head office, where we meet with each manager to gather feedback on the past year\'s progress.'],
            ],
        ],
        [
            'key'   => 'winter',
            'icon'  => 'fa-solid fa-snowflake',
            'title' => 'Winter Events',
            'cards' => [
                ['key' => 'winter-1', 'img' => 'winter/winter_pic1.jpg', 'alt' => 'Winterfest Event',
                 'title' => 'Winter Fest Celebration',
                 'desc'  => 'Enjoying the spirit of winter together.'],
                ['key' => 'winter-2', 'img' => 'winter/winter_pic3.jpg', 'alt' => 'Winter-fest Event',
                 'title' => 'Winter Clothes Distribution Program',
                 'desc'  => 'A humanitarian initiative dedicated to helping people face winter with dignity and warmth.'],
                ['key' => 'winter-3', 'img' => 'winter/winter_pic6.jpg', 'alt' => 'Winterfest Event',
                 'title' => 'Winter Clothes Distribution Program',
                 'desc'  => 'Celebration winter together.'],
            ],
        ],
        [
            'key'   => 'awareness',
            'icon'  => 'fa-solid fa-bullhorn',
            'title' => 'Awareness Events',
            'cards' => [
                ['key' => 'awareness-1', 'img' => 'Awarness/awarness_pic1.jpg', 'alt' => 'Awareness Event',
                 'title' => 'Disability Awareness Campaign',
                 'desc'  => 'Spreading Inclusion & Empowerment.'],
                ['key' => 'awareness-2', 'img' => 'Awarness/awarness_pic2.jpg', 'alt' => 'Awareness Event',
                 'title' => 'Anti-Corruption Campaign',
                 'desc'  => 'Join the fight against corruption. Together, we can build a transparent and just society!'],
                ['key' => 'awareness-3', 'img' => 'Health/health_pic3.jpg', 'alt' => 'Awareness Event',
                 'title' => 'Health Awareness',
                 'desc'  => 'Promoting healthy living for a better tomorrow!'],
                ['key' => 'awareness-4', 'img' => 'Awarness/awarness_pic5.jpg', 'alt' => 'Awareness Event',
                 'title' => 'Fire Safety Awareness for Students',
                 'desc'  => 'Know the steps to stay safe when fire strikes, stay calm, exit quickly and call for help.'],
                ['key' => 'awareness-5', 'img' => 'Awarness/awarness_pic4.jpg', 'alt' => 'Awareness Event',
                 'title' => 'First Aid Awareness for Students',
                 'desc'  => 'Stay calm and act fast—know how to assist someone who falls sick until help arrives!'],
            ],
        ],
        [
            'key'   => 'other',
            'icon'  => 'fa-solid fa-calendar-check',
            'title' => 'Others Events',
            'cards' => [
                ['key' => 'other-1', 'img' => 'training/training_pic1.jpg', 'alt' => 'Training',
                 'title' => 'Training',
                 'desc'  => 'We are providing BD wash training to our employees.'],
                ['key' => 'other-2', 'img' => 'rmpt/rmpt_pic1.jpg', 'alt' => 'Village BD Rural WASH Camp',
                 'title' => 'Village BD Rural WASH Camp for HCD Project',
                 'desc'  => 'We are conducting a rural WASH (Water, Sanitation, and Hygiene) camp in a village in Bangladesh as part of the HCD project.'],
                ['key' => 'other-3', 'img' => 'rmpt/rmpt_pic2.jpg', 'alt' => 'Free Fertilizer Distribution',
                 'title' => 'Free Fertilizer Distribution for Farmers',
                 'desc'  => 'We are providing free fertilizer to farmers to support agricultural growth and sustainability.'],
                ['key' => 'other-4', 'img' => 'FISH/fish_pic2.jpg', 'alt' => 'Field Day Celebration',
                 'title' => 'Field Day Celebration to Support Farmers',
                 'desc'  => 'We celebrated Field Day to educate farmers about sustainable fish farming, crop management, and agricultural best practices.'],
                ['key' => 'other-5', 'img' => 'FISH/fish_pic1.jpg', 'alt' => 'Nutrition Day',
                 'title' => 'Celebrating Nutrition Day with Village Communities',
                 'desc'  => 'We are celebrating Nutrition Day with the village community, promoting healthy eating and well being for all.'],
            ],
        ],
    ];
}
