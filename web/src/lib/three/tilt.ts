/** Max tilt angle in degrees. */
const DEFAULT_MAX = 6;

/** Pure tilt math: returns a `rotateX(...) rotateY(...)` string. */
export function tiltTransform(
  offsetX: number,
  offsetY: number,
  max: number = DEFAULT_MAX,
): string {
  const clamp = (v: number) => Math.max(-1, Math.min(1, v));
  const rx = -Math.round(clamp(offsetY) * max);
  const ry = Math.round(clamp(offsetX) * max);
  return `rotateX(${rx}deg) rotateY(${ry}deg)`;
}