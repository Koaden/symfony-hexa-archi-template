# Frontend (Twig, AssetMapper, HTMX)

The full-stack branch uses server-rendered Twig templates, Symfony AssetMapper and
HTMX. There is no Node build step.

## Stack

- Twig templates in `templates/`
- Symfony UX Twig components in `src/Twig/Components/`
- AssetMapper JavaScript and CSS in `assets/`
- HTMX 2.0.11 loaded from the import map
- Translations in `translations/messages.en.yaml`

## Asset pipeline

`importmap.php` declares the entrypoint and HTMX:

```php
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'htmx.org' => [
        'version' => '2.0.11',
    ],
];
```

`assets/app.js` imports HTMX and the stylesheet. In development, the Docker entrypoint
runs `importmap:install`. In production, the Dockerfile runs `importmap:install`
and `asset-map:compile`.

## HTMX flow

The demo link in `templates/demo/index.html.twig` uses:

```twig
<a href="{{ path('demo.now') }}"
   hx-boost="true"
   hx-target="#demo-result"
   hx-swap="innerHTML">
```

`Demo\NowController` returns `renderHxTarget('demo/now.html.twig', ...)`.
`App\Application\Http\Controller\Controller::renderHxTarget()` detects the
`HX-Request: true` header:

- normal request: renders the full template;
- HTMX request: renders the full template, then extracts the `#content` fragment
  and returns it with the page title when present.

The fragment is swapped into `#demo-result`.

## Controller helper

`App\Application\Http\Controller\Controller::renderHxTarget()` wraps `render()`:

- normal request: renders the whole template;
- HTMX request: renders the view, extracts the `#content` inner HTML, and returns
  it as the response body, prefixed with `<title>` when the template has one.

Use it for any route that can be requested both normally and through HTMX.

## Adding an HTMX interaction

1. Add a controller extending `App\Application\Http\Controller\Controller`.
2. Return `$this->renderHxTarget('template.html.twig', $parameters)`.
3. Add the route and template.
4. Use HTMX attributes on the triggering element.
5. Add translations in `translations/messages.en.yaml`.

## Tests

Behat covers the demo page and HTMX fragment loading:

```bash
make behat
```

## Commands

```bash
make importmap.install
make importmap.update
make importmap.audit
```
