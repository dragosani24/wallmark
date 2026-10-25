"use client";

import { useEffect, useRef, useState } from "react";
import { createLink, fetchTags, type Link, type Tag } from "@/lib/api";

interface SaveFormProps {
  onSaved: (link: Link) => void;
}

function lastToken(tagsText: string): string {
  const parts = tagsText.split(",");
  return (parts[parts.length - 1] ?? "").trim().toLowerCase();
}

function parseTags(tagsText: string): string[] {
  return tagsText
    .split(",")
    .map((tag) => tag.trim())
    .filter((tag) => tag !== "");
}

export default function SaveForm({ onSaved }: SaveFormProps) {
  const [url, setUrl] = useState("");
  const [tagsText, setTagsText] = useState("");
  const [suggestionResult, setSuggestionResult] = useState<{
    token: string;
    tags: Tag[];
  } | null>(null);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const containerRef = useRef<HTMLDivElement>(null);

  const token = lastToken(tagsText);

  const suggestions =
    token !== "" && suggestionResult !== null && suggestionResult.token === token
      ? suggestionResult.tags
          .filter(
            (tag) =>
              !parseTags(tagsText).some(
                (chosen) => chosen.toLowerCase() === tag.name,
              ),
          )
          .slice(0, 6)
      : [];

  useEffect(() => {
    if (token === "") return;

    const timer = setTimeout(() => {
      fetchTags(token)
        .then((found) => setSuggestionResult({ token, tags: found }))
        .catch(() => setSuggestionResult({ token, tags: [] }));
    }, 250);

    return () => clearTimeout(timer);
  }, [token]);

  useEffect(() => {
    function onClickOutside(event: MouseEvent) {
      if (
        containerRef.current &&
        !containerRef.current.contains(event.target as Node)
      ) {
        setSuggestionResult(null);
      }
    }
    document.addEventListener("mousedown", onClickOutside);
    return () => document.removeEventListener("mousedown", onClickOutside);
  }, []);

  function addTag(name: string) {
    const parts = tagsText.split(",").slice(0, -1);
    setTagsText([...parts, name].join(", ") + ", ");
    setSuggestionResult(null);
  }

  async function handleSubmit(event: React.FormEvent) {
    event.preventDefault();
    if (url.trim() === "" || saving) return;

    setSaving(true);
    setError(null);

    try {
      const link = await createLink(url.trim(), parseTags(tagsText));
      onSaved(link);
      setUrl("");
      setTagsText("");
      setSuggestionResult(null);
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "No se pudo guardar");
    } finally {
      setSaving(false);
    }
  }

  return (
    <form
      onSubmit={handleSubmit}
      className="rounded-2xl border border-line bg-surface p-3 shadow-sm"
    >
      <div className="flex flex-col gap-2 sm:flex-row">
        <input
          type="url"
          value={url}
          onChange={(event) => setUrl(event.target.value)}
          placeholder="Pega una URL interesante…"
          required
          className="flex-1 rounded-xl border border-line bg-paper px-3 py-2.5 text-sm focus:border-accent focus:outline-none"
        />
        <button
          type="submit"
          disabled={saving}
          className="rounded-xl bg-accent px-5 py-2.5 text-sm font-semibold text-white transition hover:brightness-110 disabled:opacity-60"
        >
          {saving ? "Guardando…" : "Guardar"}
        </button>
      </div>

      <div className="relative mt-2" ref={containerRef}>
        <input
          type="text"
          value={tagsText}
          onChange={(event) => setTagsText(event.target.value)}
          placeholder="Etiquetas separadas por coma (opcional)"
          className="w-full rounded-xl border border-line bg-paper px-3 py-2 text-sm focus:border-accent focus:outline-none"
        />

        {suggestions.length > 0 && (
          <ul className="absolute z-10 mt-1 w-full overflow-hidden rounded-xl border border-line bg-surface shadow-lg">
            {suggestions.map((tag) => (
              <li key={tag.id}>
                <button
                  type="button"
                  onClick={() => addTag(tag.name)}
                  className="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-accent-soft"
                >
                  <span>{tag.name}</span>
                  {typeof tag.links_count === "number" && (
                    <span className="text-xs text-muted">
                      {tag.links_count}
                    </span>
                  )}
                </button>
              </li>
            ))}
          </ul>
        )}
      </div>

      {error && (
        <p className="mt-2 rounded-lg bg-accent-soft px-3 py-2 text-sm text-accent">
          {error}
        </p>
      )}
    </form>
  );
}
