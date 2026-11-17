import { z } from 'zod';
import { GOVERNORATES, PAYMENT_METHODS } from '@/lib/orders';

type Translate = (key: string) => string;

const governorates = GOVERNORATES.map((item) => item.value) as [string, ...string[]];

/** Client-side mirror of the API's StoreOrderRequest shape rules. */
export function checkoutSchema(t: Translate) {
  const required = { error: t('errors.required') };

  return z.object({
    name: z.string().trim().min(1, required).max(120),
    phone: z
      .string()
      .trim()
      .regex(/^\+?[0-9 ]{7,20}$/, { error: t('errors.phone') }),
    email: z.union([z.literal(''), z.email({ error: t('errors.email') }).max(255)]),
    governorate: z.enum(governorates, { error: t('errors.governorate') }),
    wilayat: z.string().trim().min(1, required).max(64),
    line1: z.string().trim().min(1, required).max(255),
    paymentMethod: z.enum(PAYMENT_METHODS as [string, ...string[]]),
  });
}

export type CheckoutValues = z.infer<ReturnType<typeof checkoutSchema>>;
