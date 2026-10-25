"use client";

import { useEffect, useState } from "react";
import FilterBar from "@/components/FilterBar";
import LinkCard from "@/components/LinkCard";
import SaveForm from "@/components/SaveForm";
import { fetchLinks, fetchTags, type Link, type Tag } from "@/lib/api";

interface WallResult {
  key: string;
  links: Link[];
  meta: { currentPage: number; lastPage: number; total: number };
}

function useDebouncedValue(value: string, delayMs: number): string {
  const [debounced, setDebounced] = useState(value);

  useEffect(() => {
    const timer = setTimeout(() => setDebounced(value), delayMs);
    return () => clearTimeout(timer);
  }, [value, delayMs]);

  return debounced;
}

export default function WallPage() {
  const [result, setResult] = useState<WallResult | null>(null);
  const [failure, setFailure] = useState<{ key: string; message: string } | null>(
    null,
  );
  const [tags, setTags] = useState<Tag[]>([]);
  const [query, setQuery] = useState("");
  const [activeTag, setActiveTag] = useState<string | null>(null);
  const [loadingMore, setLoadingMore] = useState(false);

  const debouncedQuery = useDebouncedValue(query, 300);
  const filterKey = JSON.stringify([debouncedQuery, activeTag]);

  const loading = result === null || result.key !== filterKey;
  const wallError =
    failure !== null && failure.key === filterKey ? failure.message : null;
  const current = result !== null && result.key === filterKey ? result : null;

  useEffect(() => {
    let cancelled = false;

    fetchLinks({ q: debouncedQuery || undefined, tag: activeTag ?? undefined })
      .then((page) => {
        if (cancelled) return;
        setResult({
          key: filterKey,
          links: page.data,
          meta: {
            currentPage: page.meta.current_page,
            lastPage: page.meta.last_page,
            total: page.meta.total,
          },
        });
      })
      .catch((error: unknown) => {
        if (cancelled) return;
        setFailure({
          key: filterKey,
          message:
            error instanceof Error ? error.message : "No se pudo cargar el muro",
        });
      });

    return () => {
      cancelled = true;
    };
  }, [filterKey, debouncedQuery, activeTag]);

  useEffect(() => {
    fetchTags()
      .then(setTags)
      .catch(() => setTags([]));
  }, []);

  function handleSaved(link: Link) {
    setResult((previous) =>
      previous === null
        ? previous
        : {
            ...previous,
            links: [link, ...previous.links],
            meta: { ...previous.meta, total: previous.meta.total + 1 },
          },
    );
    fetchTags()
      .then(setTags)
      .catch(() => {});
  }

  async function loadMore() {
    if (loadingMore || current === null) return;
    setLoadingMore(true);

    try {
      const page = await fetchLinks({
        q: debouncedQuery || undefined,
        tag: activeTag ?? undefined,
        page: current.meta.currentPage + 1,
      });
      setResult({
        key: filterKey,
        links: [...current.links, ...page.data],
        meta: {
          currentPage: page.meta.current_page,
          lastPage: page.meta.last_page,
          total: page.meta.total,
        },
      });
    } catch {
      setFailure({ key: filterKey, message: "No se pudo cargar más contenido." });
    } finally {
      setLoadingMore(false);
    }
  }

  const hasFilters = debouncedQuery !== "" || activeTag !== null;

  return (
    <div className="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6">
      <header className="mb-6 flex flex-col gap-4">
        <div className="flex items-baseline justify-between">
          <h1 className="text-2xl font-bold tracking-tight">
            📌 Muro de enlaces
          </h1>
          {current !== null && (
            <span className="text-sm text-muted">
              {current.meta.total}{" "}
              {current.meta.total === 1 ? "enlace" : "enlaces"}
            </span>
          )}
        </div>

        <SaveForm onSaved={handleSaved} />

        <FilterBar
          query={query}
          onQueryChange={setQuery}
          tags={tags}
          activeTag={activeTag}
          onTagChange={setActiveTag}
        />
      </header>

      {wallError && (
        <p className="mb-4 rounded-xl bg-accent-soft px-4 py-3 text-sm text-accent">
          {wallError}
        </p>
      )}

      {loading ? (
        <div className="columns-2 gap-4 sm:columns-3 xl:columns-4">
          {Array.from({ length: 8 }).map((_, index) => (
            <div
              key={index}
              className="mb-4 h-48 animate-pulse break-inside-avoid rounded-2xl bg-line/60"
            />
          ))}
        </div>
      ) : current !== null && current.links.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-line bg-surface py-16 text-center">
          <p className="text-4xl">🗒️</p>
          <p className="mt-3 font-medium">
            {hasFilters ? "Nada coincide con tu búsqueda" : "El muro está vacío"}
          </p>
          <p className="mt-1 text-sm text-muted">
            {hasFilters
              ? "Prueba con otra palabra o etiqueta."
              : "Pega tu primera URL arriba para empezar."}
          </p>
        </div>
      ) : current !== null ? (
        <>
          <div className="columns-2 gap-4 sm:columns-3 xl:columns-4">
            {current.links.map((link) => (
              <LinkCard key={link.id} link={link} />
            ))}
          </div>

          {current.meta.currentPage < current.meta.lastPage && (
            <div className="mt-6 flex justify-center">
              <button
                type="button"
                onClick={loadMore}
                disabled={loadingMore}
                className="rounded-xl border border-line bg-surface px-5 py-2.5 text-sm font-medium text-muted transition hover:text-ink disabled:opacity-60"
              >
                {loadingMore ? "Cargando…" : "Cargar más"}
              </button>
            </div>
          )}
        </>
      ) : null}
    </div>
  );
}
