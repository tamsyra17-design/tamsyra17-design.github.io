export const API_URL = (
  process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000'
).replace(/\/+$/, '');

/** Files on the API's public storage disk; absolute URLs pass through. */
export function storageUrl(path: string): string {
  return /^https?:\/\//.test(path) ? path : `${API_URL}/storage/${path.replace(/^\/+/, '')}`;
}

export type QueryParams = Record<
  string,
  string | number | boolean | null | undefined
>;

export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly url: string,
  ) {
    super(`API request failed with ${status}: ${url}`);
    this.name = 'ApiError';
  }
}

/** Build an absolute API URL, dropping empty query params. */
export function apiUrl(path: string, params?: QueryParams): string {
  const url = new URL(`${API_URL}/${path.replace(/^\/+/, '')}`);

  for (const [key, value] of Object.entries(params ?? {})) {
    if (value !== undefined && value !== null && value !== '') {
      url.searchParams.set(key, String(value));
    }
  }

  return url.toString();
}

/** Typed GET against the Laravel API. Always fetches fresh data. */
export async function apiGet<T>(
  path: string,
  params?: QueryParams,
  init?: RequestInit,
): Promise<T> {
  const url = apiUrl(path, params);
  const response = await fetch(url, {
    cache: 'no-store',
    ...init,
    headers: { Accept: 'application/json', ...init?.headers },
  });

  if (!response.ok) {
    throw new ApiError(response.status, url);
  }

  return (await response.json()) as T;
}

/** Typed POST against the Laravel API. */
export async function apiPost<T>(
  path: string,
  body?: unknown,
  init?: RequestInit,
): Promise<T> {
  const url = apiUrl(path);
  const response = await fetch(url, {
    method: 'POST',
    cache: 'no-store',
    ...init,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...init?.headers,
    },
    body: body === undefined ? undefined : JSON.stringify(body),
  });

  if (!response.ok) {
    throw new ApiError(response.status, url);
  }

  return (await response.json()) as T;
}
