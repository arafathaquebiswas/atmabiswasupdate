<?php
/**
 * Central SEO meta tag generator — Brand Search Domination Edition
 * Include inside <head> on every public page: <?php include 'seo.php'; ?>
 *
 * Outputs per-page: description, keywords, robots, canonical, Open Graph,
 * Twitter Card, og:site_name, og:locale.
 * Outputs globally: Organization+NGO+WebSite+Person(founder) JSON-LD @graph,
 * plus a per-page WebPage/AboutPage/ContactPage node and BreadcrumbList,
 * covering all brand-name variations in English and Bengali so Google can
 * confidently associate every spelling variant with atmabiswas.org.
 */
$_seo_page = basename($_SERVER['SCRIPT_NAME']);
$_seo_logo = 'https://atmabiswas.org/LOGO/NGO_logo_monogram.png';

// Optional per-page 'type' (defaults to WebPage) and 'section' (breadcrumb
// parent — must match a real, existing nav category, not an invented one).
$_seo_data = [
    'index.php' => [
        'title'       => 'ATMABISWAS – Official NGO Bangladesh | আত্মবিশ্বাস | Since 1991',
        'description' => 'ATMABISWAS (আত্মবিশ্বাস) — Bangladesh\'s trusted NGO since 1991. Empowering rural communities through microfinance, solar energy, agriculture, and enterprise development. Official website.',
        'keywords'    => 'ATMABISWAS, আত্মবিশ্বাস, atma biswas, atma-biswas, NGO Bangladesh, microfinance, solar power, PKSF, RMTP, rural development, agriculture, আত্মবিশ্বাস এনজিও, ATMABISWAS NGO, nonprofit organization Bangladesh, registered NGO Bangladesh, বাংলাদেশের এনজিও',
        'canonical'   => 'https://atmabiswas.org/',
    ],
    'aboutus.php' => [
        'title'       => 'About ATMABISWAS (আত্মবিশ্বাস) – Bangladesh NGO | Mission & Vision',
        'description' => 'ATMABISWAS (আত্মবিশ্বাস) — registered Bangladesh NGO since 1991 under the Dept. of Social Welfare. Dedicated to poverty alleviation, rural development, and community empowerment.',
        'keywords'    => 'ATMABISWAS about, আত্মবিশ্বাস, ATMABISWAS Bangladesh, non-governmental organization, rural development, community empowerment, poverty alleviation, NGO 1991 Chuadanga, registered NGO Bangladesh, বাংলাদেশ এনজিও',
        'canonical'   => 'https://atmabiswas.org/aboutus.php',
        'type'        => 'AboutPage',
    ],
    'contact.php' => [
        'title'       => 'Contact ATMABISWAS (আত্মবিশ্বাস) – Chuadanga, Bangladesh',
        'description' => 'Contact ATMABISWAS (আত্মবিশ্বাস): Asma Palace, Court Para, Chuadanga-7200, Bangladesh. Phone: +8801713302930. Email: atmabiswas_ngo@yahoo.com. Find all branch offices.',
        'keywords'    => 'ATMABISWAS contact, আত্মবিশ্বাস যোগাযোগ, NGO Bangladesh contact, ATMABISWAS address Chuadanga, ATMABISWAS phone number, Bangladesh NGO office, NGO in Dhaka, NGO in Khulna, ঢাকা এনজিও, খুলনা এনজিও',
        'canonical'   => 'https://atmabiswas.org/contact.php',
        'type'        => 'ContactPage',
    ],
    'career.php' => [
        'title'       => 'Jobs & Careers at ATMABISWAS (আত্মবিশ্বাস) – NGO Bangladesh',
        'description' => 'Explore job openings at ATMABISWAS (আত্মবিশ্বাস) NGO Bangladesh. Apply for positions in microfinance, health, agriculture, and community development. Join our team.',
        'keywords'    => 'ATMABISWAS jobs, আত্মবিশ্বাস চাকরি, NGO career Bangladesh, job vacancies Chuadanga, ATMABISWAS recruitment, NGO employment Bangladesh',
        'canonical'   => 'https://atmabiswas.org/career.php',
    ],
    'health.php' => [
        'title'       => 'Health & Nutrition Programs – ATMABISWAS (আত্মবিশ্বাস) Bangladesh',
        'description' => 'ATMABISWAS (আত্মবিশ্বাস) promotes community health in rural Bangladesh through free medicine, sanitation, and nutrition awareness campaigns.',
        'keywords'    => 'ATMABISWAS health, আত্মবিশ্বাস স্বাস্থ্য, nutrition Bangladesh, rural health NGO, free medicine, sanitation Bangladesh',
        'canonical'   => 'https://atmabiswas.org/health.php',
        'section'     => 'What We Do',
    ],
    'Green_Energy.php' => [
        'title'       => 'Green Energy & Solar Programs – ATMABISWAS (আত্মবিশ্বাস) Bangladesh',
        'description' => 'ATMABISWAS (আত্মবিশ্বাস) advances solar power, biogas, and sustainable energy programs for rural communities in Bangladesh.',
        'keywords'    => 'ATMABISWAS green energy, আত্মবিশ্বাস সোলার, solar power Bangladesh, renewable energy NGO, biogas Bangladesh, sustainable energy rural',
        'canonical'   => 'https://atmabiswas.org/Green_Energy.php',
        'section'     => 'What We Do',
    ],
    'enterprice.php' => [
        'title'       => 'Enterprise Development – ATMABISWAS (আত্মবিশ্বাস) Bangladesh',
        'description' => 'ATMABISWAS (আত্মবিশ্বাস) empowers SMEs in Bangladesh through digital innovation, vocational training, and financial support for rural entrepreneurs.',
        'keywords'    => 'ATMABISWAS enterprise, আত্মবিশ্বাস উদ্যোগ, SME Bangladesh, enterprise development, vocational training NGO Bangladesh',
        'canonical'   => 'https://atmabiswas.org/enterprice.php',
        'section'     => 'What We Do',
    ],
    'Agricultural.php' => [
        'title'       => 'Food & Agriculture Programs – ATMABISWAS (আত্মবিশ্বাস) Bangladesh',
        'description' => 'ATMABISWAS (আত্মবিশ্বাস) supports food security and sustainable agriculture in Bangladesh through farmer training, resources, and modern farming techniques.',
        'keywords'    => 'ATMABISWAS agriculture, আত্মবিশ্বাস কৃষি, food security Bangladesh, farming NGO, sustainable agriculture Bangladesh',
        'canonical'   => 'https://atmabiswas.org/Agricultural.php',
        'section'     => 'What We Do',
    ],
    'Events.php' => [
        'title'       => 'Events & Activities – ATMABISWAS (আত্মবিশ্বাস) Bangladesh',
        'description' => 'Latest events and activities by ATMABISWAS (আত্মবিশ্বাস) NGO Bangladesh — scholarship programs, women\'s rights campaigns, and community welfare events.',
        'keywords'    => 'ATMABISWAS events, আত্মবিশ্বাস ইভেন্ট, NGO events Bangladesh, ATMABISWAS programs, community events Bangladesh',
        'canonical'   => 'https://atmabiswas.org/Events.php',
    ],
    'press.php' => [
        'title'       => 'ATMABISWAS Newsroom — News & Media Center | আত্মবিশ্বাস সংবাদ',
        'description' => 'Latest news, press releases, announcements, and media coverage from ATMABISWAS (আত্মবিশ্বাস) NGO Bangladesh. Stay updated with our community impact stories.',
        'keywords'    => 'ATMABISWAS news, আত্মবিশ্বাস সংবাদ, ATMABISWAS newsroom, NGO news Bangladesh, ATMABISWAS press release, media coverage Bangladesh NGO',
        'canonical'   => 'https://atmabiswas.org/press.php',
    ],
    'readytoeat.php' => [
        'title'       => 'Ready To Eat Products – ATMABISWAS (আত্মবিশ্বাস) Bangladesh',
        'description' => 'ATMABISWAS (আত্মবিশ্বাস) Ready To Eat products — affordable, nutritious food supporting food security and rural livelihoods in Bangladesh.',
        'keywords'    => 'ATMABISWAS ready to eat, আত্মবিশ্বাস খাদ্য, RTE food Bangladesh, nutritious food NGO, food products Bangladesh',
        'canonical'   => 'https://atmabiswas.org/readytoeat.php',
        'section'     => 'What We Do',
    ],
    'founder.php' => [
        'title'       => 'Our Founder – ATMABISWAS (আত্মবিশ্বাস) Bangladesh NGO',
        'description' => 'Learn about the visionary founder of ATMABISWAS (আত্মবিশ্বাস) who established the NGO in 1991 to empower communities across rural Bangladesh.',
        'keywords'    => 'ATMABISWAS founder, আত্মবিশ্বাস প্রতিষ্ঠাতা, NGO founder Bangladesh, ATMABISWAS history 1991',
        'canonical'   => 'https://atmabiswas.org/founder.php',
        'section'     => 'Our Team',
    ],
    'OurTeam.php' => [
        'title'       => 'Our Team – ATMABISWAS (আত্মবিশ্বাস) Bangladesh NGO',
        'description' => 'Meet the dedicated team driving ATMABISWAS (আত্মবিশ্বাস) NGO\'s mission of sustainable community development and social impact across Bangladesh.',
        'keywords'    => 'ATMABISWAS team, আত্মবিশ্বাস দল, NGO team Bangladesh, ATMABISWAS staff Bangladesh',
        'canonical'   => 'https://atmabiswas.org/OurTeam.php',
    ],
    'SeniorManagement.php' => [
        'title'       => 'Senior Management – ATMABISWAS (আত্মবিশ্বাস) Bangladesh NGO',
        'description' => 'Meet the senior management and directors of ATMABISWAS (আত্মবিশ্বাস) NGO leading microfinance, health, agriculture, and community development programs.',
        'keywords'    => 'ATMABISWAS senior management, আত্মবিশ্বাস পরিচালনা, NGO directors Bangladesh, ATMABISWAS leadership team',
        'canonical'   => 'https://atmabiswas.org/SeniorManagement.php',
        'section'     => 'Our Team',
    ],
    'generalbody.php' => [
        'title'       => 'General Body & Governance – ATMABISWAS (আত্মবিশ্বাস) Bangladesh',
        'description' => 'The General Body and Executive Committee of ATMABISWAS (আত্মবিশ্বাস) — the governing body overseeing community development programs in Bangladesh since 1991.',
        'keywords'    => 'ATMABISWAS general body, আত্মবিশ্বাস সাধারণ পরিষদ, executive committee NGO, ATMABISWAS governance Bangladesh',
        'canonical'   => 'https://atmabiswas.org/generalbody.php',
        'section'     => 'Our Team',
    ],
    'eve.php' => [
        'title'       => 'Executive Committee – ATMABISWAS (আত্মবিশ্বাস) Bangladesh',
        'description' => 'Meet the executive members of ATMABISWAS (আত্মবিশ্বাস) NGO driving sustainable development and community empowerment across Bangladesh.',
        'keywords'    => 'ATMABISWAS executive, আত্মবিশ্বাস নির্বাহী কমিটি, NGO executive Bangladesh, ATMABISWAS committee members',
        'canonical'   => 'https://atmabiswas.org/eve.php',
        'section'     => 'Our Team',
    ],
    'notice.php' => [
        'title'       => 'Official Notices – ATMABISWAS (আত্মবিশ্বাস) NGO Bangladesh',
        'description' => 'Official notices, announcements, and important updates from ATMABISWAS (আত্মবিশ্বাস) NGO Bangladesh.',
        'keywords'    => 'ATMABISWAS notice, আত্মবিশ্বাস নোটিশ, NGO announcements Bangladesh, official notice ATMABISWAS Bangladesh',
        'canonical'   => 'https://atmabiswas.org/notice.php',
    ],
    'storelocation.php' => [
        'title'       => 'Branch Locations – ATMABISWAS (আত্মবিশ্বাস) Bangladesh',
        'description' => 'Find ATMABISWAS (আত্মবিশ্বাস) branch and office locations across Bangladesh. Addresses and directions to our service centers and field offices.',
        'keywords'    => 'ATMABISWAS locations, আত্মবিশ্বাস শাখা, NGO branches Bangladesh, ATMABISWAS offices Chuadanga, Bangladesh NGO address, NGO in Khulna, খুলনা এনজিও',
        'canonical'   => 'https://atmabiswas.org/storelocation.php',
        'section'     => 'Contact',
    ],
    'loc.php' => [
        'title'       => 'Branch Locator – ATMABISWAS (আত্মবিশ্বাস) Bangladesh',
        'description' => 'Find an ATMABISWAS (আত্মবিশ্বাস) branch office near you, anywhere in Bangladesh. Interactive map and full list of branch addresses and contact numbers.',
        'keywords'    => 'ATMABISWAS branch locator, আত্মবিশ্বাস শাখা, NGO branch finder Bangladesh, ATMABISWAS office map, Bangladesh NGO branches, NGO in Dhaka, NGO in Khulna',
        'canonical'   => 'https://atmabiswas.org/loc.php',
        'section'     => 'Contact',
    ],
    'social.php' => [
        'title'       => 'Social Development Programs – ATMABISWAS (আত্মবিশ্বাস) Bangladesh',
        'description' => 'ATMABISWAS (আত্মবিশ্বাস) social development programs — supporting vulnerable communities through welfare, education, and women\'s empowerment in Bangladesh.',
        'keywords'    => 'ATMABISWAS social programs, আত্মবিশ্বাস সামাজিক উন্নয়ন, social development Bangladesh, women empowerment NGO Bangladesh, সমাজকল্যাণ সংস্থা, নারী উন্নয়ন সংস্থা',
        'canonical'   => 'https://atmabiswas.org/social.php',
    ],
];

$_d = $_seo_data[$_seo_page] ?? [
    'title'       => 'ATMABISWAS (আত্মবিশ্বাস) – Bangladesh NGO',
    'description' => 'ATMABISWAS (আত্মবিশ্বাস) is a registered non-governmental organization in Bangladesh empowering rural communities since 1991 through microfinance, agriculture, health, and green energy.',
    'keywords'    => 'ATMABISWAS, আত্মবিশ্বাস, NGO Bangladesh, community development, rural empowerment',
    'canonical'   => 'https://atmabiswas.org/',
];
?>
<link rel="preconnect" href="https://cdnjs.cloudflare.com">
<link rel="preconnect" href="https://www.googletagmanager.com">
<link rel="dns-prefetch" href="https://www.google-analytics.com">
<meta name="description" content="<?= htmlspecialchars($_d['description']) ?>">
<meta name="keywords" content="<?= htmlspecialchars($_d['keywords']) ?>">
<meta name="robots" content="index, follow">
<?php /* Meta/Facebook Business domain verification (Business ID 4487371514875279).
   Emitted server-side inside the head element: Meta rejects the tag if it is
   outside the head or injected by JavaScript. */ ?>
<meta name="facebook-domain-verification" content="98h9x23l4x08g8yghb8fvv15j7oa00" />
<?php /* Google Search Console site verification (URL-prefix property). Server-rendered
   inside the head element: Google reads the raw HTML and does not run page JavaScript. */ ?>
<meta name="google-site-verification" content="uwjsTOOKUDyqwLLh_Aehx9vW5Ae0otjEjsLuwDwpEms" />
<link rel="canonical" href="<?= $_d['canonical'] ?>">

<?php /* Icons. Absolute URLs so they resolve identically from every page depth, and
   a real /favicon.ico at the site root because that is where Google probes for the
   search-result favicon. All are square, which Google requires. */ ?>
<link rel="icon" href="https://atmabiswas.org/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="48x48" href="https://atmabiswas.org/favicon-48x48.png">
<link rel="icon" type="image/png" sizes="192x192" href="https://atmabiswas.org/favicon-192x192.png">
<link rel="apple-touch-icon" sizes="180x180" href="https://atmabiswas.org/apple-touch-icon.png">
<meta property="og:type" content="website">
<meta property="og:site_name" content="ATMABISWAS">
<meta property="og:locale" content="en_BD">
<meta property="og:title" content="<?= htmlspecialchars($_d['title']) ?>">
<meta property="og:description" content="<?= htmlspecialchars($_d['description']) ?>">
<meta property="og:image" content="<?= $_seo_logo ?>">
<meta property="og:image:width" content="512">
<meta property="og:image:height" content="512">
<meta property="og:image:alt" content="ATMABISWAS NGO Official Logo">
<meta property="og:url" content="<?= $_d['canonical'] ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($_d['title']) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($_d['description']) ?>">
<meta name="twitter:image" content="<?= $_seo_logo ?>">
<meta name="twitter:image:alt" content="ATMABISWAS NGO Official Logo">
<?php
// ── Build the JSON-LD @graph as a real PHP array, then json_encode() it —
// safer than hand-editing a JSON string as the graph grows (guarantees
// valid syntax and correct Bengali/unicode escaping). ──────────────────

$graph = [];

// Organization + NGO — unchanged from before, plus an expanded knowsAbout
// (only entities genuinely demonstrated in this site's own content — no
// invented focus areas) and a founder reference to the Person node below.
$graph[] = [
    '@type'       => ['Organization', 'NGO'],
    '@id'         => 'https://atmabiswas.org/#organization',
    'name'        => 'ATMABISWAS',
    'alternateName' => [
        'ATMA BISWAS', 'Atto Biswas', 'Atmabiswash',
        'AtmaBishwas', 'Atmavishwas', 'attobiswas', 'atmobiswas', 'atma-biswas',
        'Atma Bisbash', 'Atmabisash', 'Atma Bishwas',
        'ATMABISWAS NGO', 'ATMABISWAS Bangladesh',
        'ATMABISWAS Foundation', 'ATMABISWAS Organization',
        'আত্মবিশ্বাস', 'আত্ম বিশ্বাস', 'আত্তো বিশ্বাস', 'আত্মা বিশ্বাস', 'আত্নবিশ্বাস',
        'এটিএমএবিসওয়াস',
    ],
    'url'  => 'https://atmabiswas.org/',
    'logo' => [
        '@type'      => 'ImageObject',
        '@id'        => 'https://atmabiswas.org/#logo',
        'url'        => 'https://atmabiswas.org/LOGO/NGO_logo_monogram.png',
        'contentUrl' => 'https://atmabiswas.org/LOGO/NGO_logo_monogram.png',
        'width'      => 615,
        'height'     => 609,
        'caption'    => 'ATMABISWAS NGO Official Logo',
    ],
    'image'           => ['@id' => 'https://atmabiswas.org/#logo'],
    'description'     => 'ATMABISWAS is a Bangladesh NGO established in 1991 working in rural development, microfinance, agriculture, solar energy, health, and social welfare.',
    'foundingDate'    => '1991',
    'founder'         => ['@id' => 'https://atmabiswas.org/#founder'],
    'foundingLocation' => [
        '@type' => 'Place',
        'name'  => 'Chuadanga, Khulna Division, Bangladesh',
    ],
    'address' => [
        '@type'         => 'PostalAddress',
        'streetAddress' => 'Asma Palace, Court Para',
        'addressLocality' => 'Chuadanga',
        'postalCode'    => '7200',
        'addressRegion' => 'Khulna',
        'addressCountry' => 'BD',
    ],
    'location' => [
        '@type'   => 'Place',
        'name'    => 'ATMABISWAS Head Office — Chuadanga',
        'address' => [
            '@type'         => 'PostalAddress',
            'streetAddress' => 'Asma Palace, Court Para',
            'addressLocality' => 'Chuadanga',
            'postalCode'    => '7200',
            'addressCountry' => 'BD',
        ],
    ],
    'telephone'  => '+8801713302930',
    'email'      => 'atmabiswas_ngo@yahoo.com',
    // Country-level plus specific divisions: Khulna (headquarters + all 15
    // branches — verified in locations.json) and Dhaka (real Liaison Office,
    // address below) are independently confirmed in this codebase. Rajshahi
    // is included as a service area per the organization's own confirmation,
    // though no branch/address exists for it in any site data — this is why
    // it appears only here (service area) and not as a location/department.
    'areaServed' => [
        ['@type' => 'Country', 'name' => 'Bangladesh'],
        ['@type' => 'AdministrativeArea', 'name' => 'Khulna Division'],
        ['@type' => 'AdministrativeArea', 'name' => 'Dhaka Division'],
        ['@type' => 'AdministrativeArea', 'name' => 'Rajshahi Division'],
    ],
    // Liaison Office — real, verified address already published on contact.php.
    'department' => [[
        '@type'   => 'Organization',
        'name'    => 'ATMABISWAS Liaison Office',
        'address' => [
            '@type'         => 'PostalAddress',
            'streetAddress' => '59, Mia Tower, West Agargaon, BNP Bazar',
            'addressLocality' => 'Dhaka',
            'postalCode'    => '1207',
            'addressRegion' => 'Dhaka',
            'addressCountry' => 'BD',
        ],
    ]],
    'knowsAbout' => [
        'Microfinance', 'Rural Development', 'Agriculture', 'Solar Energy',
        'Health', 'Social Welfare', 'Education', "Women's Empowerment",
        'Community Development', 'Sustainable Development',
    ],
    'sameAs' => [
        'https://www.facebook.com/atmabiswas.official',
        'https://www.facebook.com/people/ATMABISWAS-Ngo/61573032346859/',
        'https://www.facebook.com/atmabiswas.chuadanga/',
        'https://www.youtube.com/@ATMABISWAS01',
        'https://www.youtube.com/channel/UCeqHBixXXoYfaX1gBOP-zOw',
        'https://www.linkedin.com/company/atmabiswas/',
    ],
    'contactPoint' => [[
        '@type'       => 'ContactPoint',
        'telephone'   => '+8801713302930',
        'email'       => 'atmabiswas_ngo@yahoo.com',
        'contactType' => 'customer service',
        'areaServed'  => 'BD',
        'availableLanguage' => ['Bengali', 'English'],
    ]],
];

// WebSite — unchanged from before.
$graph[] = [
    '@type'         => 'WebSite',
    '@id'           => 'https://atmabiswas.org/#website',
    'url'           => 'https://atmabiswas.org/',
    'name'          => 'ATMABISWAS',
    'alternateName' => ['আত্মবিশ্বাস', 'ATMABISWAS NGO Bangladesh'],
    'description'   => 'Official website of ATMABISWAS – a registered non-governmental organization in Bangladesh empowering rural communities since 1991.',
    'publisher'     => ['@id' => 'https://atmabiswas.org/#organization'],
    'inLanguage'    => 'en-BD',
];

// Person (Founder) — new. Uses only the name/title already published on
// founder.php; the bio there is summarized here, not invented.
$graph[] = [
    '@type'    => 'Person',
    '@id'      => 'https://atmabiswas.org/#founder',
    'name'     => 'Akramul Haque Biswas',
    'jobTitle' => 'Founder & Executive Director',
    'worksFor' => ['@id' => 'https://atmabiswas.org/#organization'],
    'image'    => [
        '@type' => 'ImageObject',
        'url'   => 'https://atmabiswas.org/Executives/ED_sir.jpg',
        'caption' => 'Akramul Haque Biswas, Founder & Executive Director of ATMABISWAS',
    ],
    'url'      => 'https://atmabiswas.org/founder.php',
];

// Per-page WebPage/AboutPage/ContactPage node.
$pageType = $_d['type'] ?? 'WebPage';
$graph[] = [
    '@type'      => $pageType,
    '@id'        => $_d['canonical'] . '#webpage',
    'url'        => $_d['canonical'],
    'name'       => $_d['title'],
    'description' => $_d['description'],
    'isPartOf'   => ['@id' => 'https://atmabiswas.org/#website'],
    'about'      => ['@id' => 'https://atmabiswas.org/#organization'],
    'inLanguage' => 'en-BD',
];

// BreadcrumbList — only built for non-home pages; the section (if any)
// must match a real nav category (see $_seo_data above), never invented.
if ($_seo_page !== 'index.php' && $_seo_page !== '') {
    $items = [[
        '@type'    => 'ListItem',
        'position' => 1,
        'name'     => 'Home',
        'item'     => 'https://atmabiswas.org/',
    ]];

    $position = 2;
    if (!empty($_d['section'])) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => $_d['section'],
        ];
    }

    $items[] = [
        '@type'    => 'ListItem',
        'position' => $position,
        'name'     => explode(' – ', explode(' — ', $_d['title'])[0])[0],
        'item'     => $_d['canonical'],
    ];

    $graph[] = [
        '@type'           => 'BreadcrumbList',
        '@id'             => $_d['canonical'] . '#breadcrumb',
        'itemListElement' => $items,
    ];
}

echo '<script type="application/ld+json">' . "\n";
echo json_encode(['@context' => 'https://schema.org', '@graph' => $graph],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
echo "\n" . '</script>' . "\n";
