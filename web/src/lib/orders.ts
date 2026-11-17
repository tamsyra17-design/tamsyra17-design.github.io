import { API_URL } from '@/lib/api';
import type { Translations } from '@/lib/types';

/** Oman's 11 governorates. `value` matches the API's shipping zone key. */
export const GOVERNORATES = [
  { value: 'Muscat', name: { en: 'Muscat', ar: 'مسقط' } },
  { value: 'Dhofar', name: { en: 'Dhofar', ar: 'ظفار' } },
  { value: 'Musandam', name: { en: 'Musandam', ar: 'مسندم' } },
  { value: 'Al Buraimi', name: { en: 'Al Buraimi', ar: 'البريمي' } },
  { value: 'Ad Dakhiliyah', name: { en: 'Ad Dakhiliyah', ar: 'الداخلية' } },
  { value: 'Al Batinah North', name: { en: 'Al Batinah North', ar: 'شمال الباطنة' } },
  { value: 'Al Batinah South', name: { en: 'Al Batinah South', ar: 'جنوب الباطنة' } },
  { value: 'Ash Sharqiyah North', name: { en: 'Ash Sharqiyah North', ar: 'شمال الشرقية' } },
  { value: 'Ash Sharqiyah South', name: { en: 'Ash Sharqiyah South', ar: 'جنوب الشرقية' } },
  { value: 'Ad Dhahirah', name: { en: 'Ad Dhahirah', ar: 'الظاهرة' } },
  { value: 'Al Wusta', name: { en: 'Al Wusta', ar: 'الوسطى' } },
] as const;

export type PaymentMethod = 'cod' | 'bank_transfer';

export const PAYMENT_METHODS: readonly PaymentMethod[] = ['cod', 'bank_transfer'];

export function isPaymentMethod(value: unknown): value is PaymentMethod {
  return PAYMENT_METHODS.includes(value as PaymentMethod);
}

export interface OrderItem {
  variant_id: number | null;
  name: { product: Translations; variant: Translations | null };
  sku: string;
  unit_price_baisa: number;
  qty: number;
  line_total_baisa: number;
}

export interface BankInstructions {
  bank_name?: string | null;
  account_name?: string | null;
  iban?: string | null;
  reference?: string | null;
  amount_baisa?: number | null;
  [key: string]: unknown;
}

/** `OrderResource` from the API. */
export interface Order {
  number: string;
  status: string;
  payment_status: string;
  payment_method: string;
  customer: { name: string; phone: string; email: string | null };
  address: {
    governorate: string;
    wilayat: string;
    line1: string;
    line2: string | null;
    postal_code: string | null;
  };
  subtotal_baisa: number;
  shipping_baisa: number;
  total_baisa: number;
  placed_at: string | null;
  items: OrderItem[];
  payment: {
    method: string;
    status: string;
    instructions: BankInstructions | null;
  } | null;
}

export interface OrderPayload {
  customer: { name: string; phone: string; email?: string };
  address: { governorate: string; wilayat: string; line1: string };
  items: { variant_id: number; qty: number }[];
  payment_method: PaymentMethod;
}

/** Thrown for a 422: field errors keyed by API path (`address.governorate`). */
export class OrderValidationError extends Error {
  constructor(public readonly errors: Record<string, string[]>) {
    super('Order validation failed');
    this.name = 'OrderValidationError';
  }
}

export async function placeOrder(payload: OrderPayload): Promise<Order> {
  const response = await fetch(`${API_URL}/api/v1/orders`, {
    method: 'POST',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });

  if (response.status === 422) {
    const body = (await response.json()) as { errors?: Record<string, string[]> };
    throw new OrderValidationError(body.errors ?? {});
  }

  if (!response.ok) {
    throw new Error(`Order request failed with ${response.status}`);
  }

  return ((await response.json()) as { data: Order }).data;
}

/** Guest lookup. Resolves `null` when the number/phone pair does not match. */
export async function lookupOrder(number: string, phone: string): Promise<Order | null> {
  const url = new URL(`${API_URL}/api/v1/orders/${encodeURIComponent(number)}`);
  url.searchParams.set('phone', phone);

  const response = await fetch(url, {
    cache: 'no-store',
    headers: { Accept: 'application/json' },
  });

  if (response.status === 404) {
    return null;
  }

  if (!response.ok) {
    throw new Error(`Order lookup failed with ${response.status}`);
  }

  return ((await response.json()) as { data: Order }).data;
}

/*
 * The order page needs the phone to read an order, but the phone must not go
 * in the URL (history, logs, referrers). After checkout or a successful
 * lookup the order is handed to the page through sessionStorage instead.
 */
const storageKey = (number: string) => `omani-order:${number}`;

export function rememberOrder(order: Order): void {
  try {
    sessionStorage.setItem(storageKey(order.number), JSON.stringify(order));
  } catch {
    // Storage full or disabled: the page falls back to the lookup form.
  }
}

export function recallOrder(number: string): Order | null {
  try {
    const raw = sessionStorage.getItem(storageKey(number));
    return raw ? (JSON.parse(raw) as Order) : null;
  } catch {
    return null;
  }
}
