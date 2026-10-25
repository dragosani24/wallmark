export interface Tag {
  id: number;
  name: string;
  links_count?: number;
}

export interface Link {
  id: number;
  url: string;
  title: string;
  description: string | null;
  image_url: string | null;
  favicon_url: string | null;
  site_name: string | null;
  created_at: string | null;
  tags: Tag[];
}

export interface Paginated<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api/v1";

async function parseError(response: Response): Promise<Error> {
  try {
    const body = await response.json();
    const first = body.errors ? Object.values(body.errors)[0] : undefined;
    if (Array.isArray(first) && first.length > 0) {
      return new Error(String(first[0]));
    }
    if (typeof body.message === "string") {
      return new Error(body.message);
    }
  } catch {
    // respuesta sin cuerpo JSON: usamos el estado
  }
  return new Error(`Error del servidor (${response.status})`);
}

export async function fetchLinks(params: {
  q?: string;
  tag?: string;
  page?: number;
}): Promise<Paginated<Link>> {
  const query = new URLSearchParams();
  if (params.q) query.set("q", params.q);
  if (params.tag) query.set("tag", params.tag);
  if (params.page) query.set("page", String(params.page));

  const response = await fetch(`${API_URL}/links?${query.toString()}`);
  if (!response.ok) throw await parseError(response);
  return response.json();
}

export async function fetchTags(q?: string): Promise<Tag[]> {
  const query = new URLSearchParams();
  if (q) query.set("q", q);

  const response = await fetch(`${API_URL}/tags?${query.toString()}`);
  if (!response.ok) throw await parseError(response);
  const body = await response.json();
  return body.data;
}

export async function createLink(url: string, tags: string[]): Promise<Link> {
  const response = await fetch(`${API_URL}/links`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ url, tags }),
  });
  if (!response.ok) throw await parseError(response);
  const body = await response.json();
  return body.data;
}

export function hostOf(url: string): string {
  try {
    return new URL(url).hostname.replace(/^www\./, "");
  } catch {
    return url;
  }
}
