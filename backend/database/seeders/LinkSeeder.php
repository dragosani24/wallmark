<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class LinkSeeder extends Seeder
{
    /**
     * Seed the wall with demo entries (static metadata, no network calls).
     */
    public function run(): void
    {
        $entries = [
            [
                'url' => 'https://developer.mozilla.org/es/docs/Web/CSS/CSS_flexible_box_layout/Basic_concepts_of_flexible_layout',
                'title' => 'Conceptos básicos del layout flexible (Flexbox)',
                'description' => 'Guía de MDN sobre el modelo de caja flexible: ejes, alineación y distribución del espacio.',
                'image_url' => 'https://picsum.photos/seed/flexbox/600/380',
                'site_name' => 'MDN Web Docs',
                'tags' => ['css', 'frontend', 'referencia'],
            ],
            [
                'url' => 'https://laravel.com/docs/13.x/eloquent',
                'title' => 'Eloquent ORM — Laravel 13',
                'description' => 'Modelos, relaciones, colecciones y consultas del ORM de Laravel.',
                'image_url' => 'https://picsum.photos/seed/eloquent/600/520',
                'site_name' => 'Laravel',
                'tags' => ['php', 'laravel', 'backend'],
            ],
            [
                'url' => 'https://nextjs.org/docs/app/building-your-application/data-fetching',
                'title' => 'Data Fetching en Next.js App Router',
                'description' => 'Cómo obtener datos con fetch, revalidación y server components.',
                'image_url' => 'https://picsum.photos/seed/nextjs/600/340',
                'site_name' => 'Next.js',
                'tags' => ['javascript', 'nextjs', 'frontend'],
            ],
            [
                'url' => 'https://excalidraw.com/',
                'title' => 'Excalidraw — pizarra de dibujo a mano alzada',
                'description' => 'Pizarra colaborativa con estilo de boceto, ideal para diagramas rápidos.',
                'image_url' => 'https://picsum.photos/seed/excalidraw/600/600',
                'site_name' => 'Excalidraw',
                'tags' => ['herramientas', 'diagramas'],
            ],
            [
                'url' => 'https://www.smashingmagazine.com/2019/12/web-animations-performance-guide/',
                'title' => 'Guía de rendimiento de animaciones web',
                'description' => 'Cuándo animar con CSS, JS o Web Animations API, y cómo medir el rendimiento.',
                'image_url' => 'https://picsum.photos/seed/anim/600/450',
                'site_name' => 'Smashing Magazine',
                'tags' => ['css', 'animaciones', 'rendimiento'],
            ],
            [
                'url' => 'https://sqlite.org/whentouse.html',
                'title' => 'Cuándo usar SQLite',
                'description' => 'Escenarios ideales (y no ideales) para SQLite según su documentación oficial.',
                'image_url' => null,
                'site_name' => 'SQLite',
                'tags' => ['base de datos', 'sqlite'],
            ],
        ];

        foreach ($entries as $entry) {
            $tags = $entry['tags'];
            unset($entry['tags']);

            $link = Link::updateOrCreate(['url' => $entry['url']], $entry);

            $ids = [];
            foreach ($tags as $name) {
                $ids[] = Tag::firstOrCreate(['name' => $name])->id;
            }

            $link->tags()->sync($ids);
        }
    }
}
