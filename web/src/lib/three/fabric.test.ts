import { expect, it } from 'vitest';
import { fabricUniforms } from './fabric';

it('reduces detail by tier and disables drape on low', () => {
  expect(fabricUniforms('high')).toEqual({ uSegments: 96, uStrength: 0.08 });
  expect(fabricUniforms('medium')).toEqual({ uSegments: 48, uStrength: 0.05 });
  expect(fabricUniforms('low')).toEqual({ uSegments: 24, uStrength: 0 });
});
