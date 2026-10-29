<?php

declare(strict_types=1);

namespace App\Application\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller extends AbstractController
{
    /**
     * @param array<mixed> $parameters
     */
    protected function renderHxTarget(
        string $view,
        array $parameters = [],
        ?Response $response = null,
    ): Response {
        $requestStack = $this->container->get('request_stack');
        assert($requestStack instanceof RequestStack);
        $request = $requestStack->getCurrentRequest();
        $isHtmx = 'true' === ($request?->headers->get('HX-Request') ?? '');

        if (!$isHtmx) {
            return parent::render($view, $parameters, $response);
        }

        $fullHtml = $this->renderView($view, $parameters);
        $fragment = $this->extractContentFragment($fullHtml);

        $response ??= new Response();

        return $response->setContent($fragment);
    }

    private function extractContentFragment(string $html): string
    {
        $previous = libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $doc->loadHTML(
            '<div id="__wrapper">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR,
        );

        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($doc);
        $nodeList = $xpath->query('//div[@id="content"]');
        $contentDiv = false !== $nodeList ? $nodeList->item(0) : null;

        if (null === $contentDiv) {
            return $html;
        }

        $titleElement = $doc->getElementsByTagName('title')->item(0);
        $title = $titleElement instanceof \DOMNode ? trim($titleElement->textContent) : '';

        $innerHtml = '';
        if ($contentDiv instanceof \DOMNode) {
            foreach ($contentDiv->childNodes as $child) {
                $innerHtml .= $doc->saveHTML($child);
            }
        }

        if ('' !== $title) {
            $innerHtml = '<title>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</title>'.$innerHtml;
        }

        return $innerHtml;
    }
}
