<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Server-side sanitizer for blog/press rich-text HTML (summary + content).
 * Client-side stripping in the editor is a UX convenience only — this is
 * the actual security boundary, since a request can bypass the browser
 * entirely (curl, modified fetch, disabled JS).
 */
function sanitize_blog_html(string $dirty): string
{
    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
    $config->set('Core.Encoding', 'UTF-8');

    // This HTMLPurifier version's bundled definition has no HTML5 module,
    // so figure/figcaption/mark/input aren't available — captions and
    // checklists below are built from span/div/ul/li with CSS classes
    // instead of those tags.
    $config->set('HTML.Allowed',
        'h1,h2,h3,h4,h5,h6,p,br,hr,' .
        'strong,b,em,i,u,s,strike,sup,sub,span[class],div[class],' .
        'a[href|title|target|rel],' .
        'ul[class],ol,li[class],blockquote,pre[class],code[class],' .
        'table,thead,tbody,tr,th[colspan|rowspan],td[colspan|rowspan],' .
        'img[src|alt|title|width|height|style|class]'
    );

    // Inline styles are how TinyMCE encodes font family/size/color and
    // alignment — allow only the specific properties the editor emits.
    $config->set('CSS.AllowedProperties', [
        'color', 'background-color',
        'text-align', 'text-decoration',
        'font-family', 'font-size', 'font-weight', 'font-style',
        'width', 'height', 'max-width',
    ]);

    $config->set('Attr.AllowedFrameTargets', ['_blank']);
    $config->set('Attr.EnableID', false);
    // http(s)/mailto only — no "data:" URIs, closing off the inline-base64
    // image path the old editor used to accept.
    $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
    $config->set('HTML.SafeIframe', false);

    $purifier = new HTMLPurifier($config);
    return $purifier->purify($dirty);
}
