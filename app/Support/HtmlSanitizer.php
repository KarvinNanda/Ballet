<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Cleans rich text (Rule & Regulation) before it is stored or printed with {!! !!}.
 * Allows text formatting plus the Bootstrap accordion markup the rules use.
 * Removes scripts, event handlers, style, javascript: URLs, unknown classes and external images.
 * Ids get a "rule-" prefix so content cannot take over ids the app uses. Running it twice gives the same result.
 */
class HtmlSanitizer
{
    private const ID_PREFIX = 'rule-';

    private const ALLOWED = 'p,br,strong,b,em,i,u,s,blockquote,ul,ol,li,h2[class],h3,h4,'
        .'a[href|title|target],img[src|alt|class|width|height],span[class],'
        .'div[class|id|aria-labelledby],'
        .'button[type|class|aria-expanded|aria-controls|data-bs-toggle|data-bs-target]';

    private const ALLOWED_CLASSES = [
        'accordion', 'accordion-flush', 'accordion-item', 'accordion-header', 'accordion-button',
        'accordion-collapse', 'accordion-body', 'collapse', 'collapsed', 'show',
        'img-fluid', 'text-center', 'fw-bold', 'fst-italic',
    ];

    private ?HTMLPurifier $purifier = null;

    public function clean(?string $html): string
    {
        $clean = $this->purifier()->purify($html ?? '');

        return $this->buttonsDoNotSubmit($this->prefixIds($clean));
    }

    /** id, aria-controls, aria-labelledby and data-bs-target="#..." all get the same prefix, once. */
    private function prefixIds(string $html): string
    {
        $prefix = fn (string $id) => str_starts_with($id, self::ID_PREFIX) ? $id : self::ID_PREFIX.$id;

        $html = preg_replace_callback(
            '/\b(id|aria-controls|aria-labelledby)="([^"]+)"/',
            fn ($m) => $m[1].'="'.$prefix($m[2]).'"',
            $html
        );

        return preg_replace_callback(
            '/\bdata-bs-target="#([^"]+)"/',
            fn ($m) => 'data-bs-target="#'.$prefix($m[1]).'"',
            $html
        );
    }

    /** Rules are shown inside the Add Student form: a button without type="button" would submit it. */
    private function buttonsDoNotSubmit(string $html): string
    {
        return preg_replace('/<button(?![^>]*\btype=)/', '<button type="button"', $html);
    }

    private function purifier(): HTMLPurifier
    {
        if ($this->purifier !== null) {
            return $this->purifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null); // no writes to vendor/ or storage/
        $config->set('Attr.EnableID', true);        // accordion panels are targeted by id
        $config->set('Attr.AllowedClasses', self::ALLOWED_CLASSES);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('URI.Host', parse_url(config('app.url'), PHP_URL_HOST));
        $config->set('URI.DisableExternalResources', true); // no images from other hosts (tracking pixels)
        $config->set('HTML.Allowed', self::ALLOWED);    // every set() must come before getHTMLDefinition()

        $definition = $config->getHTMLDefinition(true);
        $definition->addElement('button', 'Inline', 'Inline', 'Common', ['type' => 'Enum#button']);
        $definition->addAttribute('button', 'data-bs-toggle', 'Enum#collapse');
        $definition->addAttribute('button', 'data-bs-target', 'Text');
        $definition->addAttribute('button', 'aria-expanded', 'Enum#true,false');
        $definition->addAttribute('button', 'aria-controls', 'Text');
        $definition->addAttribute('div', 'aria-labelledby', 'Text');

        return $this->purifier = new HTMLPurifier($config);
    }
}
