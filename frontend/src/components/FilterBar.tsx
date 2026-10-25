"use client";

import type { Tag } from "@/lib/api";

interface FilterBarProps {
  query: string;
  onQueryChange: (query: string) => void;
  tags: Tag[];
  activeTag: string | null;
  onTagChange: (tag: string | null) => void;
}

export default function FilterBar({
  query,
  onQueryChange,
  tags,
  activeTag,
  onTagChange,
}: FilterBarProps) {
  return (
    <div className="flex flex-col gap-3">
      <div className="flex items-center gap-2 rounded-xl border border-line bg-surface px-3 py-2 shadow-sm">
        <span aria-hidden className="text-muted">
          🔍
        </span>
        <input
          type="search"
          value={query}
          onChange={(event) => onQueryChange(event.target.value)}
          placeholder="Buscar en el muro…"
          className="w-full bg-transparent text-sm focus:outline-none"
        />
      </div>

      <div className="flex flex-wrap items-center gap-2">
        <button
          type="button"
          onClick={() => onTagChange(null)}
          className={
            activeTag === null
              ? "rounded-full bg-ink px-3 py-1 text-xs font-medium text-paper"
              : "rounded-full border border-line bg-surface px-3 py-1 text-xs text-muted transition hover:text-ink"
          }
        >
          Todo
        </button>

        {tags.map((tag) => (
          <button
            key={tag.id}
            type="button"
            onClick={() => onTagChange(activeTag === tag.name ? null : tag.name)}
            className={
              activeTag === tag.name
                ? "rounded-full bg-accent px-3 py-1 text-xs font-medium text-white"
                : "rounded-full border border-line bg-surface px-3 py-1 text-xs text-muted transition hover:text-ink"
            }
          >
            {tag.name}
            {typeof tag.links_count === "number" && (
              <span className="ml-1 opacity-70">{tag.links_count}</span>
            )}
          </button>
        ))}
      </div>
    </div>
  );
}
