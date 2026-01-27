<?php

declare(strict_types=1);

namespace App\Markdown;

use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;

/**
 * Custom fenced code renderer that preserves info string modifiers.
 *
 * Extracts the first info word as the language class and stores
 * remaining words in a data-modifiers attribute for downstream processing.
 */
final class FencedCodeRenderer implements NodeRendererInterface
{
    /**
     * @param  FencedCode  $node
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        FencedCode::assertInstanceOf($node);

        $attrs = $node->data->getData('attributes');
        $preAttrs = [];

        $infoWords = $node->getInfoWords();

        if ($infoWords !== [] && $infoWords[0] !== '') {
            $language = $infoWords[0];

            if (! str_starts_with($language, 'language-')) {
                $language = 'language-'.$language;
            }

            $attrs->append('class', $language);

            // Store modifiers (words after the language) in data attribute
            $modifiers = array_slice($infoWords, 1);

            if ($modifiers !== []) {
                $preAttrs['data-modifiers'] = implode(' ', $modifiers);
            }
        }

        return new HtmlElement(
            'pre',
            $preAttrs,
            new HtmlElement('code', $attrs->export(), Xml::escape($node->getLiteral()))
        );
    }
}
