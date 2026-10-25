import { hostOf, type Link } from "@/lib/api";

const GRADIENTS = [
  "from-amber-200 to-rose-200",
  "from-sky-200 to-indigo-200",
  "from-emerald-200 to-teal-200",
  "from-fuchsia-200 to-pink-200",
  "from-lime-200 to-emerald-200",
  "from-orange-200 to-amber-200",
];

function gradientFor(link: Link): string {
  return GRADIENTS[link.id % GRADIENTS.length];
}

export default function LinkCard({ link }: { link: Link }) {
  return (
    <article className="mb-4 break-inside-avoid overflow-hidden rounded-2xl border border-line bg-surface shadow-sm transition-shadow hover:shadow-md">
      <a
        href={link.url}
        target="_blank"
        rel="noopener noreferrer"
        className="block"
      >
        {link.image_url ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img
            src={link.image_url}
            alt={link.title}
            loading="lazy"
            className="w-full object-cover"
            onError={(event) => {
              event.currentTarget.style.display = "none";
            }}
          />
        ) : (
          <div
            className={`flex h-36 w-full items-center justify-center bg-gradient-to-br ${gradientFor(link)} text-4xl font-bold text-ink/30`}
          >
            {(link.site_name ?? hostOf(link.url)).charAt(0).toUpperCase()}
          </div>
        )}

        <div className="px-4 pt-3">
          <h2 className="text-[15px] leading-snug font-semibold text-ink">
            {link.title}
          </h2>

          {link.description && (
            <p className="mt-1 line-clamp-3 text-[13px] leading-relaxed text-muted">
              {link.description}
            </p>
          )}
        </div>
      </a>

      <div className="flex items-center gap-2 px-4 pt-2 pb-3">
        {link.favicon_url && (
          // eslint-disable-next-line @next/next/no-img-element
          <img
            src={link.favicon_url}
            alt=""
            className="h-4 w-4 rounded-sm"
            loading="lazy"
            onError={(event) => {
              event.currentTarget.style.display = "none";
            }}
          />
        )}
        <span className="truncate text-xs text-muted">
          {link.site_name ?? hostOf(link.url)}
        </span>
        <span aria-hidden className="ml-auto text-xs text-muted">
          ↗
        </span>
      </div>

      {link.tags.length > 0 && (
        <div className="flex flex-wrap gap-1 px-4 pb-4">
          {link.tags.map((tag) => (
            <span
              key={tag.id}
              className="rounded-full bg-accent-soft px-2.5 py-0.5 text-[11px] text-accent"
            >
              {tag.name}
            </span>
          ))}
        </div>
      )}
    </article>
  );
}
