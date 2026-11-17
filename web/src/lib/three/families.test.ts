import { describe, expect, it } from 'vitest';
import { familyFor } from './families';

describe('familyFor', () => {
  it('maps heritage categories to three families', () => {
    expect(familyFor('khanjar')).toBe('metal');
    expect(familyFor('musar')).toBe('woven');
    expect(familyFor('bisht')).toBe('layered');
    expect(familyFor('unknown-thing')).toBe('layered');
  });
});