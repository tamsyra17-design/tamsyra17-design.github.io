import { describe, expect, it } from 'vitest';
import { tiltTransform } from './tilt';

describe('tiltTransform', () => {
  it('clamps to the maximum angle', () => {
    expect(tiltTransform(1, 1)).toBe('rotateX(-6deg) rotateY(6deg)');
    expect(tiltTransform(0, 0)).toBe('rotateX(0deg) rotateY(0deg)');
  });
});