<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LinkApiTest extends TestCase
{
    use RefreshDatabase;

    private const HTML_FIXTURE = <<<'HTML'
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <title>Título genérico</title>
            <meta name="description" content="Descripción genérica">
            <meta property="og:title" content="Receta de pasta al pesto">
            <meta property="og:description" content="Pesto casero en 10 minutos.">
            <meta name="og:image" content="/images/pesto.jpg">
            <meta name="og:site_name" content="Cocina Fácil">
            <link rel="icon" href="/favicon.png">
        </head>
        <body></body>
        </html>
        HTML;

    public function test_store_extracts_metadata_and_creates_link_with_normalized_tags(): void
    {
        Http::fake(['*' => Http::response(self::HTML_FIXTURE, 200, ['Content-Type' => 'text/html; charset=utf-8'])]);

        $response = $this->postJson('/api/v1/links', [
            'url' => 'https://cocina.example.com/recetas/pesto',
            'tags' => ['Cocina', ' Pasta '],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Receta de pasta al pesto')
            ->assertJsonPath('data.description', 'Pesto casero en 10 minutos.')
            ->assertJsonPath('data.image_url', 'https://cocina.example.com/images/pesto.jpg')
            ->assertJsonPath('data.site_name', 'Cocina Fácil')
            ->assertJsonPath('data.tags.0.name', 'cocina')
            ->assertJsonPath('data.tags.1.name', 'pasta');

        $this->assertDatabaseHas('links', ['url' => 'https://cocina.example.com/recetas/pesto']);
        $this->assertDatabaseHas('tags', ['name' => 'cocina']);
    }

    public function test_store_falls_back_when_the_remote_fetch_fails(): void
    {
        Http::fake(['*' => Http::response('', 500)]);

        $response = $this->postJson('/api/v1/links', [
            'url' => 'https://caido.example.com/pagina',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'pagina')
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.favicon_url', 'https://caido.example.com/favicon.ico');
    }

    public function test_store_rejects_duplicate_urls(): void
    {
        Http::fake();
        Link::factory()->create(['url' => 'https://duplicado.example.com/uno']);

        $response = $this->postJson('/api/v1/links', ['url' => 'https://duplicado.example.com/uno']);

        $response->assertUnprocessable()->assertJsonValidationErrors('url');
    }

    public function test_store_rejects_invalid_urls(): void
    {
        $response = $this->postJson('/api/v1/links', ['url' => 'javascript:alert(1)']);

        $response->assertUnprocessable()->assertJsonValidationErrors('url');
    }

    public function test_index_filters_by_search_term(): void
    {
        Link::factory()->create(['title' => 'Guía de React', 'url' => 'https://react.example.com/guia']);
        Link::factory()->create(['title' => 'Trucos de cocina', 'url' => 'https://cocina.example.com/trucos']);

        $response = $this->getJson('/api/v1/links?q=React');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Guía de React');
    }

    public function test_index_filters_by_tag(): void
    {
        $css = Tag::factory()->create(['name' => 'css']);
        $php = Tag::factory()->create(['name' => 'php']);

        $withCss = Link::factory()->create();
        $withCss->tags()->attach($css);

        $withPhp = Link::factory()->create();
        $withPhp->tags()->attach($php);

        $response = $this->getJson('/api/v1/links?tag=css');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $withCss->id);
    }

    public function test_tags_endpoint_serves_typeahead_suggestions_ordered_by_usage(): void
    {
        $used = Tag::factory()->create(['name' => 'frontend']);
        $unused = Tag::factory()->create(['name' => 'frontmatter']);
        $used->links()->attach(Link::factory()->count(2)->create());

        $response = $this->getJson('/api/v1/tags?q=front');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'frontend')
            ->assertJsonPath('data.0.links_count', 2)
            ->assertJsonPath('data.1.name', 'frontmatter');
    }

    public function test_update_rewrites_title_and_tags(): void
    {
        $link = Link::factory()->create(['title' => 'Título viejo']);
        $link->tags()->attach(Tag::factory()->create(['name' => 'vieja']));

        $response = $this->patchJson("/api/v1/links/{$link->id}", [
            'title' => 'Título nuevo',
            'tags' => ['nueva'],
        ]);

        $response->assertOk()->assertJsonPath('data.title', 'Título nuevo');

        $this->assertDatabaseHas('links', ['id' => $link->id, 'title' => 'Título nuevo']);
        $this->assertDatabaseHas('tags', ['name' => 'nueva']);
        $this->assertEqualsCanonicalizing(['nueva'], $link->fresh()->tags->pluck('name')->all());
    }

    public function test_destroy_removes_the_link(): void
    {
        $link = Link::factory()->create();

        $this->deleteJson("/api/v1/links/{$link->id}")->assertNoContent();

        $this->assertDatabaseMissing('links', ['id' => $link->id]);
    }
}
