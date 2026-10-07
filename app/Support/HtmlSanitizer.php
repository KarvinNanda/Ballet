<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Cleans rich text (Rule & Regulation) before it is stored or printed with {!! !!}.
 * Allows text formatting plus the Bootstrap accordion markup the rules use.
 * Removes scripts, event handlers (onerror, onclick...), style, and javascript: URLs.
 */
class HtmlSanitizer
{
    private const ALLOWED = 'p,br,strong,b,em,i,u,s,blockquote,ul,ol,li,h2[class],h3,h4,'
        .'a[href|title|target],img[src|alt|class|width|height],span[class],'
        .'div[class|id|aria-labelledby|data-bs-parent],'
        .'button[type|class|aria-expanded|aria-controls|data-bs-toggle|data-bs-target]';

    private ?HTMLPurifier $purifier = null;

    public function clean(?string $html): string
    {
        return $this->purifier()->purify($html ?? '');
    }

    private function purifier(): HTMLPurifier
    {
        if ($this->purifier !== null) {
            return $this->purifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null); // no writes to vendor/ or storage/
        $config->set('Attr.EnableID', true);        // accordion panels are targeted by id
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('HTML.Allowed', self::ALLOWED); // every set() must come before getHTMLDefinition()

        $definition = $config->getHTMLDefinition(true);
        $definition->addElement('button', 'Inline', 'Inline', 'Common', ['type' => 'Enum#button']);
        $definition->addAttribute('button', 'data-bs-toggle', 'Enum#collapse');
        $definition->addAttribute('button', 'data-bs-target', 'Text');
        $definition->addAttribute('button', 'aria-expanded', 'Enum#true,false');
        $definition->addAttribute('button', 'aria-controls', 'Text');
        $definition->addAttribute('div', 'data-bs-parent', 'Text');
        $definition->addAttribute('div', 'aria-labelledby', 'Text');

        return $this->purifier = new HTMLPurifier($config);
    }
}
