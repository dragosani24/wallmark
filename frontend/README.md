# Wallmark · Frontend

Muro público de **Wallmark**: Next.js 16 (App Router) + React 19 + TypeScript + Tailwind CSS 4.

Muestra los enlaces guardados en un layout masonry, con búsqueda con *debounce*, chips de etiqueta y un formulario que guarda URLs nuevas contra la API del backend.

## Requisitos

- Node.js **>= 20.9** (requisito de Next.js 16)
- El backend corriendo en `http://127.0.0.1:8000` — ver [`../backend/README.md`](../backend/README.md)

## Configuración

Crea `.env.local` en esta carpeta:

```bash
NEXT_PUBLIC_API_URL=http://127.0.0.1:8000/api/v1
```

Si no existe, la app usa ese valor por defecto (ver `src/lib/api.ts`).

## Comandos

| Comando | Descripción |
|---|---|
| `npm run dev` | Servidor de desarrollo con **Turbopack** → http://localhost:3000 |
| `npm run build` | Build de producción |
| `npm start` | Sirve el build de producción |
| `npm run lint` | ESLint |

## Estructura

```
src/
├── app/
│   ├── layout.tsx        Tipografías Geist y metadata del sitio
│   ├── page.tsx          El muro: estado, filtros, paginación
│   └── globals.css       Tokens de Tailwind
├── components/
│   ├── FilterBar.tsx     Búsqueda + chips de etiqueta
│   ├── LinkCard.tsx      Tarjeta del muro
│   └── SaveForm.tsx      Pegar URL + etiquetas
└── lib/
    └── api.ts            Cliente HTTP tipado de la API
```

## Integración con la API

- **Cliente**: `src/lib/api.ts` expone `fetchLinks`, `fetchTags` y `createLink` con tipos compartidos (`Link`, `Tag`, `Paginated<T>`).
- **Errores**: cada respuesta no exitosa se traduce a un `Error` con el primer mensaje de `errors` o `message`, y se muestra como aviso en el muro.
- **Paginación**: el listado usa `meta.current_page` / `meta.last_page`; el botón "Cargar más" **agrega** en lugar de reemplazar.
- **Sin estado global**: `page.tsx` maneja el estado con hooks. La clave `filterKey` (query + etiqueta activa) permite mantener visible el listado anterior mientras carga la nueva consulta, evitando parpadeos.
- **Debounce**: la búsqueda espera 300 ms antes de consultar la API.

## Notas sobre Next.js 16

Esta versión tiene cambios respecto a versiones anteriores:

- **Turbopack es el bundler por defecto** en `next dev`. Para Webpack: `next dev --webpack`.
- **Cache Components** está activo (`cacheComponents: true`) junto con `partialPrefetching: true` en `next.config.ts`; el modelo de caché y revalidación de versiones anteriores no aplica.
- Tailwind se integra vía el loader `@tailwindcss/turbopack` (configurado en `next.config.ts`).
- La documentación local vive en `node_modules/next/dist/docs/` — consulta esa guía antes de asumir convenciones de versiones previas.
