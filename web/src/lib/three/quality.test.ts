import { describe, expect, it } from 'vitest';
import { chooseTier } from './quality';

describe('chooseTier', () => {
  it('drops to low on a weak phone', () => {
    expect(chooseTier({ deviceMemory: 2, hardwareConcurrency: 2, dpr: 3 })).toBe('low');
  });
  it('gives a strong desktop high', () => {
    expect(chooseTier({ deviceMemory: 8, hardwareConcurrency: 8, dpr: 2 })).toBe('high');
  });
  it('defaults to medium when the browser hides deviceMemory', () => {
    expect(chooseTier({ hardwareConcurrency: 4, dpr: 2 })).toBe('medium');
  });
});